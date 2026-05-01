<?php
/**
 * Template Name: About
 */
get_header();

$post_id  = get_the_ID();
$headshot_id = get_theme_mod('sb_headshot','');

$into_fields = [
  '_sb_about_reading'   => ['Reading',        '📚'],
  '_sb_about_listening' => ['Listening',       '🎵'],
  '_sb_about_building'  => ['Building',        '🛠'],
  '_sb_about_cooking'   => ['Cooking',         '🍞'],
  '_sb_about_watching'  => ['Watching',        '📺'],
  '_sb_about_skiing'    => ['Skiing / Outdoors','⛷'],
];
?>

<section class="about-hero">
  <div class="container-narrow">
    <span class="eyebrow"><?php _e('About','stevebaron'); ?></span>
    <h1 class="page-title" style="font-size:clamp(40px,6vw,64px);">
      <?php the_title(); ?>
    </h1>

    <div class="about-intro">
      <?php if ($headshot_id) : ?>
        <?php echo wp_get_attachment_image($headshot_id, 'sb-square', false, ['class'=>'about-headshot','alt'=>get_bloginfo('name')]); ?>
      <?php elseif (has_post_thumbnail()) : ?>
        <?php the_post_thumbnail('sb-square', ['class'=>'about-headshot']); ?>
      <?php else : ?>
        <div class="about-headshot-ph ph">headshot · 1:1</div>
      <?php endif; ?>

      <div class="about-lead">
        <?php the_excerpt(); ?>
      </div>
    </div>

    <div class="about-long">
      <?php the_content(); ?>
    </div>

    <?php
    $has_into = false;
    foreach ($into_fields as $key => $data) {
      if (get_post_meta($post_id, $key, true)) { $has_into = true; break; }
    }
    if ($has_into) :
    ?>
    <div class="currently-into">
      <h2><?php _e('Currently into','stevebaron'); ?></h2>
      <ul class="into-list">
        <?php foreach ($into_fields as $key => [$label, $emoji]) : ?>
          <?php $val = get_post_meta($post_id, $key, true); if (!$val) continue; ?>
          <li class="into-item">
            <span class="into-key"><?php echo esc_html($label); ?></span>
            <span><?php echo esc_html($val); ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>

  </div>
</section>

<?php get_footer(); ?>
