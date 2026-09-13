<?php
/**
 * 深伯乐 自定义 Header 模板 — HarmonyOS 生态服务商品牌
 */
if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="bolent-header">
    <div class="bolent-header-inner">
        <!-- Logo -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="bolent-logo">
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                <rect x="2" y="4" width="14" height="14" rx="3" fill="#1A73E8"/>
                <rect x="2" y="20" width="14" height="8" rx="3" fill="#6B4EFF" opacity="0.6"/>
                <path d="M18 4 Q30 12 18 30" stroke="#1A73E8" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                <circle cx="26" cy="12" r="3.5" fill="#6B4EFF"/>
            </svg>
            <span class="bolent-logo-text">深伯乐</span>
        </a>

        <!-- 导航菜单 -->
        <?php
        wp_nav_menu(array(
            'theme_location' => 'primary',
            'container' => 'nav',
            'container_class' => 'bolent-nav',
            'menu_class' => '',
            'fallback_cb' => 'bolent_default_menu',
        ));
        ?>

        <!-- CTA 按钮 -->
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="bolent-header-cta">立即咨询</a>

        <!-- 移动端汉堡菜单 -->
        <div class="bolent-mobile-toggle" onclick="bolentToggleMenu()">
            <span></span><span></span><span></span>
        </div>
    </div>
</header>

<script>
function bolentToggleMenu() {
    var nav = document.querySelector('.bolent-nav');
    nav.classList.toggle('bolent-nav-open');
}
</script>
