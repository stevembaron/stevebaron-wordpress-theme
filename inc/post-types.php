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

// ── Seed data (resume content) ───────────────────────────────────────────────

/**
 * Returns the canonical CV entry data — used by the activation seed and by
 * the "Reset to resume data" admin action.
 */
function stevebaron_seed_cv_entries(): array {
	return [
		[
			'title'  => 'Advisor / Fractional Executive',
			'org'    => 'Various clients',
			'dates'  => '2023 — present',
			'blurb'  => 'AI product strategy, media technology advisory, and go-to-market for early-stage companies at the intersection of AI and journalism.',
			'type'   => 'Experience',
			'order'  => 1,
		],
		[
			'title'  => 'VP, Product',
			'org'    => 'FOX Weather / FOX Corporation',
			'dates'  => '2021 — 2023',
			'blurb'  => 'Built FOX Weather from concept to launch — hit #1 on the US App Store on launch day and surpassed 250,000 pre-orders. Led product, design, and engineering for consumer apps, CMS, and data platforms. Grew the team from 6 to 40+.',
			'type'   => 'Experience',
			'order'  => 2,
		],
		[
			'title'  => 'VP, Digital Products & Strategy',
			'org'    => 'Tribune Publishing',
			'dates'  => '2016 — 2021',
			'blurb'  => 'Owned digital product strategy across a national portfolio of news brands reaching 100M monthly uniques. Led platform consolidation, subscription product, and mobile apps. Managed a team of 25+ across product, engineering, and analytics.',
			'type'   => 'Experience',
			'order'  => 3,
		],
		[
			'title'  => 'Director, Digital Innovation',
			'org'    => 'Tribune Media',
			'dates'  => '2013 — 2016',
			'blurb'  => 'Built and led the digital product team for local TV stations. Launched the first live-streaming weather app and rebuilt the network\'s CMS on WordPress at scale.',
			'type'   => 'Experience',
			'order'  => 4,
		],
		[
			'title'  => 'Digital Product Manager',
			'org'    => 'Raycom Media',
			'dates'  => '2010 — 2013',
			'blurb'  => 'Managed web and mobile products across 50+ local TV stations. Rebuilt weather pages and launched the company\'s first responsive mobile site.',
			'type'   => 'Experience',
			'order'  => 5,
		],
		[
			'title'  => 'Broadcast Meteorologist & Digital Producer',
			'org'    => 'Local TV stations',
			'dates'  => '2006 — 2010',
			'blurb'  => 'On-air meteorologist in multiple markets. Began transitioning to digital — built station websites, wrote the first weather app briefs, and developed an interest in how forecasting translates to product.',
			'type'   => 'Experience',
			'order'  => 6,
		],
		[
			'title'  => 'Meteorologist',
			'org'    => 'WSFA / NBC affiliate',
			'dates'  => '2001 — 2006',
			'blurb'  => 'Chief meteorologist. Emmy Award winner, 2006.',
			'type'   => 'Experience',
			'order'  => 7,
		],
		[
			'title'  => 'Weekend Meteorologist',
			'org'    => 'WCTV / CBS affiliate',
			'dates'  => '1997 — 2001',
			'blurb'  => 'First TV job. Forecast, produce, and present.',
			'type'   => 'Experience',
			'order'  => 8,
		],
		[
			'title'  => 'BS, Telecommunication News',
			'org'    => 'University of Florida',
			'dates'  => '1993 — 1997',
			'blurb'  => 'College of Journalism and Communications.',
			'type'   => 'Education',
			'order'  => 1,
		],
		[
			'title'  => 'Certificate, Broadcast Meteorology',
			'org'    => 'Mississippi State University',
			'dates'  => '1997 — 1999',
			'blurb'  => 'Completed while working full-time in broadcasting.',
			'type'   => 'Education',
			'order'  => 2,
		],
		[
			'title'  => 'Digital Innovator of the Year',
			'org'    => 'Broadcasting & Cable',
			'dates'  => '2018',
			'blurb'  => '',
			'type'   => 'Recognition',
			'order'  => 1,
		],
		[
			'title'  => 'Webby Award — Best News App',
			'org'    => 'The Webby Awards',
			'dates'  => '2023',
			'blurb'  => 'FOX Weather iOS app.',
			'type'   => 'Recognition',
			'order'  => 2,
		],
		[
			'title'  => 'Emmy Award — Weather Excellence',
			'org'    => 'National Academy of Television Arts & Sciences',
			'dates'  => '2006',
			'blurb'  => '',
			'type'   => 'Recognition',
			'order'  => 3,
		],
	];
}

/**
 * Returns the canonical project data.
 */
