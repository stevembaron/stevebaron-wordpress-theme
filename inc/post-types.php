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
		// ── Experience (most recent first) ────────────────────────────────────
		[
			'title'  => 'Independent Product, AI & Marketing Strategy Advisor',
			'org'    => 'Independent Advisory',
			'dates'  => 'Jun 2023 — Present',
			'blurb'  => 'Advising consumer technology, AI, and digital platform companies on pre-launch product strategy, applied AI, and go-to-market execution. Engagements include pre-launch product reviews for a publicly traded consumer electronics brand, product strategy and response-quality work for a widely used AI assistant (supervised fine-tuning and RAG), and product strategy for a category-leading AI product at a global technology company.',
			'type'   => 'Experience',
			'order'  => 1,
		],
		[
			'title'  => 'Senior Vice President, Digital Products & Strategy — FOX Weather',
			'org'    => 'Fox Corporation',
			'dates'  => 'Feb 2021 — May 2023',
			'blurb'  => 'Built and led the digital product organization that launched FOX Weather, Fox Corporation\'s first new editorial brand in two decades. Drove FOX Weather to #1 on the US App Store at launch — outranking TikTok, Facebook, and Instagram — through advanced App Store Optimization and a pre-order campaign that generated 250K pre-orders. Launched the full mobile, web, livestream, and VOD portfolio in six months, including mobile 3D radar and an API infrastructure handling ~1B requests per month at peak. Built SEO and content distribution strategy across Google News, Apple News, Yahoo News, and partner channels, achieving 80% YoY SEO-optimized growth. Awarded a Webby for Visual Storytelling in 2023.',
			'type'   => 'Experience',
			'order'  => 2,
		],
		[
			'title'  => 'Chief Strategy Officer',
			'org'    => 'Local Media Association',
			'dates'  => 'Aug 2020 — Jan 2021',
			'blurb'  => 'Recruited as digital product and transformation subject-matter expert for North America\'s leading nonprofit trade association serving local TV, radio, and newspaper companies. Directed corporate strategy, transformation projects, and partnership development; advised member companies on roadmaps for digital transformation, product investment, and operating-model change.',
			'type'   => 'Experience',
			'order'  => 3,
		],
		[
			'title'  => 'Vice President, Digital',
			'org'    => 'Nexstar Media Group, Inc.',
			'dates'  => 'Sep 2019 — May 2020',
			'blurb'  => 'Joined post-Tribune acquisition under a retention package to guide digital integration and operational alignment across the nation\'s largest local TV broadcaster (200+ stations, ~70% of US TV households). Managed a $10M+ operating budget and the post-acquisition integration of Tribune and Nexstar digital capabilities, teams, and vendor relationships. Led change management across 30+ external partner transitions; achieved 100% retention of digital teams through the transition.',
			'type'   => 'Experience',
			'order'  => 4,
		],
		[
			'title'  => 'Vice President, Digital & Head of Product and Engineering',
			'org'    => 'Tribune Media',
			'dates'  => 'Dec 2013 — Sep 2019',
			'blurb'  => 'Ran a global org of 12 direct reports and 150+ team members spanning product, engineering, content, analytics, and platform. Unified 40+ local TV station websites into a single national platform reaching 100M monthly uniques — the largest local-media digital network in the country at the time. Owned the migration from a proprietary Rails CMS to WordPress VIP, reducing costs by ~80% while improving scalability. Served as product owner for news and weather apps across the portfolio, integrating video, ad-tech, mobile, OTT, and AI/ML capabilities. Tribune was sold to Nexstar in 2019 in a $7.2B transaction. President of the Board, Covers Media Group (2017–2019); Board Member, Dose Media (2017–2019).',
			'type'   => 'Experience',
			'order'  => 5,
		],
		[
			'title'  => 'Vice President, Digital Content & Technology',
			'org'    => 'Local TV, LLC',
			'dates'  => 'Dec 2008 — Nov 2013',
			'blurb'  => 'Recruited at startup phase to lead digital content, technology products, and operations for a private-equity-backed local TV group on a $5M+ annual budget. Built the first live-video-focused weather app with 2× faster push notifications than competitors. Developed a customized WordPress CMS powering web, mobile, AVOD, streaming, and connected-platform ecosystems across the station group. Local TV, LLC was sold to Tribune Media in 2013 for $2.7B — one of the largest local broadcast group transactions of the decade.',
			'type'   => 'Experience',
			'order'  => 6,
		],
		[
			'title'  => 'Director of Product & Content',
			'org'    => 'Fox Television Stations',
			'dates'  => 'Jun 2006 — Dec 2008',
			'blurb'  => 'First digital leadership role after a decade in broadcast. Based at FOX32 Chicago with product and editorial influence across Fox digital properties nationwide. Hired and led a five-member editorial and product operations team. Launched Fox Chicago\'s first website, achieving 250% traffic growth in its first year and establishing the model for subsequent station digital builds.',
			'type'   => 'Experience',
			'order'  => 7,
		],
		[
			'title'  => 'Meteorologist / Reporter',
			'org'    => 'KSTU-TV (FOX 13 News)',
			'dates'  => 'Jun 1997 — Jun 2006',
			'blurb'  => 'On-camera meteorologist and reporter for Salt Lake City audiences for nearly a decade. Built deep expertise in live communication, breaking-news operations, and audience trust at a top-50 market station. Earned an Emmy Award for Breaking News Coverage in 2006.',
			'type'   => 'Experience',
			'order'  => 8,
		],
		[
			'title'  => 'Meteorologist / Reporter',
			'org'    => 'WSBT22',
			'dates'  => 'Jan 1995 — Jun 1997',
			'blurb'  => 'Broadcast meteorologist and news reporter for the South Bend / Mishawaka / Elkhart market.',
			'type'   => 'Experience',
			'order'  => 9,
		],
		[
			'title'  => 'Weather Anchor / Reporter / Photographer',
			'org'    => 'WCJB TV20',
			'dates'  => 'Jul 1993 — Jan 1995',
			'blurb'  => 'First broadcast role. Weather anchor, field reporter, and on-the-ground photographer covering north central Florida.',
			'type'   => 'Experience',
			'order'  => 10,
		],

		// ── Education ─────────────────────────────────────────────────────────
		[
			'title'  => 'Bachelor of Science, Telecommunication News',
			'org'    => 'University of Florida',
			'dates'  => '',
			'blurb'  => '',
			'type'   => 'Education',
			'order'  => 1,
		],
		[
			'title'  => 'Certificate, Broadcast Meteorology',
			'org'    => 'Mississippi State University',
			'dates'  => '',
			'blurb'  => '',
			'type'   => 'Education',
			'order'  => 2,
		],

		// ── Recognition (Honors & Awards) ─────────────────────────────────────
		[
			'title'  => 'Webby Award, Visual Storytelling',
			'org'    => 'International Academy of Digital Arts and Sciences · FOX Weather',
			'dates'  => '2023',
			'blurb'  => '',
			'type'   => 'Recognition',
			'order'  => 1,
		],
		[
			'title'  => 'Digital Innovator of the Year',
			'org'    => 'Local Media Association · Tribune Media',
			'dates'  => '2018',
			'blurb'  => '',
			'type'   => 'Recognition',
			'order'  => 2,
		],
		[
			'title'  => 'Emmy Award, Breaking News Coverage',
			'org'    => 'National Academy of Television Arts and Sciences · KSTU-TV (FOX 13 News)',
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
			'title'  => 'FOX Weather',
			'desc'   => 'Built and launched Fox Corporation\'s first new editorial brand in two decades. Drove FOX Weather to #1 on the US App Store at launch — outranking TikTok, Facebook, and Instagram — through advanced ASO and a pre-order campaign that delivered 250K pre-orders. Shipped the full mobile, web, livestream, and VOD portfolio in six months, including mobile 3D radar and an API infrastructure handling ~1B requests per month at peak. Webby Award for Visual Storytelling, 2023.',
			'year'   => '2021 — 2023',
			'status' => 'Shipped',
			'order'  => 1,
			'image'  => 'fox-weather',
		],
		[
			'title'  => 'Tribune National Digital Platform',
			'desc'   => 'Unified 40+ local TV station websites into a single national platform reaching 100M monthly uniques — the largest local-media digital network in the country at the time. Owned the migration from a proprietary Rails CMS to WordPress VIP, reducing platform costs by ~80% while improving scalability.',
			'year'   => '2013 — 2019',
			'status' => 'Shipped',
			'order'  => 2,
			'image'  => 'tribune',
		],
		[
			'title'  => 'First Live-Video Weather App',
			'desc'   => 'Built the first live-video-focused weather app while at Local TV, LLC, with 2× faster push notifications than competitors. Industry-leading product at the time, later scaled across the Tribune station portfolio.',
			'year'   => '2008 — 2013',
			'status' => 'Shipped',
			'order'  => 3,
			'image'  => 'kfor',
		],
		[
			'title'  => 'Customized WordPress CMS for Local TV',
			'desc'   => 'Developed a customized WordPress CMS powering web, mobile, AVOD, streaming, and connected-platform ecosystems across the Local TV, LLC station group. Operational and product playbook later scaled across 40+ Tribune stations after the 2013 acquisition.',
			'year'   => '2008 — 2013',
			'status' => 'Shipped',
			'order'  => 4,
			'image'  => 'local-tv',
		],
	];
}

