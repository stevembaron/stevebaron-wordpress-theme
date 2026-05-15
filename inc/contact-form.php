<?php
/**
 * Contact form handler
 *
 * Renders the form on /contact/ (when enabled in Customizer) and handles
 * submissions via admin-post.php. Defensive in depth:
 *   - WP nonce for CSRF
 *   - Honeypot field ("website") — bots fill it, humans don't
 *   - Time-based check — submissions in under 3 seconds = bot
 *   - Rate limit — max 3 submissions per IP per hour (transient)
 *   - Strict field length caps
 *   - Wrapped in is_email() / sanitize_*() for output safety
 */

if ( ! defined( 'ABSPATH' ) ) exit;

const STEVEBARON_CONTACT_ACTION = 'sb_contact_submit';
const STEVEBARON_CONTACT_RATE   = 3;        // max submissions
const STEVEBARON_CONTACT_WINDOW = 3600;     // per N seconds (1 hour)
const STEVEBARON_CONTACT_MIN_S  = 3;        // form must be open at least N seconds

/**
 * Renders the contact form HTML. Called from page-contact.php.
 */
function stevebaron_contact_form_render(): void {
	if ( ! get_theme_mod( 'sb_contact_form_enabled', true ) ) return;
	$success = isset( $_GET['contact'] ) && $_GET['contact'] === 'sent';
	$error   = isset( $_GET['contact'] ) && $_GET['contact'] === 'error' ? sanitize_text_field( wp_unslash( $_GET['why'] ?? '' ) ) : '';
	$action  = esc_url( admin_url( 'admin-post.php' ) );
	?>
	<section class="contact-form-wrap" id="contact-form">
		<h2 class="contact-form-heading"><?php esc_html_e( 'Or use the form', 'stevebaron' ); ?></h2>

		<?php if ( $success ) : ?>
			<div class="contact-form-success" role="status">
				<strong><?php esc_html_e( 'Sent.', 'stevebaron' ); ?></strong>
				<?php esc_html_e( "Thanks — your message is on its way. I'll respond within a day or two.", 'stevebaron' ); ?>
			</div>
		<?php endif; ?>

		<?php if ( $error ) : ?>
			<div class="contact-form-error" role="alert">
				<strong><?php esc_html_e( "Couldn't send.", 'stevebaron' ); ?></strong>
				<?php
				$messages = [
					'nonce'    => __( 'Security check failed. Please reload the page and try again.', 'stevebaron' ),
					'spam'     => __( 'Submission flagged as spam. If this is a mistake, email me directly.', 'stevebaron' ),
					'fields'   => __( 'Please fill in your name, a valid email, and a message.', 'stevebaron' ),
					'rate'     => __( 'Too many submissions from this address recently. Try again later.', 'stevebaron' ),
					'mail'     => __( 'The email server rejected the message. Please email me directly.', 'stevebaron' ),
				];
				echo esc_html( $messages[ $error ] ?? __( 'Unknown error.', 'stevebaron' ) );
				?>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo $action; ?>" class="contact-form" novalidate>
			<input type="hidden" name="action" value="<?php echo esc_attr( STEVEBARON_CONTACT_ACTION ); ?>">
			<?php wp_nonce_field( STEVEBARON_CONTACT_ACTION, 'sb_contact_nonce' ); ?>
			<input type="hidden" name="sb_form_ts" value="<?php echo esc_attr( time() ); ?>">

			<!-- Honeypot. Real users never see this; bots tend to fill every text field. -->
			<div class="sb-honey" aria-hidden="true">
				<label>Your website (leave empty)
					<input type="text" name="website" tabindex="-1" autocomplete="off">
				</label>
			</div>

			<div class="contact-form-row">
				<label class="contact-form-field">
					<span><?php esc_html_e( 'Your name', 'stevebaron' ); ?> <em>*</em></span>
					<input type="text" name="sb_name" required maxlength="120" autocomplete="name">
				</label>
				<label class="contact-form-field">
					<span><?php esc_html_e( 'Email', 'stevebaron' ); ?> <em>*</em></span>
					<input type="email" name="sb_email" required maxlength="160" autocomplete="email" inputmode="email">
				</label>
			</div>

			<label class="contact-form-field">
				<span><?php esc_html_e( 'Company (optional)', 'stevebaron' ); ?></span>
				<input type="text" name="sb_company" maxlength="160" autocomplete="organization">
			</label>

			<label class="contact-form-field">
				<span><?php esc_html_e( "What are you building?", 'stevebaron' ); ?> <em>*</em></span>
				<textarea name="sb_message" required minlength="20" maxlength="4000" rows="5" placeholder="<?php esc_attr_e( 'A short description of what you’re working on and what you’re hoping to talk about.', 'stevebaron' ); ?>"></textarea>
			</label>

			<div class="contact-form-row">
				<label class="contact-form-field">
					<span><?php esc_html_e( 'Type of conversation', 'stevebaron' ); ?></span>
					<select name="sb_type">
						<option value=""><?php esc_html_e( 'Not sure yet', 'stevebaron' ); ?></option>
						<option value="advisory"><?php esc_html_e( 'Advisory engagement', 'stevebaron' ); ?></option>
						<option value="fractional"><?php esc_html_e( 'Fractional executive', 'stevebaron' ); ?></option>
						<option value="fulltime"><?php esc_html_e( 'Full-time role', 'stevebaron' ); ?></option>
						<option value="speaking"><?php esc_html_e( 'Speaking / panel', 'stevebaron' ); ?></option>
						<option value="press"><?php esc_html_e( 'Press / interview', 'stevebaron' ); ?></option>
						<option value="other"><?php esc_html_e( 'Something else', 'stevebaron' ); ?></option>
					</select>
				</label>
				<label class="contact-form-field">
					<span><?php esc_html_e( 'Timeline', 'stevebaron' ); ?></span>
					<select name="sb_timeline">
						<option value=""><?php esc_html_e( 'Whenever works', 'stevebaron' ); ?></option>
						<option value="this-month"><?php esc_html_e( 'This month', 'stevebaron' ); ?></option>
						<option value="this-quarter"><?php esc_html_e( 'This quarter', 'stevebaron' ); ?></option>
						<option value="exploring"><?php esc_html_e( 'Just exploring', 'stevebaron' ); ?></option>
					</select>
				</label>
			</div>

			<button type="submit" class="btn btn-primary contact-form-submit">
				<span class="contact-form-submit-label"><?php esc_html_e( 'Send message →', 'stevebaron' ); ?></span>
				<span class="contact-form-submit-busy" hidden><?php esc_html_e( 'Sending…', 'stevebaron' ); ?></span>
			</button>
			<p class="contact-form-foot mono muted">
				<?php esc_html_e( 'No CAPTCHAs. No tracking. Goes straight to my inbox.', 'stevebaron' ); ?>
			</p>
		</form>
	</section>
	<?php
}

