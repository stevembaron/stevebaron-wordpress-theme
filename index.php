<?php
/**
 * Fallback index — handles all uncaught queries.
 */
get_header();
?>

<section class="post-list" style="padding-top:var(--space-xl);">
  <div class="container-narrow">

    <?php if (is_search()) : ?>
      <span class="eyebrow"><?php _e('Search results','stevebaron'); ?></span>
      <h1 class="page-title" style="margin-top:16px;">
        <?php printf(esc_html__('Results for "%s"','stevebaron'), get_search_query()); ?>
      </h1>
    <?php elseif (is_home() || is_front_page()) : ?>
      <h1 class="page-title"><?php _e('Latest','stevebaron'); ?></h1>
    <?php else : ?>
      <?php the_archive_title('<h1 class="page-title" style="margin-bottom:var(--space-lg);">','</h1>'); ?>
    <?php endif; ?>

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <?php $cats = get_the_category(); $cat_name = $cats ? $cats[0]->name : ''; ?>
      <a href="<?php the_permalink(); ?>" class="post-list-item">
        <div class="post-list-date"><?php echo get_the_date(); ?></div>
        <div>
          <?php if ($cat_name) : ?><div class="post-list-tag"><?php echo esc_html($cat_name); ?></div><?php endif; ?>
          <div class="post-list-title"><?php the_title(); ?></div>
          <div class="post-list-blurb"><?php echo stevebaron_excerpt(18); ?></div>
        </div>
        <div class="post-list-read"><?php echo stevebaron_reading_time(); ?></div>
      </a>
    <?php endwhile;
    the_posts_pagination();
    else : ?>
      <p style="color:var(--ink-3);font-family:var(--font-mono);font-size:13px;padding:var(--space-lg) 0;">
        <?php is_search() ? _e('No results found. Try a different search.','stevebaron') : _e('Nothing here yet.','stevebaron'); ?>
      </p>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
