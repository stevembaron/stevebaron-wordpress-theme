<?php
/**
 * Steve Baron Theme — functions.php
 */

define( 'STEVEBARON_VERSION', '1.7.0' );
define( 'STEVEBARON_DIR', get_template_directory() );
define( 'STEVEBARON_URI', get_template_directory_uri() );

// ── Theme setup ──────────────────────────────────────────────────────────────

function stevebaron_setup() {
	load_theme_textdomain( 'stevebaron', STEVEBARON_DIR . '/languages' );

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
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'automatic-feed-links' );

	register_nav_menus( [
		'primary' => __( 'Primary Menu', 'stevebaron' ),
		'footer'  => __( 'Footer Menu', 'stevebaron' ),
	] );

	add_image_size( 'sb-hero',    1280, 720,  true );
	add_image_size( 'sb-card',    800,  600,  true );
	add_image_size( 'sb-square',  600,  600,  true );
	add_image_size( 'sb-photo',   1200, 1600, false );
}
add_action( 'after_setup_theme', 'stevebaron_setup' );

// ── Asset version: filemtime when readable, fallback to theme version ────────

function stevebaron_asset_version( string $relative_path ): string {
	$abs = STEVEBARON_DIR . '/' . ltrim( $relative_path, '/' );
	return file_exists( $abs ) ? (string) filemtime( $abs ) : STEVEBARON_VERSION;
}

// ── Enqueue scripts & styles ──────────────────────────────────────────────────

function stevebaron_scripts() {
	// Google Fonts (preconnect handled in header.php)
	wp_enqueue_style(
		'stevebaron-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Inter+Tight:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,500&family=JetBrains+Mono:wght@400;500&display=swap',
		[],
		null
	);

	wp_enqueue_style(
		'stevebaron-main',
		STEVEBARON_URI . '/assets/css/main.css',
		[ 'stevebaron-fonts' ],
		stevebaron_asset_version( 'assets/css/main.css' )
	);

	wp_enqueue_script(
		'stevebaron-main',
		STEVEBARON_URI . '/assets/js/main.js',
		[],
		stevebaron_asset_version( 'assets/js/main.js' ),
		[ 'in_footer' => true, 'strategy' => 'defer' ]
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'stevebaron_scripts' );

// ── Inline customizer CSS + hero variant flag ────────────────────────────────

function stevebaron_customizer_css() {
	$accent   = sanitize_hex_color( get_theme_mod( 'sb_accent_color', '#c2410c' ) ) ?: '#c2410c';
	$accent_2 = sanitize_hex_color( get_theme_mod( 'sb_accent_2_color', '#7c2d12' ) ) ?: '#7c2d12';
	$hero_var = get_theme_mod( 'sb_hero_variant', 'topo' );
	echo '<style id="sb-customizer-css">:root{--accent:' . esc_attr( $accent ) . ';--accent-2:' . esc_attr( $accent_2 ) . ';}</style>' . "\n";
	echo '<script>window.SB=' . wp_json_encode( [
		'home' => home_url( '/' ),
	] ) . ';window.SB_HERO_VARIANT=' . wp_json_encode( $hero_var ) . ';</script>' . "\n";
}
add_action( 'wp_head', 'stevebaron_customizer_css', 5 );

// ── Dynamic SVG favicon keyed to accent color ────────────────────────────────

function stevebaron_dynamic_favicon() {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) return;
	$accent = sanitize_hex_color( get_theme_mod( 'sb_accent_color', '#c2410c' ) ) ?: '#c2410c';
	$svg    = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'
		. '<rect width="100" height="100" rx="22" fill="' . esc_attr( $accent ) . '"/>'
		. '<text x="50" y="70" font-family="ui-sans-serif,system-ui,-apple-system,Segoe UI,sans-serif" font-size="62" font-weight="800" text-anchor="middle" fill="#fff">S</text>'
		. '</svg>';
	echo '<link rel="icon" type="image/svg+xml" href="data:image/svg+xml,' . rawurlencode( $svg ) . '">' . "\n";
}
add_action( 'wp_head', 'stevebaron_dynamic_favicon', 4 );

// ── JSON-LD structured data ──────────────────────────────────────────────────

