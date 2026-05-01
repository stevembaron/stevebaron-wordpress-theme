<?php
/**
 * Template part: project card (used on home + projects page)
 */
$year   = get_post_meta(get_the_ID(),'_sb_year',true);
$status = get_post_meta(get_the_ID(),'_sb_status',true) ?: 'Active';
$link   = get_post_meta(get_the_ID(),'_sb_link',true);
$href   = $link ?: get_the_permalink();
$status_class = 'status-' . strtolower($status);
?>
<a href="<?php echo esc_url($href); ?>" class="project-card" <?php if ($link) echo 'target="_blank" rel="noopener noreferrer"'; ?>>
  <?php if (has_post_thumbnail()) : ?>
    <?php the_post_thumbnail('sb-card', ['class'=>'project-image','style'=>'width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:var(--radius);']); ?>
  <?php else : ?>
    <div class="project-image ph"><?php the_title(); ?> · screenshot</div>
  <?php endif; ?>
  <div class="project-card-meta">
    <h3><?php the_title(); ?></h3>
    <?php if ($year) : ?><span class="year"><?php echo esc_html($year); ?></span><?php endif; ?>
  </div>
  <p><?php echo stevebaron_excerpt(15); ?></p>
</a>
