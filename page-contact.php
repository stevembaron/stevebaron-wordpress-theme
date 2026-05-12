<?php
/**
 * Template Name: Contact
 */
get_header();

$headline   = get_theme_mod('sb_contact_headline',"Let's work together.");
$subtext    = get_theme_mod('sb_contact_subtext','Email is best. I read everything and try to respond within a day or two — sooner if you include a weather observation.');
$available  = get_theme_mod('sb_contact_available', true);
$avail_text = get_theme_mod('sb_contact_availability_text','Open to advisory, fractional, and full-time conversations. Based in Salt Lake City and comfortable working remote, hybrid, or traveling as needed.');

$cards = [
  ['Email',     'email',     'mailto:'],
  ['LinkedIn',  'linkedin',  ''],
  ['Twitter',   'twitter',   ''],
  ['Instagram', 'instagram', ''],
  ['GitHub',    'github',    ''],
];
?>

<section class="contact-hero">
  <div class="container-narrow">
    <span class="eyebrow"><?php _e('Say hi','stevebaron'); ?></span>
    <h1><?php echo esc_html($headline); ?></h1>
    <?php if ($subtext) : ?>
      <p class="lead"><?php echo esc_html($subtext); ?></p>
    <?php endif; ?>

    <div class="contact-cards">
      <?php foreach ($cards as [$label, $key, $prefix]) : ?>
        <?php $val = get_theme_mod('sb_social_' . $key,''); if (!$val) continue; ?>
        <?php $href = $prefix ? $prefix . sanitize_email($val) : esc_url($val); ?>
        <a href="<?php echo esc_attr($href); ?>" class="contact-card" <?php if ($key !== 'email') echo 'target="_blank" rel="noopener noreferrer"'; ?>>
          <div class="contact-card-label"><?php echo esc_html($label); ?></div>
          <div class="contact-card-value"><?php echo esc_html($val); ?></div>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($available) : ?>
      <div class="availability-box">
        <div class="mono muted" style="font-size:11px;letter-spacing:.08em;text-transform:uppercase;margin-bottom:8px;">
          <span class="availability-indicator"></span>
          <?php _e('Currently available','stevebaron'); ?>
        </div>
        <div style="font-size:16px;line-height:1.55;">
          <?php echo esc_html($avail_text); ?>
        </div>
      </div>
    <?php endif; ?>

    <?php if (have_posts()) : the_post(); if (get_the_content()) : ?>
      <div class="entry-content" style="margin-top:var(--space-lg);">
        <?php the_content(); ?>
      </div>
    <?php endif; endif; ?>

  </div>
</section>

<?php get_footer(); ?>