/**
 * Sideloads an image file from the theme into the Media Library and
 * returns the new attachment ID. Returns 0 on failure.
 *
 * Tries common extensions for the given basename (png/jpg/jpeg/webp).
 */
function stevebaron_sideload_project_image( string $basename, int $parent_post_id ): int {
	if ( $basename === '' ) return 0;

	$dir = STEVEBARON_DIR . '/assets/projects/';
	$candidates = [ '.png', '.jpg', '.jpeg', '.webp' ];
	$src_path = '';
	foreach ( $candidates as $ext ) {
		if ( file_exists( $dir . $basename . $ext ) ) {
			$src_path = $dir . $basename . $ext;
			break;
		}
	}
	if ( ! $src_path ) return 0;

	if ( ! function_exists( 'wp_insert_attachment' ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';
	}

	$upload = wp_upload_dir();
	if ( ! empty( $upload['error'] ) ) return 0;
	$target_dir  = $upload['path'];
	$target_name = wp_unique_filename( $target_dir, basename( $src_path ) );
	$target_path = trailingslashit( $target_dir ) . $target_name;

	if ( ! @copy( $src_path, $target_path ) ) return 0;

	$filetype = wp_check_filetype( $target_name );
	$attach_id = wp_insert_attachment(
		[
			'post_mime_type' => $filetype['type'] ?: 'image/png',
			'post_title'     => sanitize_text_field( pathinfo( $target_name, PATHINFO_FILENAME ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		],
		$target_path,
		$parent_post_id
	);
	if ( is_wp_error( $attach_id ) || ! $attach_id ) return 0;

	$meta = wp_generate_attachment_metadata( $attach_id, $target_path );
	wp_update_attachment_metadata( $attach_id, $meta );

	return (int) $attach_id;
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

			if ( ! empty( $p['image'] ) ) {
				$attach_id = stevebaron_sideload_project_image( $p['image'], (int) $post_id );
				if ( $attach_id ) set_post_thumbnail( $post_id, $attach_id );
			}

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
