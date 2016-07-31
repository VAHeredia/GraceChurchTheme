<?php
/**
 * Grace-Church Framework
 *
 * @package grace_church
 * @since grace_church 1.0
 */

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Framework directory path from theme root
if ( ! defined( 'GRACE_CHURCH_FW_DIR' ) )		define( 'GRACE_CHURCH_FW_DIR', '/fw/' );

// Theme timing
if ( ! defined( 'GRACE_CHURCH_START_TIME' ) )	define( 'GRACE_CHURCH_START_TIME', microtime());			// Framework start time
if ( ! defined( 'GRACE_CHURCH_START_MEMORY' ) )	define( 'GRACE_CHURCH_START_MEMORY', memory_get_usage());	// Memory usage before core loading

// Global variables storage
global $GRACE_CHURCH_GLOBALS;
$GRACE_CHURCH_GLOBALS = array(
    'page_template'	=> '',
    'allowed_tags'	=> array(		// Allowed tags list (with attributes) in translations
        'b' => array(),
        'strong' => array(),
        'i' => array(),
        'em' => array(),
        'u' => array(),
        'a' => array(
            'href' => array(),
            'title' => array(),
            'target' => array(),
            'id' => array(),
            'class' => array()
        ),
        'span' => array(
            'id' => array(),
            'class' => array()
        )
    )
);

/* Theme setup section
-------------------------------------------------------------------- */
if ( !function_exists( 'grace_church_loader_theme_setup' ) ) {
    add_action( 'after_setup_theme', 'grace_church_loader_theme_setup', 20 );
    function grace_church_loader_theme_setup() {

        // Init admin url and nonce
        global $GRACE_CHURCH_GLOBALS;
        $GRACE_CHURCH_GLOBALS['admin_url']	= get_admin_url();
        $GRACE_CHURCH_GLOBALS['admin_nonce']= wp_create_nonce(get_admin_url());
        $GRACE_CHURCH_GLOBALS['ajax_url']	= admin_url('admin-ajax.php');
        $GRACE_CHURCH_GLOBALS['ajax_nonce']	= wp_create_nonce(admin_url('admin-ajax.php'));

        // Before init theme
        do_action('grace_church_action_before_init_theme');

        // Load current values for main theme options
        grace_church_load_main_options();

        // Theme core init - only for admin side. In frontend it called from header.php
        if ( is_admin() ) {
            grace_church_core_init_theme();
        }
    }
}


/* Include core parts
------------------------------------------------------------------------ */

// Manual load important libraries before load all rest files
// core.strings must be first - we use grace_church_str...() in the grace_church_get_file_dir()
require_once( (file_exists(get_stylesheet_directory().(GRACE_CHURCH_FW_DIR).'core/core.strings.php') ? get_stylesheet_directory() : get_template_directory()).(GRACE_CHURCH_FW_DIR).'core/core.strings.php' );
// core.files must be first - we use grace_church_get_file_dir() to include all rest parts
require_once( (file_exists(get_stylesheet_directory().(GRACE_CHURCH_FW_DIR).'core/core.files.php') ? get_stylesheet_directory() : get_template_directory()).(GRACE_CHURCH_FW_DIR).'core/core.files.php' );

// Include core files
grace_church_autoload_folder( 'core' );

// Include custom theme files
grace_church_autoload_folder( 'includes' );

// Include theme templates
grace_church_autoload_folder( 'templates' );

// Include theme widgets
grace_church_autoload_folder( 'widgets' );
?>