function stevebaron_seed_projects(): array {
	return [
		[
			'title'  => 'FOX Weather App',
			'desc'   => 'Built FOX Weather from zero — product strategy, design system, engineering org, and launch. Hit #1 on the US App Store on day one. 250,000+ pre-orders before launch. Earned a Webby Award for Best News App in 2023.',
			'year'   => '2021 — 2023',
			'status' => 'Shipped',
			'order'  => 1,
		],
		[
			'title'  => 'Tribune National Digital Platform',
			'desc'   => 'Led the consolidation of 10+ regional news brands onto a single digital platform. Grew the combined audience to 100M monthly uniques. Built the subscription product and data infrastructure that underpinned the company\'s digital revenue.',
			'year'   => '2016 — 2021',
			'status' => 'Shipped',
			'order'  => 2,
		],
		[
			'title'  => 'Live-Video Weather App',
			'desc'   => 'Launched what I believe was the first live-streaming weather app for local TV stations. Built on a custom WordPress CMS with live video ingest. Deployed across 30+ markets.',
			'year'   => '2013 — 2016',
			'status' => 'Shipped',
			'order'  => 3,
		],
		[
			'title'  => 'Responsive Weather CMS',
			'desc'   => 'Designed and shipped a WordPress-based CMS for local TV weather teams — automated NWS data ingestion, radar widgets, and forecast templating — used by meteorologists across 50+ stations.',
			'year'   => '2010 — 2013',
			'status' => 'Shipped',
			'order'  => 4,
		],
	];
}

/**
 * Inserts the CV entries and projects from the canonical seed data.
 * Each post is tagged with _sb_seed = 1 so the reseed action can identify them.
 */
function stevebaron_insert_seed_content(): array {
	$counts = [ 'cv' => 0, 'projects' => 0 ];

	foreach ( stevebaron_seed_cv_entries() as $entry ) {
		$post_id = wp_insert_post( [
			'post_title'   => $entry['title'],
			'post_content' => $entry['blurb'],
			'post_type'    => 'sb_experience',
			'post_status'  => 'publish',
			'menu_order'   => $entry['order'],
		] );
		if ( ! is_wp_error( $post_id ) && $post_id ) {
			update_post_meta( $post_id, '_sb_org',   $entry['org'] );
			update_post_meta( $post_id, '_sb_dates', $entry['dates'] );
			update_post_meta( $post_id, '_sb_seed',  '1' );
			wp_set_object_terms( $post_id, $entry['type'], 'sb_cv_section' );
			$counts['cv']++;
		}
	}

	foreach ( stevebaron_seed_projects() as $p ) {
		$post_id = wp_insert_post( [
			'post_title'   => $p['title'],
			'post_content' => $p['desc'],
			'post_type'    => 'sb_project',
			'post_status'  => 'publish',
			'menu_order'   => $p['order'],
		] );
		if ( ! is_wp_error( $post_id ) && $post_id ) {
			update_post_meta( $post_id, '_sb_year',   $p['year'] );
			update_post_meta( $post_id, '_sb_status', $p['status'] );
			update_post_meta( $post_id, '_sb_seed',   '1' );
			$counts['projects']++;
		}
	}

	return $counts;
}

/**
 * Auto-seed on theme activation (only ever runs once).
 */
function stevebaron_seed_defaults() {
	if ( get_option( 'stevebaron_seeded' ) ) return;
	stevebaron_insert_seed_content();
	update_option( 'stevebaron_seeded', true );
}
add_action( 'after_switch_theme', 'stevebaron_seed_defaults' );

/**
 * Manual reset: trashes existing sb_experience + sb_project posts and
 * re-inserts the canonical seed content. Returns a summary array.
 * Trashed posts are recoverable from the admin Trash for ~30 days.
 */
function stevebaron_reseed_content(): array {
	$trashed = [ 'cv' => 0, 'projects' => 0 ];

	$existing_cv = get_posts( [
		'post_type'      => 'sb_experience',
		'posts_per_page' => -1,
		'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
		'fields'         => 'ids',
	] );
	foreach ( $existing_cv as $id ) {
		if ( wp_trash_post( $id ) ) $trashed['cv']++;
	}

	$existing_projects = get_posts( [
		'post_type'      => 'sb_project',
		'posts_per_page' => -1,
		'post_status'    => [ 'publish', 'draft', 'pending', 'private', 'future' ],
		'fields'         => 'ids',
	] );
	foreach ( $existing_projects as $id ) {
		if ( wp_trash_post( $id ) ) $trashed['projects']++;
	}

	$inserted = stevebaron_insert_seed_content();
	update_option( 'stevebaron_seeded', true );

	return [ 'trashed' => $trashed, 'inserted' => $inserted ];
}
