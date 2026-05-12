<?php
/**
 * Site Setup
 *
 * Auto-creates the pages this theme expects (Home, About, CV, Projects, …),
 * binds them to the right page templates, configures Settings → Reading,
 * and builds the Primary nav menu.
 *
 * Runs once on theme activation. Idempotent — safe to re-run via the
 * Tools → Site Setup admin page (it skips pages that already exist).
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Pages this theme expects. Order here = order in the nav menu.
 *
 * 'slug'     => [
 *   'title'    => Page title (also reused as nav label),
 *   'template' => Page template file ('' for default),
 *   'role'     => 'front' | 'posts' | null,
 *   'in_menu'  => Whether to add to the Primary menu,
 * ]
 */
function stevebaron_expected_pages(): array {
	return [
		'home'     => [ 'title' => 'Home',     'template' => '',                   'role' => 'front', 'in_menu' => true  ],
		'about'    => [ 'title' => 'About',    'template' => 'page-about.php',     'role' => null,    'in_menu' => true  ],
		'cv'       => [ 'title' => 'CV',       'template' => 'page-cv.php',        'role' => null,    'in_menu' => true  ],
		'projects' => [ 'title' => 'Projects', 'template' => 'page-projects.php',  'role' => null,    'in_menu' => true  ],
		'writing'  => [ 'title' => 'Writing',  'template' => '',                   'role' => 'posts', 'in_menu' => true  ],
		'photos'   => [ 'title' => 'Photos',   'template' => 'page-photos.php',    'role' => null,    'in_menu' => true  ],
		'now'      => [ 'title' => 'Now',      'template' => 'page-now.php',       'role' => null,    'in_menu' => true  ],
		'contact'  => [ 'title' => 'Contact',  'template' => 'page-contact.php',   'role' => null,    'in_menu' => true  ],
	];
}

/**
 * Run the full setup. Returns a per-slug status map:
 *   'created' | 'existed' | 'template-updated' | 'error'
 */
function stevebaron_run_site_setup(): array {
	$pages   = stevebaron_expected_pages();
	$status  = [];
	$ids     = [];

	foreach ( $pages as $slug => $cfg ) {
		$existing = get_page_by_path( $slug );

		if ( $existing ) {
			$ids[ $slug ] = $existing->ID;
			$status[ $slug ] = 'existed';

			// If the page exists but has no/wrong template, fix that.
			if ( $cfg['template'] ) {
				$current_template = get_post_meta( $existing->ID, '_wp_page_template', true );
				if ( $current_template !== $cfg['template'] ) {
					update_post_meta( $existing->ID, '_wp_page_template', $cfg['template'] );
					$status[ $slug ] = 'template-updated';
				}
			}
			continue;
		}

		$page_id = wp_insert_post( [
			'post_title'   => $cfg['title'],
			'post_name'    => $slug,
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_content' => '',
		], true );

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			$status[ $slug ] = 'error';
			continue;
		}

		if ( $cfg['template'] ) {
			update_post_meta( $page_id, '_wp_page_template', $cfg['template'] );
		}
		$ids[ $slug ]   = $page_id;
		$status[ $slug ] = 'created';
	}

	// ── Settings → Reading: static front + posts page ────────────────────
	if ( isset( $ids['home'], $ids['writing'] ) ) {
		update_option( 'show_on_front',  'page' );
		update_option( 'page_on_front',  $ids['home'] );
		update_option( 'page_for_posts', $ids['writing'] );
	}

	// ── Primary menu ─────────────────────────────────────────────────────
	$menu_name = 'Primary';
	$menu      = wp_get_nav_menu_object( $menu_name );
	$menu_id   = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $menu_name );

	if ( $menu_id && ! is_wp_error( $menu_id ) ) {
		$existing_items = wp_get_nav_menu_items( $menu_id ) ?: [];
		$existing_page_ids = array_map(
			fn( $item ) => (int) $item->object_id,
			array_filter( $existing_items, fn( $i ) => $i->object === 'page' )
		);

		foreach ( $pages as $slug => $cfg ) {
			if ( ! $cfg['in_menu'] || empty( $ids[ $slug ] ) ) continue;
			if ( in_array( (int) $ids[ $slug ], $existing_page_ids, true ) ) continue;

			wp_update_nav_menu_item( $menu_id, 0, [
				'menu-item-title'     => $cfg['title'],
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $ids[ $slug ],
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			] );
		}

		$locations = get_theme_mod( 'nav_menu_locations', [] );
		if ( empty( $locations['primary'] ) ) {
			$locations['primary'] = $menu_id;
			set_theme_mod( 'nav_menu_locations', $locations );
		}
	}

	update_option( 'stevebaron_site_setup', current_time( 'mysql' ) );
	flush_rewrite_rules( false );

	return $status;
}

