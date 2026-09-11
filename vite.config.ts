/// <reference types="vitest/config" />
import { fileURLToPath, URL } from 'node:url';
import { readFileSync } from 'node:fs';
import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import AutoImport from 'unplugin-auto-import/vite';
import Components from 'unplugin-vue-components/vite';

// https://vitejs.dev/config/
import path from 'node:path';
import { storybookTest } from '@storybook/addon-vitest/vitest-plugin';
import { playwright } from '@vitest/browser-playwright';
const dirname = typeof __dirname !== 'undefined' ? __dirname : path.dirname(fileURLToPath(import.meta.url));

// ── 入口契约（分流唯一真源：contracts/entry.json）────────────────────────
// 改分流规则请改契约文件，不要在下面手写 proxy。生产 nginx 由同一份契约生成：
//   node scripts/gen-entry.mjs
interface EntryUpstream {
  desc?: string;
  dev: string;
  prod: string;
  env?: string;
  proxyHostHeader?: string;
}
interface EntryContract {
  version: number;
  dev: { port: number; host: string };
  upstreams: Record<string, EntryUpstream>;
  routes: Array<{ prefix: string; upstream: string; desc?: string }>;
}

const entry: EntryContract = JSON.parse(
  readFileSync(path.resolve(process.cwd(), 'contracts/entry.json'), 'utf-8')
);

/**
 * 契约 → vite proxy。
 * key 的顺序即匹配优先级：/v1/menus|pages|permissions 必须排在兜底 /v1 之前，
 * 次序由 contracts/entry.json 的 routes 数组保证（生成器有校验）。
 */
function buildProxy(): Record<string, { target: string; changeOrigin: boolean }> {
  const proxy: Record<string, { target: string; changeOrigin: boolean }> = {};
  for (const route of entry.routes) {
    const up = entry.upstreams[route.upstream];
    if (!up) {
      throw new Error(`[entry] route ${route.prefix} 指向未定义的上游: ${route.upstream}`);
    }
    proxy[route.prefix] = {
      target: (up.env && process.env[up.env]) || up.dev,
      changeOrigin: true,
    };
  }
  return proxy;
}

// More info at: https://storybook.js.org/docs/next/writing-tests/integrations/vitest-addon
export default defineConfig({
  base: process.env.VITE_BASE_PATH || '/',
  plugins: [vue(),
  // 自动导入 Vue 相关函数
  AutoImport({
    imports: ['vue', 'vue-router', 'pinia'],
    dts: 'src/auto-imports.d.ts'
  }),
  // 自动导入组件
  Components({
    dts: 'src/components.d.ts'
  })],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    port: entry.dev.port,
    host: entry.dev.host,
    open: true,
    proxy: buildProxy()
  },
  build: {
    outDir: 'dist',
    assetsDir: 'assets',
    sourcemap: false,
    minify: 'esbuild',
    chunkSizeWarningLimit: 1000
  },
  test: {
    projects: [{
      extends: true,
      plugins: [
      // The plugin will run tests for the stories defined in your Storybook config
      // See https://storybook.js.org/docs/next/writing-tests/integrations/vitest-addon#storybooktest
      storybookTest({
        configDir: path.join(dirname, '.storybook')
      })],
      test: {
        name: 'storybook',
        browser: {
          enabled: true,
          headless: true,
          provider: playwright({}),
          instances: [{
            browser: 'chromium'
          }]
        }
      }
    }]
  }
});
