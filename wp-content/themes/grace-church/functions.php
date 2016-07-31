<?php
/**
 * Theme sprecific functions and definitions
 */


/* Theme setup section
------------------------------------------------------------------- */

// Set the content width based on the theme's design and stylesheet.
if ( ! isset( $content_width ) ) $content_width = 1170; /* pixels */

// Add theme specific actions and filters
// Attention! Function were add theme specific actions and filters handlers must have priority 1
if ( !function_exists( 'grace_church_theme_setup' ) ) {
	add_action( 'grace_church_action_before_init_theme', 'grace_church_theme_setup', 1 );
	function grace_church_theme_setup() {

		// Register theme menus
		add_filter( 'grace_church_filter_add_theme_menus',		'grace_church_add_theme_menus' );

		// Register theme sidebars
		add_filter( 'grace_church_filter_add_theme_sidebars',	'grace_church_add_theme_sidebars' );

		// Set options for importer
		add_filter( 'grace_church_filter_importer_options',		'grace_church_set_importer_options' );

	}
}


// Add/Remove theme nav menus
if ( !function_exists( 'grace_church_add_theme_menus' ) ) {
	//add_filter( 'grace_church_filter_add_theme_menus', 'grace_church_add_theme_menus' );
	function grace_church_add_theme_menus($menus) {
		//For example:
		//$menus['menu_footer'] = esc_html__('Footer Menu', 'grace-church');
		//if (isset($menus['menu_panel'])) unset($menus['menu_panel']);
		return $menus;
	}
}


// Add theme specific widgetized areas
if ( !function_exists( 'grace_church_add_theme_sidebars' ) ) {
	//add_filter( 'grace_church_filter_add_theme_sidebars',	'grace_church_add_theme_sidebars' );
	function grace_church_add_theme_sidebars($sidebars=array()) {
		if (is_array($sidebars)) {
			$theme_sidebars = array(
				'sidebar_main'		=> esc_html__( 'Main Sidebar', 'grace-church' ),
				'sidebar_footer'	=> esc_html__( 'Footer Sidebar', 'grace-church' )
			);
			if (grace_church_exists_woocommerce()) {
				$theme_sidebars['sidebar_cart']  = esc_html__( 'WooCommerce Cart Sidebar', 'grace-church' );
			}
			$sidebars = array_merge($theme_sidebars, $sidebars);
		}
		return $sidebars;
	}
}


// Set theme specific importer options
if ( !function_exists( 'grace_church_set_importer_options' ) ) {
	//add_filter( 'grace_church_filter_importer_options',	'grace_church_set_importer_options' );
	function grace_church_set_importer_options($options=array()) {
		if (is_array($options)) {
            $options['domain_dev'] = esc_url('church.ancorathemes.dnw');
            $options['domain_demo'] = esc_url('gracechurch.ancorathemes.com');
			$options['page_on_front'] = 'Home';
			$options['page_for_posts'] = 'Blog';
			$options['menus'] = array(						// Menus locations and names
                'menu-main'	  => esc_html__('Main menu', 'grace-church'),
                'menu-user'	  => esc_html__('User menu', 'grace-church'),
                'menu-footer' => esc_html__('Footer menu', 'grace-church'),
                'menu-outer'  => esc_html__('Main menu', 'grace-church')
			);
		}
		return $options;
	}
}

if (!function_exists('grace_church_tribe_events_theme_setup')) {
    add_action( 'grace_church_action_before_init_theme', 'grace_church_tribe_events_theme_setup' );
    function grace_church_tribe_events_theme_setup() {
        if (grace_church_exists_tribe_events()) {

            //if (grace_church_is_tribe_events_page()) {
            // Detect current page type, taxonomy and title (for custom post_types use priority < 10 to fire it handles early, than for standard post types)
            add_filter('grace_church_filter_get_blog_type',				'grace_church_tribe_events_get_blog_type', 9, 2);
            add_filter('grace_church_filter_get_blog_title',			'grace_church_tribe_events_get_blog_title', 9, 2);
            add_filter('grace_church_filter_get_current_taxonomy',		'grace_church_tribe_events_get_current_taxonomy', 9, 2);
            add_filter('grace_church_filter_is_taxonomy',				'grace_church_tribe_events_is_taxonomy', 9, 2);
            add_filter('grace_church_filter_get_stream_page_title',		'grace_church_tribe_events_get_stream_page_title', 9, 2);
            add_filter('grace_church_filter_get_stream_page_link',		'grace_church_tribe_events_get_stream_page_link', 9, 2);
            add_filter('grace_church_filter_get_stream_page_id',		'grace_church_tribe_events_get_stream_page_id', 9, 2);
            add_filter('grace_church_filter_get_period_links',			'grace_church_tribe_events_get_period_links', 9, 3);
            add_filter('grace_church_filter_detect_inheritance_key',	'grace_church_tribe_events_detect_inheritance_key', 9, 1);
            //}

            add_action( 'grace_church_action_add_styles',				'grace_church_tribe_events_frontend_scripts' );

            add_filter('grace_church_filter_list_post_types', 			'grace_church_tribe_events_list_post_types', 10, 1);

            // Advanced Calendar filters
            add_filter('grace_church_filter_calendar_get_month_link',		'grace_church_tribe_events_calendar_get_month_link', 9, 2);
            add_filter('grace_church_filter_calendar_get_prev_month',		'grace_church_tribe_events_calendar_get_prev_month', 9, 2);
            add_filter('grace_church_filter_calendar_get_next_month',		'grace_church_tribe_events_calendar_get_next_month', 9, 2);
            add_filter('grace_church_filter_calendar_get_curr_month_posts',	'grace_church_tribe_events_calendar_get_curr_month_posts', 9, 2);

            // Extra column for events lists
            if (grace_church_get_theme_option('show_overriden_posts')=='yes') {
                add_filter('manage_edit-'.TribeEvents::POSTTYPE.'_columns',			'grace_church_post_add_options_column', 9);
                add_filter('manage_'.TribeEvents::POSTTYPE.'_posts_custom_column',	'grace_church_post_fill_options_column', 9, 2);
            }
        }
    }
}


/* Include framework core files
------------------------------------------------------------------- */
// If now is WP Heartbeat call - skip loading theme core files
if (!isset($_POST['action']) || $_POST['action']!="heartbeat") {
	require_once( get_template_directory().'/fw/loader.php' );
}
?>