// Auto-run once on activation.
add_action( 'after_switch_theme', function () {
	if ( ! get_option( 'stevebaron_site_setup' ) ) {
		stevebaron_run_site_setup();
	}
} );

// ── FOX Weather launch draft post ────────────────────────────────────────────

/**
 * Returns the Gutenberg-formatted body of the FOX Weather launch essay.
 */
function stevebaron_fox_weather_post_content(): string {
	$paragraphs = [
		[ 'p',  "The morning of launch I was up at 4:30, like I had been every morning that week, watching the App Store rankings on a second monitor while waiting for the kettle. By 6:30 AM Eastern we were #4. By the time I finished my coffee we were #2, behind Instagram. Around 9, we passed them. By the afternoon FOX Weather was the #1 free app in the United States — ahead of TikTok, Instagram, Facebook, and every other app on every other phone in the country." ],
		[ 'p',  "If you'd told me at 23, standing in front of a chroma-key wall at WSBT in South Bend, Indiana, that the path from \"weekend meteorologist\" to \"#1 on the App Store\" was a thing that could happen, I would have laughed and asked you to also predict tomorrow's snow totals. But it did happen, and the path through it — almost three decades of broadcast, digital, product, and a long apprenticeship in not knowing what I didn't know — turned out to be exactly what the work needed." ],
		[ 'p',  "This is a longer post than I usually write. It's the story of how we built FOX Weather, what we got right, what almost broke us, and what I wish I'd known before we started." ],
		[ 'h2', "Fox Corporation's first new editorial brand in two decades" ],
		[ 'p',  "In February 2021, Fox Corporation asked me to come build the digital product organization for a new brand — not a spin-off, not a redesign, not a refresh. A net-new editorial property, with its own newsroom, its own meteorologists, its own visual identity, and a mandate to ship across mobile, web, livestream, VOD, OTT, and partner ecosystems. The first new brand Fox had launched in twenty years." ],
		[ 'p',  "I had two reactions to the offer. The first was \"yes, immediately.\" The second was \"we're going to need a bigger boat.\"" ],
		[ 'p',  "The mandate I wrote on the first day, and re-read every Monday for the next two years, was three sentences long:" ],
		[ 'list', [
			"Beat the legacy weather apps on day one.",
			"Be the best place to watch live weather coverage on any screen.",
			"Build the platform so the brand can grow into things that haven't been invented yet.",
		] ],
		[ 'p',  "Everything we did rolled up to one of those three sentences. When I had a hard product decision and an opinionated team and a deadline, I'd ask which of the three a given path served — and if the answer was \"none of them,\" we'd cut it. We cut a lot." ],
		[ 'h2', "The team" ],
		[ 'p',  "You cannot build a brand-new digital brand with a small team and a small clock. You can build one with a small team and a long clock, or a large team and a short clock, but the third option — small team, short clock — is a way to ship something that nobody uses." ],
		[ 'p',  "We went large team, short clock. Six months from product start to public launch. I grew the org from a starting cluster of six people to forty-plus across product, design, engineering, content operations, growth, and analytics. We hired people who had launched things before, on purpose. We hired meteorologists who had run their own social channels, because we knew the brand had to live outside the app at least as much as inside it. We hired engineers who had built CMS-driven publishing platforms at scale, because we knew the editorial throughput on day one was going to be terrifying and we couldn't afford to learn that lesson live." ],
		[ 'p',  "The single biggest decision I made that year was not a product decision. It was a hiring decision. I picked engineering and design leads who had each, separately, launched mobile products at consumer scale. They were not the cheapest hires available. They were the right hires, and within thirty days I knew it." ],
		[ 'h2', "App Store Optimization, or how we became the most pre-ordered weather app at launch" ],
		[ 'p',  "The marketing strategy I argued for, and lost a few rounds on before I won the round that mattered, was this: we were going to win the App Store before we won the App Store." ],
		[ 'p',  "App Store Optimization is one of those phrases that sounds boring enough to skip past in a deck. It is, in fact, the difference between \"your brand-new app launches and nobody finds it\" and \"your brand-new app launches and is the most-downloaded app in the country.\" We took it seriously. We treated the app listing — the screenshots, the description, the keyword strategy, the icon — as a first-class editorial product. We A/B-tested everything we could test. We watched ranking signals like meteorologists watch dew point." ],
		[ 'p',  "The pre-order campaign was the other piece. We opened pre-orders months before launch, paired them with a content drumbeat — meteorologists, on-camera anchors, behind-the-scenes content — and ended up with <strong>two hundred and fifty thousand</strong> pre-orders on the day FOX Weather opened. Those pre-orders auto-downloaded on launch day. That single mechanic — a quarter of a million phones, all over the country, all auto-installing the app within the same six-hour window — is what put us at #1." ],
		[ 'p',  "We launched ahead of TikTok. It is a sentence I still sometimes say out loud and have to remind myself is true." ],
		[ 'h2', "Six months, full portfolio" ],
		[ 'p',  "Inside the six months we shipped:" ],
		[ 'list', [
			"<strong>iOS and Android apps</strong> with native mobile 3D radar — a serious technical lift, because doing 3D radar well on a phone is doing it in 4 dimensions when you count time and animating it in real time.",
			"<strong>The web product</strong>, designed to live alongside the app rather than mirror it. Different surface, different reader, different content velocity.",
			"<strong>24/7 livestream</strong>, the editorial flagship. Free, ad-supported, never behind any wall.",
			"<strong>A VOD library</strong> for the moments when the live show has just covered something extraordinary and you want to find it later.",
			"<strong>A year-ahead forecast</strong> — controversial inside the building, beloved outside it, and something I'd argue is one of the most quietly innovative features we shipped.",
			"<strong>An API infrastructure</strong> that, at peak, was handling roughly a billion requests per month.",
		] ],
		[ 'p',  "Doing all of that in six months means making decisions in fifteen minutes that you'd normally take three weeks for. It means having the right team. It means having a clear mandate. It means cutting things, including good things, because shipping a smaller product on time is worth more than shipping a bigger product late." ],
		[ 'h2', "Distribution" ],
		[ 'p',  "The other thing I'll say about that year, because it doesn't get talked about enough: the app was the product, but it wasn't the only product. We built SEO and content distribution across Google News, Yahoo News, Apple News, NewsBreak, SmartNews, and a few partner channels I won't name. Our SEO-optimized organic growth ran 80% year over year for the back half of my tenure. That kind of growth at that kind of scale is not luck. It's a strategy. We treated discovery on every surface — app store, search, news aggregators, social — as part of the same job. Because, for a consumer brand in 2021, it was." ],
		[ 'h2', "What I'd do again" ],
		[ 'p',  "The thing I'd do again, the thing I'd argue for every time, is the team. Pick the team first. Argue for the budget the team needs, even if the budget gets you in trouble. Be willing to lose a smaller fight to win the team fight. Everything else is downstream of the people you put in the room." ],
		[ 'p',  "The other thing I'd do again is the mandate. Three sentences. Re-read every Monday. Use as the answer to every roadmap question." ],
		[ 'h2', "What I'd do differently" ],
		[ 'p',  "We launched the editorial product, the app, the livestream, the VOD library, and the API in six months. I would not do that again on a six-month clock unless I had to. Some of those products would have been better with another quarter of incubation. I think we got away with it, but I think we got away with it on the strength of a remarkable team rather than a remarkable plan." ],
		[ 'p',  "I'd also have shipped fewer features at launch and more features at month three. The launch carried weight because it was a launch. Once you've launched, the second hill — proving people will come back tomorrow — is the actual hill. I'd have invested more in that hill earlier." ],
		[ 'h2', "What came next" ],
		[ 'p',  "A Webby Award for Visual Storytelling in 2023, which I am still proud of. A team that retained, almost to a person, through the next two years. An editorial brand that, last I checked, was still running, still hiring, and still hitting the App Store top charts on weather days." ],
		[ 'p',  "I left in May of 2023 to start doing something I'd been quietly wanting to do for years: advising other product and AI companies on what comes before launch. Two years in, that's the work I'm doing every day, and it's an enormous amount of fun." ],
		[ 'p',  "The path from chroma-key wall to #1 on the App Store is long. The path between any two adjacent stops is short. Most of the people I respect most in this work are people who took the next short step, and the next one, and the next one, and then looked up one day to find that they'd ended up somewhere they couldn't have predicted from the start." ],
		[ 'p',  "That's the post. There are probably six other posts inside it, and over the next few months I'll write some of them. If there's a piece of this you'd like me to expand on, <a href=\"/contact/\">say hi</a> — I read everything." ],
		[ 'p',  "— Steve" ],
	];

	$out = '';
	foreach ( $paragraphs as $block ) {
		[ $type, $content ] = $block;
		if ( $type === 'p' ) {
			$out .= "<!-- wp:paragraph -->\n<p>{$content}</p>\n<!-- /wp:paragraph -->\n\n";
		} elseif ( $type === 'h2' ) {
			$out .= "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$content}</h2>\n<!-- /wp:heading -->\n\n";
		} elseif ( $type === 'list' ) {
			$items = '';
			foreach ( (array) $content as $li ) {
				$items .= "<!-- wp:list-item -->\n<li>{$li}</li>\n<!-- /wp:list-item -->\n";
			}
			$out .= "<!-- wp:list -->\n<ul class=\"wp-block-list\">\n{$items}</ul>\n<!-- /wp:list -->\n\n";
		}
	}
	return $out;
}

