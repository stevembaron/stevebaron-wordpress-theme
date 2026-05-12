<?php get_header(); ?>

<?php
$hero_variant = get_theme_mod('sb_hero_variant','topo');
$headline_raw = get_theme_mod('sb_hero_headline', "Hi, I'm Steve.\nMeteorologist turned\nproduct exec. Building\nat the edge of AI & media.");
$subtext      = get_theme_mod('sb_hero_subtext','25+ years from the weather desk to the executive suite. I\'ve launched apps that hit #1, built platforms reaching 100M monthly readers, and I\'m still at my best when a forecast goes sideways.');
$cta_label    = get_theme_mod('sb_hero_cta_label','Read the latest →');
$cta_url      = get_theme_mod('sb_hero_cta_url','') ?: get_permalink(get_option('page_for_posts'));
$cv_label     = get_theme_mod('sb_hero_cv_label','Download CV');
$cv_url       = get_theme_mod('sb_cv_pdf_url','');
$weather_text = get_theme_mod('sb_hero_weather','☀ 64°F · clear skies over the Wasatch');
$eyebrow_text = get_theme_mod('sb_hero_eyebrow','Salt Lake City · 40.76° N');

$headline_html = nl2br(esc_html($headline_raw));
?>

<!-- HERO -->
<section class="hero">
  <?php if ($hero_variant === 'topo') : ?>
    <svg class="topo-bg" id="topo-bg" viewBox="0 0 1000 600" preserveAspectRatio="xMidYMid slice" aria-hidden="true"></svg>
  <?php elseif ($hero_variant === 'mountains') : ?>
    <div class="mountain-silhouette" aria-hidden="true">
      <svg viewBox="0 0 800 280" style="width:100%;height:auto;display:block;" preserveAspectRatio="none">
        <path d="M0,280 L0,180 L80,120 L140,160 L200,80 L280,140 L340,90 L420,150 L500,100 L580,170 L660,110 L740,160 L800,130 L800,280 Z" fill="var(--accent-2)" opacity="0.35"/>
        <path d="M0,280 L0,210 L60,170 L130,200 L200,130 L260,180 L340,140 L420,200 L490,150 L570,210 L650,160 L730,200 L800,170 L800,280 Z" fill="var(--accent)" opacity="0.55"/>
        <path d="M0,280 L0,240 L70,210 L150,230 L220,190 L300,220 L380,200 L460,230 L540,200 L620,235 L700,210 L780,230 L800,220 L800,280 Z" fill="var(--leaf)" opacity="0.85"/>
      </svg>
    </div>
  <?php endif; ?>

  <div class="container hero-content">
    <?php if ($eyebrow_text) : ?>
      <span class="eyebrow" style="margin-bottom:24px;display:inline-flex;"><?php echo esc_html($eyebrow_text); ?></span>
    <?php endif; ?>

    <h1 class="hero-headline"><?php echo $headline_html; ?></h1>

    <?php if ($subtext) : ?>
      <p class="hero-sub"><?php echo wp_kses_post($subtext); ?></p>
    <?php endif; ?>

    <div class="hero-actions">
      <?php if ($cta_url) : ?>
        <a href="<?php echo esc_url($cta_url); ?>" class="btn btn-primary"><?php echo esc_html($cta_label); ?></a>
      <?php endif; ?>
      <?php if ($cv_url) : ?>
        <a href="<?php echo esc_url($cv_url); ?>" class="btn" download><?php echo esc_html($cv_label); ?></a>
      <?php else : ?>
        <a href="<?php echo esc_url(home_url('/cv/')); ?>" class="btn"><?php echo esc_html($cv_label); ?></a>
      <?php endif; ?>
      <?php if ($weather_text) : ?>
        <span class="hero-weather"><?php echo esc_html($weather_text); ?></span>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- STATS STRIP -->
