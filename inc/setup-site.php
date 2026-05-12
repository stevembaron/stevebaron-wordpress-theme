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

	$ran    = false;
	$status = [];
	if ( isset( $_POST['stevebaron_setup_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['stevebaron_setup_nonce'] ) ), 'stevebaron_setup' ) ) {
		$status = stevebaron_run_site_setup();
		$ran    = true;
	}

	$pages = stevebaron_expected_pages();
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
