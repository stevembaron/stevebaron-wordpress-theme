<?php
/**
 * Custom Post Types and Taxonomies
 */

// ── Projects CPT ─────────────────────────────────────────────────────────────

function stevebaron_register_projects() {
	register_post_type( 'sb_project', [
		'labels' => [
			'name'               => __( 'Projects', 'stevebaron' ),
			'singular_name'      => __( 'Project', 'stevebaron' ),
			'add_new_item'       => __( 'Add New Project', 'stevebaron' ),
			'edit_item'          => __( 'Edit Project', 'stevebaron' ),
			'new_item'           => __( 'New Project', 'stevebaron' ),
			'view_item'          => __( 'View Project', 'stevebaron' ),
			'search_items'       => __( 'Search Projects', 'stevebaron' ),
			'not_found'          => __( 'No projects found.', 'stevebaron' ),
			'not_found_in_trash' => __( 'No projects in trash.', 'stevebaron' ),
		],
		'public'            => true,
		'show_in_rest'      => true,
		'menu_icon'         => 'dashicons-hammer',
		'menu_position'     => 5,
		'supports'          => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],
		'rewrite'           => [ 'slug' => 'projects' ],
		'has_archive'       => false,
		'show_in_nav_menus' => false,
	] );
}
add_action( 'init', 'stevebaron_register_projects' );

// ── Experience CPT (CV entries) ───────────────────────────────────────────────

function stevebaron_register_experience() {
	register_post_type( 'sb_experience', [
		'labels' => [
			'name'               => __( 'CV Entries', 'stevebaron' ),
			'singular_name'      => __( 'CV Entry', 'stevebaron' ),
			'add_new_item'       => __( 'Add CV Entry', 'stevebaron' ),
			'edit_item'          => __( 'Edit CV Entry', 'stevebaron' ),
			'not_found'          => __( 'No CV entries found.', 'stevebaron' ),
			'not_found_in_trash' => __( 'No CV entries in trash.', 'stevebaron' ),
		],
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'menu_icon'         => 'dashicons-portfolio',
		'menu_position'     => 6,
		'supports'          => [ 'title', 'editor', 'page-attributes' ],
		'rewrite'           => false,
		'has_archive'       => false,
		'show_in_nav_menus' => false,
	] );

	// CV section taxonomy: Experience | Education | Speaking | Skills
	register_taxonomy( 'sb_cv_section', 'sb_experience', [
		'labels' => [
			'name'          => __( 'CV Section', 'stevebaron' ),
			'singular_name' => __( 'CV Section', 'stevebaron' ),
		],
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'hierarchical'      => false,
		'show_in_nav_menus' => false,
	] );
}
add_action( 'init', 'stevebaron_register_experience' );

// ── Photos CPT ────────────────────────────────────────────────────────────────

function stevebaron_register_photos() {
	register_post_type( 'sb_photo', [
		'labels' => [
			'name'               => __( 'Photos', 'stevebaron' ),
			'singular_name'      => __( 'Photo', 'stevebaron' ),
			'add_new_item'       => __( 'Add New Photo', 'stevebaron' ),
			'edit_item'          => __( 'Edit Photo', 'stevebaron' ),
			'not_found'          => __( 'No photos found.', 'stevebaron' ),
			'not_found_in_trash' => __( 'No photos in trash.', 'stevebaron' ),
		],
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'menu_icon'         => 'dashicons-camera',
		'menu_position'     => 7,
		'supports'          => [ 'title', 'thumbnail', 'page-attributes' ],
		'rewrite'           => false,
		'has_archive'       => false,
		'show_in_nav_menus' => false,
	] );

	register_taxonomy( 'sb_photo_cat', 'sb_photo', [
		'labels' => [
			'name'          => __( 'Photo Categories', 'stevebaron' ),
			'singular_name' => __( 'Photo Category', 'stevebaron' ),
			'add_new_item'  => __( 'Add Category', 'stevebaron' ),
		],
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => true,
		'hierarchical'      => false,
		'show_in_nav_menus' => false,
	] );
}
add_action( 'init', 'stevebaron_register_photos' );

// ── Seed default CV entries on theme activation ───────────────────────────────

