<?php
/**
 * Template Name: CV
 */
get_header();

$pdf_url  = get_theme_mod('sb_cv_pdf_url','');
$tagline  = get_theme_mod('sb_cv_tagline','Product, AI & Digital Transformation Executive. Salt Lake City, UT.');
$skills   = get_theme_mod('sb_skills','Product Strategy, Artificial Intelligence (AI), Generative AI, Retrieval-Augmented Generation (RAG), Digital Transformation, Go-to-Market Strategy, Executive Leadership, App Store Optimization (ASO), SEO, Mobile Applications, OTT, Streaming Media, Live Streaming, Content Strategy, WordPress VIP, Platform Modernization, Cross-functional Team Leadership, Mergers & Acquisitions, Change Management, Board Leadership, Advisory');

$sections = ['Experience','Education','Recognition'];
?>

<section class="cv-hero">
  <div class="container-narrow">
    <div class="cv-header-row">
      <span class="eyebrow"><?php _e('Curriculum Vitae','stevebaron'); ?></span>
      <?php if ($pdf_url) : ?>
        <a href="<?php echo esc_url($pdf_url); ?>" class="mono muted" style="font-size:12px;text-decoration:none;" download>↓ pdf</a>
      <?php endif; ?>
    </div>
    <h1 class="cv-title"><?php bloginfo('name'); ?></h1>
    <?php if ($tagline) : ?>
      <p class="cv-tagline"><?php echo esc_html($tagline); ?></p>
    <?php endif; ?>

    <?php foreach ($sections as $section) : ?>
      <?php
      $entries = new WP_Query([
        'post_type'      => 'sb_experience',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
        'post_status'    => 'publish',
        'tax_query'      => [[
          'taxonomy' => 'sb_cv_section',
          'field'    => 'name',
          'terms'    => $section,
        ]],
      ]);
      if (!$entries->have_posts()) continue;
      ?>
      <div class="cv-section">
        <h2 class="cv-section-heading"><?php echo esc_html($section); ?></h2>
        <div class="cv-entries">
          <?php while ($entries->have_posts()) : $entries->the_post(); ?>
            <div class="cv-entry">
              <div class="cv-entry-dates"><?php echo esc_html(get_post_meta(get_the_ID(),'_sb_dates',true)); ?></div>
              <div>
                <div class="cv-entry-role"><?php the_title(); ?></div>
                <div class="cv-entry-org"><?php echo esc_html(get_post_meta(get_the_ID(),'_sb_org',true)); ?></div>
                <?php $blurb = get_the_content(); if ($blurb) : ?>
                  <p class="cv-entry-blurb"><?php echo wp_kses_post(wpautop($blurb)); ?></p>
                <?php endif; ?>
              </div>
            </div>
          <?php endwhile; wp_reset_postdata(); ?>
        </div>
      </div>
    <?php endforeach; ?>

    <?php if ($skills) : ?>
      <div class="cv-skills">
        <h2><?php _e('Tools I reach for','stevebaron'); ?></h2>
        <div class="skills-chips">
          <?php foreach (array_map('trim', explode(',', $skills)) as $skill) : ?>
            <span class="chip"><?php echo esc_html($skill); ?></span>
          <?php endforeach; ?>
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