function stevebaron_jsonld() {
	if ( is_admin() ) return;
	$home = home_url( '/' );

	if ( is_singular( 'post' ) ) {
		global $post;
		$schema = [
			'@context'         => 'https://schema.org',
			'@type'            => 'BlogPosting',
			'headline'         => get_the_title( $post ),
			'datePublished'    => get_the_date( 'c', $post ),
			'dateModified'     => get_the_modified_date( 'c', $post ),
			'mainEntityOfPage' => get_permalink( $post ),
			'author'           => [
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', $post->post_author ),
				'url'   => $home,
			],
			'publisher'        => [
				'@type' => 'Person',
				'name'  => get_bloginfo( 'name' ),
				'url'   => $home,
			],
		];
		if ( has_post_thumbnail( $post ) ) {
			$schema['image'] = get_the_post_thumbnail_url( $post, 'sb-hero' );
		}
		$desc = has_excerpt( $post )
			? wp_strip_all_tags( get_the_excerpt( $post ) )
			: wp_trim_words( wp_strip_all_tags( $post->post_content ), 30 );
		if ( $desc ) $schema['description'] = $desc;
		echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
		return;
	}

	if ( is_front_page() || is_page( [ 'about', 'cv', 'contact' ] ) ) {
		$social = array_values( array_filter( [
			get_theme_mod( 'sb_social_linkedin', '' ),
			get_theme_mod( 'sb_social_twitter', '' ),
			get_theme_mod( 'sb_social_github', '' ),
			get_theme_mod( 'sb_social_instagram', '' ),
			get_theme_mod( 'sb_social_facebook', '' ),
		] ) );
		$schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Person',
			'name'        => get_bloginfo( 'name' ),
			'url'         => $home,
			'jobTitle'    => 'Product, AI & Digital Transformation Executive',
			'description' => get_theme_mod( 'sb_hero_subtext', get_bloginfo( 'description' ) ),
			'address'     => [
				'@type'           => 'PostalAddress',
				'addressLocality' => 'Salt Lake City',
				'addressRegion'   => 'UT',
				'addressCountry'  => 'US',
			],
		];
		$email = get_theme_mod( 'sb_social_email', '' );
		if ( $email ) $schema['email'] = 'mailto:' . sanitize_email( $email );
		if ( $social ) $schema['sameAs'] = $social;
		if ( has_custom_logo() ) {
			$logo_id = get_theme_mod( 'custom_logo' );
			$img     = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
			if ( $img ) $schema['image'] = $img;
		}

		$website = [
			'@context' => 'https://schema.org',
			'@type'    => 'WebSite',
			'url'      => $home,
			'name'     => get_bloginfo( 'name' ),
			'potentialAction' => [
				'@type'       => 'SearchAction',
				'target'      => $home . '?s={search_term_string}',
				'query-input' => 'required name=search_term_string',
			],
		];

		echo "\n<script type=\"application/ld+json\">" . wp_json_encode( $schema,  JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
		echo "<script type=\"application/ld+json\">"   . wp_json_encode( $website, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "</script>\n";
	}
}
add_action( 'wp_head', 'stevebaron_jsonld', 8 );

// ── Open Graph + Twitter Card meta ───────────────────────────────────────────

function stevebaron_meta_tags() {
	if ( is_admin() ) return;

	$site_name = get_bloginfo( 'name' );
	$desc      = get_bloginfo( 'description' );
	$url       = home_url( add_query_arg( null, null ) );
	$image     = '';

	if ( is_singular() ) {
		global $post;
		$desc  = has_excerpt( $post )
			? wp_strip_all_tags( get_the_excerpt( $post ) )
			: wp_trim_words( wp_strip_all_tags( $post->post_content ?? '' ), 30 );
		$url   = get_permalink( $post );
		if ( has_post_thumbnail( $post ) ) {
			$image = get_the_post_thumbnail_url( $post, 'sb-hero' );
		}
	}
	if ( ! $image && has_custom_logo() ) {
		$logo_id = get_theme_mod( 'custom_logo' );
		$image   = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	}
	if ( ! $image ) {
		// Theme-provided default OG card (1200x630 PNG)
		$default_og = STEVEBARON_DIR . '/assets/og-default.png';
		if ( file_exists( $default_og ) ) {
			$image = STEVEBARON_URI . '/assets/og-default.png?v=' . filemtime( $default_og );
		}
	}

	$title = wp_get_document_title();
	echo "<meta name=\"description\" content=\"" . esc_attr( $desc ) . "\">\n";
	echo "<meta property=\"og:type\" content=\"" . ( is_singular( 'post' ) ? 'article' : 'website' ) . "\">\n";
	echo "<meta property=\"og:title\" content=\"" . esc_attr( $title ) . "\">\n";
	echo "<meta property=\"og:description\" content=\"" . esc_attr( $desc ) . "\">\n";
	echo "<meta property=\"og:url\" content=\"" . esc_url( $url ) . "\">\n";
	echo "<meta property=\"og:site_name\" content=\"" . esc_attr( $site_name ) . "\">\n";
	if ( $image ) {
		echo "<meta property=\"og:image\" content=\"" . esc_url( $image ) . "\">\n";
		echo "<meta property=\"og:image:width\" content=\"1200\">\n";
		echo "<meta property=\"og:image:height\" content=\"630\">\n";
		echo "<meta property=\"og:image:alt\" content=\"" . esc_attr( $title ) . "\">\n";
	}
	echo "<meta name=\"twitter:card\" content=\"" . ( $image ? 'summary_large_image' : 'summary' ) . "\">\n";
	$twitter = get_theme_mod( 'sb_social_twitter', '' );
	if ( $twitter ) {
		$handle = '@' . ltrim( basename( untrailingslashit( $twitter ) ), '@' );
		echo "<meta name=\"twitter:site\" content=\"" . esc_attr( $handle ) . "\">\n";
	}
}
add_action( 'wp_head', 'stevebaron_meta_tags', 6 );

// ── Includes ──────────────────────────────────────────────────────────────────

require STEVEBARON_DIR . '/inc/customizer.php';
require STEVEBARON_DIR . '/inc/post-types.php';
require STEVEBARON_DIR . '/inc/meta-boxes.php';
require STEVEBARON_DIR . '/inc/setup-site.php';
require STEVEBARON_DIR . '/inc/contact-form.php';

// ── Helper: reading time ──────────────────────────────────────────────────────

function stevebaron_reading_time( $post_id = null ) {
	$content    = get_post_field( 'post_content', $post_id ?: get_the_ID() );
	$word_count = str_word_count( wp_strip_all_tags( $content ) );
	$minutes    = max( 1, (int) ceil( $word_count / 200 ) );
	/* translators: %d: number of minutes */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'stevebaron' ), $minutes );
}

