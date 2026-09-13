/**
 * 深伯乐 门户品牌配置 v2.4
 *
 * 双域名架构：`.cn`（个人备案 · 内容站）+ `.com.cn`（企业备案 · 门户/API）
 * 门户域名：`https://www.szbolent.com.cn` —— **企业备案**
 *   备案号 `粤ICP备2026134984号-1` · 主体 深伯乐（深圳）科技有限公司
 *   生产主机 `14.29.216.219`（天翼云）· 门户与 API 同机（nginx `/v1/` 反代本机 Looma `:5203`）
 *   ⚠️ 备案云资源原为 `1.14.202.161(gz)`（腾讯云）；改迁天翼后须在**天翼云**办「新增接入备案」
 *   ⚠️ 遗留线（端口口径不同，勿混用）：`www.szbolent.cn` 个人备案 WP（阿里云 `47.115.168.107`）
 *     + `api.genz.ltd`（腾讯云 gz，Looma 宿主 `:5200`）
 *
 * 天翼站点 nginx 见 `nginx-tianyi-portal.conf`；待执行项见 `docs/SZBOLENT_COM_CN_CUTOVER_CHECKLIST.md`
 */

/**
 * 公安联网备案（页脚法定展示项）
 *
 * 办理入口：https://beian.mps.gov.cn
 * 时限：网站开通之日起 30 日内完成公安联网备案；
 *       备案成功后 30 日内完成备案号悬挂（本条才是页脚要填的）。
 * 主体须与 ICP 备案一致（深伯乐（深圳）科技有限公司）。
 *
 * ⚠️ 术语区分（易混，勿搞错）：
 *   · 「公安联网备案数据码」= ICP 接入商（原腾讯云 → 现天翼云）下发的 30 日有效令牌，
 *     用于在公安部平台「绑定」以自动带入备案信息。它不进页脚、不进本文件。
 *   · 「公安网安备案号」= 公安平台审核通过后下发的备案号，
 *     形如「粤公网安备 44030502001234号」。**这个才是页脚展示项**。
 *
 * 本配置只填备案号中的数字部分，展示文案与查询链接自动生成。
 * 取值路径：beian.mps.gov.cn 登录 → 我的网站 → 网站详情 → 复制备案号
 */
export const gonganInfo = {
  /** 【唯一需填】公安网安备案号的数字部分，如 '44030502001234'；未下发时留空 */
  code: '',

  /** 备案号前缀（属地简称）：广东/深圳为「粤公网安备」 */
  prefix: '粤公网安备',

  /** 备案号后缀 */
  suffix: '号',

  /** 官方查询链接模板，{code} 由数据码替换（勿改域名，网警核查即点此链接） */
  queryUrl: 'https://beian.mps.gov.cn/#/query/webSearch?code={code}',

  /** 公安备案图标（平台可下载）；放入 public/images/ 后填如 '/images/gongan.png'，留空则不显示图标 */
  icon: '',
}

/** 展示用备案号，code 为空串（避免未下发期在页脚渲染死链） */
export const gonganLabel = gonganInfo.code
  ? `${gonganInfo.prefix}${gonganInfo.code}${gonganInfo.suffix}`
  : ''

/** 官方查询链接，code 为空串 */
export const gonganUrl = gonganInfo.code
  ? gonganInfo.queryUrl.replace('{code}', gonganInfo.code)
  : ''

/**
 * 人力资源服务备案（业务资质）
 *
 * ⚠️ 这不是网站备案项，**不进页脚**。ICP 备案号是网站法定展示项；
 *    人力资源备案是经营资质，按《人力资源市场条例》在**涉及人服业务的页面显著位置**公示。
 *
 * ⚠️ 消费方待建：当前站内尚无「人服业务」独立页面（服务页仅到 IT 外包/咨询/自动化等），
 *    故本配置暂无组件引用。开设人服业务页时，须在该页显著位置引用本对象。
 *
 * 凭证：《人力资源服务备案凭证》·（粤）人服备字〔2024〕第0307020923号
 * 备案主体：深伯乐（深圳）科技有限公司（与 ICP 备案主体一致）
 *
 * ⚠️ 边界（勿越界宣传，超范围经营会被处罚）：
 *   · 本凭证是「备案」而非「许可」，范围为下列四项
 *   · 不含「网络招聘」（发布职位/接收简历）→ 需另办《人力资源服务许可证》
 *   · 不含「劳务派遣」→ 需另办《劳务派遣经营许可证》
 */
export const hrFiling = {
  /** 备案文号（完整展示用） */
  code: '（粤）人服备字〔2024〕第0307020923号',

  /** 备案编号数字段，便于检索与系统校验 */
  serial: '0307020923',

  /** 备案主体（须与合法主体全称一致） */
  entity: '深伯乐（深圳）科技有限公司',

  /** 统一社会信用代码（备案「三一致」核对用） */
  uscc: '91440300MA5H3GJL4E',

  /** 备案业务范围 —— 宣传口径不得超出此范围 */
  scope: [
    '人力资源管理咨询',
    '人力资源测评',
    '人力资源培训',
    '承接人力资源服务外包',
  ],

  /** 官方查询渠道 */
  source: '广东省人力资源市场管理服务信息系统',
} as const

