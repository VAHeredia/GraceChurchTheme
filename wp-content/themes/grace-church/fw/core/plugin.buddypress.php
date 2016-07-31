<?php
/* BuddyPress support functions
------------------------------------------------------------------------------- */

// Theme init
if (!function_exists('grace_church_buddypress_theme_setup')) {
	add_action( 'grace_church_action_before_init_theme', 'grace_church_buddypress_theme_setup' );
	function grace_church_buddypress_theme_setup() {
		if (grace_church_is_buddypress_page()) {
			// Add custom styles for Buddy & BBPress
			add_action( 'grace_church_action_add_styles', 				'grace_church_buddypress_frontend_scripts' );
			// Detect current page type, taxonomy and title (for custom post_types use priority < 10 to fire it handles early, than for standard post types)
			add_filter('grace_church_filter_get_blog_type',				'grace_church_buddypress_get_blog_type', 9, 2);
			add_filter('grace_church_filter_get_blog_title',			'grace_church_buddypress_get_blog_title', 9, 2);
			add_filter('grace_church_filter_get_stream_page_title',		'grace_church_buddypress_get_stream_page_title', 9, 2);
			add_filter('grace_church_filter_get_stream_page_link',		'grace_church_buddypress_get_stream_page_link', 9, 2);
			add_filter('grace_church_filter_get_stream_page_id',		'grace_church_buddypress_get_stream_page_id', 9, 2);
			add_filter('grace_church_filter_detect_inheritance_key',	'grace_church_buddypress_detect_inheritance_key', 9, 1);
		}
	}
}
if ( !function_exists( 'grace_church_buddypress_settings_theme_setup2' ) ) {
	add_action( 'grace_church_action_before_init_theme', 'grace_church_buddypress_settings_theme_setup2', 3 );
	function grace_church_buddypress_settings_theme_setup2() {
		if (grace_church_exists_buddypress()) {
			grace_church_add_theme_inheritance( array('buddypress' => array(
				'stream_template' => 'buddypress',
				'single_template' => '',
				'taxonomy' => array(),
				'taxonomy_tags' => array(),
				'post_type' => array('forum', 'topic', 'reply'),
				'override' => 'page'
				) )
			);
		}
	}
}

// Check if BuddyPress and/or BBPress installed and activated
if ( !function_exists( 'grace_church_exists_buddypress' ) ) {
	function grace_church_exists_buddypress() {
		return class_exists( 'BuddyPress' ) || class_exists( 'bbPress' );
	}
}

// Check if current page is BuddyPress and/or BBPress page
if ( !function_exists( 'grace_church_is_buddypress_page' ) ) {
	function grace_church_is_buddypress_page() {
		return  (function_exists('is_buddypress') && is_buddypress())
				||
				(function_exists('is_bbpress') && is_bbpress());
	}
}

// Filter to detect current page inheritance key
if ( !function_exists( 'grace_church_buddypress_detect_inheritance_key' ) ) {
	//add_filter('grace_church_filter_detect_inheritance_key',	'grace_church_buddypress_detect_inheritance_key', 9, 1);
	function grace_church_buddypress_detect_inheritance_key($key) {
		if (!empty($key)) return $key;
		return grace_church_is_buddypress_page() ? 'buddypress' : '';
	}
}

// Filter to detect current page slug
if ( !function_exists( 'grace_church_buddypress_get_blog_type' ) ) {
	//add_filter('grace_church_filter_get_blog_type',	'grace_church_buddypress_get_blog_type', 9, 2);
	function grace_church_buddypress_get_blog_type($page, $query=null) {
		if (!empty($page)) return $page;
		if ($query && $query->get('post_type')=='forum' || get_query_var('post_type')=='forum')
			$page = 'buddypress_forum';
		else if ($query && $query->get('post_type')=='topic' || get_query_var('post_type')=='topic')
			$page = 'buddypress_topic';
		else if ($query && $query->get('post_type')=='reply' || get_query_var('post_type')=='reply')
			$page = 'buddypress_reply';
		return $page;
	}
}

// Filter to detect current page title
if ( !function_exists( 'grace_church_buddypress_get_blog_title' ) ) {
	//add_filter('grace_church_filter_get_blog_title',	'grace_church_buddypress_get_blog_title', 9, 2);
	function grace_church_buddypress_get_blog_title($title, $page) {
		if (!empty($title)) return $title;
		if ( grace_church_strpos($page, 'buddypress')!==false ) {
			if ( $page == 'buddypress_forum' || $page == 'buddypress_topic' || $page == 'buddypress_reply' ) {
				$title = grace_church_get_post_title();
			} else {
				$title = esc_html__('Forums', 'grace-church');
			}
		}
		return $title;
	}
}

// Filter to detect stream page title
if ( !function_exists( 'grace_church_buddypress_get_stream_page_title' ) ) {
	//add_filter('grace_church_filter_get_stream_page_title',	'grace_church_buddypress_get_stream_page_title', 9, 2);
	function grace_church_buddypress_get_stream_page_title($title, $page) {
		if (!empty($title)) return $title;
		if (grace_church_strpos($page, 'buddypress')!==false) {
			// Page exists at root slug path, so use its permalink
			$page = bbp_get_page_by_path( bbp_get_root_slug() );
			if ( !empty( $page ) )
				$title = get_the_title( $page->ID );
			else
				$title = esc_html__('Forums', 'grace-church');
		}
		return $title;
	}
}

// Filter to detect stream page ID
if ( !function_exists( 'grace_church_buddypress_get_stream_page_id' ) ) {
	//add_filter('grace_church_filter_get_stream_page_id',	'grace_church_buddypress_get_stream_page_id', 9, 2);
	function grace_church_buddypress_get_stream_page_id($id, $page) {
		if (!empty($id)) return $id;
		if (grace_church_strpos($page, 'buddypress')!==false) {
			// Page exists at root slug path, so use its permalink
			$page = bbp_get_page_by_path( bbp_get_root_slug() );
			if ( !empty( $page ) ) $id = $page->ID;
		}
		return $id;
	}
}

// Filter to detect stream page URL
if ( !function_exists( 'grace_church_buddypress_get_stream_page_link' ) ) {
	//add_filter('grace_church_filter_get_stream_page_link', 'grace_church_buddypress_get_stream_page_link', 9, 2);
	function grace_church_buddypress_get_stream_page_link($url, $page) {
		if (!empty($url)) return $url;
		if (grace_church_strpos($page, 'buddypress')!==false) {
			// Page exists at root slug path, so use its permalink
			$page = bbp_get_page_by_path( bbp_get_root_slug() );
			if ( !empty( $page ) )
				$url = get_permalink( $page->ID );
			else
				$url = get_post_type_archive_link( bbp_get_forum_post_type() );
		}
		return $url;
	}
}


// Enqueue BuddyPress and/or BBPress custom styles
if ( !function_exists( 'grace_church_buddypress_frontend_scripts' ) ) {
	//add_action( 'grace_church_action_add_styles', 'grace_church_buddypress_frontend_scripts' );
	function grace_church_buddypress_frontend_scripts() {
		grace_church_enqueue_style( 'buddypress-style',  grace_church_get_file_url('css/buddypress-style.css'), array(), null );
	}
}

?>