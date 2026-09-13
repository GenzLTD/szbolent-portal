<?php
/**
 * Template Name: 深伯乐 首页
 * HarmonyOS 生态服务商品牌首页 v3.0 — 全内容 Customizer 可编辑
 * 「外观 → 自定义 → 深伯乐 首页内容」即可修改所有文案
 */
if (!defined('ABSPATH')) exit;
get_header();
?>

<!-- ═══════════ Hero — HarmonyOS 生态服务商 ═══════════ -->
<section class="bolent-hero">
    <div class="bolent-hero-grid"></div>
    <div class="bolent-hero-content">
        <div class="bolent-hero-badges">
            <span class="bolent-hero-badge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <?php echo esc_html(bolent_mod('hero_badge_1', 'HarmonyOS 开发者')); ?>
            </span>
            <span class="bolent-hero-badge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                <?php echo esc_html(bolent_mod('hero_badge_2', '鸿蒙生态合作伙伴')); ?>
            </span>
            <span class="bolent-hero-badge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
                <?php echo esc_html(bolent_mod('hero_badge_3', 'OpenHarmony 贡献者')); ?>
            </span>
        </div>

        <h1><?php echo esc_html(bolent_mod('hero_title_line1', 'HarmonyOS')); ?> <span class="bolent-hero-highlight"><?php echo esc_html(bolent_mod('hero_title_highlight', '生态服务商')); ?></span></h1>
        <p class="bolent-hero-subtitle"><?php echo esc_html(bolent_mod('hero_subtitle', '智能终端 · 应用开发 · 解决方案')); ?></p>
        <p class="bolent-hero-desc"><?php echo esc_html(bolent_mod('hero_desc', '覆盖从芯片适配到应用分发的 HarmonyOS 全生态链路，为企业打造端到端的鸿蒙化解决方案')); ?></p>

        <div class="bolent-hero-actions">
            <a href="<?php echo esc_url(home_url('/case-study')); ?>" class="bolent-btn-harmony">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                <?php echo esc_html(bolent_mod('hero_btn_primary', '查看案例')); ?>
            </a>
            <a href="<?php echo esc_url(home_url('/contact')); ?>" class="bolent-btn-outline-light">
                <?php echo esc_html(bolent_mod('hero_btn_secondary', '联系我们')); ?>
            </a>
        </div>
    </div>
</section>

<!-- ═══════════ 鸿蒙全栈能力 ═══════════ -->
<section class="bolent-harmony-section">
    <div class="bolent-container">
        <div class="bolent-section-header">
            <span class="bolent-section-eyebrow"><?php echo esc_html(bolent_mod('harmony_eyebrow', 'HARMONYOS ECOSYSTEM')); ?></span>
            <h2 class="bolent-section-title"><?php echo esc_html(bolent_mod('harmony_title', '鸿蒙全栈能力')); ?></h2>
            <p class="bolent-section-desc"><?php echo esc_html(bolent_mod('harmony_desc', '覆盖从芯片适配到应用分发的 HarmonyOS 全生态链路，打造端到端的鸿蒙化解决方案')); ?></p>
        </div>
        <div class="bolent-harmony-grid">
            <?php for ($i = 1; $i <= 6; $i++) : ?>
                <div class="bolent-harmony-card">
                    <div class="bolent-harmony-card-icon">
                        <?php echo bolent_get_svg_icon(bolent_mod("harmony_icon_{$i}", '')); ?>
                    </div>
                    <h3 class="bolent-harmony-card-title"><?php echo esc_html(bolent_mod("harmony_title_{$i}", '')); ?></h3>
                    <p class="bolent-harmony-card-desc"><?php echo esc_html(bolent_mod("harmony_desc_{$i}", '')); ?></p>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- ═══════════ 核心服务 ═══════════ -->
