<?php
/**
 * Template Name: 深伯乐 案例研究
 * 读取 bolent_case CPT 渲染案例列表
 */
if (!defined('ABSPATH')) exit;
get_header();
?>

<!-- Hero -->
<section class="bolent-hero" style="min-height:50vh;">
    <div class="bolent-hero-grid"></div>
    <div class="bolent-hero-content">
        <h1><?php echo esc_html(bolent_mod('page_cases_title1', '成功')); ?><span class="bolent-hero-highlight"><?php echo esc_html(bolent_mod('page_cases_highlight', '案例')); ?></span></h1>
        <p class="bolent-hero-subtitle"><?php echo esc_html(bolent_mod('page_cases_subtitle', '以结果说话')); ?></p>
        <p class="bolent-hero-desc"><?php echo esc_html(bolent_mod('page_cases_desc', '这是我们交付价值的证明')); ?></p>
    </div>
</section>

<!-- 案例列表 -->
<section class="bolent-section">
    <div class="bolent-container">
        <div class="bolent-section-header">
            <span class="bolent-section-eyebrow">CASE STUDIES</span>
            <h2 class="bolent-section-title">精选案例</h2>
            <p class="bolent-section-desc">以结果说话，这是我们交付价值的证明</p>
        </div>
        <div class="bolent-cases-grid">
            <?php
            $cases = new WP_Query(array(
                'post_type' => 'bolent_case',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
            ));

            if ($cases->have_posts()) :
                while ($cases->have_posts()) : $cases->the_post();
                    $client = get_post_meta(get_the_ID(), '_case_client', true);
                    $tags = get_post_meta(get_the_ID(), '_case_tags', true);
                    $m1 = get_post_meta(get_the_ID(), '_case_metric_1', true);
                    $m2 = get_post_meta(get_the_ID(), '_case_metric_2', true);
                    $industries = get_the_terms(get_the_ID(), 'case_industry');
            ?>
                    <div class="bolent-case-card">
                        <div class="bolent-case-visual">
                            <?php echo bolent_get_svg_icon('layers'); ?>
                            <?php if ($tags) : ?><span class="bolent-case-tag"><?php echo esc_html($tags); ?></span><?php endif; ?>
                        </div>
                        <div class="bolent-case-body">
                            <h3><a href="<?php the_permalink(); ?>" style="color:inherit;text-decoration:none;"><?php the_title(); ?></a></h3>
                            <?php if ($client) : ?>
                                <p style="font-size:12px;color:var(--bolent-text-muted);margin-bottom:6px;">客户：<?php echo esc_html($client); ?></p>
                            <?php endif; ?>
                            <p><?php echo esc_html(get_the_excerpt()); ?></p>
                            <?php if ($m1 || $m2) : ?>
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
            <?php endforeach;
            endif; ?>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bolent-cta">
    <div class="bolent-cta-content">
        <h2>您的项目就是下一个成功案例</h2>
        <p>让我们一起探讨如何以技术助力业务</p>
        <a href="<?php echo esc_url(home_url('/contact')); ?>" class="bolent-btn-harmony">立即咨询</a>
    </div>
</section>

<?php get_footer(); ?>