<?php
$stats = [
  [ get_theme_mod('sb_stat_1_num','15+'),  get_theme_mod('sb_stat_1_label','years in product & media') ],
  [ get_theme_mod('sb_stat_2_num','#1'),   get_theme_mod('sb_stat_2_label','US App Store at launch') ],
  [ get_theme_mod('sb_stat_3_num','100M'), get_theme_mod('sb_stat_3_label','monthly uniques reached') ],
  [ get_theme_mod('sb_stat_4_num','250K'), get_theme_mod('sb_stat_4_label','FOX Weather pre-orders') ],
];
?>
<div class="stats-strip">
  <div class="stats-inner">
    <?php foreach ($stats as [$num, $label]) : ?>
      <div>
        <div class="stat-num"><?php echo esc_html($num); ?></div>
        <div class="stat-label"><?php echo esc_html($label); ?></div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- RECENT WRITING -->
<section class="home-writing">
  <div class="container">
    <div class="section-heading">
      <h2><?php _e('Recent writing','stevebaron'); ?></h2>
      <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/writing/')); ?>"><?php _e('All posts →','stevebaron'); ?></a>
    </div>

    <?php
    $posts = new WP_Query(['post_type'=>'post','posts_per_page'=>5,'post_status'=>'publish','ignore_sticky_posts'=>1]);
    if ($posts->have_posts()) :
      $featured = true;
    ?>
    <div class="writing-grid">
      <!-- Featured post -->
      <div class="writing-featured">
        <?php while ($posts->have_posts()) : $posts->the_post(); if ($featured) : $featured = false; ?>
          <a href="<?php the_permalink(); ?>" style="text-decoration:none;color:inherit;display:block;">
            <?php if (has_post_thumbnail()) : ?>
              <?php the_post_thumbnail('sb-card', ['class'=>'post-image ph','style'=>'width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:var(--radius);margin-bottom:20px;']); ?>
            <?php else : ?>
              <div class="post-image ph" style="aspect-ratio:16/10;margin-bottom:20px;">feature image</div>
            <?php endif; ?>
            <div class="post-meta" style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
              <?php
              $cats = get_the_category();
              if ($cats) echo '<span class="chip">' . esc_html($cats[0]->name) . '</span>';
              ?>
              <span class="mono muted" style="font-size:12px;"><?php echo get_the_date(); ?> &middot; <?php echo stevebaron_reading_time(); ?></span>
            </div>
            <h3 style="font-size:26px;margin:0 0 10px;"><?php the_title(); ?></h3>
            <p style="color:var(--ink-2);font-size:15.5px;margin:0;"><?php echo stevebaron_excerpt(25); ?></p>
          </a>
        <?php endif; endwhile; ?>
      </div>

      <!-- List of next posts -->
      <div class="writing-list">
        <?php while ($posts->have_posts()) : $posts->the_post(); ?>
          <a href="<?php the_permalink(); ?>" class="writing-list-item">
            <div>
              <div class="tag"><?php $cats = get_the_category(); if ($cats) echo esc_html($cats[0]->name); ?></div>
              <h4><?php the_title(); ?></h4>
            </div>
            <div class="date"><?php echo get_the_date('M d'); ?></div>
          </a>
        <?php endwhile; ?>
      </div>
    </div>
    <?php endif; wp_reset_postdata(); ?>
  </div>
</section>

<!-- PROJECTS PREVIEW -->
<section class="home-projects">
  <div class="container">
    <span class="eyebrow"><?php _e('Selected work','stevebaron'); ?></span>
    <div class="section-heading" style="margin-top:16px;">
      <h2 style="max-width:540px;font-size:36px;"><?php _e("Things I've shipped, broken, and occasionally fixed.",'stevebaron'); ?></h2>
      <a href="<?php echo esc_url(home_url('/projects/')); ?>"><?php _e('All projects →','stevebaron'); ?></a>
    </div>

    <?php
    $projects = new WP_Query(['post_type'=>'sb_project','posts_per_page'=>3,'orderby'=>'menu_order','order'=>'ASC']);
    if ($projects->have_posts()) :
    ?>
    <div class="projects-grid">
      <?php while ($projects->have_posts()) : $projects->the_post(); ?>
        <?php get_template_part('template-parts/project-card'); ?>
      <?php endwhile; ?>
    </div>
    <?php endif; wp_reset_postdata(); ?>
  </div>
</section>

<?php get_footer(); ?>
