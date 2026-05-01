<?php
/**
 * Theme Customizer settings
 */

function stevebaron_customize_register( WP_Customize_Manager $wp_customize ) {

	// ── Hero section ──────────────────────────────────────────────────────────

	$wp_customize->add_section( 'sb_hero', [
		'title'    => __( 'Hero / Landing', 'stevebaron' ),
		'priority' => 30,
	] );

	$wp_customize->add_setting( 'sb_hero_headline', [
		'default'           => "Hi, I'm Steve.\nI make things on the\ninternet, mostly\nabout weather & media.",
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'sb_hero_headline', [
		'label'   => __( 'Hero headline (line breaks preserved)', 'stevebaron' ),
		'section' => 'sb_hero',
		'type'    => 'textarea',
	] );

	$wp_customize->add_setting( 'sb_hero_subtext', [
		'default'           => 'Product person, longtime weather nerd, occasional skier. Currently building something at the intersection of forecasting and storytelling. Previously digital media at a place you\'ve heard of.',
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'sb_hero_subtext', [
		'label'   => __( 'Hero subtext', 'stevebaron' ),
		'section' => 'sb_hero',
		'type'    => 'textarea',
	] );

	$wp_customize->add_setting( 'sb_hero_cta_label', [
		'default'           => 'Read the latest →',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_hero_cta_label', [
		'label'   => __( 'Primary CTA label', 'stevebaron' ),
		'section' => 'sb_hero',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'sb_hero_cta_url', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	] );
	$wp_customize->add_control( 'sb_hero_cta_url', [
		'label'       => __( 'Primary CTA URL (leave blank for blog)', 'stevebaron' ),
		'section'     => 'sb_hero',
		'type'        => 'url',
	] );

	$wp_customize->add_setting( 'sb_hero_cv_label', [
		'default'           => 'Download CV',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_hero_cv_label', [
		'label'   => __( 'Secondary CTA label', 'stevebaron' ),
		'section' => 'sb_hero',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'sb_hero_weather', [
		'default'           => '☀ 64°F · clear skies over the Wasatch',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_hero_weather', [
		'label'   => __( 'Weather text (update manually or leave blank)', 'stevebaron' ),
		'section' => 'sb_hero',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'sb_hero_eyebrow', [
		'default'           => 'Salt Lake City · 40.76° N',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_hero_eyebrow', [
		'label'   => __( 'Hero eyebrow text', 'stevebaron' ),
		'section' => 'sb_hero',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'sb_hero_variant', [
		'default'           => 'topo',
		'sanitize_callback' => function( $v ) { return in_array( $v, [ 'topo', 'mountains', 'plain' ] ) ? $v : 'topo'; },
	] );
	$wp_customize->add_control( 'sb_hero_variant', [
		'label'   => __( 'Hero background style', 'stevebaron' ),
		'section' => 'sb_hero',
		'type'    => 'select',
		'choices' => [
			'topo'      => __( 'Topo lines', 'stevebaron' ),
			'mountains' => __( 'Mountain silhouette', 'stevebaron' ),
			'plain'     => __( 'Plain (no graphic)', 'stevebaron' ),
		],
	] );

	// ── Stats strip ───────────────────────────────────────────────────────────

	$stats = [
		[ 'sb_stat_1_num', 'sb_stat_1_label', '12y', 'in product' ],
		[ 'sb_stat_2_num', 'sb_stat_2_label', '3',   'states lived in' ],
		[ 'sb_stat_3_num', 'sb_stat_3_label', '140in', 'average snow on Alta' ],
		[ 'sb_stat_4_num', 'sb_stat_4_label', '∞',   'weather charts saved' ],
	];
	foreach ( $stats as [ $num_key, $label_key, $num_def, $label_def ] ) {
		$wp_customize->add_setting( $num_key, [ 'default' => $num_def, 'sanitize_callback' => 'sanitize_text_field' ] );
		$wp_customize->add_control( $num_key, [ 'label' => sprintf( __( 'Stat: %s — number', 'stevebaron' ), $num_def ), 'section' => 'sb_hero', 'type' => 'text' ] );
		$wp_customize->add_setting( $label_key, [ 'default' => $label_def, 'sanitize_callback' => 'sanitize_text_field' ] );
		$wp_customize->add_control( $label_key, [ 'label' => sprintf( __( 'Stat: %s — label', 'stevebaron' ), $label_def ), 'section' => 'sb_hero', 'type' => 'text' ] );
	}

	// ── Colors ────────────────────────────────────────────────────────────────

	$wp_customize->add_section( 'sb_colors', [
		'title'    => __( 'Theme Colors', 'stevebaron' ),
		'priority' => 35,
	] );

	$wp_customize->add_setting( 'sb_accent_color', [
		'default'           => '#c2410c',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sb_accent_color', [
		'label'   => __( 'Accent color (ember orange)', 'stevebaron' ),
		'section' => 'sb_colors',
	] ) );

	$wp_customize->add_setting( 'sb_accent_2_color', [
		'default'           => '#7c2d12',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'sb_accent_2_color', [
		'label'   => __( 'Accent 2 color (deep ember)', 'stevebaron' ),
		'section' => 'sb_colors',
	] ) );

	// ── Social links ──────────────────────────────────────────────────────────

	$wp_customize->add_section( 'sb_social', [
		'title'    => __( 'Social Links', 'stevebaron' ),
		'priority' => 40,
	] );

	$socials = [
		'linkedin'  => [ 'LinkedIn URL', 'https://linkedin.com/in/stevembaron' ],
		'twitter'   => [ 'Twitter/X URL', 'https://twitter.com/stevebaron' ],
		'facebook'  => [ 'Facebook URL', '' ],
		'instagram' => [ 'Instagram URL', 'https://instagram.com/stevebaron' ],
		'github'    => [ 'GitHub URL', 'https://github.com/stevebaron' ],
		'email'     => [ 'Email address', 'hi@stevebaron.com' ],
	];
	foreach ( $socials as $key => [ $label, $default ] ) {
		$wp_customize->add_setting( 'sb_social_' . $key, [
			'default'           => $default,
			'sanitize_callback' => $key === 'email' ? 'sanitize_email' : 'esc_url_raw',
		] );
		$wp_customize->add_control( 'sb_social_' . $key, [
			'label'   => __( $label, 'stevebaron' ),
			'section' => 'sb_social',
			'type'    => $key === 'email' ? 'email' : 'url',
		] );
	}

	// ── About page ────────────────────────────────────────────────────────────

	$wp_customize->add_section( 'sb_about', [
		'title'    => __( 'About Page', 'stevebaron' ),
		'priority' => 42,
	] );

	$wp_customize->add_setting( 'sb_headshot', [
		'default'           => '',
		'sanitize_callback' => 'absint',
	] );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'sb_headshot', [
		'label'     => __( 'Headshot image', 'stevebaron' ),
		'section'   => 'sb_about',
		'mime_type' => 'image',
	] ) );

	// ── CV page ───────────────────────────────────────────────────────────────

	$wp_customize->add_section( 'sb_cv', [
		'title'    => __( 'CV / Résumé', 'stevebaron' ),
		'priority' => 44,
	] );

	$wp_customize->add_setting( 'sb_cv_pdf_url', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	] );
	$wp_customize->add_control( 'sb_cv_pdf_url', [
		'label'       => __( 'PDF download URL', 'stevebaron' ),
		'description' => __( 'Upload your CV PDF to the Media Library and paste the URL here.', 'stevebaron' ),
		'section'     => 'sb_cv',
		'type'        => 'url',
	] );

	$wp_customize->add_setting( 'sb_skills', [
		'default'           => 'Figma, Linear, Notion, SQL, React, Python, WordPress, Cloudflare, Mapbox, Mux',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_skills', [
		'label'       => __( 'Skills / tools (comma-separated)', 'stevebaron' ),
		'section'     => 'sb_cv',
		'type'        => 'text',
	] );

	$wp_customize->add_setting( 'sb_cv_tagline', [
		'default'           => 'Product · Digital Media · Weather. Salt Lake City, UT.',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_cv_tagline', [
		'label'   => __( 'CV tagline (under your name)', 'stevebaron' ),
		'section' => 'sb_cv',
		'type'    => 'text',
	] );

	// ── Contact page ──────────────────────────────────────────────────────────

	$wp_customize->add_section( 'sb_contact', [
		'title'    => __( 'Contact Page', 'stevebaron' ),
		'priority' => 46,
	] );

	$wp_customize->add_setting( 'sb_contact_available', [
		'default'           => '1',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_contact_available', [
		'label'   => __( 'Show "available" indicator', 'stevebaron' ),
		'section' => 'sb_contact',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'sb_contact_availability_text', [
		'default'           => 'Available for advisory work, podcast guesting, and conference panels — not full-time roles.',
		'sanitize_callback' => 'sanitize_textarea_field',
	] );
	$wp_customize->add_control( 'sb_contact_availability_text', [
		'label'   => __( 'Availability description', 'stevebaron' ),
		'section' => 'sb_contact',
		'type'    => 'textarea',
	] );

	$wp_customize->add_setting( 'sb_contact_headline', [
		'default'           => "Let's talk weather, media, or skiing.",
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_contact_headline', [
		'label'   => __( 'Contact page headline', 'stevebaron' ),
		'section' => 'sb_contact',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'sb_contact_subtext', [
		'default'           => 'Email is best. I read everything, but I\'m slow to respond if it\'s a busy forecast week.',
		'sanitize_callback' => 'sanitize_textarea_field',
	] );
	$wp_customize->add_control( 'sb_contact_subtext', [
		'label'   => __( 'Contact page subtext', 'stevebaron' ),
		'section' => 'sb_contact',
		'type'    => 'textarea',
	] );

	// ── Footer ────────────────────────────────────────────────────────────────

	$wp_customize->add_section( 'sb_footer', [
		'title'    => __( 'Footer', 'stevebaron' ),
		'priority' => 50,
	] );

	$wp_customize->add_setting( 'sb_footer_tagline', [
		'default'           => 'Salt Lake City, Utah. Building things on the internet, mostly about weather and media.',
		'sanitize_callback' => 'sanitize_textarea_field',
	] );
	$wp_customize->add_control( 'sb_footer_tagline', [
		'label'   => __( 'Footer tagline', 'stevebaron' ),
		'section' => 'sb_footer',
		'type'    => 'textarea',
	] );

	$wp_customize->add_setting( 'sb_footer_coordinates', [
		'default'           => '40.7608° N · 111.8910° W',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_footer_coordinates', [
		'label'   => __( 'Footer coordinates text', 'stevebaron' ),
		'section' => 'sb_footer',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'sb_rss_url', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	] );
	$wp_customize->add_control( 'sb_rss_url', [
		'label'   => __( 'RSS feed URL (leave blank for WordPress default)', 'stevebaron' ),
		'section' => 'sb_footer',
		'type'    => 'url',
	] );

	$wp_customize->add_setting( 'sb_newsletter_url', [
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	] );
	$wp_customize->add_control( 'sb_newsletter_url', [
		'label'   => __( 'Newsletter URL', 'stevebaron' ),
		'section' => 'sb_footer',
		'type'    => 'url',
	] );
}
add_action( 'customize_register', 'stevebaron_customize_register' );