export const companyInfo = {
  name: '深伯乐',
  fullName: '深伯乐（深圳）科技有限公司',
  chineseName: '数智企业门户',

  /**
   * 法律主体全称 —— 单一真源（勿在组件里硬编码）
   * 必须与 ICP 备案主体、微信支付商户主体保持完全一致（备案「三一致」）
   * 引用处：Footer.vue、Terms.vue
   */
  legalName: '深伯乐（深圳）科技有限公司',

  /**
   * ICP 备案号 —— 单一真源（勿在组件里硬编码）
   * 备案：粤ICP备2026134984号-1 · 域名 szbolent.com.cn · 生产主机 14.29.216.219（天翼云，需办新增接入）
   * 查询：https://beian.miit.gov.cn（Footer.vue 已挂官方链接）
   */
  icp: '粤ICP备2026134984号-1',

  /**
   * 公安联网备案 —— 页脚法定展示项（配置见本文件顶部 gonganInfo）
   * 引用处：Footer.vue
   */
  gongan: {
    ...gonganInfo,
    label: gonganLabel,
    url: gonganUrl,
  },

  tagline: '以工程精度交付价值，以人文视角连接未来',
  slogan: '深伯乐 — 融合科技与人文的数智企业',

  description:
    '深伯乐 是一家融合现代科技与文化底蕴的数智企业。我们提供软件开发、数字化、自动化、IT 管理与 IT 外包等全方位服务，并以 AI 读诗为特色板块，让技术在诗意中落地。',

  contact: {
    email: 'hello@szbolent.com.cn',
    support: 'support@szbolent.com.cn',
    workTime: '周一至周五 9:00–18:00',
  },

  address: {
    main: {
      city: '中国',
      full: 'www.szbolent.com.cn',
      postcode: '',
    },
  },

  social: {
    wechat: {
      name: '深伯乐',
      qrcode: '/images/qrcode/wechat.jpg',
    },
    github: {
      name: 'szbolent',
      url: 'https://github.com/szbolent',
    },
  },

  stats: {
    foundedYear: 2026,
  },

  values: [
    {
      icon: '◆',
      title: '领域专业',
      description: '深厚的行业经验与专业技能积累',
    },
    {
      icon: '▲',
      title: '卓越品质',
      description: '工程级精度，交付高质量解决方案',
    },
    {
      icon: '●',
      title: '技术前沿',
      description: '采用最新技术与行业最佳实践',
    },
    {
      icon: '✦',
      title: '人文视角',
      description: '以文化底蕴驱动有温度的产品体验',
    },
  ],

  /** 生态资质（首页徽章 / 关于页背书共用） */
  certifications: [
    'HarmonyOS 开发者',
    '鸿蒙生态合作伙伴',
    'OpenHarmony 贡献者',
    '昇腾 ISV 认证服务商',
  ],

  partners: [
    { name: 'HarmonyOS', logo: '' },
    { name: 'OpenHarmony', logo: '' },
    { name: '昇腾', logo: '' },
  ] as Array<{ name: string; logo: string }>,
}

export const seoConfig = {
  defaultTitle: '深伯乐 — 数智企业门户',
  titleTemplate: '%s | 深伯乐',
  defaultDescription:
    '深伯乐 是融合现代科技与文化底蕴的数智企业，提供软件开发、数字化、IT管理及AI读诗等全方位服务；HarmonyOS 生态与昇腾 ISV 认证服务商。',
  keywords: [
    '深伯乐',
    '数智企业',
    '软件开发',
    'AI读诗',
    '数字化转型',
    'IT服务',
    '企业门户',
    'HarmonyOS',
    '昇腾 ISV',
    '昇腾认证服务商',
  ],
  /** canonical / OG 口径：企业域 canonical 收敛到 www（与 nginx-tianyi-portal.conf 的 301 一致） */
  siteUrl: 'https://www.szbolent.com.cn',
  ogImage: '/images/og-image.jpg',
}

export const pricingPlans = [
  {
    id: 'free',
    name: '免费版',
    price: 0,
    period: '永久',
    features: ['每日 10 次诗词搜索', '基础诗人浏览', '诗词收藏'],
    cta: '免费开始',
    highlighted: false,
  },
  {
    id: 'supporter',
    name: '支持者',
    price: 29,
    period: '月',
    features: ['无限诗词搜索', 'AI 诗词问答', '深度诗人分析', '去广告'],
    cta: '立即订阅',
    highlighted: true,
  },
  {
    id: 'pro',
    name: '专业版',
    price: 99,
    period: '月',
    features: ['支持者全部功能', 'API 调用额度 x10', '企业级 RAG 知识库', '优先客服支持'],
    cta: '升级专业版',
    highlighted: false,
  },
]

export default {
  company: companyInfo,
  seo: seoConfig,
  pricing: pricingPlans,
}
