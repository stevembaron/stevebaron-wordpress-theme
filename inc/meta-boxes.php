<?php
/**
 * Custom meta boxes for CPTs and page templates
 */

// ── Project meta box ──────────────────────────────────────────────────────────

function stevebaron_project_meta_boxes() {
	add_meta_box(
		'sb_project_details',
		__( 'Project Details', 'stevebaron' ),
		'stevebaron_project_meta_box_html',
		'sb_project',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'stevebaron_project_meta_boxes' );

function stevebaron_project_meta_box_html( $post ) {
	wp_nonce_field( 'sb_project_save', 'sb_project_nonce' );
	$year   = get_post_meta( $post->ID, '_sb_year', true );
	$status = get_post_meta( $post->ID, '_sb_status', true ) ?: 'Active';
	$link   = get_post_meta( $post->ID, '_sb_link', true );
	?>
	<table class="form-table">
		<tr>
			<th><label for="sb_year"><?php _e( 'Year / Period', 'stevebaron' ); ?></label></th>
			<td><input type="text" id="sb_year" name="sb_year" value="<?php echo esc_attr( $year ); ?>" class="widefat" placeholder="e.g. 2024 — present"></td>
		</tr>
		<tr>
			<th><label for="sb_status"><?php _e( 'Status', 'stevebaron' ); ?></label></th>
			<td>
				<select id="sb_status" name="sb_status" class="widefat">
					<?php foreach ( [ 'Active', 'Acquired', 'Shipped', 'Hobby', 'Archived' ] as $s ) : ?>
						<option value="<?php echo esc_attr( $s ); ?>" <?php selected( $status, $s ); ?>><?php echo esc_html( $s ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="sb_link"><?php _e( 'Project URL', 'stevebaron' ); ?></label></th>
			<td><input type="url" id="sb_link" name="sb_link" value="<?php echo esc_attr( $link ); ?>" class="widefat" placeholder="https://"></td>
		</tr>
	</table>
	<p class="description"><?php _e( 'Use <strong>Order</strong> in the Publish box to control the display order (lower = first).', 'stevebaron' ); ?></p>
	<?php
}

function stevebaron_project_meta_save( $post_id ) {
	if ( ! isset( $_POST['sb_project_nonce'] ) || ! wp_verify_nonce( $_POST['sb_project_nonce'], 'sb_project_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( isset( $_POST['sb_year'] ) )   update_post_meta( $post_id, '_sb_year',   sanitize_text_field( $_POST['sb_year'] ) );
	if ( isset( $_POST['sb_status'] ) ) update_post_meta( $post_id, '_sb_status', sanitize_text_field( $_POST['sb_status'] ) );
	if ( isset( $_POST['sb_link'] ) )   update_post_meta( $post_id, '_sb_link',   esc_url_raw( $_POST['sb_link'] ) );
}
add_action( 'save_post_sb_project', 'stevebaron_project_meta_save' );

// ── Experience (CV) meta box ──────────────────────────────────────────────────

function stevebaron_experience_meta_boxes() {
	add_meta_box(
		'sb_experience_details',
		__( 'CV Entry Details', 'stevebaron' ),
		'stevebaron_experience_meta_box_html',
		'sb_experience',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'stevebaron_experience_meta_boxes' );

function stevebaron_experience_meta_box_html( $post ) {
	wp_nonce_field( 'sb_experience_save', 'sb_experience_nonce' );
	$org   = get_post_meta( $post->ID, '_sb_org', true );
	$dates = get_post_meta( $post->ID, '_sb_dates', true );
	?>
	<table class="form-table">
		<tr>
			<th><label for="sb_org"><?php _e( 'Organization / Institution', 'stevebaron' ); ?></label></th>
			<td><input type="text" id="sb_org" name="sb_org" value="<?php echo esc_attr( $org ); ?>" class="widefat" placeholder="e.g. University of Utah"></td>
		</tr>
		<tr>
			<th><label for="sb_dates"><?php _e( 'Dates / Period', 'stevebaron' ); ?></label></th>
			<td><input type="text" id="sb_dates" name="sb_dates" value="<?php echo esc_attr( $dates ); ?>" class="widefat" placeholder="e.g. 2021 — present"></td>
		</tr>
	</table>
	<p class="description">
		<?php _e( 'The <strong>Title</strong> is your role/degree. Use <strong>Content</strong> for a description. Set <strong>Section</strong> via the CV Section taxonomy. Use <strong>Order</strong> to control display order within each section.', 'stevebaron' ); ?>
	</p>
	<?php
}

function stevebaron_experience_meta_save( $post_id ) {
	if ( ! isset( $_POST['sb_experience_nonce'] ) || ! wp_verify_nonce( $_POST['sb_experience_nonce'], 'sb_experience_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	if ( isset( $_POST['sb_org'] ) )   update_post_meta( $post_id, '_sb_org',   sanitize_text_field( $_POST['sb_org'] ) );
	if ( isset( $_POST['sb_dates'] ) ) update_post_meta( $post_id, '_sb_dates', sanitize_text_field( $_POST['sb_dates'] ) );
}
add_action( 'save_post_sb_experience', 'stevebaron_experience_meta_save' );

// ── Now page meta box ─────────────────────────────────────────────────────────

function stevebaron_now_meta_boxes( $post_type, $post ) {
	if ( $post_type !== 'page' ) return;
	if ( get_page_template_slug( $post->ID ) !== 'page-now.php' && $post->post_name !== 'now' ) return;

	add_meta_box(
		'sb_now_items',
		__( 'Now Page Items', 'stevebaron' ),
		'stevebaron_now_meta_box_html',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'stevebaron_now_meta_boxes', 10, 2 );

function stevebaron_now_meta_box_html( $post ) {
	wp_nonce_field( 'sb_now_save', 'sb_now_nonce' );

	$fields = [
		'_sb_now_working_on' => [ 'Working on', '💼' ],
		'_sb_now_reading'    => [ 'Reading', '📚' ],
		'_sb_now_watching'   => [ 'Watching', '📺' ],
		'_sb_now_learning'   => [ 'Learning', '🦀' ],
		'_sb_now_outside'    => [ 'Outside', '🥾' ],
		'_sb_now_yes_to'     => [ 'Saying yes to', '☕' ],
		'_sb_now_no_to'      => [ 'Saying no to', '✈️' ],
		'_sb_now_updated'    => [ 'Last updated (shown on page)', '' ],
		'_sb_now_location'   => [ 'Location', '' ],
	];
	?>
	<table class="form-table">
		<?php foreach ( $fields as $key => [ $label, $emoji ] ) : ?>
			<tr>
				<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo $emoji ? esc_html( $emoji . ' ' ) : ''; ?><?php echo esc_html( $label ); ?></label></th>
				<td><input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"
					value="<?php echo esc_attr( get_post_meta( $post->ID, $key, true ) ); ?>"
					class="widefat"
					<?php if ( $key === '_sb_now_updated' ) echo 'placeholder="e.g. April 28, 2026"'; ?>
					<?php if ( $key === '_sb_now_location' ) echo 'placeholder="e.g. Salt Lake City"'; ?>
				></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<?php
}

function stevebaron_now_meta_save( $post_id ) {
	if ( ! isset( $_POST['sb_now_nonce'] ) || ! wp_verify_nonce( $_POST['sb_now_nonce'], 'sb_now_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = [
		'_sb_now_working_on', '_sb_now_reading', '_sb_now_watching',
		'_sb_now_learning', '_sb_now_outside', '_sb_now_yes_to',
		'_sb_now_no_to', '_sb_now_updated', '_sb_now_location',
	];
	foreach ( $fields as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
		}
	}
}
add_action( 'save_post_page', 'stevebaron_now_meta_save' );

// ── About page "Currently Into" meta box ──────────────────────────────────────

function stevebaron_about_meta_boxes( $post_type, $post ) {
	if ( $post_type !== 'page' ) return;
	if ( get_page_template_slug( $post->ID ) !== 'page-about.php' && $post->post_name !== 'about' ) return;

	add_meta_box(
		'sb_about_currently',
		__( 'Currently Into', 'stevebaron' ),
		'stevebaron_about_meta_box_html',
		'page',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'stevebaron_about_meta_boxes', 10, 2 );

function stevebaron_about_meta_box_html( $post ) {
	wp_nonce_field( 'sb_about_save', 'sb_about_nonce' );

	$fields = [
		'_sb_about_reading'   => [ 'Reading', '📚' ],
		'_sb_about_listening' => [ 'Listening', '🎵' ],
		'_sb_about_building'  => [ 'Building', '🛠' ],
		'_sb_about_cooking'   => [ 'Cooking', '🍞' ],
		'_sb_about_watching'  => [ 'Watching', '📺' ],
		'_sb_about_skiing'    => [ 'Skiing / Outdoors', '⛷' ],
	];
	?>
	<table class="form-table">
		<?php foreach ( $fields as $key => [ $label, $emoji ] ) : ?>
			<tr>
				<th><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $emoji . ' ' . $label ); ?></label></th>
				<td><input type="text" id="<?php echo esc_attr( $key ); ?>" name="<?php echo esc_attr( $key ); ?>"
					value="<?php echo esc_attr( get_post_meta( $post->ID, $key, true ) ); ?>"
					class="widefat"
				></td>
			</tr>
		<?php endforeach; ?>
	</table>
	<p class="description"><?php _e( 'Leave any field blank to hide it on the About page.', 'stevebaron' ); ?></p>
	<?php
}

function stevebaron_about_meta_save( $post_id ) {
	if ( ! isset( $_POST['sb_about_nonce'] ) || ! wp_verify_nonce( $_POST['sb_about_nonce'], 'sb_about_save' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;

	$fields = [
		'_sb_about_reading', '_sb_about_listening', '_sb_about_building',
		'_sb_about_cooking', '_sb_about_watching', '_sb_about_skiing',
	];
	foreach ( $fields as $key ) {
		if ( isset( $_POST[ $key ] ) ) {
			update_post_meta( $post_id, $key, sanitize_text_field( $_POST[ $key ] ) );
		}
	}
}
add_action( 'save_post_page', 'stevebaron_about_meta_save' );
