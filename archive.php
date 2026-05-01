<?php get_header(); ?>

<section class="blog-hero">
  <div class="container-narrow">
    <span class="eyebrow"><?php _e('The Writing','stevebaron'); ?></span>
    <h1>
      <?php
      if (is_category()) single_cat_title();
      elseif (is_tag()) single_tag_title();
      else _e('Notes from a media nerd in the mountains.','stevebaron');
      ?>
    </h1>
    <?php if (!is_category() && !is_tag()) : ?>
      <p><?php _e('Long essays, short notes, weather charts I find pretty. Roughly weekly, unless it\'s a powder day.','stevebaron'); ?></p>
    <?php else : ?>
      <?php the_archive_description('<p>','</p>'); ?>
    <?php endif; ?>

    <?php
    $cats = get_categories(['hide_empty'=>true]);
    if ($cats) :
      $current_cat = get_query_var('cat');
    ?>
    <div class="blog-filters" data-filter-group="posts">
      <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/writing/')); ?>"
         class="chip <?php echo !is_category() ? 'active' : ''; ?>">
        <?php _e('All','stevebaron'); ?>
      </a>
      <?php foreach ($cats as $cat) : ?>
        <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"
           class="chip <?php echo (is_category($cat->term_id)) ? 'active' : ''; ?>">
          <?php echo esc_html($cat->name); ?>
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<section class="post-list">
  <div class="container-narrow">
    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
      <?php
      $cats = get_the_category();
      $cat_name = $cats ? $cats[0]->name : '';
      ?>
      <a href="<?php the_permalink(); ?>" class="post-list-item">
        <div class="post-list-date"><?php echo get_the_date(); ?></div>
        <div>
          <?php if ($cat_name) : ?>
            <div class="post-list-tag"><?php echo esc_html($cat_name); ?></div>
          <?php endif; ?>
          <div class="post-list-title"><?php the_title(); ?></div>
          <div class="post-list-blurb"><?php echo stevebaron_excerpt(18); ?></div>
        </div>
        <div class="post-list-read"><?php echo stevebaron_reading_time(); ?></div>
      </a>
    <?php endwhile;

    the_posts_pagination([
      'mid_size'  => 2,
      'prev_text' => '← '.__('Older','stevebaron'),
      'next_text' => __('Newer','stevebaron').' →',
    ]);

    else : ?>
      <p style="color:var(--ink-3);font-family:var(--font-mono);font-size:13px;padding:var(--space-lg) 0;">
        <?php _e('No posts yet. Publish your first post in the WordPress admin.','stevebaron'); ?>
      </p>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
