<?php
/**
 * Template Name: Photos
 */
get_header();

// Get all photo categories
$photo_cats = get_terms(['taxonomy'=>'sb_photo_cat','hide_empty'=>true]);
?>

<section class="photos-hero">
  <div class="container">
    <span class="eyebrow"><?php _e('Photos','stevebaron'); ?></span>
    <h1><?php the_title(); ?></h1>
    <?php if (have_posts()) : the_post(); if (get_the_excerpt()) : ?>
      <p style="color:var(--ink-2);font-size:18px;max-width:540px;"><?php the_excerpt(); ?></p>
    <?php endif; endif; ?>

    <?php if ($photo_cats && !is_wp_error($photo_cats)) : ?>
      <div class="photo-filters" data-filter-group="photos">
        <span class="chip active" data-filter="all"><?php _e('All','stevebaron'); ?></span>
        <?php foreach ($photo_cats as $cat) : ?>
          <span class="chip" data-filter="<?php echo esc_attr($cat->slug); ?>"><?php echo esc_html($cat->name); ?></span>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="photos-grid">
  <div class="container" data-filter-list="photos">
    <?php
    $photos = new WP_Query([
      'post_type'      => 'sb_photo',
      'posts_per_page' => -1,
      'orderby'        => 'menu_order',
      'order'          => 'ASC',
      'post_status'    => 'publish',
    ]);
    ?>
    <?php if ($photos->have_posts()) : ?>
      <div class="photos-masonry">
        <?php while ($photos->have_posts()) : $photos->the_post(); ?>
          <?php
          $cats    = get_the_terms(get_the_ID(),'sb_photo_cat');
          $cat_slug = $cats && !is_wp_error($cats) ? $cats[0]->slug : '';
          $cat_name = $cats && !is_wp_error($cats) ? $cats[0]->name : '';
          ?>
          <div class="photo-item" data-cat="<?php echo esc_attr($cat_slug); ?>">
            <?php if (has_post_thumbnail()) : ?>
              <?php the_post_thumbnail('sb-photo', ['alt'=>get_the_title()]); ?>
            <?php else : ?>
              <div class="ph" style="aspect-ratio:4/3;"><?php the_title(); ?></div>
            <?php endif; ?>
            <div class="photo-caption">
              <span><?php the_title(); ?></span>
              <?php if ($cat_name) : ?>
                <span class="photo-caption-tag"><?php echo esc_html($cat_name); ?></span>
              <?php endif; ?>
            </div>
          </div>
        <?php endwhile; wp_reset_postdata(); ?>
      </div>
    <?php else : ?>
      <p style="color:var(--ink-3);font-family:var(--font-mono);font-size:13px;padding:var(--space-lg) 0;">
        <?php _e('No photos yet — add some in Photos › New Photo.','stevebaron'); ?>
      </p>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
