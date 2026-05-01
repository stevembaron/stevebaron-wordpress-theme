<?php
/**
 * Template Name: Projects
 */
get_header();
?>

<section class="projects-hero">
  <div class="container">
    <span class="eyebrow"><?php _e('Projects','stevebaron'); ?></span>
    <h1><?php the_title(); ?></h1>
    <?php if (have_posts()) : the_post(); if (get_the_excerpt()) : ?>
      <p style="color:var(--ink-2);font-size:18px;max-width:580px;"><?php the_excerpt(); ?></p>
    <?php endif; endif; ?>
  </div>
</section>

<section class="projects-list">
  <div class="container">
    <?php
    $projects = new WP_Query([
      'post_type'      => 'sb_project',
      'posts_per_page' => -1,
      'orderby'        => 'menu_order',
      'order'          => 'ASC',
      'post_status'    => 'publish',
    ]);
    $i = 1;
    if ($projects->have_posts()) :
      while ($projects->have_posts()) : $projects->the_post();
        $year   = get_post_meta(get_the_ID(),'_sb_year',true);
        $status = get_post_meta(get_the_ID(),'_sb_status',true) ?: 'Active';
        $link   = get_post_meta(get_the_ID(),'_sb_link',true);
        $href   = $link ?: get_the_permalink();
        $status_class = 'status-' . strtolower($status);
    ?>
      <div class="project-list-item">
        <div class="project-num">0<?php echo $i++; ?></div>
        <div>
          <div class="project-info-row">
            <h2><?php the_title(); ?></h2>
            <?php if ($year) : ?>
              <span class="project-when"><?php echo esc_html($year); ?></span>
            <?php endif; ?>
          </div>
          <p class="project-desc"><?php echo stevebaron_excerpt(25); ?></p>
          <?php if ($link) : ?>
            <a href="<?php echo esc_url($link); ?>" class="btn" style="margin-top:12px;font-size:13px;" target="_blank" rel="noopener">
              <?php _e('View project →','stevebaron'); ?>
            </a>
          <?php endif; ?>
        </div>
        <span class="status-chip <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status); ?></span>
      </div>
    <?php endwhile; wp_reset_postdata();
    else : ?>
      <p style="color:var(--ink-3);font-family:var(--font-mono);font-size:13px;padding:var(--space-lg) 0;">
        <?php _e('No projects yet — add some in Projects › New Project.','stevebaron'); ?>
      </p>
    <?php endif; ?>
  </div>
</section>

<?php get_footer(); ?>
