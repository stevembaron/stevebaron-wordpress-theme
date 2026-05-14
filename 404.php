<?php get_header(); ?>

<div class="container">
  <div class="error-page">
    <div class="error-content">
      <p class="error-code">404</p>
      <h1 class="error-msg"><?php _e('No page in the forecast.','stevebaron'); ?></h1>

      <div class="error-forecast">
        <div class="error-forecast-row">
          <span class="error-forecast-key"><?php _e('Conditions','stevebaron'); ?></span>
          <span class="error-forecast-val"><?php _e('clear at every other URL on this site','stevebaron'); ?></span>
        </div>
        <div class="error-forecast-row">
          <span class="error-forecast-key"><?php _e('Visibility','stevebaron'); ?></span>
          <span class="error-forecast-val"><?php _e('0 ft (no page here)','stevebaron'); ?></span>
        </div>
        <div class="error-forecast-row">
          <span class="error-forecast-key"><?php _e('Chance of recovery','stevebaron'); ?></span>
          <span class="error-forecast-val"><?php _e('100% if you head back home','stevebaron'); ?></span>
        </div>
        <div class="error-forecast-row">
          <span class="error-forecast-key"><?php _e('Issued','stevebaron'); ?></span>
          <span class="error-forecast-val"><?php echo esc_html(wp_date('l, F j · g:i a')); ?></span>
        </div>
      </div>

      <p style="color:var(--ink-2);font-size:16px;margin-bottom:var(--space-md);">
        <?php _e("That page doesn't exist — maybe it moved, maybe it never existed. Either way, the mountains are still there.",'stevebaron'); ?>
      </p>

      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary"><?php _e('← Back home','stevebaron'); ?></a>
        <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/writing/')); ?>" class="btn"><?php _e('Read something','stevebaron'); ?></a>
        <button type="button" class="btn js-random-page" data-home="<?php echo esc_attr(home_url('/')); ?>"><?php _e('🎲 Take me somewhere','stevebaron'); ?></button>
      </div>
    </div>
  </div>
</div>

<?php get_footer(); ?>