<section class="bolent-section">
    <div class="bolent-container">
        <div class="bolent-section-header">
            <span class="bolent-section-eyebrow"><?php echo esc_html(bolent_mod('services_eyebrow', 'SERVICES')); ?></span>
            <h2 class="bolent-section-title"><?php echo esc_html(bolent_mod('services_title', '核心服务')); ?></h2>
            <p class="bolent-section-desc"><?php echo esc_html(bolent_mod('services_desc', '全方位 IT 服务，以工程精度交付每一个项目')); ?></p>
        </div>
        <div class="bolent-service-grid">
            <?php
            $services = new WP_Query(array(
                'post_type' => 'bolent_service',
                'posts_per_page' => 6,
                'orderby' => 'menu_order',
                'order' => 'ASC',
            ));

            if ($services->have_posts()) :
                $service_icons = array('database', 'code', 'bot', 'settings', 'rocket', 'globe');
                $service_gradients = array(
                    'linear-gradient(135deg, #1A73E8, #4A90E2)',
                    'linear-gradient(135deg, #6B4EFF, #9B85FF)',
                    'linear-gradient(135deg, #0E6E6A, #1A8F8A)',
                    'linear-gradient(135deg, #2E8B57, #4CAF50)',
                    'linear-gradient(135deg, #FF9500, #FFB347)',
                    'linear-gradient(135deg, #E0932B, #F5B14A)',
                );
                $idx = 0;
                while ($services->have_posts()) : $services->the_post();
                    $desc = get_post_meta(get_the_ID(), '_service_short_desc', true) ?: get_the_excerpt();
                    $icon_name = $service_icons[$idx % count($service_icons)];
                    $gradient = $service_gradients[$idx % count($service_gradients)];
            ?>
                    <div class="bolent-service-card">
                        <div class="bolent-service-icon" style="background: <?php echo esc_attr($gradient); ?>;">
                            <?php echo bolent_get_svg_icon($icon_name, '#fff'); ?>
                        </div>
                        <h3 style="font-size:1.2rem;font-weight:700;margin-bottom:12px;color:var(--bolent-ink);"><?php the_title(); ?></h3>
                        <p style="font-size:0.95rem;color:var(--bolent-text-secondary);margin-bottom:20px;line-height:1.65;"><?php echo esc_html($desc); ?></p>
                        <a href="<?php the_permalink(); ?>" style="font-size:14px;font-weight:600;color:var(--bolent-primary);display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                            了解更多
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
            <?php
                    $idx++;
                endwhile;
                wp_reset_postdata();
            else :
                // 无 CPT 数据时显示默认服务
                $default_services = array(
                    array('icon' => 'database', 'title' => '数字化 & 数据', 'desc' => '数据采集、治理与分析平台搭建，让数据成为您的核心资产', 'gradient' => 'linear-gradient(135deg, #1A73E8, #4A90E2)'),
                    array('icon' => 'code', 'title' => '软件开发', 'desc' => '从需求到交付的全周期应用开发，前后端、移动端全覆盖', 'gradient' => 'linear-gradient(135deg, #6B4EFF, #9B85FF)'),
                    array('icon' => 'bot', 'title' => '自动化 & QA', 'desc' => '测试自动化框架搭建、CI/CD 流水线建设，提升交付质量与效率', 'gradient' => 'linear-gradient(135deg, #0E6E6A, #1A8F8A)'),
                    array('icon' => 'settings', 'title' => 'IT 管理', 'desc' => '基础设施运维、监控告警、成本优化，让 IT 资产可管可控', 'gradient' => 'linear-gradient(135deg, #2E8B57, #4CAF50)'),
                    array('icon' => 'rocket', 'title' => '敏捷咨询', 'desc' => 'Scrum/Kanban 落地辅导、工程效能诊断，帮团队跑得更快', 'gradient' => 'linear-gradient(135deg, #FF9500, #FFB347)'),
                    array('icon' => 'globe', 'title' => 'IT 外包', 'desc' => '按需组建技术团队，灵活扩展产能，专注核心业务增长', 'gradient' => 'linear-gradient(135deg, #E0932B, #F5B14A)'),
                );
                foreach ($default_services as $svc) :
            ?>
                    <div class="bolent-service-card">
                        <div class="bolent-service-icon" style="background: <?php echo esc_attr($svc['gradient']); ?>;">
                            <?php echo bolent_get_svg_icon($svc['icon'], '#fff'); ?>
                        </div>
                        <h3 style="font-size:1.2rem;font-weight:700;margin-bottom:12px;color:var(--bolent-ink);"><?php echo esc_html($svc['title']); ?></h3>
                        <p style="font-size:0.95rem;color:var(--bolent-text-secondary);margin-bottom:20px;line-height:1.65;"><?php echo esc_html($svc['desc']); ?></p>
                        <a href="<?php echo esc_url(home_url('/services')); ?>" style="font-size:14px;font-weight:600;color:var(--bolent-primary);display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                            了解更多
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>
    </div>
</section>

