<?php
/**
 * Steve Baron Theme — functions.php
 */

define( 'STEVEBARON_VERSION', '1.0.0' );

// ── Theme setup ──────────────────────────────────────────────────────────────

function stevebaron_setup() {
	load_theme_textdomain( 'stevebaron', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'custom-logo', [
		'height'      => 40,
		'width'       => 160,
		'flex-height' => true,
		'flex-width'  => true,
	] );
	add_theme_support( 'custom-background' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'align-wide' );

	register_nav_menus( [
		'primary' => __( 'Primary Menu', 'stevebaron' ),
		'footer'  => __( 'Footer Menu', 'stevebaron' ),
	] );

	// Image sizes
	add_image_size( 'sb-hero',    1280, 720,  true );
	add_image_size( 'sb-card',    800,  600,  true );
	add_image_size( 'sb-square',  600,  600,  true );
	add_image_size( 'sb-photo',   1200, 1600, false );
}
add_action( 'after_setup_theme', 'stevebaron_setup' );

// ── Enqueue scripts & styles ──────────────────────────────────────────────────

function stevebaron_scripts() {
	// Google Fonts
	wp_enqueue_style(
		'stevebaron-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Inter+Tight:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,500&family=JetBrains+Mono:wght@400;500&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'stevebaron-main',
		get_template_directory_uri() . '/assets/css/main.css',
		[ 'stevebaron-fonts' ],
		STEVEBARON_VERSION
	);

	wp_enqueue_script(
		'stevebaron-main',
		get_template_directory_uri() . '/assets/js/main.js',
		[],
		STEVEBARON_VERSION,
		true
	);

	// Pass theme data to JS
	wp_localize_script( 'stevebaron-main', 'SB', [
		'themeUri' => get_template_directory_uri(),
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
	] );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'stevebaron_scripts' );

// ── Dynamic accent color from Customizer ─────────────────────────────────────

function stevebaron_customizer_css() {
	$accent     = get_theme_mod( 'sb_accent_color', '#c2410c' );
	$accent_2   = get_theme_mod( 'sb_accent_2_color', '#7c2d12' );
	$hero_var   = get_theme_mod( 'sb_hero_variant', 'topo' );
	echo '<style id="sb-customizer-css">';
	echo ':root{--accent:' . esc_attr( $accent ) . ';--accent-2:' . esc_attr( $accent_2 ) . ';}';
	echo '</style>';
	echo '<script>window.SB_HERO_VARIANT=' . wp_json_encode( $hero_var ) . ';</script>';
}
add_action( 'wp_head', 'stevebaron_customizer_css', 5 );

// ── Includes ──────────────────────────────────────────────────────────────────

require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/post-types.php';
require get_template_directory() . '/inc/meta-boxes.php';

// ── Helper: reading time ──────────────────────────────────────────────────────

function stevebaron_reading_time( $post_id = null ) {
	$content    = get_post_field( 'post_content', $post_id ?: get_the_ID() );
	$word_count = str_word_count( wp_strip_all_tags( $content ) );
	$minutes    = max( 1, (int) ceil( $word_count / 200 ) );
	return $minutes . ' min read';
}

// ── Helper: social icon SVG ───────────────────────────────────────────────────