/**
 * Inserts the FOX Weather launch essay as a draft post. Idempotent — if a
 * draft with the same slug already exists, returns its ID without dupes.
 */
function stevebaron_create_fox_weather_draft(): int {
	$slug = 'how-we-built-fox-weather-to-1';
	$existing = get_page_by_path( $slug, OBJECT, 'post' );
	if ( $existing ) return (int) $existing->ID;

	$post_id = wp_insert_post( [
		'post_title'   => 'How We Built FOX Weather to #1',
		'post_name'    => $slug,
		'post_status'  => 'draft',
		'post_type'    => 'post',
		'post_content' => stevebaron_fox_weather_post_content(),
		'post_excerpt' => "Notes on launching Fox Corporation's first new editorial brand in two decades — the team, the bets, and the launch day we ended up ahead of TikTok on the App Store.",
	], true );

	if ( is_wp_error( $post_id ) ) return 0;

	// Category: "Product" (create if needed)
	$cat_id = get_cat_ID( 'Product' );
	if ( ! $cat_id ) {
		$cat = wp_create_category( 'Product' );
		if ( ! is_wp_error( $cat ) ) $cat_id = (int) $cat;
	}
	if ( $cat_id ) wp_set_post_categories( $post_id, [ $cat_id ] );

	// Tags
	wp_set_post_tags( $post_id, [ 'FOX Weather', 'product', 'launch', 'App Store' ] );

	return (int) $post_id;
}

