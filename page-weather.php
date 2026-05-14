<?php
/**
 * Template Name: Weather
 *
 * Live SLC conditions and forecast from the National Weather Service.
 * Pure-front-end: all data is fetched client-side in main.js. The PHP
 * here is just structure + placeholders.
 */
get_header();
?>

<section class="weather-hero">
  <div class="container-narrow">
    <span class="eyebrow"><?php _e( 'Weather kit', 'stevebaron' ); ?></span>
    <h1 class="weather-title"><?php _e( 'Salt Lake City', 'stevebaron' ); ?></h1>
    <p class="weather-lead" data-weather-updated>
      <?php _e( 'Loading current conditions from the National Weather Service…', 'stevebaron' ); ?>
    </p>
  </div>
</section>

<section class="weather-now" data-weather-now>
  <div class="container">
    <div class="weather-now-grid">

      <div class="weather-now-primary">
        <div class="weather-icon" data-wx-icon>—</div>
        <div class="weather-temp">
          <span data-wx-temp>—</span><span class="weather-deg">°<span data-wx-unit>F</span></span>
        </div>
        <div class="weather-shortforecast" data-wx-short>—</div>
      </div>

      <dl class="weather-stats">
        <div><dt><?php _e( 'Feels like', 'stevebaron' ); ?></dt><dd data-wx-feels>—</dd></div>
        <div><dt><?php _e( 'Wind', 'stevebaron' ); ?></dt><dd data-wx-wind>—</dd></div>
        <div><dt><?php _e( 'Humidity', 'stevebaron' ); ?></dt><dd data-wx-humidity>—</dd></div>
        <div><dt><?php _e( 'Dew point', 'stevebaron' ); ?></dt><dd data-wx-dew>—</dd></div>
        <div><dt><?php _e( 'Pressure', 'stevebaron' ); ?></dt><dd data-wx-pressure>—</dd></div>
        <div><dt><?php _e( 'Visibility', 'stevebaron' ); ?></dt><dd data-wx-visibility>—</dd></div>
      </dl>

    </div>

    <div class="weather-sun" data-wx-sun>
      <div><span class="sun-key"><?php _e( 'Sunrise', 'stevebaron' ); ?></span> <span data-wx-sunrise>—</span></div>
      <div><span class="sun-key"><?php _e( 'Solar noon', 'stevebaron' ); ?></span> <span data-wx-noon>—</span></div>
      <div><span class="sun-key"><?php _e( 'Sunset', 'stevebaron' ); ?></span> <span data-wx-sunset>—</span></div>
      <div><span class="sun-key"><?php _e( 'Daylight', 'stevebaron' ); ?></span> <span data-wx-daylight>—</span></div>
    </div>
  </div>
</section>

<section class="weather-forecast">
  <div class="container">
    <h2 class="weather-section-heading"><?php _e( 'Seven-day forecast', 'stevebaron' ); ?></h2>
    <div class="forecast-grid" data-wx-forecast>
      <div class="forecast-placeholder mono muted"><?php _e( 'Loading forecast…', 'stevebaron' ); ?></div>
    </div>
  </div>
</section>

<?php if (have_posts()) : the_post(); if (get_the_content()) : ?>
  <section class="container-narrow" style="padding-bottom:var(--space-xl);">
    <div class="entry-content"><?php the_content(); ?></div>
  </section>
<?php endif; endif; ?>

<section class="weather-credit">
  <div class="container-narrow">
    <p class="mono muted">
      <?php
      printf(
        wp_kses(
          /* translators: %1$s = National Weather Service link, %2$s = sunrise-sunset.org link */
          __( 'Live data from %1$s · sunrise & sunset from %2$s · all times shown in your browser\'s local timezone.', 'stevebaron' ),
          [ 'a' => [ 'href' => true, 'target' => true, 'rel' => true ] ]
        ),
        '<a href="https://www.weather.gov/" target="_blank" rel="noopener">api.weather.gov</a>',
        '<a href="https://sunrise-sunset.org/" target="_blank" rel="noopener">sunrise-sunset.org</a>'
      );
      ?>
    </p>
  </div>
</section>

<?php get_footer(); ?>
