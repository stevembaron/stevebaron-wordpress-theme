<!doctype html>
<html <?php language_attributes(); ?> data-mode="light">
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<?php wp_head(); ?>
<script>
/* Apply saved dark mode before first paint to avoid flash */
(function(){var m=localStorage.getItem('sb-mode');if(m)document.documentElement.setAttribute('data-mode',m);})();
</script>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main"><?php _e('Skip to content','stevebaron'); ?></a>

<header id="site-header">
  <div class="nav-inner">

    <!-- Brand -->
    <a href="<?php echo esc_url(home_url('/')); ?>" class="nav-brand">
      <?php if (has_custom_logo()) : ?>
        <?php the_custom_logo(); ?>
      <?php else : ?>
        <span class="nav-brand-mark" aria-hidden="true">SB</span>
        <?php bloginfo('name'); ?>
      <?php endif; ?>
    </a>

    <!-- Primary nav -->
    <nav class="nav-links" id="site-nav" aria-label="<?php _e('Primary','stevebaron'); ?>">
      <?php
      if (has_nav_menu('primary')) {
        wp_nav_menu([
          'theme_location' => 'primary',
          'menu_id'        => 'primary-menu',
          'container'      => false,
          'items_wrap'     => '%3$s',
          'walker'         => new Stevebaron_Nav_Walker(),
          'fallback_cb'    => false,
        ]);
      } else {
        /* Fallback hard-coded links */
        $pages = [
          'Home'     => home_url('/'),
          'About'    => home_url('/about/'),
          'CV'       => home_url('/cv/'),
          'Projects' => home_url('/projects/'),
          'Writing'  => get_permalink(get_option('page_for_posts')) ?: home_url('/writing/'),
          'Photos'   => home_url('/photos/'),
          'Now'      => home_url('/now/'),
          'Contact'  => home_url('/contact/'),
        ];
        foreach ($pages as $label => $url) {
          $active = (untrailingslashit($_SERVER['REQUEST_URI']) === parse_url($url, PHP_URL_PATH)) ? 'active' : '';
          printf('<a href="%s" class="%s">%s</a>', esc_url($url), esc_attr($active), esc_html($label));
        }
      }
      ?>
    </nav>

    <div class="nav-actions">
      <!-- Dark mode toggle -->
      <button class="dark-toggle" aria-label="<?php _e('Toggle dark mode','stevebaron'); ?>">
        <svg class="icon-moon" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
        </svg>
        <svg class="icon-sun" viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="5"/>
          <line x1="12" y1="1" x2="12" y2="3"/>
          <line x1="12" y1="21" x2="12" y2="23"/>
          <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
          <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
          <line x1="1" y1="12" x2="3" y2="12"/>
          <line x1="21" y1="12" x2="23" y2="12"/>
          <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
          <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
        </svg>
      </button>

      <!-- Mobile hamburger -->
      <button class="nav-burger" aria-expanded="false" aria-controls="site-nav" aria-label="<?php _e('Open menu','stevebaron'); ?>">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 12h18M3 6h18M3 18h18"/>
        </svg>
      </button>
    </div>

  </div>
</header>

<main id="main">