function stevebaron_social_icon( string $network ): string {
	$icons = [
		'linkedin'  => '<path d="M4 4h3v12H4zM5.5 2.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zM9 8h3v1.5h.05A3.3 3.3 0 0115 7.8c3 0 3.5 2 3.5 4.6V20h-3v-6.4c0-1.5-.03-3.5-2.1-3.5-2.1 0-2.4 1.6-2.4 3.4V20H8z"/>',
		'twitter'   => '<path d="M18 4.5L13 10l6 9.5h-4.2L11 13.7 6.5 19.5H4l5.5-6.5L4 4.5h4.2L11.5 9 16 4.5z"/>',
		'facebook'  => '<path d="M13 21v-7h2.4l.4-3H13V9.2c0-.9.3-1.5 1.5-1.5H16V5c-.3 0-1.3-.1-2.5-.1-2.4 0-4 1.4-4 4.1V11H7v3h2.5v7H13z"/>',
		'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="12" cy="12" r="4" fill="none" stroke="currentColor" stroke-width="1.6"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>',
		'github'    => '<path d="M12 2a10 10 0 00-3.16 19.5c.5.1.7-.2.7-.5v-1.7c-2.8.6-3.4-1.3-3.4-1.3-.5-1.2-1.1-1.5-1.1-1.5-.9-.6.07-.6.07-.6 1 .07 1.5 1 1.5 1 .9 1.5 2.4 1.1 3 .8.1-.6.4-1.1.7-1.4-2.2-.25-4.5-1.1-4.5-5 0-1.1.4-2 1-2.7-.1-.3-.5-1.3.1-2.6 0 0 .8-.27 2.7 1a9.4 9.4 0 015 0c1.9-1.27 2.7-1 2.7-1 .6 1.3.2 2.3.1 2.6.6.7 1 1.6 1 2.7 0 3.9-2.3 4.75-4.5 5 .35.3.7.9.7 1.8v2.7c0 .3.2.6.7.5A10 10 0 0012 2z"/>',
		'email'     => '<path d="M3 6h18v12H3z" fill="none" stroke="currentColor" stroke-width="1.6"/><path d="M3 6l9 7 9-7" fill="none" stroke="currentColor" stroke-width="1.6"/>',
	];
	return $icons[ $network ] ?? '';
}

function stevebaron_social_links_html( array $networks = [] ): string {
	if ( empty( $networks ) ) {
		$networks = [ 'linkedin', 'twitter', 'facebook', 'instagram', 'github', 'email' ];
	}
	$labels = [
		'linkedin'  => 'LinkedIn',
		'twitter'   => 'Twitter / X',
		'facebook'  => 'Facebook',
		'instagram' => 'Instagram',
		'github'    => 'GitHub',
		'email'     => 'Email',
	];
	$out = '<div class="social-icons">';
	foreach ( $networks as $net ) {
		$url = get_theme_mod( 'sb_social_' . $net, '' );
		if ( ! $url ) continue;
		if ( $net === 'email' ) $url = 'mailto:' . sanitize_email( $url );
		$icon = stevebaron_social_icon( $net );
		$label = $labels[ $net ] ?? ucfirst( $net );
		$out .= sprintf(
			'<a href="%s" class="social-icon" aria-label="%s" %s><svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor">%s</svg></a>',
			esc_url( $url ),
			esc_attr( $label ),
			( $net !== 'email' ) ? 'target="_blank" rel="noopener noreferrer"' : '',
			$icon
		);
	}
	$out .= '</div>';
	return $out;
}

// ── Walker: clean nav menu ────────────────────────────────────────────────────

class Stevebaron_Nav_Walker extends Walker_Nav_Menu {
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes   = empty( $item->classes ) ? [] : (array) $item->classes;
		$is_active = in_array( 'current-menu-item', $classes ) || in_array( 'current-page-ancestor', $classes );
		$url       = $item->url ?: '#';
		$title     = apply_filters( 'the_title', $item->title, $item->ID );
		$output   .= sprintf(
			'<a href="%s" class="%s">%s</a>',
			esc_url( $url ),
			$is_active ? 'active' : '',
			esc_html( $title )
		);
	}
}

// ── Disable emoji (lightweight theme) ────────────────────────────────────────

remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// ── Excerpt length ────────────────────────────────────────────────────────────

add_filter( 'excerpt_length', fn() => 30 );
add_filter( 'excerpt_more', fn() => '…' );

// ── Custom excerpt (auto-generated for cards) ─────────────────────────────────

function stevebaron_excerpt( int $length = 20 ): string {
	if ( has_excerpt() ) {
		return esc_html( get_the_excerpt() );
	}
	return esc_html( wp_trim_words( get_the_content(), $length ) );
}
