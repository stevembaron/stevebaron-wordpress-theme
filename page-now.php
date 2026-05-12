<?php
/**
 * Template Name: Now
 */
get_header();

$post_id  = get_the_ID();
$updated  = get_post_meta($post_id,'_sb_now_updated',true) ?: date('F j, Y');
$location = get_post_meta($post_id,'_sb_now_location',true) ?: 'Salt Lake City';

$items = [
  '_sb_now_working_on' => ['Working on',   '💼'],
  '_sb_now_reading'    => ['Reading',       '📚'],
  '_sb_now_watching'   => ['Watching',      '📺'],
  '_sb_now_learning'   => ['Learning',      '🦀'],
  '_sb_now_outside'    => ['Outside',       '🥾'],
  '_sb_now_yes_to'     => ['Saying yes to', '☕'],
  '_sb_now_no_to'      => ['Saying no to',  '✈️'],
];
?>

<section class="now-hero">
  <div class="container-narrow">
    <span class="eyebrow">/now</span>
    <h1 class="now-title"><?php the_title(); ?></h1>
    <p class="now-updated mono muted">
      <?php printf(
        esc_html__('Last updated: %1$s · %2$s','stevebaron'),
        esc_html($updated),
        esc_html($location)
      ); ?>
    </p>

    <div class="now-items">
      <?php foreach ($items as $key => [$label, $emoji]) : ?>
        <?php $val = get_post_meta($post_id, $key, true); if (!$val) continue; ?>
        <div class="now-item">
          <div class="now-key"><?php echo esc_html($label); ?></div>
          <div class="now-val"><?php echo esc_html($val); ?></div>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if (have_posts()) : the_post(); if (get_the_content()) : ?>
      <div class="entry-content" style="margin-top:var(--space-lg);">
        <?php the_content(); ?>
      </div>
    <?php endif; endif; ?>

    <p class="now-footer-note">
      <?php printf(
        wp_kses(__('This is a <a href="%s">/now page</a>, an idea from Derek Sivers.','stevebaron'), ['a'=>['href'=>[]]]),
        'https://nownownow.com'
      ); ?>
    </p>
  </div>
</section>

<?php get_footer(); ?>
