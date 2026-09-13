<?php
/**
 * Template Name: 深伯乐 服务列表
 * 读取 bolent_service CPT 渲染所有服务
 */
if (!defined('ABSPATH')) exit;
get_header();
?>

<!-- Hero -->
<section class="bolent-hero" style="min-height:50vh;">
    <div class="bolent-hero-grid"></div>
    <div class="bolent-hero-content">
        <h1><?php echo esc_html(bolent_mod('page_services_title1', '我们的')); ?><span class="bolent-hero-highlight"><?php echo esc_html(bolent_mod('page_services_highlight', '服务')); ?></span></h1>
        <p class="bolent-hero-subtitle"><?php echo esc_html(bolent_mod('page_services_subtitle', '全方位 IT 服务 · 鸿蒙全栈能力')); ?></p>
        <p class="bolent-hero-desc"><?php echo esc_html(bolent_mod('page_services_desc', '以工程精度交付每一个项目，从鸿蒙生态到企业数字化')); ?></p>
    </div>
</section>

<!-- 鸿蒙全栈能力 -->
<section class="bolent-harmony-section">
    <div class="bolent-container">
        <div class="bolent-section-header">
            <span class="bolent-section-eyebrow">HARMONYOS ECOSYSTEM</span>
            <h2 class="bolent-section-title">鸿蒙全栈能力</h2>
            <p class="bolent-section-desc">覆盖从芯片适配到应用分发的 HarmonyOS 全生态链路</p>
        </div>
        <div class="bolent-harmony-grid">
            <?php
            $harmony_caps = array(
                array('icon' => 'smartphone', 'title' => '鸿蒙应用开发', 'desc' => '基于 ArkUI + ArkTS 的原生应用开发，覆盖手机、平板、车机等多设备形态'),
                array('icon' => 'cpu', 'title' => '硬件适配 & 驱动', 'desc' => '芯片平台适配、HDF 驱动开发、外设接入，让硬件轻松融入鸿蒙生态'),
                array('icon' => 'atom', 'title' => '原子化服务', 'desc' => '免安装、即用即走的轻量化服务，多端流转，提升用户体验与留存'),
                array('icon' => 'package', 'title' => '鸿蒙分发 & 上架', 'desc' => '全流程指引 AppGallery Connect 上架、测试分发、合规审核'),
                array('icon' => 'gitmerge', 'title' => 'OpenHarmony 共建', 'desc' => '开源社区贡献、SIG 组参与、行业发行版定制，推动生态共建'),
                array('icon' => 'layers', 'title' => '跨端迁移方案', 'desc' => '从 Android/iOS 到 HarmonyOS 的应用迁移，降低迁移成本与风险'),
            );
            foreach ($harmony_caps as $cap) :
            ?>
                <div class="bolent-harmony-card">
                    <div class="bolent-harmony-card-icon"><?php echo bolent_get_svg_icon($cap['icon']); ?></div>
                    <h3 class="bolent-harmony-card-title"><?php echo esc_html($cap['title']); ?></h3>
                    <p class="bolent-harmony-card-desc"><?php echo esc_html($cap['desc']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 核心服务列表 -->
<section class="bolent-section">
    <div class="bolent-container">
        <div class="bolent-section-header">
            <span class="bolent-section-eyebrow">CORE SERVICES</span>
            <h2 class="bolent-section-title">核心服务</h2>
            <p class="bolent-section-desc">全方位 IT 服务，以工程精度交付每一个项目</p>
        </div>
        <div class="bolent-service-grid">
            <?php
            $services = new WP_Query(array(
                'post_type' => 'bolent_service',
                'posts_per_page' => -1,
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
                    $features = get_post_meta(get_the_ID(), '_service_features', true);
            ?>
                    <div class="bolent-service-card">
                        <div class="bolent-service-icon" style="background:<?php echo esc_attr($service_gradients[$idx % count($service_gradients)]); ?>;">
                            <?php echo bolent_get_svg_icon($service_icons[$idx % count($service_icons)], '#fff'); ?>
                        </div>
                        <h3 style="font-size:1.2rem;font-weight:700;margin-bottom:12px;color:var(--bolent-ink);"><?php the_title(); ?></h3>
                        <p style="font-size:0.95rem;color:var(--bolent-text-secondary);margin-bottom:20px;line-height:1.65;"><?php echo esc_html($desc); ?></p>
                        <?php if ($features) : $feat_list = explode(',', $features); ?>
                            <ul style="list-style:none;margin-bottom:20px;">
                                <?php foreach ($feat_list as $feat) : ?>
                                    <li style="font-size:14px;color:var(--bolent-text-secondary);padding:4px 0 4px 24px;position:relative;">
                                        <span style="position:absolute;left:0;color:var(--bolent-primary);">✓</span>
                                        <?php echo esc_html(trim($feat)); ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                        <a href="<?php the_permalink(); ?>" style="font-size:14px;font-weight:600;color:var(--bolent-primary);display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                            了解更多 <?php echo bolent_get_svg_icon('arrow-right'); ?>
                        </a>
                    </div>
            <?php $idx++; endwhile; wp_reset_postdata();
            else :
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
                        <div class="bolent-service-icon" style="background:<?php echo esc_attr($svc['gradient']); ?>;">
                            <?php echo bolent_get_svg_icon($svc['icon'], '#fff'); ?>
                        </div>
                        <h3 style="font-size:1.2rem;font-weight:700;margin-bottom:12px;color:var(--bolent-ink);"><?php echo esc_html($svc['title']); ?></h3>
                        <p style="font-size:0.95rem;color:var(--bolent-text-secondary);margin-bottom:20px;line-height:1.65;"><?php echo esc_html($svc['desc']); ?></p>
                    </div>
            <?php endforeach;
            endif; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bolent-cta">
    <div class="bolent-cta-content">
        <h2>找到您需要的服务了吗？</h2>
        <p>联系我们，获取专属解决方案</p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="bolent-btn-harmony">立即咨询</a>
    </div>
</section>

<?php get_footer(); ?>