<!-- ═══════════ 精选案例 ═══════════ -->
<section class="bolent-section" style="background: var(--bolent-bg-soft);">
    <div class="bolent-container">
        <div class="bolent-section-header">
            <span class="bolent-section-eyebrow"><?php echo esc_html(bolent_mod('cases_eyebrow', 'CASE STUDIES')); ?></span>
            <h2 class="bolent-section-title"><?php echo esc_html(bolent_mod('cases_title', '精选案例')); ?></h2>
            <p class="bolent-section-desc"><?php echo esc_html(bolent_mod('cases_desc', '以结果说话，这是我们交付价值的证明')); ?></p>
        </div>
        <div class="bolent-cases-grid">
            <?php
            $cases = new WP_Query(array(
                'post_type' => 'bolent_case',
                'posts_per_page' => 3,
                'orderby' => 'date',
                'order' => 'DESC',
            ));

            if ($cases->have_posts()) :
                while ($cases->have_posts()) : $cases->the_post();
                    $client = get_post_meta(get_the_ID(), '_case_client', true);
                    $tags = get_post_meta(get_the_ID(), '_case_tags', true);
                    $m1 = get_post_meta(get_the_ID(), '_case_metric_1', true);
                    $m2 = get_post_meta(get_the_ID(), '_case_metric_2', true);
                    $m3 = get_post_meta(get_the_ID(), '_case_metric_3', true);
            ?>
                    <div class="bolent-case-card">
                        <div class="bolent-case-visual">
                            <?php echo bolent_get_svg_icon('layers'); ?>
                            <?php if ($tags) : ?><span class="bolent-case-tag"><?php echo esc_html($tags); ?></span><?php endif; ?>
                        </div>
                        <div class="bolent-case-body">
                            <h3><?php the_title(); ?></h3>
                            <p><?php echo esc_html(get_the_excerpt()); ?></p>
                            <?php if ($m1 || $m2 || $m3) : ?>
                            <div class="bolent-case-metrics">
                                <?php if ($m1) : ?><div class="bolent-metric"><span class="bolent-metric-value"><?php echo esc_html($m1); ?></span><span class="bolent-metric-label">指标一</span></div><?php endif; ?>
                                <?php if ($m2) : ?><div class="bolent-metric"><span class="bolent-metric-value"><?php echo esc_html($m2); ?></span><span class="bolent-metric-label">指标二</span></div><?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
                $default_cases = array(
                    array('icon' => 'smartphone', 'tag' => '鸿蒙应用', 'title' => '智能诗词 App — HarmonyOS 原生版', 'desc' => '基于 ArkUI 重构诗词鉴赏应用，集成 AI 语音朗读与卡片流转，已上架 AppGallery', 'm1' => '4.8★', 'l1' => '应用评分', 'm2' => '50K+', 'l2' => '下载量'),
                    array('icon' => 'cloud', 'tag' => '数字化', 'title' => '某制造业工厂数字化平台', 'desc' => '端到端数据采集与可视化系统，实现产线实时监控与异常预警，良品率提升 12%', 'm1' => '12%', 'l1' => '良品率提升', 'm2' => '30%', 'l2' => '运维效率提升'),
                    array('icon' => 'shopping', 'tag' => '电商', 'title' => '跨境电商全链路平台', 'desc' => '从选品到履约的完整电商系统，多语言、多币种、多仓储，月 GMV 破百万', 'm1' => '1M+', 'l1' => '月 GMV', 'm2' => '99.9%', 'l2' => '系统可用性'),
                );
                foreach ($default_cases as $case) :
            ?>
                    <div class="bolent-case-card">
                        <div class="bolent-case-visual">
                            <?php echo bolent_get_svg_icon($case['icon']); ?>
                            <span class="bolent-case-tag"><?php echo esc_html($case['tag']); ?></span>
                        </div>
                        <div class="bolent-case-body">
                            <h3><?php echo esc_html($case['title']); ?></h3>
                            <p><?php echo esc_html($case['desc']); ?></p>
                            <div class="bolent-case-metrics">
                                <div class="bolent-metric"><span class="bolent-metric-value"><?php echo esc_html($case['m1']); ?></span><span class="bolent-metric-label"><?php echo esc_html($case['l1']); ?></span></div>
                                <div class="bolent-metric"><span class="bolent-metric-value"><?php echo esc_html($case['m2']); ?></span><span class="bolent-metric-label"><?php echo esc_html($case['l2']); ?></span></div>
                            </div>
                        </div>
                    </div>
            <?php
                endforeach;
            endif;
            ?>
        </div>
    </div>
</section>

<!-- ═══════════ 为什么选择我们 ═══════════ -->
<section class="bolent-section">
    <div class="bolent-container">
        <div class="bolent-section-header">
            <span class="bolent-section-eyebrow"><?php echo esc_html(bolent_mod('whyus_eyebrow', 'WHY BOLENT')); ?></span>
            <h2 class="bolent-section-title"><?php echo esc_html(bolent_mod('whyus_title', '为什么选择我们')); ?></h2>
            <p class="bolent-section-desc"><?php echo esc_html(bolent_mod('whyus_desc', '不只是外包，我们是您值得信赖的技术合伙人')); ?></p>
        </div>
        <div class="bolent-features-grid">
            <?php for ($i = 1; $i <= 4; $i++) : ?>
                <div class="bolent-feature-card">
                    <div class="bolent-feature-icon">
                        <?php echo bolent_get_svg_icon(bolent_mod("whyus_icon_{$i}", '')); ?>
                    </div>
                    <h4><?php echo esc_html(bolent_mod("whyus_title_{$i}", '')); ?></h4>
                    <p><?php echo esc_html(bolent_mod("whyus_desc_{$i}", '')); ?></p>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<!-- ═══════════ CTA ═══════════ -->
<section class="bolent-cta">
    <div class="bolent-cta-content">
        <h2><?php echo esc_html(bolent_mod('cta_title', '准备好开启鸿蒙化之旅了吗？')); ?></h2>
        <p><?php echo esc_html(bolent_mod('cta_desc', '无论是应用迁移、硬件适配还是生态共建，我们与您并肩前行')); ?></p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="bolent-btn-harmony">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <?php echo esc_html(bolent_mod('cta_btn', '立即咨询')); ?>
        </a>
    </div>
</section>

<?php get_footer(); ?>
