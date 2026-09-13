<?php
/**
 * Template Name: 深伯乐 博客
 * 读取标准 WP posts 渲染博客列表
 */
if (!defined('ABSPATH')) exit;
get_header();
?>

<!-- Hero -->
<section class="bolent-hero" style="min-height:50vh;">
    <div class="bolent-hero-grid"></div>
    <div class="bolent-hero-content">
        <h1><?php echo esc_html(bolent_mod('page_blog_title1', '技术')); ?><span class="bolent-hero-highlight"><?php echo esc_html(bolent_mod('page_blog_highlight', '博客')); ?></span></h1>
        <p class="bolent-hero-subtitle"><?php echo esc_html(bolent_mod('page_blog_subtitle', 'HarmonyOS · 技术实践 · 行业洞察')); ?></p>
        <p class="bolent-hero-desc"><?php echo esc_html(bolent_mod('page_blog_desc', '分享鸿蒙生态开发经验与技术心得')); ?></p>
    </div>
</section>

<!-- 博客列表 -->
<section class="bolent-section">
    <div class="bolent-container">
        <div class="bolent-section-header">
            <span class="bolent-section-eyebrow">BLOG</span>
            <h2 class="bolent-section-title">最新文章</h2>
        </div>

        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:28px;">
            <?php
            $posts = new WP_Query(array(
                'post_type' => 'post',
                'posts_per_page' => 10,
                'post_status' => 'publish',
            ));

            if ($posts->have_posts()) :
                while ($posts->have_posts()) : $posts->the_post();
                    $categories = get_the_category();
            ?>
                    <article style="background:#fff;border:1px solid var(--bolent-border);border-radius:var(--bolent-radius-lg);overflow:hidden;transition:var(--bolent-transition);" onmouseover="this.style.boxShadow='var(--bolent-shadow-lg)';this.style.transform='translateY(-4px)';" onmouseout="this.style.boxShadow='none';this.style.transform='none';">
                        <?php if (has_post_thumbnail()) : ?>
                            <a href="<?php the_permalink(); ?>" style="display:block;overflow:hidden;">
                                <?php the_post_thumbnail('large', array('style' => 'width:100%;height:200px;object-fit:cover;transition:transform 0.3s;')); ?>
                            </a>
                        <?php else : ?>
                            <a href="<?php the_permalink(); ?>" style="display:block;height:200px;background:var(--bolent-gradient-soft);display:flex;align-items:center;justify-content:center;text-decoration:none;">
                                <?php echo bolent_get_svg_icon('layers', 'var(--bolent-primary)'); ?>
                            </a>
                        <?php endif; ?>
                        <div style="padding:28px;">
                            <div style="display:flex;gap:12px;align-items:center;margin-bottom:12px;">
                                <?php if ($categories) : ?>
                                    <span style="display:inline-block;padding:4px 12px;background:var(--bolent-primary-50);color:var(--bolent-primary);font-size:12px;font-weight:600;border-radius:var(--bolent-radius-pill);">
                                        <?php echo esc_html($categories[0]->name); ?>
                                    </span>
                                <?php endif; ?>
                                <span style="font-size:13px;color:var(--bolent-text-muted);"><?php echo esc_html(get_the_date()); ?></span>
                            </div>
                            <h3 style="font-size:1.15rem;font-weight:700;margin-bottom:10px;">
                                <a href="<?php the_permalink(); ?>" style="color:var(--bolent-ink);text-decoration:none;"><?php the_title(); ?></a>
                            </h3>
                            <p style="font-size:0.9rem;color:var(--bolent-text-secondary);line-height:1.6;margin-bottom:16px;">
                                <?php echo esc_html(wp_trim_words(get_the_excerpt(), 30)); ?>
                            </p>
                            <a href="<?php the_permalink(); ?>" style="font-size:14px;font-weight:600;color:var(--bolent-primary);display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                                阅读全文 <?php echo bolent_get_svg_icon('arrow-right'); ?>
                            </a>
                        </div>
                    </article>
            <?php
                endwhile;
                wp_reset_postdata();
            else :
            ?>
                <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--bolent-text-muted);">
                    <p style="font-size:18px;">暂无文章，敬请期待。</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- 分页 -->
        <div style="margin-top:48px;text-align:center;">
            <?php
            echo paginate_links(array(
                'total' => $posts->max_num_pages,
                'prev_text' => '&laquo; 上一页',
                'next_text' => '下一页 &raquo;',
            ));
            ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
