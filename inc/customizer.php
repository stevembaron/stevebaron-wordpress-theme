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
		'default'           => "Hi, I'm Steve.\nProduct, AI & digital\ntransformation executive.\nFormer SVP at Fox Corporation.",
		'sanitize_callback' => 'wp_kses_post',
		'transport'         => 'refresh',
	] );
	$wp_customize->add_control( 'sb_hero_headline', [
		'label'   => __( 'Hero headline (line breaks preserved)', 'stevebaron' ),
		'section' => 'sb_hero',
		'type'    => 'textarea',
	] );

	$wp_customize->add_setting( 'sb_hero_subtext', [
		'default'           => 'From driving FOX Weather to #1 on the US App Store at launch to unifying 40+ TV station sites into a national platform reaching 100M monthly uniques, I\'ve spent 15+ years turning ambiguous product, audience, and growth challenges into shipped outcomes at scale.',
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
		[ 'sb_stat_1_num', 'sb_stat_1_label', '#1',   'US App Store at FOX Weather launch' ],
		[ 'sb_stat_2_num', 'sb_stat_2_label', '100M', 'monthly uniques on the Tribune platform' ],
		[ 'sb_stat_3_num', 'sb_stat_3_label', '250K', 'FOX Weather pre-orders' ],
		[ 'sb_stat_4_num', 'sb_stat_4_label', '150+', 'team led across product, eng & content' ],
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
		'github'    => [ 'GitHub URL', 'https://github.com/stevembaron' ],
		'email'     => [ 'Email address', 'steve@stevebaron.com' ],
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
		'default'           => 'Product Strategy, Artificial Intelligence (AI), Generative AI, Retrieval-Augmented Generation (RAG), Digital Transformation, Go-to-Market Strategy, Executive Leadership, App Store Optimization (ASO), SEO, Mobile Applications, OTT, Streaming Media, Live Streaming, Content Strategy, WordPress VIP, Platform Modernization, Cross-functional Team Leadership, Mergers & Acquisitions, Change Management, Board Leadership, Advisory',
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_skills', [
		'label'       => __( 'Skills / tools (comma-separated)', 'stevebaron' ),
		'section'     => 'sb_cv',
		'type'        => 'text',
	] );

	$wp_customize->add_setting( 'sb_cv_tagline', [
		'default'           => 'Product, AI & Digital Transformation Executive. Salt Lake City, UT.',
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
		'default'           => true,
		'sanitize_callback' => function( $v ) { return ! empty( $v ); },
	] );
	$wp_customize->add_control( 'sb_contact_available', [
		'label'   => __( 'Show "available" indicator', 'stevebaron' ),
		'section' => 'sb_contact',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'sb_contact_availability_text', [
		'default'           => 'Open to advisory, fractional, and full-time conversations. Based in Salt Lake City and comfortable working remote, hybrid, or traveling as needed.',
		'sanitize_callback' => 'sanitize_textarea_field',
	] );
	$wp_customize->add_control( 'sb_contact_availability_text', [
		'label'   => __( 'Availability description', 'stevebaron' ),
		'section' => 'sb_contact',
		'type'    => 'textarea',
	] );

	$wp_customize->add_setting( 'sb_contact_headline', [
		'default'           => "Let's work together.",
		'sanitize_callback' => 'sanitize_text_field',
	] );
	$wp_customize->add_control( 'sb_contact_headline', [
		'label'   => __( 'Contact page headline', 'stevebaron' ),
		'section' => 'sb_contact',
		'type'    => 'text',
	] );

	$wp_customize->add_setting( 'sb_contact_subtext', [
		'default'           => 'Email is best. I read everything and try to respond within a day or two — sooner if you include a weather observation.',
		'sanitize_callback' => 'sanitize_textarea_field',
	] );
	$wp_customize->add_control( 'sb_contact_subtext', [
		'label'   => __( 'Contact page subtext', 'stevebaron' ),
		'section' => 'sb_contact',
		'type'    => 'textarea',
	] );

	// Contact form
	$wp_customize->add_setting( 'sb_contact_form_enabled', [
		'default'           => true,
		'sanitize_callback' => function( $v ) { return ! empty( $v ); },
	] );
	$wp_customize->add_control( 'sb_contact_form_enabled', [
		'label'   => __( 'Show contact form on /contact/', 'stevebaron' ),
		'section' => 'sb_contact',
		'type'    => 'checkbox',
	] );

	$wp_customize->add_setting( 'sb_contact_form_to', [
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
	] );
	$wp_customize->add_control( 'sb_contact_form_to', [
		'label'       => __( 'Send contact form submissions to', 'stevebaron' ),
		'description' => __( 'Leave blank to use the email under Social Links.', 'stevebaron' ),
		'section'     => 'sb_contact',
		'type'        => 'email',
	] );

	// ── Footer ────────────────────────────────────────────────────────────────

	$wp_customize->add_section( 'sb_footer', [
		'title'    => __( 'Footer', 'stevebaron' ),
		'priority' => 50,
	] );

	$wp_customize->add_setting( 'sb_footer_tagline', [
		'default'           => 'Salt Lake City, Utah. Product, AI & digital transformation executive. Former SVP at Fox Corporation.',
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
