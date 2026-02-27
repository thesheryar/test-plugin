# Smart Contact Form Plugin - Developer Documentation

## Architecture Overview

This plugin follows a modern Object-Oriented Programming (OOP) approach with proper WordPress integration patterns.

### Plugin Structure

```
smart-contact-form/
├── index.php                           # Main plugin entry point
├── uninstall.php                       # Uninstall handler
├── includes/
│   └── class-smart-form-handler.php   # Main plugin class
├── assets/
│   ├── smart-form.css                 # Frontend stylesheet
│   ├── smart-form.js                  # Frontend JavaScript
│   └── admin-style.css                # Admin stylesheet
├── languages/
│   └── smart-contact-form.pot         # Translation template
├── phpcs.xml.dist                     # PHP CodeSniffer config
├── README.md                          # User documentation
└── DEVELOPMENT.md                     # This file
```

## Class Architecture

### Smart_Form_Handler Class

The main plugin class handles all functionality:

```php
class Smart_Form_Handler {
    private $version;
    private $plugin_slug;
    private $table_name;
    
    public function __construct()
    public function init_plugin()
    public function enqueue_frontend_assets()
    public function enqueue_admin_assets()
    public function render_form()
    public function handle_form_submission()
    public function validate_form_fields()
    public function insert_submission()
    public function register_admin_menu()
    public function render_admin_page()
    public function get_all_submissions()
    public static function create_tables()
    public function get_version()
}
```

## Initialization Flow

1. **Plugin Load** (`index.php`)
   - Constants are defined
   - Text domain is loaded
   - Main class is included
   - `plugins_loaded` hook triggers

2. **Class Initialization** (`Smart_Form_Handler`)
   - Constructor sets up hooks
   - Shortcode is registered
   - AJAX actions are registered
   - Admin menu is registered

3. **Frontend**
   - User loads page with `[smrt_form]` shortcode
   - Form CSS/JS are enqueued
   - Nonce is generated
   - Form HTML is rendered

4. **Form Submission**
   - AJAX POST to `wp-ajax.php`
   - Nonce is verified
   - Input is sanitized
   - Fields are validated
   - Data is inserted into database
   - JSON response is sent

## Database Schema

The plugin creates a single table:

```sql
CREATE TABLE wp_smart_form_submissions (
    id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message LONGTEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY created_at (created_at) COMMENT 'Index for sorting by date'
)
```

### Table Justification

- **BIGINT UNSIGNED**: Supports future growth up to 18 quintillion records
- **VARCHAR(100)**: Sufficient for names and emails
- **LONGTEXT**: Allows messages up to 4GB
- **DATETIME**: Automatic timestamp for sorting and filtering
- **Index on created_at**: Improves query performance for date-based sorting

## Security Implementation

### 1. Nonce Verification

```php
// Frontend form rendering
wp_nonce_field( 'smart_form_action', 'smart_form_nonce' );

// AJAX submission validation
$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
if ( ! wp_verify_nonce( $nonce, 'smart_form_nonce' ) ) {
    wp_send_json_error( __( 'Security check failed.', 'smart-contact-form' ) );
}
```

### 2. Input Sanitization

Different sanitization functions are used based on input type:

```php
$name    = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';
```

### 3. Data Validation

Each field is validated for:

- **Presence**: Not empty
- **Format**: Email format validation using `is_email()`
- **Length**: Minimum and maximum length checks
- **Type**: Correct data type

### 4. Prepared Statements

All database queries use prepared statements:

```php
$wpdb->insert(
    $this->table_name,
    array(
        'name'       => $name,
        'email'      => $email,
        'message'    => $message,
        'created_at' => current_time( 'mysql' ),
    ),
    array( '%s', '%s', '%s', '%s' ) // Placeholder types
);
```

### 5. Output Escaping

Different escaping functions are used based on context:

```php
esc_html()         // Escaping text content
esc_attr()         // Escaping HTML attributes
esc_url()          // Escaping URLs
wp_kses_post()     // Escaping HTML with allowed tags
wp_trim_words()    // Safe text trimming
```

### 6. Capability Checking

Admin pages verify user permissions:

```php
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( esc_html__( 'You do not have permission to access this page.', 'smart-contact-form' ) );
}
```

## Hooks and Filters

### WordPress Hooks Used

```php
// Initialization
add_action( 'plugins_loaded', 'smart_form_load_plugin' );
add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );

// Frontend Assets
add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_assets' ] );

// Admin Assets
add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

// Admin Menu
add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );

// AJAX Handlers
add_action( 'wp_ajax_nopriv_smrt_submit_form', [ $this, 'handle_form_submission' ] );
add_action( 'wp_ajax_smrt_submit_form', [ $this, 'handle_form_submission' ] );

// Plugin Activation/Deactivation
register_activation_hook( SMART_FORM_PLUGIN_FILE, function() { ... } );
register_deactivation_hook( SMART_FORM_PLUGIN_FILE, function() { ... } );

// Shortcode Registration
add_shortcode( 'smrt_form', [ $this, 'render_form' ] );

// Text Domain
add_action( 'init', 'smart_form_load_textdomain' );
```

