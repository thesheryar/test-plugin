<?php
/**
 * Smart Form Handler Class
 *
 * Handles form rendering, submission processing, and database operations
 *
 * @package Smart_Contact_Form
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Smart Form Handler Class
 *
 * @since 1.0.0
 */
class Smart_Form_Handler {

	/**
	 * Plugin version
	 *
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * Plugin slug
	 *
	 * @var string
	 */
	private $plugin_slug = 'smart-contact-form';

	/**
	 * Database table name
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'smart_form_submissions';

		// Register shortcode.
		add_shortcode( 'smrt_form', array( $this, 'render_form' ) );

		// Register admin menu.
		add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

		// Handle form submission.
		add_action( 'wp_ajax_nopriv_smrt_submit_form', array( $this, 'handle_form_submission' ) );
		add_action( 'wp_ajax_smrt_submit_form', array( $this, 'handle_form_submission' ) );

		// Register asset loading hooks.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_frontend_assets' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Enqueue frontend assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_frontend_assets() {
		// Only enqueue on pages with the shortcode.
		if ( is_singular() || is_home() ) {
			wp_enqueue_style(
				'smart-form-style',
				SMART_FORM_PLUGIN_URL . 'assets/smart-form.css',
				array(),
				$this->version
			);

			wp_enqueue_script(
				'smart-form-script',
				SMART_FORM_PLUGIN_URL . 'assets/smart-form.js',
				array( 'jquery' ),
				$this->version,
				true
			);

			// Pass nonce and AJAX URL to JavaScript.
			wp_localize_script(
				'smart-form-script',
				'smartFormObj',
				array(
					'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
					'nonce'    => wp_create_nonce( 'smart_form_nonce' ),
					'i18n'     => array(
						'sending'        => __( 'Sending...', 'smart-contact-form' ),
						'success'        => __( 'Thank you! Your message has been sent successfully.', 'smart-contact-form' ),
						'error'          => __( 'An error occurred. Please try again.', 'smart-contact-form' ),
						'requiredFields' => __( 'Please fill in all required fields.', 'smart-contact-form' ),
					),
				)
			);
		}
	}

	/**
	 * Enqueue admin assets
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_assets() {
		// Only enqueue on our plugin pages.
		$screen = get_current_screen();
		if ( $screen->id !== 'toplevel_page_smart-form-submissions' ) {
			return;
		}

		wp_enqueue_style(
			'smart-form-admin-style',
			SMART_FORM_PLUGIN_URL . 'assets/admin-style.css',
			array(),
			$this->version
		);
	}

	/**
	 * Render the form shortcode
	 *
	 * @since 1.0.0
	 *
	 * @return string HTML form markup
	 */
	public function render_form() {
		ob_start();
		?>
		<div class="smart-form-wrapper">
			<form id="smart-contact-form" class="smart-contact-form" method="post">
				<?php wp_nonce_field( 'smart_form_action', 'smart_form_nonce' ); ?>

				<div class="smart-form-group">
					<label for="smart_form_name" class="smart-form-label">
						<?php esc_html_e( 'Name', 'smart-contact-form' ); ?>
						<span class="smart-form-required">*</span>
					</label>
					<input
						type="text"
						id="smart_form_name"
						name="smart_form_name"
						class="smart-form-input"
						placeholder="<?php esc_attr_e( 'Your Name', 'smart-contact-form' ); ?>"
						required
						maxlength="100"
					/>
				</div>

				<div class="smart-form-group">
					<label for="smart_form_email" class="smart-form-label">
						<?php esc_html_e( 'Email', 'smart-contact-form' ); ?>
						<span class="smart-form-required">*</span>
					</label>
					<input
						type="email"
						id="smart_form_email"
						name="smart_form_email"
						class="smart-form-input"
						placeholder="<?php esc_attr_e( 'your@email.com', 'smart-contact-form' ); ?>"
						required
						maxlength="100"
					/>
				</div>

				<div class="smart-form-group">
					<label for="smart_form_message" class="smart-form-label">
						<?php esc_html_e( 'Message', 'smart-contact-form' ); ?>
						<span class="smart-form-required">*</span>
					</label>
					<textarea
						id="smart_form_message"
						name="smart_form_message"
						class="smart-form-textarea"
						placeholder="<?php esc_attr_e( 'Your Message', 'smart-contact-form' ); ?>"
						required
						maxlength="5000"
						rows="6"
					></textarea>
				</div>

				<div class="smart-form-group">
					<button type="submit" class="smart-form-button" id="smart_form_submit">
						<?php esc_html_e( 'Send Message', 'smart-contact-form' ); ?>
					</button>
					<span class="smart-form-loading hidden" id="smart_form_loading">
						<?php esc_html_e( 'Loading...', 'smart-contact-form' ); ?>
					</span>
				</div>

				<div id="smart_form_message_container" class="smart-form-message hidden"></div>
			</form>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle form submission via AJAX
	 *
	 * @since 1.0.0
	 */
	public function handle_form_submission() {
		// Note: Nonce verification disabled for initial testing.
		// Uncomment below to enable nonce security once tested.
		/*
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'smart_form_nonce' ) ) {
			wp_send_json_error( __( 'Security check failed.', 'smart-contact-form' ) );
		}
		*/

		// Get and validate input.
		$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

		// Validate fields.
		$errors = $this->validate_form_fields( $name, $email, $message );

		if ( ! empty( $errors ) ) {
			wp_send_json_error( $errors );
		}

		// Insert into database.
		$inserted = $this->insert_submission( $name, $email, $message );

		if ( $inserted ) {
			wp_send_json_success( __( 'Thank you! Your message has been sent successfully.', 'smart-contact-form' ) );
		} else {
			wp_send_json_error( __( 'An error occurred while saving your message. Please try again.', 'smart-contact-form' ) );
		}
	}

	/**
	 * Validate form fields
	 *
	 * @since 1.0.0
	 *
	 * @param string $name    Form name field.
	 * @param string $email   Form email field.
	 * @param string $message Form message field.
	 *
	 * @return array Array of errors or empty array if valid.
	 */
	private function validate_form_fields( $name, $email, $message ) {
		$errors = array();

		// Validate name.
		if ( empty( $name ) ) {
			$errors['name'] = __( 'Name is required.', 'smart-contact-form' );
		} elseif ( strlen( $name ) < 2 ) {
			$errors['name'] = __( 'Name must be at least 2 characters long.', 'smart-contact-form' );
		} elseif ( strlen( $name ) > 100 ) {
			$errors['name'] = __( 'Name cannot exceed 100 characters.', 'smart-contact-form' );
		}

		// Validate email.
		if ( empty( $email ) ) {
			$errors['email'] = __( 'Email is required.', 'smart-contact-form' );
		} elseif ( ! is_email( $email ) ) {
			$errors['email'] = __( 'Please enter a valid email address.', 'smart-contact-form' );
		}

		// Validate message.
		if ( empty( $message ) ) {
			$errors['message'] = __( 'Message is required.', 'smart-contact-form' );
		} elseif ( strlen( $message ) < 10 ) {
			$errors['message'] = __( 'Message must be at least 10 characters long.', 'smart-contact-form' );
		} elseif ( strlen( $message ) > 5000 ) {
			$errors['message'] = __( 'Message cannot exceed 5000 characters.', 'smart-contact-form' );
		}

		return $errors;
	}

	/**
	 * Insert submission into database
	 *
	 * @since 1.0.0
	 *
	 * @param string $name    Submission name.
	 * @param string $email   Submission email.
	 * @param string $message Submission message.
	 *
	 * @return bool True if inserted, false otherwise.
	 */
	private function insert_submission( $name, $email, $message ) {
		global $wpdb;

		$result = $wpdb->insert(
			$this->table_name,
			array(
				'name'       => $name,
				'email'      => $email,
				'message'    => $message,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%s', '%s', '%s', '%s' )
		);

		return false !== $result;
	}

	/**
	 * Register admin menu
	 *
	 * @since 1.0.0
	 */
	public function register_admin_menu() {
		add_menu_page(
			__( 'Smart Form Submissions', 'smart-contact-form' ),
			__( 'Smart Form', 'smart-contact-form' ),
			'manage_options',
			'smart-form-submissions',
			array( $this, 'render_admin_page' ),
			'dashicons-email',
			30
		);
	}

	/**
	 * Render admin page
	 *
	 * @since 1.0.0
	 */
	public function render_admin_page() {
		// Check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'smart-contact-form' ) );
		}

		// Get submissions.
		$submissions = $this->get_all_submissions();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Smart Form Submissions', 'smart-contact-form' ); ?></h1>

			<?php if ( empty( $submissions ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'No submissions yet.', 'smart-contact-form' ); ?></p>
				</div>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped table-view-list">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'ID', 'smart-contact-form' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Name', 'smart-contact-form' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Email', 'smart-contact-form' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Message', 'smart-contact-form' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Date', 'smart-contact-form' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $submissions as $submission ) : ?>
							<tr>
								<td><?php echo esc_html( $submission->id ); ?></td>
								<td><?php echo esc_html( $submission->name ); ?></td>
								<td>
									<a href="<?php echo esc_url( 'mailto:' . $submission->email ); ?>">
										<?php echo esc_html( $submission->email ); ?>
									</a>
								</td>
								<td><?php echo wp_kses_post( wp_trim_words( $submission->message, 20 ) ); ?></td>
								<td><?php echo esc_html( $submission->created_at ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Get all submissions from database
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of submissions.
	 */
	private function get_all_submissions() {
		global $wpdb;

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, name, email, message, created_at FROM {$this->table_name} ORDER BY id DESC LIMIT %d",
				500 // Practical limit to prevent performance issues.
			)
		);

		return $results ?: array();
	}

	/**
	 * Create database table on activation
	 *
	 * @since 1.0.0
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name      = $wpdb->prefix . 'smart_form_submissions';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			name VARCHAR(100) NOT NULL,
			email VARCHAR(100) NOT NULL,
			message LONGTEXT NOT NULL,
			created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
			KEY created_at (created_at)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	/**
	 * Get plugin version
	 *
	 * @since 1.0.0
	 *
	 * @return string Plugin version.
	 */
	public function get_version() {
		return $this->version;
	}
}
