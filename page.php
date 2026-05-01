<?php get_header(); ?>

<?php while (have_posts()) : the_post(); ?>
<section class="page-content">
  <div class="container-narrow">
    <span class="eyebrow"><?php echo esc_html(get_post_type_object(get_post_type())->labels->singular_name ?? 'Page'); ?></span>
    <h1 class="page-title"><?php the_title(); ?></h1>
    <div class="entry-content">
      <?php the_content(); ?>
    </div>
  </div>
</section>
<?php endwhile; ?>

<?php get_footer(); ?>