/**
 * Handle contact form submission. Hooked into admin-post for logged-out
 * and logged-in users.
 */
function stevebaron_contact_form_handle(): void {
	$contact_url = home_url( '/contact/' );

	$bail = function ( string $why ) use ( $contact_url ) {
		if ( wp_doing_ajax() || ( $_SERVER['HTTP_ACCEPT'] ?? '' ) === 'application/json' ) {
			wp_send_json( [ 'ok' => false, 'error' => $why ], 400 );
		}
		wp_safe_redirect( add_query_arg( [ 'contact' => 'error', 'why' => $why ], $contact_url ) . '#contact-form' );
		exit;
	};

	// Nonce
	if ( ! isset( $_POST['sb_contact_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sb_contact_nonce'] ) ), STEVEBARON_CONTACT_ACTION ) ) {
		$bail( 'nonce' );
	}

	// Honeypot
	if ( ! empty( $_POST['website'] ) ) {
		$bail( 'spam' );
	}

	// Time-based check
	$ts = isset( $_POST['sb_form_ts'] ) ? (int) $_POST['sb_form_ts'] : 0;
	if ( $ts <= 0 || ( time() - $ts ) < STEVEBARON_CONTACT_MIN_S ) {
		$bail( 'spam' );
	}

	// Field sanitization
	$name    = isset( $_POST['sb_name'] )    ? trim( sanitize_text_field( wp_unslash( $_POST['sb_name'] ) ) ) : '';
	$email   = isset( $_POST['sb_email'] )   ? sanitize_email( wp_unslash( $_POST['sb_email'] ) ) : '';
	$company = isset( $_POST['sb_company'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['sb_company'] ) ) ) : '';
	$message = isset( $_POST['sb_message'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['sb_message'] ) ) ) : '';
	$type    = isset( $_POST['sb_type'] )    ? sanitize_key( wp_unslash( $_POST['sb_type'] ) ) : '';
	$tl      = isset( $_POST['sb_timeline'] ) ? sanitize_key( wp_unslash( $_POST['sb_timeline'] ) ) : '';

	if ( $name === '' || ! is_email( $email ) || strlen( $message ) < 20 ) {
		$bail( 'fields' );
	}
	if ( strlen( $name ) > 120 || strlen( $email ) > 160 || strlen( $company ) > 160 || strlen( $message ) > 4000 ) {
		$bail( 'fields' );
	}

	// Rate limit per IP (1 hour window)
	$ip       = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$rate_key = 'sb_contact_rate_' . md5( $ip );
	$count    = (int) get_transient( $rate_key );
	if ( $count >= STEVEBARON_CONTACT_RATE ) {
		$bail( 'rate' );
	}
	set_transient( $rate_key, $count + 1, STEVEBARON_CONTACT_WINDOW );

	// Build email
	$type_labels = [
		'advisory'   => 'Advisory engagement',
		'fractional' => 'Fractional executive',
		'fulltime'   => 'Full-time role',
		'speaking'   => 'Speaking / panel',
		'press'      => 'Press / interview',
		'other'      => 'Something else',
	];
	$tl_labels = [
		'this-month'   => 'This month',
		'this-quarter' => 'This quarter',
		'exploring'    => 'Just exploring',
	];

	$to = sanitize_email( get_theme_mod( 'sb_contact_form_to', '' ) ?: get_theme_mod( 'sb_social_email', '' ) );
	if ( ! $to ) $to = get_option( 'admin_email' );

	$subject = sprintf(
		/* translators: %1$s = site name, %2$s = sender name */
		'[%1$s] %2$s wants to talk',
		wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
		$name
	);

	$body  = "From:    {$name} <{$email}>\n";
	if ( $company ) $body .= "Company: {$company}\n";
	if ( $type )    $body .= "Type:    " . ( $type_labels[ $type ] ?? $type ) . "\n";
	if ( $tl )      $body .= "Timeline: " . ( $tl_labels[ $tl ] ?? $tl ) . "\n";
	$body .= "\n---\n\n{$message}\n\n---\n";
	$body .= "\nSent from " . home_url( '/contact/' ) . "\n";
	if ( $ip ) $body .= "IP: {$ip}\n";

	$headers = [
		'From: ' . get_bloginfo( 'name' ) . ' <wordpress@' . wp_parse_url( home_url(), PHP_URL_HOST ) . '>',
		'Reply-To: ' . $name . ' <' . $email . '>',
	];

	$ok = wp_mail( $to, wp_specialchars_decode( $subject, ENT_QUOTES ), $body, $headers );

	if ( wp_doing_ajax() || ( $_SERVER['HTTP_ACCEPT'] ?? '' ) === 'application/json' ) {
		wp_send_json( [ 'ok' => (bool) $ok, 'error' => $ok ? '' : 'mail' ], $ok ? 200 : 500 );
	}

	if ( ! $ok ) $bail( 'mail' );

	wp_safe_redirect( add_query_arg( 'contact', 'sent', $contact_url ) . '#contact-form' );
	exit;
}
add_action( 'admin_post_'        . STEVEBARON_CONTACT_ACTION, 'stevebaron_contact_form_handle' );
add_action( 'admin_post_nopriv_' . STEVEBARON_CONTACT_ACTION, 'stevebaron_contact_form_handle' );
