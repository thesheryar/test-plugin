# Smart Contact Form Plugin

A production-ready WordPress plugin for handling form submissions with secure database storage.

## Features

- ✅ OOP Architecture
- ✅ Secure form submission with AJAX
- ✅ Nonce verification
- ✅ Input sanitization & validation
- ✅ Database storage with custom table
- ✅ Admin dashboard to view submissions
- ✅ WordPress 6.4+ compatible
- ✅ PHP 8.0+ required
- ✅ Prepared statements
- ✅ Proper escaping on output
- ✅ WordPress coding standards compliant
- ✅ Translatable strings

## Installation

1. Download or clone this plugin into your WordPress plugins directory (`wp-content/plugins/`)
2. Activate the plugin through the WordPress admin dashboard
3. A database table will be automatically created on activation

## Usage

### Shortcode

Add the contact form to any page or post using the shortcode:

```
[smrt_form]
```

### Admin Dashboard

Access submitted forms through the WordPress admin menu:
- Navigate to **Smart Form** in the left sidebar
- View all submissions in a table format

## Technical Specifications

### Security Features

- **Nonce Verification**: AJAX requests are verified with WordPress nonces
- **Input Sanitization**: All user input is sanitized using WordPress functions (`sanitize_text_field`, `sanitize_email`, `sanitize_textarea_field`)
- **Output Escaping**: All output is properly escaped using `esc_html`, `esc_attr`, `esc_url`, and `wp_kses_post`
- **Prepared Statements**: Database queries use `$wpdb->prepare()` to prevent SQL injection
- **No Direct Access**: Plugin file checks for `ABSPATH` to prevent direct access

### Database Table

The plugin creates a custom table with the following structure:

```sql
CREATE TABLE wp_smart_form_submissions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message LONGTEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    KEY created_at (created_at)
)
```

### Form Fields

- **Name** (text, required, 2-100 characters)
- **Email** (email, required, valid email format)
- **Message** (textarea, required, 10-5000 characters)

### Hooks & Filters

The plugin provides several hooks for customization:

- `plugins_loaded` - Main plugin initialization
- `wp_enqueue_scripts` - Frontend assets loading
- `admin_enqueue_scripts` - Admin assets loading
- `admin_menu` - Admin menu registration
- `wp_ajax_nopriv_smrt_submit_form` - Form submission (unauthenticated users)
- `wp_ajax_smrt_submit_form` - Form submission (authenticated users)

### Plugin Constants

- `SMART_FORM_PLUGIN_VERSION` - Plugin version
- `SMART_FORM_PLUGIN_PATH` - Plugin directory path
- `SMART_FORM_PLUGIN_URL` - Plugin directory URL
- `SMART_FORM_PLUGIN_FILE` - Main plugin file path
- `SMART_FORM_PLUGIN_BASENAME` - Plugin basename

## File Structure

```
my-plugin/
├── index.php                              # Main plugin file
├── includes/
│   └── class-smart-form-handler.php       # Main plugin class
├── assets/
│   ├── smart-form.css                     # Frontend styles
│   ├── smart-form.js                      # Frontend JavaScript
│   └── admin-style.css                    # Admin styles
├── languages/
│   └── smart-contact-form.pot             # Translation template
└── README.md                              # This file
```

## Development

### Class Methods

#### Smart_Form_Handler

- `__construct()` - Initialize hooks
- `init_plugin()` - Register shortcode and actions
- `enqueue_frontend_assets()` - Load frontend CSS/JS
- `enqueue_admin_assets()` - Load admin CSS
- `render_form()` - Render form shortcode
- `handle_form_submission()` - Process AJAX submission
- `register_admin_menu()` - Register admin menu
- `render_admin_page()` - Render admin dashboard
- `create_tables()` - Create database table (static method)

### Filters & Validations

All form input is:
1. Checked for required fields
2. Validated for proper format (email validation, length checks)
3. Sanitized before insertion
4. Escaped before output

### Error Handling

The plugin provides user-friendly error messages:
- Invalid email format
- Missing required fields
- Message too short or too long
- Database insertion errors

## Requirements

- WordPress 6.4 or higher
- PHP 8.0 or higher
- MySQL 5.7 or higher (included with most WordPress hosting)

## Compatibility

- ✅ WordPress 6.4+
- ✅ PHP 8.0+
- ✅ PHP 8.1+
- ✅ PHP 8.2+
- ✅ PHP 8.3+
- ✅ Multisite compatible
- ✅ Translation ready

## Code Quality

- Follows WordPress Coding Standards
- Uses proper documentation comments
- Implements proper error handling
- Uses `wp_send_json_*` for AJAX responses
- Uses `wp_create_nonce` for security
- Proper use of hooks and filters

## Customization

You can customize the plugin behavior by modifying:

1. **Form fields** - Edit `render_form()` method in the class
2. **Validation rules** - Edit `validate_form_fields()` method
3. **Admin table columns** - Edit `render_admin_page()` method
4. **Styling** - Modify CSS files in the assets directory
5. **Email notifications** - Add `wp_mail()` calls in `insert_submission()`

## Support

For issues or feature requests, please contact the plugin developer.

## License

GPL-2.0-or-later

## Changelog

### Version 1.0.0
- Initial release
- Form submission with AJAX
- Database storage
- Admin dashboard
- Security features implemented

---

**Author:** Sheryar Khan  
**Version:** 1.0.0  
**Last Updated:** February 2026
