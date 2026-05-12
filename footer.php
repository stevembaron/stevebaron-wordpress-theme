</main><!-- #main -->

<footer id="site-footer">
  <div class="footer-inner">

    <!-- Col 1: Brand + social -->
    <div class="footer-col">
      <h4><?php bloginfo('name'); ?></h4>
      <p style="margin:0;color:var(--ink-2);max-width:320px;">
        <?php echo esc_html(get_theme_mod('sb_footer_tagline','Salt Lake City, Utah. Product, AI & digital transformation executive. Former SVP at Fox Corporation.')); ?>
      </p>
      <?php echo stevebaron_social_links_html(); ?>
    </div>

    <!-- Col 2: Around the site -->
    <div class="footer-col">
      <h4><?php _e('Around the site','stevebaron'); ?></h4>
      <ul>
        <li><a href="<?php echo esc_url(home_url('/about/')); ?>"><?php _e('About','stevebaron'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/cv/')); ?>"><?php _e('CV / Résumé','stevebaron'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/projects/')); ?>"><?php _e('Projects','stevebaron'); ?></a></li>
        <?php
        $blog_url = get_permalink(get_option('page_for_posts'));
        if (!$blog_url) $blog_url = home_url('/writing/');
        ?>
        <li><a href="<?php echo esc_url($blog_url); ?>"><?php _e('Writing','stevebaron'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/photos/')); ?>"><?php _e('Photos','stevebaron'); ?></a></li>
        <li><a href="<?php echo esc_url(home_url('/now/')); ?>"><?php _e('Now','stevebaron'); ?></a></li>
      </ul>
    </div>

    <!-- Col 3: Elsewhere -->
    <div class="footer-col">
      <h4><?php _e('Elsewhere','stevebaron'); ?></h4>
      <ul>
        <?php
        $rss = get_theme_mod('sb_rss_url','') ?: get_feed_link();
        $newsletter = get_theme_mod('sb_newsletter_url','');
        $email = get_theme_mod('sb_social_email','');
        ?>
        <li><a href="<?php echo esc_url($rss); ?>"><?php _e('RSS feed','stevebaron'); ?></a></li>
        <?php if ($email) : ?>
          <li><a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></li>
        <?php endif; ?>
        <?php if ($newsletter) : ?>
          <li><a href="<?php echo esc_url($newsletter); ?>" target="_blank" rel="noopener"><?php _e('Newsletter','stevebaron'); ?></a></li>
        <?php endif; ?>
      </ul>
    </div>

  </div><!-- .footer-inner -->

  <div class="footer-bottom">
    <span>
      &copy; <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?> &middot;
      <?php printf(
        wp_kses(__('Built with <a href="%s">WordPress</a> &amp; love','stevebaron'), ['a'=>['href'=>true,'rel'=>true]]),
        'https://wordpress.org'
      ); ?>
    </span>
    <span><?php echo esc_html(get_theme_mod('sb_footer_coordinates','40.7608° N · 111.8910° W')); ?></span>
  </div>

</footer>

<?php wp_footer(); ?>
</body>
</html>