// ── Admin page: Tools → Site Setup ───────────────────────────────────────

add_action( 'admin_menu', function () {
	add_management_page(
		__( 'Site Setup (Steve Baron theme)', 'stevebaron' ),
		__( 'Site Setup', 'stevebaron' ),
		'manage_options',
		'stevebaron-setup',
		'stevebaron_setup_admin_page'
	);
} );

function stevebaron_setup_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) return;

	$ran     = false;
	$reseeded = false;
	$status  = [];
	$reseed_result = null;

	if ( isset( $_POST['stevebaron_setup_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stevebaron_setup_nonce'] ) ), 'stevebaron_setup' ) ) {
		$status = stevebaron_run_site_setup();
		$ran    = true;
	}

	if ( isset( $_POST['stevebaron_reseed_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stevebaron_reseed_nonce'] ) ), 'stevebaron_reseed' ) ) {
		$reseed_result = stevebaron_reseed_content();
		$reseeded      = true;
	}

	$fox_draft_id  = 0;
	$fox_created   = false;
	if ( isset( $_POST['stevebaron_fox_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stevebaron_fox_nonce'] ) ), 'stevebaron_fox' ) ) {
		$existing      = get_page_by_path( 'how-we-built-fox-weather-to-1', OBJECT, 'post' );
		$fox_created   = ! $existing;
		$fox_draft_id  = stevebaron_create_fox_weather_draft();
	}

	$pages = stevebaron_expected_pages();

	// Counts for the CV/Projects status display
	$cv_count       = (int) wp_count_posts( 'sb_experience' )->publish;
	$project_count  = (int) wp_count_posts( 'sb_project' )->publish;
	$fox_existing   = get_page_by_path( 'how-we-built-fox-weather-to-1', OBJECT, 'post' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Site Setup', 'stevebaron' ); ?></h1>
		<p>
			<?php esc_html_e( 'Creates the pages this theme expects (Home, About, CV, Projects, Writing, Photos, Now, Contact), binds each to the right page template, sets up Settings → Reading, and builds a Primary nav menu pointed at all of them.', 'stevebaron' ); ?>
		</p>
		<p>
			<?php esc_html_e( 'Safe to re-run. It will not touch existing pages other than to set the correct page template if missing.', 'stevebaron' ); ?>
		</p>

		<?php if ( $ran ) : ?>
			<div class="notice notice-success">
				<p><strong><?php esc_html_e( 'Setup complete.', 'stevebaron' ); ?></strong></p>
				<ul style="margin-left:1.5em;list-style:disc;">
					<?php foreach ( $status as $slug => $result ) :
						$label = $pages[ $slug ]['title'] ?? $slug;
						$msg   = [
							'created'          => __( 'Created', 'stevebaron' ),
							'existed'          => __( 'Already existed (left alone)', 'stevebaron' ),
							'template-updated' => __( 'Existed — page template fixed', 'stevebaron' ),
							'error'            => __( 'Error', 'stevebaron' ),
						][ $result ] ?? $result;
					?>
						<li><strong><?php echo esc_html( $label ); ?>:</strong> <?php echo esc_html( $msg ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p>
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="button"><?php esc_html_e( 'View site →', 'stevebaron' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" class="button"><?php esc_html_e( 'Edit Primary menu', 'stevebaron' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'options-reading.php' ) ); ?>" class="button"><?php esc_html_e( 'Reading settings', 'stevebaron' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<h2><?php esc_html_e( 'Current state', 'stevebaron' ); ?></h2>
		<table class="widefat striped" style="max-width:760px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Page', 'stevebaron' ); ?></th>
					<th><?php esc_html_e( 'Slug', 'stevebaron' ); ?></th>
					<th><?php esc_html_e( 'Template', 'stevebaron' ); ?></th>
					<th><?php esc_html_e( 'Status', 'stevebaron' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $pages as $slug => $cfg ) :
					$page = get_page_by_path( $slug );
					$tpl  = $page ? get_post_meta( $page->ID, '_wp_page_template', true ) : '';
					$tpl_ok = ! $cfg['template'] || $tpl === $cfg['template'];
				?>
					<tr>
						<td><?php echo esc_html( $cfg['title'] ); ?></td>
						<td><code><?php echo esc_html( $slug ); ?></code></td>
						<td>
							<?php if ( $cfg['template'] ) : ?>
								<code><?php echo esc_html( $cfg['template'] ); ?></code>
								<?php if ( $page && ! $tpl_ok ) : ?>
									<br><small style="color:#b32d2e;">
										<?php
										/* translators: %s: current template filename */
										printf( esc_html__( 'currently: %s', 'stevebaron' ), '<code>' . esc_html( $tpl ?: 'default' ) . '</code>' );
										?>
									</small>
								<?php endif; ?>
							<?php else : ?>
								<em><?php esc_html_e( 'default', 'stevebaron' ); ?></em>
							<?php endif; ?>
						</td>
						<td>
							<?php if ( $page ) : ?>
								<span style="color:#1a7f37;">✓ <?php esc_html_e( 'Exists', 'stevebaron' ); ?></span>
								&nbsp;<a href="<?php echo esc_url( get_edit_post_link( $page->ID ) ); ?>"><?php esc_html_e( 'edit', 'stevebaron' ); ?></a>
								&middot; <a href="<?php echo esc_url( get_permalink( $page->ID ) ); ?>" target="_blank"><?php esc_html_e( 'view', 'stevebaron' ); ?></a>
							<?php else : ?>
								<span style="color:#b32d2e;">✗ <?php esc_html_e( 'Missing', 'stevebaron' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<form method="post" style="margin-top:24px;">
			<?php wp_nonce_field( 'stevebaron_setup', 'stevebaron_setup_nonce' ); ?>
			<?php submit_button( __( 'Run site setup', 'stevebaron' ), 'primary large' ); ?>
		</form>

		<hr style="margin:48px 0 24px;">

		<h2><?php esc_html_e( 'CV & Projects content', 'stevebaron' ); ?></h2>
		<p>
			<?php
			printf(
				/* translators: 1: CV entry count, 2: project count */
				esc_html__( 'Currently: %1$d CV entries, %2$d projects.', 'stevebaron' ),
				(int) $cv_count,
				(int) $project_count
			);
			?>
		</p>

		<?php if ( $reseeded && $reseed_result ) : ?>
			<div class="notice notice-success">
				<p>
					<strong><?php esc_html_e( 'Reset complete.', 'stevebaron' ); ?></strong>
					<?php
					printf(
						/* translators: 1: CV trashed count, 2: project trashed count, 3: CV inserted count, 4: project inserted count */
						esc_html__( 'Trashed %1$d CV entries and %2$d projects, then inserted %3$d CV entries and %4$d projects from the resume.', 'stevebaron' ),
						(int) $reseed_result['trashed']['cv'],
						(int) $reseed_result['trashed']['projects'],
						(int) $reseed_result['inserted']['cv'],
						(int) $reseed_result['inserted']['projects']
					);
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( home_url( '/cv/' ) ); ?>" class="button" target="_blank"><?php esc_html_e( 'View CV page →', 'stevebaron' ); ?></a>
					<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=sb_experience&post_status=trash' ) ); ?>" class="button"><?php esc_html_e( 'View trashed entries', 'stevebaron' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<div class="notice notice-warning inline" style="padding:12px 14px;">
			<p style="margin:0;">
				<strong><?php esc_html_e( 'Heads up:', 'stevebaron' ); ?></strong>
				<?php esc_html_e( 'This will send all existing CV Entries and Projects to the Trash (recoverable from the admin) and recreate them from the canonical resume data shipped with the theme. Use this if the seed ran with old placeholder data.', 'stevebaron' ); ?>
			</p>
		</div>

		<form method="post" style="margin-top:16px;" onsubmit="return confirm('<?php echo esc_js( __( "Trash all existing CV Entries and Projects, then reseed from the resume? You can restore them from the admin Trash if needed.", "stevebaron" ) ); ?>');">
			<?php wp_nonce_field( 'stevebaron_reseed', 'stevebaron_reseed_nonce' ); ?>
			<button type="submit" class="button button-secondary" style="color:#b32d2e;border-color:#b32d2e;">
				<?php esc_html_e( 'Reset CV & Projects to resume data', 'stevebaron' ); ?>
			</button>
		</form>

		<hr style="margin:48px 0 24px;">

		<h2><?php esc_html_e( 'Drafts ready to paste', 'stevebaron' ); ?></h2>
		<p>
			<?php esc_html_e( "A long-form essay about launching FOX Weather is shipped with the theme. Click below to insert it as a draft post in your admin — it won't be published. You can then review, edit, and publish when you're ready.", 'stevebaron' ); ?>
		</p>

		<?php if ( $fox_draft_id ) : ?>
			<div class="notice notice-success">
				<p>
					<strong><?php echo $fox_created ? esc_html__( 'Draft created.', 'stevebaron' ) : esc_html__( 'Draft already existed — opening the existing one.', 'stevebaron' ); ?></strong>
					"How We Built FOX Weather to #1"
				</p>
				<p>
					<a href="<?php echo esc_url( get_edit_post_link( $fox_draft_id ) ); ?>" class="button button-primary"><?php esc_html_e( 'Open in editor →', 'stevebaron' ); ?></a>
					<a href="<?php echo esc_url( get_preview_post_link( $fox_draft_id ) ); ?>" class="button" target="_blank"><?php esc_html_e( 'Preview', 'stevebaron' ); ?></a>
				</p>
			</div>
		<?php endif; ?>

		<form method="post" style="margin-top:16px;">
			<?php wp_nonce_field( 'stevebaron_fox', 'stevebaron_fox_nonce' ); ?>
			<button type="submit" class="button button-primary">
				<?php echo $fox_existing
					? esc_html__( 'Open the FOX Weather draft', 'stevebaron' )
					: esc_html__( 'Create FOX Weather launch draft post', 'stevebaron' ); ?>
			</button>
			<?php if ( $fox_existing ) : ?>
				<span class="description" style="margin-left:8px;">
					<?php esc_html_e( 'A draft already exists. Clicking will just open it again.', 'stevebaron' ); ?>
				</span>
			<?php endif; ?>
		</form>
	</div>
	<?php
}

// ── Admin notice prompting setup ─────────────────────────────────────────

add_action( 'admin_notices', function () {
	if ( ! current_user_can( 'manage_options' ) ) return;
	if ( get_option( 'stevebaron_site_setup' ) ) return;
	$screen = get_current_screen();
	if ( $screen && $screen->id === 'tools_page_stevebaron-setup' ) return;
	?>
	<div class="notice notice-info is-dismissible">
		<p>
			<strong><?php esc_html_e( 'Steve Baron theme:', 'stevebaron' ); ?></strong>
			<?php esc_html_e( 'Finish setting up your site in one click.', 'stevebaron' ); ?>
			<a href="<?php echo esc_url( admin_url( 'tools.php?page=stevebaron-setup' ) ); ?>" class="button button-primary" style="margin-left:8px;">
				<?php esc_html_e( 'Run Site Setup', 'stevebaron' ); ?>
			</a>
		</p>
	</div>
	<?php
} );
