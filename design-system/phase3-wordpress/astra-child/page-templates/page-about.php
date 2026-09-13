<?php
/**
 * Template Name: 深伯乐 关于我们
 * HarmonyOS 生态服务商品牌
 */
if (!defined('ABSPATH')) exit;
get_header();
?>

<!-- Hero -->
<section class="bolent-hero" style="min-height:60vh;">
    <div class="bolent-hero-grid"></div>
    <div class="bolent-hero-content">
        <h1><?php echo esc_html(bolent_mod('page_about_title1', '关于 ')); ?><span class="bolent-hero-highlight"><?php echo esc_html(bolent_mod('page_about_highlight', '深伯乐')); ?></span></h1>
        <p class="bolent-hero-subtitle"><?php echo esc_html(bolent_mod('page_about_subtitle', 'HarmonyOS 生态服务商')); ?></p>
        <p class="bolent-hero-desc"><?php echo esc_html(bolent_mod('page_about_desc', '覆盖从芯片适配到应用分发的 HarmonyOS 全生态链路，为企业打造端到端的鸿蒙化解决方案')); ?></p>
    </div>
</section>

<!-- 公司介绍 -->
<section class="bolent-section">
    <div class="bolent-container">
        <div style="max-width:800px;margin:0 auto;">
            <span class="bolent-section-eyebrow">ABOUT US</span>
            <h2 class="bolent-section-title" style="text-align:left;">谁是深伯乐</h2>
            <p style="font-size:17px;color:var(--bolent-text-secondary);line-height:1.8;margin-bottom:24px;">
                深伯乐是一家 HarmonyOS 生态服务商，专注于鸿蒙应用开发、硬件适配、原子化服务与企业数字化解决方案。
                我们覆盖从芯片适配到应用分发的全生态链路，帮助企业完成鸿蒙化转型。
            </p>
            <p style="font-size:17px;color:var(--bolent-text-secondary);line-height:1.8;margin-bottom:24px;">
                团队深耕 HarmonyOS/OpenHarmony 技术栈，熟悉 ArkUI/ArkTS 开发框架、HDF 驱动模型、
                AppGallery Connect 分发流程，并积极参与 OpenHarmony 开源社区贡献。
            </p>
            <p style="font-size:17px;color:var(--bolent-text-secondary);line-height:1.8;">
                除鸿蒙生态外，我们还提供全方位 IT 服务，包括软件开发、数字化&数据、自动化&QA、
                IT 管理与敏捷咨询，以工程精度交付每一个项目。
            </p>
        </div>
    </div>
</section>

<!-- 使命与愿景 -->
<section class="bolent-section" style="background:var(--bolent-bg-soft);">
    <div class="bolent-container">
        <div class="bolent-features-grid" style="grid-template-columns:repeat(2,1fr);">
            <div class="bolent-feature-card" style="text-align:left;">
                <div class="bolent-feature-icon" style="margin:0 0 20px 0;">
                    <?php echo bolent_get_svg_icon('target'); ?>
                </div>
                <h4 style="font-size:1.3rem;margin-bottom:12px;">我们的使命</h4>
                <p>让每一家企业都能轻松拥抱鸿蒙生态，以技术驱动产业升级，让智能化惠及每一个终端。</p>
            </div>
            <div class="bolent-feature-card" style="text-align:left;">
                <div class="bolent-feature-icon" style="margin:0 0 20px 0;">
                    <?php echo bolent_get_svg_icon('sparkles'); ?>
                </div>
                <h4 style="font-size:1.3rem;margin-bottom:12px;">我们的愿景</h4>
                <p>成为 HarmonyOS 生态中最值得信赖的技术伙伴，推动 OpenHarmony 开源共建，共建万物互联的智能世界。</p>
            </div>
        </div>
    </div>
</section>

<!-- 核心价值 -->
<section class="bolent-section">
    <div class="bolent-container">
        <div class="bolent-section-header">
            <span class="bolent-section-eyebrow">OUR VALUES</span>
            <h2 class="bolent-section-title">核心价值</h2>
        </div>
        <div class="bolent-features-grid">
            <div class="bolent-feature-card">
                <div class="bolent-feature-icon"><?php echo bolent_get_svg_icon('target'); ?></div>
                <h4>领域专业</h4>
                <p>深耕鸿蒙生态与企业数字化，丰富的行业落地经验</p>
            </div>
            <div class="bolent-feature-card">
                <div class="bolent-feature-icon"><?php echo bolent_get_svg_icon('sparkles'); ?></div>
                <h4>卓越品质</h4>
                <p>工程级精度交付，代码评审覆盖率 > 85%，测试覆盖率 > 80%</p>
            </div>
            <div class="bolent-feature-card">
                <div class="bolent-feature-icon"><?php echo bolent_get_svg_icon('wrench'); ?></div>
                <h4>技术前沿</h4>
                <p>持续跟进 HarmonyOS/OpenHarmony 最新版本与技术演进</p>
            </div>
            <div class="bolent-feature-card">
                <div class="bolent-feature-icon"><?php echo bolent_get_svg_icon('heart'); ?></div>
                <h4>客户至上</h4>
                <p>不止于交付，我们关注长期合作与客户成功</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bolent-cta">
    <div class="bolent-cta-content">
        <h2>与我们一起共建鸿蒙生态</h2>
        <p>无论是应用迁移、硬件适配还是生态共建，我们与您并肩前行</p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="bolent-btn-harmony">立即咨询</a>
    </div>
</section>

<?php get_footer(); ?>
