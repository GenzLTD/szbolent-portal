<?php
/**
 * 深伯乐 自定义 Footer 模板 — HarmonyOS 生态服务商品牌
 */
if (!defined('ABSPATH')) exit;
?>
<footer class="bolent-footer">
    <div class="bolent-footer-grid">
        <!-- 品牌区 -->
        <div class="bolent-footer-brand">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="bolent-logo">
                <svg width="36" height="36" viewBox="0 0 36 36" fill="none">
                    <rect x="2" y="4" width="14" height="14" rx="3" fill="#FFFFFF"/>
                    <rect x="2" y="20" width="14" height="8" rx="3" fill="#9B85FF" opacity="0.7"/>
                    <path d="M18 4 Q30 12 18 30" stroke="#FFFFFF" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                    <circle cx="26" cy="12" r="3.5" fill="#9B85FF"/>
                </svg>
                <span class="bolent-logo-text" style="color:#fff">深伯乐</span>
            </a>
            <p>HarmonyOS 生态服务商，提供鸿蒙应用开发、硬件适配、数字化与 IT 全栈解决方案。覆盖从芯片适配到应用分发的全生态链路。</p>
        </div>

        <!-- 服务 -->
        <div class="bolent-footer-col">
            <h5>服务</h5>
            <?php
            wp_nav_menu(array(
                'theme_location' => 'footer-services',
                'container' => false,
                'menu_class' => '',
                'fallback_cb' => 'bolent_footer_services_menu',
            ));
            ?>
        </div>

        <!-- 探索 -->
        <div class="bolent-footer-col">
            <h5>探索</h5>
            <?php
            wp_nav_menu(array(
                'theme_location' => 'footer-explore',
                'container' => false,
                'menu_class' => '',
                'fallback_cb' => 'bolent_footer_explore_menu',
            ));
            ?>
        </div>

        <!-- 联系 -->
        <div class="bolent-footer-col">
            <h5>联系</h5>
            <?php
            wp_nav_menu(array(
                'theme_location' => 'footer-contact',
                'container' => false,
                'menu_class' => '',
                'fallback_cb' => 'bolent_footer_contact_menu',
            ));
            ?>
        </div>
    </div>
    <div class="bolent-footer-bottom">
        <span>&copy; <?php echo date('Y'); ?> 深伯乐（深圳）科技有限公司. All rights reserved.</span>
        <span>www.szbolent.cn</span>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