// ── Helper: social icon SVG paths ────────────────────────────────────────────

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
		$icon  = stevebaron_social_icon( $net );
		$label = $labels[ $net ] ?? ucfirst( $net );
		$attrs = $net !== 'email' ? ' target="_blank" rel="noopener noreferrer"' : '';
		$out .= sprintf(
			'<a href="%s" class="social-icon" aria-label="%s"%s><svg viewBox="0 0 24 24" width="15" height="15" fill="currentColor" aria-hidden="true">%s</svg></a>',
			esc_url( $url ),
			esc_attr( $label ),
			$attrs,
			$icon
		);
	}
	$out .= '</div>';
	return $out;
}

// ── Walker: clean nav menu (with sub-menu support) ────────────────────────────

class Stevebaron_Nav_Walker extends Walker_Nav_Menu {
	public function start_lvl( &$output, $depth = 0, $args = null ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "\n{$indent}<ul class=\"sub-menu\">\n";
	}

	public function end_lvl( &$output, $depth = 0, $args = null ) {
		$indent  = str_repeat( "\t", $depth );
		$output .= "{$indent}</ul>\n";
	}

	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes   = empty( $item->classes ) ? [] : (array) $item->classes;
		$is_active = in_array( 'current-menu-item', $classes, true ) || in_array( 'current-page-ancestor', $classes, true );
		$url       = $item->url ?: '#';
		$title     = apply_filters( 'the_title', $item->title, $item->ID );
		$target    = ! empty( $item->target ) ? sprintf( ' target="%s"', esc_attr( $item->target ) ) : '';
		$rel       = ! empty( $item->xfn )    ? sprintf( ' rel="%s"',    esc_attr( $item->xfn ) ) : '';
		$output   .= sprintf(
			'<a href="%s" class="%s"%s%s>%s</a>',
			esc_url( $url ),
			$is_active ? 'active' : '',
			$target,
			$rel,
			esc_html( $title )
		);
	}

	public function end_el( &$output, $item, $depth = 0, $args = null ) {
		$output .= "\n";
	}
}

// ── Excerpt length / "more" ──────────────────────────────────────────────────

add_filter( 'excerpt_length', fn() => 30 );
add_filter( 'excerpt_more',   fn() => '…' );

// ── Custom excerpt for cards ─────────────────────────────────────────────────

function stevebaron_excerpt( int $length = 20 ): string {
	if ( has_excerpt() ) {
		return esc_html( get_the_excerpt() );
	}
	return esc_html( wp_trim_words( get_the_content(), $length ) );
}

// ── Head cleanup: remove WP bloat ────────────────────────────────────────────

remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
remove_action( 'admin_print_styles', 'print_emoji_styles' );
remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
remove_filter( 'comment_text_rss',  'wp_staticize_emoji' );
remove_filter( 'wp_mail',           'wp_staticize_emoji_for_email' );

// Strip ?ver= from src URLs by removing version query (kept for cache busting via path query)
add_filter( 'the_generator', '__return_empty_string' );

// ── Default image attrs: lazy + async decode (WP already adds loading=lazy
//    for in-content images; this extends to the_post_thumbnail) ──────────────

function stevebaron_thumbnail_attrs( $attr ) {
	if ( ! isset( $attr['loading'] ) )  $attr['loading']  = 'lazy';
	if ( ! isset( $attr['decoding'] ) ) $attr['decoding'] = 'async';
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'stevebaron_thumbnail_attrs' );

// ── Skip-link focus fix is handled in CSS; a11y body class ───────────────────

function stevebaron_body_class( $classes ) {
	if ( is_singular() && has_post_thumbnail() ) $classes[] = 'has-featured-image';
	return $classes;
}
add_filter( 'body_class', 'stevebaron_body_class' );