## Extension Points

### 1. Adding Custom Validation

Extend the `validate_form_fields()` method:

```php
private function validate_form_fields( $name, $email, $message ) {
    $errors = array();
    
    // ... existing validation ...
    
    // Custom validation - e.g., spam checking
    if ( $this->is_spam( $message ) ) {
        $errors['message'] = __( 'Message appears to be spam.', 'smart-contact-form' );
    }
    
    return $errors;
}
```

### 2. Adding Email Notifications

Extend the `insert_submission()` method:

```php
private function insert_submission( $name, $email, $message ) {
    global $wpdb;
    
    $result = $wpdb->insert( ... );
    
    if ( $result ) {
        // Send email notification
        wp_mail(
            get_option( 'admin_email' ),
            sprintf( 'New Form Submission from %s', $name ),
            sprintf( "Email: %s\n\nMessage:\n%s", $email, $message )
        );
    }
    
    return false !== $result;
}
```

### 3. Adding Form Fields

Extend the `render_form()` method and update validation:

```php
public function render_form() {
    ob_start();
    ?>
    <form id="smart-contact-form" class="smart-contact-form" method="post">
        <?php wp_nonce_field( 'smart_form_action', 'smart_form_nonce' ); ?>
        
        <!-- Existing fields ... -->
        
        <!-- New field -->
        <div class="smart-form-group">
            <label for="smart_form_phone">Phone</label>
            <input type="tel" id="smart_form_phone" name="smart_form_phone" />
        </div>
    </form>
    <?php
    return ob_get_clean();
}
```

## Testing

### Manual Testing Checklist

- [ ] Plugin activates without errors
- [ ] Database table is created
- [ ] Shortcode renders on page/post
- [ ] Form validates required fields
- [ ] Form validates email format
- [ ] Form validates message length
- [ ] AJAX submission works
- [ ] Success message appears
- [ ] Data is saved to database
- [ ] Admin page displays submissions
- [ ] Admin page escapes all output
- [ ] Nonce verification works
- [ ] Invalid nonce shows error
- [ ] Sanitization removes dangerous code

### Backend Testing

```php
// Test database queries
$submissions = $wpdb->get_results(
    "SELECT * FROM wp_smart_form_submissions"
);
var_dump( $submissions );

// Test insertion
$result = $wpdb->insert(
    'wp_smart_form_submissions',
    array(
        'name' => 'Test User',
        'email' => 'test@example.com',
        'message' => 'This is a test message',
        'created_at' => current_time( 'mysql' ),
    ),
    array( '%s', '%s', '%s', '%s' )
);
var_dump( $result );
```

## Performance Considerations

1. **Database Indexing**: `created_at` column is indexed for faster sorting
2. **Query Limit**: Admin page limits results to 500 to prevent memory issues
3. **Asset Loading**: CSS/JS only loaded on pages with shortcode
4. **Caching**: Plugin properly clears cache on activation/deactivation

## Internationalization (i18n)

All user-facing strings use translation functions:

```php
__( 'String', 'smart-contact-form' )           // Translation only
esc_html__( 'String', 'smart-contact-form' )   // Translation + escaping
esc_html_e( 'String', 'smart-contact-form' )   // Translation + escaping + echo
_e( 'String', 'smart-contact-form' )           // Translation + echo
```

## Error Handling

The plugin handles errors gracefully:

```php
// AJAX errors
if ( ! wp_verify_nonce( $nonce, 'smart_form_nonce' ) ) {
    wp_send_json_error( __( 'Security check failed.', 'smart-contact-form' ) );
}

// Validation errors
if ( ! empty( $errors ) ) {
    wp_send_json_error( $errors );
}

// Database errors
if ( $inserted ) {
    wp_send_json_success( __( 'Thank you!', 'smart-contact-form' ) );
} else {
    wp_send_json_error( __( 'An error occurred.', 'smart-contact-form' ) );
}
```

## Code Style

The plugin follows WordPress Coding Standards:

- Class names use PascalCase: `Smart_Form_Handler`
- Function names use snake_case: `handle_form_submission()`
- Variable names use snake_case: `$form_data`
- Constants use UPPER_CASE: `SMART_FORM_PLUGIN_VERSION`
- Proper spacing and indentation (4 spaces)
- Documentation comments for all functions

## Debugging

Enable WordPress debug mode to help with development:

```php
// wp-config.php
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

// Logs to wp-content/debug.log
```

## Contributors

- Initial development: Sheryar Khan

## License

GPL-2.0-or-later
