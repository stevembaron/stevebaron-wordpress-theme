<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>

<?php
$cats     = get_the_category();
$cat_name = $cats ? $cats[0]->name : '';
$cat_url  = $cats ? get_category_link($cats[0]->term_id) : '';
$tags     = get_the_tags();
?>

<article class="post-hero" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
  <div class="container-narrow">

    <div class="post-eyebrow">
      <?php if ($cat_name) : ?>
        <a href="<?php echo esc_url($cat_url); ?>" style="color:inherit;text-decoration:none;"><?php echo esc_html($cat_name); ?></a> &middot;
      <?php endif; ?>
      <?php echo get_the_date(); ?> &middot; <?php echo stevebaron_reading_time(); ?>
    </div>

    <h1 class="post-title"><?php the_title(); ?></h1>

    <?php if (has_excerpt()) : ?>
      <p class="post-summary"><?php the_excerpt(); ?></p>
    <?php endif; ?>

    <div class="post-byline">
      <?php
      $author_avatar = get_avatar(get_the_author_meta('email'), 44, '', get_the_author(), ['class'=>'post-avatar']);
      if ($author_avatar) echo $author_avatar;
      else echo '<div class="post-avatar-ph ph"></div>';
      ?>
      <div>
        <div class="post-author-name"><?php the_author(); ?></div>
        <?php $twitter = get_theme_mod('sb_social_twitter',''); if ($twitter) : ?>
          <div class="post-author-handle mono muted">@<?php echo esc_html(ltrim(basename($twitter),'@')); ?></div>
        <?php endif; ?>
      </div>
      <div class="post-share">
        <button class="btn" style="font-size:12px;padding:6px 12px;" onclick="navigator.share ? navigator.share({title:document.title,url:location.href}) : navigator.clipboard.writeText(location.href).then(()=>alert('Link copied!'))" >
          <?php _e('Share','stevebaron'); ?>
        </button>
      </div>
    </div>

    <?php if (has_post_thumbnail()) : ?>
      <?php the_post_thumbnail('sb-hero', ['class'=>'post-featured-image']); ?>
    <?php endif; ?>

    <div class="entry-content">
      <?php the_content(); ?>
    </div>

    <?php if ($tags) : ?>
      <div style="margin-top:var(--space-lg);display:flex;gap:8px;flex-wrap:wrap;">
        <?php foreach ($tags as $tag) : ?>
          <a href="<?php echo esc_url(get_tag_link($tag)); ?>" class="chip"><?php echo esc_html($tag->name); ?></a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <nav class="post-nav" aria-label="<?php _e('Post navigation','stevebaron'); ?>">
      <div>
        <?php previous_post_link('%link', '← %title'); ?>
      </div>
      <a href="<?php echo esc_url(get_permalink(get_option('page_for_posts')) ?: home_url('/writing/')); ?>">
        <?php _e('↑ All posts','stevebaron'); ?>
      </a>
      <div>
        <?php next_post_link('%link', '%title →'); ?>
      </div>
    </nav>

    <?php
    if (comments_open() || get_comments_number()) {
      comments_template();
    }
    ?>

  </div>
</article>

<?php endwhile; ?>

<?php get_footer(); ?>