function stevebaron_seed_defaults() {
	// Only seed once
	if ( get_option( 'stevebaron_seeded' ) ) return;

	// Default CV entries
	$cv_entries = [
		[
			'title'  => 'VP, Product',
			'org'    => 'A national broadcast network',
			'dates'  => '2021 — present',
			'blurb'  => 'Led the rebuild of forecast tooling, news apps, and digital revenue stack. Hired and grew a team from 8 to 32.',
			'type'   => 'Experience',
			'order'  => 1,
		],
		[
			'title'  => 'Director, Digital Media',
			'org'    => 'Regional media group',
			'dates'  => '2017 — 2021',
			'blurb'  => 'Owned web + apps for 14 stations. Doubled mobile audience.',
			'type'   => 'Experience',
			'order'  => 2,
		],
		[
			'title'  => 'Senior Product Manager',
			'org'    => 'A weather data startup',
			'dates'  => '2014 — 2017',
			'blurb'  => 'Acquired in 2017.',
			'type'   => 'Experience',
			'order'  => 3,
		],
		[
			'title'  => 'BA, Atmospheric Science (minor in Journalism)',
			'org'    => 'University of Utah',
			'dates'  => '2010 — 2014',
			'blurb'  => '',
			'type'   => 'Education',
			'order'  => 1,
		],
		[
			'title'  => 'The Future of Local Forecasts',
			'org'    => 'AMS Broadcast Conference',
			'dates'  => '2024',
			'blurb'  => '',
			'type'   => 'Speaking',
			'order'  => 1,
		],
		[
			'title'  => 'Why CMS is the New Newsroom',
			'org'    => 'ONA',
			'dates'  => '2023',
			'blurb'  => '',
			'type'   => 'Speaking',
			'order'  => 2,
		],
	];

	foreach ( $cv_entries as $entry ) {
		$post_id = wp_insert_post( [
			'post_title'  => $entry['title'],
			'post_content'=> $entry['blurb'],
			'post_type'   => 'sb_experience',
			'post_status' => 'publish',
			'menu_order'  => $entry['order'],
		] );
		if ( ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_sb_org',   $entry['org'] );
			update_post_meta( $post_id, '_sb_dates', $entry['dates'] );
			wp_set_object_terms( $post_id, $entry['type'], 'sb_cv_section' );
		}
	}

	// Default projects
	$projects = [
		[
			'title'  => 'Sky Atlas',
			'desc'   => 'A field guide to clouds, on your phone. Built with React Native, Mapbox, and a stubborn refusal to add ads.',
			'year'   => '2024 — present',
			'status' => 'Active',
			'order'  => 1,
		],
		[
			'title'  => 'Forecast.fm',
			'desc'   => 'A podcast network for hyperlocal weather, made with three friends. Acquired by a station group in 2024.',
			'year'   => '2023',
			'status' => 'Acquired',
			'order'  => 2,
		],
		[
			'title'  => 'The Snow Report',
			'desc'   => 'Daily SLC snow + avy digest, sent at 5:42am every morning. ~3,400 subscribers.',
			'year'   => 'Ongoing',
			'status' => 'Active',
			'order'  => 3,
		],
		[
			'title'  => 'Tiny Weather Radio',
			'desc'   => 'An ESP32 that reads NWS WeatherWire forecasts in a charmingly old voice.',
			'year'   => '2022',
			'status' => 'Hobby',
			'order'  => 4,
		],
		[
			'title'  => 'KSL Weather Rebuild',
			'desc'   => 'Led a rebuild of the weather product for a Salt Lake station group. 4× the audience, 1/3 the bundle size.',
			'year'   => '2019 — 2021',
			'status' => 'Shipped',
			'order'  => 5,
		],
	];

	foreach ( $projects as $p ) {
		$post_id = wp_insert_post( [
			'post_title'   => $p['title'],
			'post_content' => $p['desc'],
			'post_type'    => 'sb_project',
			'post_status'  => 'publish',
			'menu_order'   => $p['order'],
		] );
		if ( ! is_wp_error( $post_id ) ) {
			update_post_meta( $post_id, '_sb_year',   $p['year'] );
			update_post_meta( $post_id, '_sb_status', $p['status'] );
		}
	}

	update_option( 'stevebaron_seeded', true );
}
add_action( 'after_switch_theme', 'stevebaron_seed_defaults' );
