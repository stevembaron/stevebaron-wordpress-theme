<?php get_header(); ?>

<div class="container">
  <div class="error-page">
    <div>
      <p class="error-code">404</p>
      <h1 class="error-msg"><?php _e('Page not found.','stevebaron'); ?></h1>
      <p style="color:var(--ink-2);font-size:17px;margin-bottom:var(--space-md);">
        <?php _e('That page doesn\'t exist — maybe it moved, maybe it never existed. Either way, the mountains are still there.','stevebaron'); ?>
      </p>
      <a href="<?php echo esc_url(home_url('/')); ?>" class="btn btn-primary"><?php _e('← Back home','stevebaron'); ?></a>
      <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/writing/')); ?>" class="btn" style="margin-left:8px;"><?php _e('Read something','stevebaron'); ?></a>
    </div>
  </div>
</div>

<?php get_footer(); ?>
