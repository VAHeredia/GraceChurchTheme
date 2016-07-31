<?php
if (is_admin()
		|| (isset($_GET['vc_editable']) && $_GET['vc_editable']=='true' )
		|| (isset($_GET['vc_action']) && $_GET['vc_action']=='vc_inline')
	) {
	require_once( grace_church_get_file_dir('core/core.shortcodes/shortcodes_vc_classes.php') );
}

// Width and height params
if ( !function_exists( 'grace_church_vc_width' ) ) {
	function grace_church_vc_width($w='') {
		return array(
			"param_name" => "width",
			"heading" => esc_html__("Width", "grace-church"),
			"description" => esc_html__("Width (in pixels or percent) of the current element", "grace-church"),
			"group" => esc_html__('Size &amp; Margins', 'grace-church'),
			"value" => $w,
			"type" => "textfield"
		);
	}
}
if ( !function_exists( 'grace_church_vc_height' ) ) {
	function grace_church_vc_height($h='') {
		return array(
			"param_name" => "height",
			"heading" => esc_html__("Height", "grace-church"),
			"description" => esc_html__("Height (only in pixels) of the current element", "grace-church"),
			"group" => esc_html__('Size &amp; Margins', 'grace-church'),
			"value" => $h,
			"type" => "textfield"
		);
	}
}

// Load scripts and styles for VC support
if ( !function_exists( 'grace_church_shortcodes_vc_scripts_admin' ) ) {
	//add_action( 'admin_enqueue_scripts', 'grace_church_shortcodes_vc_scripts_admin' );
	function grace_church_shortcodes_vc_scripts_admin() {
		// Include CSS 
		grace_church_enqueue_style ( 'shortcodes_vc-style', grace_church_get_file_url('core/core.shortcodes/shortcodes_vc_admin.css'), array(), null );
		// Include JS
		grace_church_enqueue_script( 'shortcodes_vc-script', grace_church_get_file_url('core/core.shortcodes/shortcodes_vc_admin.js'), array(), null, true );
	}
}

// Load scripts and styles for VC support
if ( !function_exists( 'grace_church_shortcodes_vc_scripts_front' ) ) {
	//add_action( 'wp_enqueue_scripts', 'grace_church_shortcodes_vc_scripts_front' );
	function grace_church_shortcodes_vc_scripts_front() {
		if (grace_church_vc_is_frontend()) {
			// Include CSS 
			grace_church_enqueue_style ( 'shortcodes_vc-style', grace_church_get_file_url('core/core.shortcodes/shortcodes_vc_front.css'), array(), null );
			// Include JS
			grace_church_enqueue_script( 'shortcodes_vc-script', grace_church_get_file_url('core/core.shortcodes/shortcodes_vc_front.js'), array(), null, true );
		}
	}
}

// Add init script into shortcodes output in VC frontend editor
if ( !function_exists( 'grace_church_shortcodes_vc_add_init_script' ) ) {
	//add_filter('grace_church_shortcode_output', 'grace_church_shortcodes_vc_add_init_script', 10, 4);
	function grace_church_shortcodes_vc_add_init_script($output, $tag='', $atts=array(), $content='') {
		if ( (isset($_GET['vc_editable']) && $_GET['vc_editable']=='true') && (isset($_POST['action']) && $_POST['action']=='vc_load_shortcode')
				&& ( isset($_POST['shortcodes'][0]['tag']) && $_POST['shortcodes'][0]['tag']==$tag )
		) {
			if (grace_church_strpos($output, 'grace_church_vc_init_shortcodes')===false) {
				$id = "grace_church_vc_init_shortcodes_".str_replace('.', '', mt_rand());
				$output .= '
					<script id="'.esc_attr($id).'">
						try {
							grace_church_init_post_formats();
							grace_church_init_shortcodes(jQuery("body").eq(0));
							grace_church_scroll_actions();
						} catch (e) { };
					</script>
				';
			}
		}
		return $output;
	}
}


/* Theme setup section
-------------------------------------------------------------------- */

if ( !function_exists( 'grace_church_shortcodes_vc_theme_setup' ) ) {
	//if ( grace_church_vc_is_frontend() )
	if ( (isset($_GET['vc_editable']) && $_GET['vc_editable']=='true') || (isset($_GET['vc_action']) && $_GET['vc_action']=='vc_inline') )
		add_action( 'grace_church_action_before_init_theme', 'grace_church_shortcodes_vc_theme_setup', 20 );
	else
		add_action( 'grace_church_action_after_init_theme', 'grace_church_shortcodes_vc_theme_setup' );
	function grace_church_shortcodes_vc_theme_setup() {
	
		// Set dir with theme specific VC shortcodes
		if ( function_exists( 'vc_set_shortcodes_templates_dir' ) ) {
			vc_set_shortcodes_templates_dir( grace_church_get_folder_dir('core/core.shortcodes/vc_shortcodes' ) );
		}
		
		// Add/Remove params in the standard VC shortcodes
		vc_add_param("vc_row", array(
					"param_name" => "scheme",
					"heading" => esc_html__("Color scheme", "grace-church"),
					"description" => esc_html__("Select color scheme for this block", "grace-church"),
					"group" => esc_html__('Color scheme', 'grace-church'),
					"class" => "",
					"value" => array_flip(grace_church_get_list_color_schemes(true)),
					"type" => "dropdown"
		));

		if (grace_church_shortcodes_is_used()) {

			// Set VC as main editor for the theme
			vc_set_as_theme( true );
			
			// Enable VC on follow post types
			vc_set_default_editor_post_types( array('page', 'team') );
			
			// Disable frontend editor
			//vc_disable_frontend();

			// Load scripts and styles for VC support
			add_action( 'wp_enqueue_scripts',		'grace_church_shortcodes_vc_scripts_front');
			add_action( 'admin_enqueue_scripts',	'grace_church_shortcodes_vc_scripts_admin' );

			// Add init script into shortcodes output in VC frontend editor
			add_filter('grace_church_shortcode_output', 'grace_church_shortcodes_vc_add_init_script', 10, 4);

			// Remove standard VC shortcodes
			vc_remove_element("vc_button");
			vc_remove_element("vc_posts_slider");
			vc_remove_element("vc_gmaps");
			vc_remove_element("vc_teaser_grid");
			vc_remove_element("vc_progress_bar");
			vc_remove_element("vc_facebook");
			vc_remove_element("vc_tweetmeme");
			vc_remove_element("vc_googleplus");
			vc_remove_element("vc_facebook");
			vc_remove_element("vc_pinterest");
			vc_remove_element("vc_message");
			vc_remove_element("vc_posts_grid");
			vc_remove_element("vc_carousel");
			vc_remove_element("vc_flickr");
			vc_remove_element("vc_tour");
			vc_remove_element("vc_separator");
			vc_remove_element("vc_single_image");
			vc_remove_element("vc_cta_button");
//			vc_remove_element("vc_accordion");
//			vc_remove_element("vc_accordion_tab");
			vc_remove_element("vc_toggle");
			vc_remove_element("vc_tabs");
			vc_remove_element("vc_tab");
			vc_remove_element("vc_images_carousel");
			
			// Remove standard WP widgets
			vc_remove_element("vc_wp_archives");
			vc_remove_element("vc_wp_calendar");
			vc_remove_element("vc_wp_categories");
			vc_remove_element("vc_wp_custommenu");
			vc_remove_element("vc_wp_links");
			vc_remove_element("vc_wp_meta");
			vc_remove_element("vc_wp_pages");
			vc_remove_element("vc_wp_posts");
			vc_remove_element("vc_wp_recentcomments");
			vc_remove_element("vc_wp_rss");
			vc_remove_element("vc_wp_search");
			vc_remove_element("vc_wp_tagcloud");
			vc_remove_element("vc_wp_text");
			
			global $GRACE_CHURCH_GLOBALS;
			
			$GRACE_CHURCH_GLOBALS['vc_params'] = array(
				
				// Common arrays and strings
				'category' => esc_html__("Grace-Church shortcodes", "grace-church"),
			
				// Current element id
				'id' => array(
					"param_name" => "id",
					"heading" => esc_html__("Element ID", "grace-church"),
					"description" => esc_html__("ID for current element", "grace-church"),
					"group" => esc_html__('ID &amp; Class', 'grace-church'),
					"value" => "",
					"type" => "textfield"
				),
			
				// Current element class
				'class' => array(
					"param_name" => "class",
					"heading" => esc_html__("Element CSS class", "grace-church"),
					"description" => esc_html__("CSS class for current element", "grace-church"),
					"group" => esc_html__('ID &amp; Class', 'grace-church'),
					"value" => "",
					"type" => "textfield"
				),

				// Current element animation
				'animation' => array(
					"param_name" => "animation",
					"heading" => esc_html__("Animation", "grace-church"),
					"description" => esc_html__("Select animation while object enter in the visible area of page", "grace-church"),
					"group" => esc_html__('ID &amp; Class', 'grace-church'),
					"class" => "",
					"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['animations']),
					"type" => "dropdown"
				),
			
				// Current element style
				'css' => array(
					"param_name" => "css",
					"heading" => esc_html__("CSS styles", "grace-church"),
					"description" => esc_html__("Any additional CSS rules (if need)", "grace-church"),
					"group" => esc_html__('ID &amp; Class', 'grace-church'),
					"class" => "",
					"value" => "",
					"type" => "textfield"
				),
			
				// Margins params
				'margin_top' => array(
					"param_name" => "top",
					"heading" => esc_html__("Top margin", "grace-church"),
					"description" => esc_html__("Top margin (in pixels).", "grace-church"),
					"group" => esc_html__('Size &amp; Margins', 'grace-church'),
					"value" => "",
					"type" => "textfield"
				),
			
				'margin_bottom' => array(
					"param_name" => "bottom",
					"heading" => esc_html__("Bottom margin", "grace-church"),
					"description" => esc_html__("Bottom margin (in pixels).", "grace-church"),
					"group" => esc_html__('Size &amp; Margins', 'grace-church'),
					"value" => "",
					"type" => "textfield"
				),
			
				'margin_left' => array(
					"param_name" => "left",
					"heading" => esc_html__("Left margin", "grace-church"),
					"description" => esc_html__("Left margin (in pixels).", "grace-church"),
					"group" => esc_html__('Size &amp; Margins', 'grace-church'),
					"value" => "",
					"type" => "textfield"
				),
				
				'margin_right' => array(
					"param_name" => "right",
					"heading" => esc_html__("Right margin", "grace-church"),
					"description" => esc_html__("Right margin (in pixels).", "grace-church"),
					"group" => esc_html__('Size &amp; Margins', 'grace-church'),
					"value" => "",
					"type" => "textfield"
				)
			);
	
	
	
			// Accordion
			//-------------------------------------------------------------------------------------
			vc_map( array(
				"base" => "trx_accordion",
				"name" => esc_html__("Accordion", "grace-church"),
				"description" => esc_html__("Accordion items", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_accordion',
				"class" => "trx_sc_collection trx_sc_accordion",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => false,
				"as_parent" => array('only' => 'trx_accordion_item'),	// Use only|except attributes to limit child shortcodes (separate multiple values with comma)
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Accordion style", "grace-church"),
						"description" => esc_html__("Select style for display accordion", "grace-church"),
						"class" => "",
						"admin_label" => true,
						"value" => array_flip(grace_church_get_list_styles(1, 2)),
						"type" => "dropdown"
					),
					array(
						"param_name" => "counter",
						"heading" => esc_html__("Counter", "grace-church"),
						"description" => esc_html__("Display counter before each accordion title", "grace-church"),
						"class" => "",
						"value" => array("Add item numbers before each element" => "on" ),
						"type" => "checkbox"
					),
					array(
						"param_name" => "initial",
						"heading" => esc_html__("Initially opened item", "grace-church"),
						"description" => esc_html__("Number of initially opened item", "grace-church"),
						"class" => "",
						"value" => 1,
						"type" => "textfield"
					),
					array(
						"param_name" => "icon_closed",
						"heading" => esc_html__("Icon while closed", "grace-church"),
						"description" => esc_html__("Select icon for the closed accordion item from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "icon_opened",
						"heading" => esc_html__("Icon while opened", "grace-church"),
						"description" => esc_html__("Select icon for the opened accordion item from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'default_content' => '
					[trx_accordion_item title="' . esc_html__( 'Item 1 title', 'grace-church' ) . '"][/trx_accordion_item]
					[trx_accordion_item title="' . esc_html__( 'Item 2 title', 'grace-church' ) . '"][/trx_accordion_item]
				',
				"custom_markup" => '
					<div class="wpb_accordion_holder wpb_holder clearfix vc_container_for_children">
						%content%
					</div>
					<div class="tab_controls">
						<button class="add_tab" title="'.__("Add item", "grace-church").'">'.__("Add item", "grace-church").'</button>
					</div>
				',
				'js_view' => 'VcTrxAccordionView'
			) );
			
			
			vc_map( array(
				"base" => "trx_accordion_item",
				"name" => esc_html__("Accordion item", "grace-church"),
				"description" => esc_html__("Inner accordion item", "grace-church"),
				"show_settings_on_create" => true,
				"content_element" => true,
				"is_container" => true,
				'icon' => 'icon_trx_accordion_item',
				"as_child" => array('only' => 'trx_accordion'), 	// Use only|except attributes to limit parent (separate multiple values with comma)
				"as_parent" => array('except' => 'trx_accordion'),
				"params" => array(
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Title for current accordion item", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "icon_closed",
						"heading" => esc_html__("Icon while closed", "grace-church"),
						"description" => esc_html__("Select icon for the closed accordion item from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "icon_opened",
						"heading" => esc_html__("Icon while opened", "grace-church"),
						"description" => esc_html__("Select icon for the opened accordion item from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css']
				),
			  'js_view' => 'VcTrxAccordionTabView'
			) );

			class WPBakeryShortCode_Trx_Accordion extends GRACE_CHURCH_VC_ShortCodeAccordion {}
			class WPBakeryShortCode_Trx_Accordion_Item extends GRACE_CHURCH_VC_ShortCodeAccordionItem {}
			
			
			
			
			
			
			// Anchor
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_anchor",
				"name" => esc_html__("Anchor", "grace-church"),
				"description" => esc_html__("Insert anchor for the TOC (table of content)", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_anchor',
				"class" => "trx_sc_single trx_sc_anchor",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "icon",
						"heading" => esc_html__("Anchor's icon", "grace-church"),
						"description" => esc_html__("Select icon for the anchor from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "title",
						"heading" => esc_html__("Short title", "grace-church"),
						"description" => esc_html__("Short title of the anchor (for the table of content)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "description",
						"heading" => esc_html__("Long description", "grace-church"),
						"description" => esc_html__("Description for the popup (then hover on the icon). You can use:<br>'{{' and '}}' - to make the text italic,<br>'((' and '))' - to make the text bold,<br>'||' - to insert line break", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "url",
						"heading" => esc_html__("External URL", "grace-church"),
						"description" => esc_html__("External URL for this TOC item", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "separator",
						"heading" => esc_html__("Add separator", "grace-church"),
						"description" => esc_html__("Add separator under item in the TOC", "grace-church"),
						"class" => "",
						"value" => array("Add separator" => "yes" ),
						"type" => "checkbox"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id']
				),
			) );
			
			class WPBakeryShortCode_Trx_Anchor extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			// Audio
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_audio",
				"name" => esc_html__("Audio", "grace-church"),
				"description" => esc_html__("Insert audio player", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_audio',
				"class" => "trx_sc_single trx_sc_audio",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "url",
						"heading" => esc_html__("URL for audio file", "grace-church"),
						"description" => esc_html__("Put here URL for audio file", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "image",
						"heading" => esc_html__("Cover image", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site for audio cover", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Title of the audio file", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "author",
						"heading" => esc_html__("Author", "grace-church"),
						"description" => esc_html__("Author of the audio file", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "type",
						"heading" => esc_html__("Type", "grace-church"),
						"description" => esc_html__("Select type of display", "grace-church"),
						"class" => "",
                        "value" => array(
                            esc_html__('Normal', 'grace-church') => 'default',
                            esc_html__('Minimal', 'grace-church') => 'minimal'
                        ),
						"type" => "dropdown"
					),
					array(
						"param_name" => "controls",
						"heading" => esc_html__("Controls", "grace-church"),
						"description" => esc_html__("Show/hide controls", "grace-church"),
						"class" => "",
						"value" => array("Hide controls" => "hide" ),
						"type" => "checkbox"
					),
					array(
						"param_name" => "autoplay",
						"heading" => esc_html__("Autoplay", "grace-church"),
						"description" => esc_html__("Autoplay audio on page load", "grace-church"),
						"class" => "",
						"value" => array("Autoplay" => "on" ),
						"type" => "checkbox"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Select block alignment", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
                    array(
                        "param_name" => "inverse_color_player",
                        "heading" => esc_html__("Inverse color", "grace-church"),
                        "description" => wp_kses( __("Change color to light (for dark background)", "grace-church"), $GRACE_CHURCH_GLOBALS['allowed_tags'] ),
                        "class" => "",
                        "std" => 'no',
                        "value" => array(esc_html__('Inverse color', 'grace-church') => 'yes'),
                        "type" => "checkbox"
                    ),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
			) );
			
			class WPBakeryShortCode_Trx_Audio extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Block
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_block",
				"name" => esc_html__("Block container", "grace-church"),
				"description" => esc_html__("Container for any block ([section] analog - to enable nesting)", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_block',
				"class" => "trx_sc_collection trx_sc_block",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "dedicated",
						"heading" => esc_html__("Dedicated", "grace-church"),
						"description" => esc_html__("Use this block as dedicated content - show it before post title on single page", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array( esc_html__('Use as dedicated content', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Select block alignment", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "columns",
						"heading" => esc_html__("Columns emulation", "grace-church"),
						"description" => esc_html__("Select width for columns emulation", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['columns']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "pan",
						"heading" => esc_html__("Use pan effect", "grace-church"),
						"description" => esc_html__("Use pan effect to show section content", "grace-church"),
						"group" => esc_html__('Scroll', 'grace-church'),
						"class" => "",
						"value" => array( esc_html__('Content scroller', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "scroll",
						"heading" => esc_html__("Use scroller", "grace-church"),
						"description" => esc_html__("Use scroller to show section content", "grace-church"),
						"group" => esc_html__('Scroll', 'grace-church'),
						"admin_label" => true,
						"class" => "",
						"value" => array( esc_html__('Content scroller', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "scroll_dir",
						"heading" => esc_html__("Scroll direction", "grace-church"),
						"description" => esc_html__("Scroll direction (if Use scroller = yes)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"group" => esc_html__('Scroll', 'grace-church'),
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['dir']),
						'dependency' => array(
							'element' => 'scroll',
							'not_empty' => true
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "scroll_controls",
						"heading" => esc_html__("Scroll controls", "grace-church"),
						"description" => esc_html__("Show scroll controls (if Use scroller = yes)", "grace-church"),
						"class" => "",
						"group" => esc_html__('Scroll', 'grace-church'),
						'dependency' => array(
							'element' => 'scroll',
							'not_empty' => true
						),
						"value" => array( esc_html__('Show scroll controls', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "scheme",
						"heading" => esc_html__("Color scheme", "grace-church"),
						"description" => esc_html__("Select color scheme for this block", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['schemes']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Fore color", "grace-church"),
						"description" => esc_html__("Any color for objects in this section", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Background color", "grace-church"),
						"description" => esc_html__("Any background color for this section", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_image",
						"heading" => esc_html__("Background image URL", "grace-church"),
						"description" => esc_html__("Select background image from library for this section", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "bg_overlay",
						"heading" => esc_html__("Overlay", "grace-church"),
						"description" => esc_html__("Overlay color opacity (from 0.0 to 1.0)", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_texture",
						"heading" => esc_html__("Texture", "grace-church"),
						"description" => esc_html__("Texture style from 1 to 11. Empty or 0 - without texture.", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "font_size",
						"heading" => esc_html__("Font size", "grace-church"),
						"description" => esc_html__("Font size of the text (default - in pixels, allows any CSS units of measure)", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "font_weight",
						"heading" => esc_html__("Font weight", "grace-church"),
						"description" => esc_html__("Font weight of the text", "grace-church"),
						"class" => "",
						"value" => array(
							__('Default', 'grace-church') => 'inherit',
							__('Thin (100)', 'grace-church') => '100',
							__('Light (300)', 'grace-church') => '300',
							__('Normal (400)', 'grace-church') => '400',
							__('Bold (700)', 'grace-church') => '700'
						),
						"type" => "dropdown"
					),
					/*
					array(
						"param_name" => "content",
						"heading" => esc_html__("Container content", "grace-church"),
						"description" => esc_html__("Content for section container", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					*/
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Block extends GRACE_CHURCH_VC_ShortCodeCollection {}
			
			
			
			
			
			
			// Blogger
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_blogger",
				"name" => esc_html__("Blogger", "grace-church"),
				"description" => esc_html__("Insert posts (pages) in many styles from desired categories or directly from ids", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_blogger',
				"class" => "trx_sc_single trx_sc_blogger",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Output style", "grace-church"),
						"description" => esc_html__("Select desired style for posts output", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['blogger_styles']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "show_button",
						"heading" => esc_html__("Show/Hide Button", "grace-church"),
//						"description" => esc_html__("Show/Hide Button", "grace-church"),
                        'dependency' => array(
                            'element' => 'style',
                            'value' => 'list'
                        ),
                        "std" => "no",
                        "value" => array(
                            esc_html__('Show Button "Read more"', 'grace-church') => 'yes' ),
                        "type" => "checkbox"
					),
					array(
						"param_name" => "filters",
						"heading" => esc_html__("Show filters", "grace-church"),
						"description" => esc_html__("Use post's tags or categories as filter buttons", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['filters']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "hover",
						"heading" => esc_html__("Hover effect", "grace-church"),
						"description" => esc_html__("Select hover effect (only if style=Portfolio)", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['hovers']),
						'dependency' => array(
							'element' => 'style',
							'value' => array('portfolio_2','portfolio_3','portfolio_4','grid_2','grid_3','grid_4','square_2','square_3','square_4','short_2','short_3','short_4','colored_2','colored_3','colored_4')
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "hover_dir",
						"heading" => esc_html__("Hover direction", "grace-church"),
						"description" => esc_html__("Select hover direction (only if style=Portfolio and hover=Circle|Square)", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['hovers_dir']),
						'dependency' => array(
							'element' => 'style',
							'value' => array('portfolio_2','portfolio_3','portfolio_4','grid_2','grid_3','grid_4','square_2','square_3','square_4','short_2','short_3','short_4','colored_2','colored_3','colored_4')
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "location",
						"heading" => esc_html__("Dedicated content location", "grace-church"),
						"description" => esc_html__("Select position for dedicated content (only for style=excerpt)", "grace-church"),
						"class" => "",
						'dependency' => array(
							'element' => 'style',
							'value' => array('excerpt')
						),
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['locations']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "dir",
						"heading" => esc_html__("Posts direction", "grace-church"),
						"description" => esc_html__("Display posts in horizontal or vertical direction", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"std" => "horizontal",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['dir']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "rating",
						"heading" => esc_html__("Show rating stars", "grace-church"),
						"description" => esc_html__("Show rating stars under post's header", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						"class" => "",
						"value" => array( esc_html__('Show rating', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "info",
						"heading" => esc_html__("Show post info block", "grace-church"),
						"description" => esc_html__("Show post info block (author, date, tags, etc.)", "grace-church"),
						"class" => "",
						"std" => 'yes',
						"value" => array( esc_html__('Show info', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "descr",
						"heading" => esc_html__("Description length", "grace-church"),
						"description" => esc_html__("How many characters are displayed from post excerpt? If 0 - don't show description", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						"class" => "",
						"value" => 0,
						"type" => "textfield"
					),
					array(
						"param_name" => "links",
						"heading" => esc_html__("Allow links to the post", "grace-church"),
						"description" => esc_html__("Allow links to the post from each blogger item", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						"class" => "",
						"std" => 'yes',
						"value" => array( esc_html__('Allow links', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "readmore",
						"heading" => esc_html__("More link text", "grace-church"),
						"description" => esc_html__("Read more link text. If empty - show 'More', else - used as link text", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Title for the block", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "subtitle",
						"heading" => esc_html__("Subtitle", "grace-church"),
						"description" => esc_html__("Subtitle for the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "description",
						"heading" => esc_html__("Description", "grace-church"),
						"description" => esc_html__("Description for the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textarea"
					),
					array(
						"param_name" => "link",
						"heading" => esc_html__("Button URL", "grace-church"),
						"description" => esc_html__("Link URL for the button at the bottom of the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "link_caption",
						"heading" => esc_html__("Button caption", "grace-church"),
						"description" => esc_html__("Caption for the button at the bottom of the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "post_type",
						"heading" => esc_html__("Post type", "grace-church"),
						"description" => esc_html__("Select post type to show", "grace-church"),
						"group" => esc_html__('Query', 'grace-church'),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['posts_types']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "ids",
						"heading" => esc_html__("Post IDs list", "grace-church"),
						"description" => esc_html__("Comma separated list of posts ID. If set - parameters above are ignored!", "grace-church"),
						"group" => esc_html__('Query', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "cat",
						"heading" => esc_html__("Categories list", "grace-church"),
						"description" => esc_html__("Select category. If empty - show posts from any category or from IDs list", "grace-church"),
						'dependency' => array(
							'element' => 'ids',
							'is_empty' => true
						),
						"group" => esc_html__('Query', 'grace-church'),
						"class" => "",
						"value" => array_flip(grace_church_array_merge(array(0 => esc_html__('- Select category -', 'grace-church')), $GRACE_CHURCH_GLOBALS['sc_params']['categories'])),
						"type" => "dropdown"
					),
					array(
						"param_name" => "count",
						"heading" => esc_html__("Total posts to show", "grace-church"),
						"description" => esc_html__("How many posts will be displayed? If used IDs - this parameter ignored.", "grace-church"),
						'dependency' => array(
							'element' => 'ids',
							'is_empty' => true
						),
						"admin_label" => true,
						"group" => esc_html__('Query', 'grace-church'),
						"class" => "",
						"value" => 3,
						"type" => "textfield"
					),
					array(
						"param_name" => "columns",
						"heading" => esc_html__("Columns number", "grace-church"),
						"description" => esc_html__("How many columns used to display posts?", "grace-church"),
						'dependency' => array(
							'element' => 'dir',
							'value' => 'horizontal'
						),
						"group" => esc_html__('Query', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "offset",
						"heading" => esc_html__("Offset before select posts", "grace-church"),
						"description" => esc_html__("Skip posts before select next part.", "grace-church"),
						'dependency' => array(
							'element' => 'ids',
							'is_empty' => true
						),
						"group" => esc_html__('Query', 'grace-church'),
						"class" => "",
						"value" => 0,
						"type" => "textfield"
					),
					array(
						"param_name" => "orderby",
						"heading" => esc_html__("Post order by", "grace-church"),
						"description" => esc_html__("Select desired posts sorting method", "grace-church"),
						"class" => "",
						"group" => esc_html__('Query', 'grace-church'),
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['sorting']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "order",
						"heading" => esc_html__("Post order", "grace-church"),
						"description" => esc_html__("Select desired posts order", "grace-church"),
						"class" => "",
						"group" => esc_html__('Query', 'grace-church'),
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "only",
						"heading" => esc_html__("Select posts only", "grace-church"),
						"description" => esc_html__("Select posts only with reviews, videos, audios, thumbs or galleries", "grace-church"),
						"class" => "",
						"group" => esc_html__('Query', 'grace-church'),
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['formats']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "scroll",
						"heading" => esc_html__("Use scroller", "grace-church"),
						"description" => esc_html__("Use scroller to show all posts", "grace-church"),
						"group" => esc_html__('Scroll', 'grace-church'),
						"class" => "",
						"value" => array( esc_html__('Use scroller', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "controls",
						"heading" => esc_html__("Show slider controls", "grace-church"),
						"description" => esc_html__("Show arrows to control scroll slider", "grace-church"),
						"group" => esc_html__('Scroll', 'grace-church'),
						"class" => "",
						"value" => array( esc_html__('Show controls', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
			) );
			
			class WPBakeryShortCode_Trx_Blogger extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			// Br
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_br",
				"name" => esc_html__("Line break", "grace-church"),
				"description" => esc_html__("Line break or Clear Floating", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_br',
				"class" => "trx_sc_single trx_sc_br",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "clear",
						"heading" => esc_html__("Clear floating", "grace-church"),
						"description" => esc_html__("Select clear side (if need)", "grace-church"),
						"class" => "",
						"value" => "",
						"value" => array(
							__('None', 'grace-church') => 'none',
							__('Left', 'grace-church') => 'left',
							__('Right', 'grace-church') => 'right',
							__('Both', 'grace-church') => 'both'
						),
						"type" => "dropdown"
					)
				)
			) );
			
			class WPBakeryShortCode_Trx_Br extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Button
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_button",
				"name" => esc_html__("Button", "grace-church"),
				"description" => esc_html__("Button with link", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_button',
				"class" => "trx_sc_single trx_sc_button",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "content",
						"heading" => esc_html__("Caption", "grace-church"),
						"description" => esc_html__("Button caption", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "type",
						"heading" => esc_html__("Button's shape", "grace-church"),
						"description" => esc_html__("Select button's shape", "grace-church"),
						"class" => "",
						"value" => array(
							__('Square', 'grace-church') => 'square',
							__('Round', 'grace-church') => 'round'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "style",
						"heading" => esc_html__("Button's style", "grace-church"),
						"description" => esc_html__("Select button's style", "grace-church"),
						"class" => "",
						"value" => array(
							__('Filled', 'grace-church') => 'filled',
							__('Border', 'grace-church') => 'border'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "size",
						"heading" => esc_html__("Button's size", "grace-church"),
						"description" => esc_html__("Select button's size", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array(
							__('Small', 'grace-church') => 'small',
							__('Medium', 'grace-church') => 'medium',
							__('Large', 'grace-church') => 'large'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "icon",
						"heading" => esc_html__("Button's icon", "grace-church"),
						"description" => esc_html__("Select icon for the title from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Button's text color", "grace-church"),
						"description" => esc_html__("Any color for button's caption", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Button's backcolor", "grace-church"),
						"description" => esc_html__("Any color for button's background", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Button's alignment", "grace-church"),
						"description" => esc_html__("Align button to left, center or right", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "link",
						"heading" => esc_html__("Link URL", "grace-church"),
						"description" => esc_html__("URL for the link on button click", "grace-church"),
						"class" => "",
						"group" => esc_html__('Link', 'grace-church'),
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "target",
						"heading" => esc_html__("Link target", "grace-church"),
						"description" => esc_html__("Target for the link on button click", "grace-church"),
						"class" => "",
						"group" => esc_html__('Link', 'grace-church'),
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "popup",
						"heading" => esc_html__("Open link in popup", "grace-church"),
						"description" => esc_html__("Open link target in popup window", "grace-church"),
						"class" => "",
						"group" => esc_html__('Link', 'grace-church'),
						"value" => array( esc_html__('Open in popup', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "rel",
						"heading" => esc_html__("Rel attribute", "grace-church"),
						"description" => esc_html__("Rel attribute for the button's link (if need", "grace-church"),
						"class" => "",
						"group" => esc_html__('Link', 'grace-church'),
						"value" => "",
						"type" => "textfield"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'js_view' => 'VcTrxTextView'
			) );
			
			class WPBakeryShortCode_Trx_Button extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Call to Action block
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_call_to_action",
				"name" => esc_html__("Call to Action", "grace-church"),
				"description" => esc_html__("Insert call to action block in your page (post)", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_call_to_action',
				"class" => "trx_sc_collection trx_sc_call_to_action",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Block's style", "grace-church"),
						"description" => esc_html__("Select style to display this block", "grace-church"),
						"class" => "",
						"admin_label" => true,
						"value" => array_flip(grace_church_get_list_styles(1, 2)),
						"type" => "dropdown"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Select block alignment", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "accent",
						"heading" => esc_html__("Accent", "grace-church"),
						"description" => esc_html__("Fill entire block with Accent1 color from current color scheme", "grace-church"),
						"class" => "",
						"value" => array("Fill with Accent1 color" => "yes" ),
						"type" => "checkbox"
					),
					array(
						"param_name" => "custom",
						"heading" => esc_html__("Custom", "grace-church"),
						"description" => esc_html__("Allow get featured image or video from inner shortcodes (custom) or get it from shortcode parameters below", "grace-church"),
						"class" => "",
						"value" => array("Custom content" => "yes" ),
						"type" => "checkbox"
					),
					array(
						"param_name" => "image",
						"heading" => esc_html__("Image", "grace-church"),
						"description" => esc_html__("Image to display inside block", "grace-church"),
				        'dependency' => array(
							'element' => 'custom',
							'is_empty' => true
						),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "video",
						"heading" => esc_html__("URL for video file", "grace-church"),
						"description" => esc_html__("Paste URL for video file to display inside block", "grace-church"),
				        'dependency' => array(
							'element' => 'custom',
							'is_empty' => true
						),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Title for the block", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "subtitle",
						"heading" => esc_html__("Subtitle", "grace-church"),
						"description" => esc_html__("Subtitle for the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "description",
						"heading" => esc_html__("Description", "grace-church"),
						"description" => esc_html__("Description for the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textarea"
					),
					array(
						"param_name" => "link",
						"heading" => esc_html__("Button URL", "grace-church"),
						"description" => esc_html__("Link URL for the button at the bottom of the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "link_caption",
						"heading" => esc_html__("Button caption", "grace-church"),
						"description" => esc_html__("Caption for the button at the bottom of the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "link2",
						"heading" => esc_html__("Button 2 URL", "grace-church"),
						"description" => esc_html__("Link URL for the second button at the bottom of the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "link2_caption",
						"heading" => esc_html__("Button 2 caption", "grace-church"),
						"description" => esc_html__("Caption for the second button at the bottom of the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Call_To_Action extends GRACE_CHURCH_VC_ShortCodeCollection {}


			
			
			
			
			// Chat
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_chat",
				"name" => esc_html__("Chat", "grace-church"),
				"description" => esc_html__("Chat message", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_chat',
				"class" => "trx_sc_container trx_sc_chat",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "title",
						"heading" => esc_html__("Item title", "grace-church"),
						"description" => esc_html__("Title for current chat item", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "photo",
						"heading" => esc_html__("Item photo", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site for the item photo (avatar)", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "link",
						"heading" => esc_html__("Link URL", "grace-church"),
						"description" => esc_html__("URL for the link on chat title click", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					/*
					array(
						"param_name" => "content",
						"heading" => esc_html__("Chat item content", "grace-church"),
						"description" => esc_html__("Current chat item content", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					*/
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'js_view' => 'VcTrxTextContainerView'
			
			) );
			
			class WPBakeryShortCode_Trx_Chat extends GRACE_CHURCH_VC_ShortCodeContainer {}
			
			
			
			
			
			
			// Columns
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_columns",
				"name" => esc_html__("Columns", "grace-church"),
				"description" => esc_html__("Insert columns with margins", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_columns',
				"class" => "trx_sc_columns",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => false,
				"as_parent" => array('only' => 'trx_column_item'),
				"params" => array(
					array(
						"param_name" => "count",
						"heading" => esc_html__("Columns count", "grace-church"),
						"description" => esc_html__("Number of the columns in the container.", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "2",
						"type" => "textfield"
					),
					array(
						"param_name" => "fluid",
						"heading" => esc_html__("Fluid columns", "grace-church"),
						"description" => esc_html__("To squeeze the columns when reducing the size of the window (fluid=yes) or to rebuild them (fluid=no)", "grace-church"),
						"class" => "",
						"value" => array( esc_html__('Fluid columns', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'default_content' => '
					[trx_column_item][/trx_column_item]
					[trx_column_item][/trx_column_item]
				',
				'js_view' => 'VcTrxColumnsView'
			) );
			
			
			vc_map( array(
				"base" => "trx_column_item",
				"name" => esc_html__("Column", "grace-church"),
				"description" => esc_html__("Column item", "grace-church"),
				"show_settings_on_create" => true,
				"class" => "trx_sc_collection trx_sc_column_item",
				"content_element" => true,
				"is_container" => true,
				'icon' => 'icon_trx_column_item',
				"as_child" => array('only' => 'trx_columns'),
				"as_parent" => array('except' => 'trx_columns'),
				"params" => array(
					array(
						"param_name" => "span",
						"heading" => esc_html__("Merge columns", "grace-church"),
						"description" => esc_html__("Count merged columns from current", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Alignment text in the column", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Fore color", "grace-church"),
						"description" => esc_html__("Any color for objects in this column", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Background color", "grace-church"),
						"description" => esc_html__("Any background color for this column", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_image",
						"heading" => esc_html__("URL for background image file", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site for the background", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					/*
					array(
						"param_name" => "content",
						"heading" => esc_html__("Column's content", "grace-church"),
						"description" => esc_html__("Content of the current column", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					*/
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css']
				),
				'js_view' => 'VcTrxColumnItemView'
			) );
			
			class WPBakeryShortCode_Trx_Columns extends GRACE_CHURCH_VC_ShortCodeColumns {}
			class WPBakeryShortCode_Trx_Column_Item extends GRACE_CHURCH_VC_ShortCodeCollection {}
			
			
			
			
			
			
			
			// Contact form
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_contact_form",
				"name" => esc_html__("Contact form", "grace-church"),
				"description" => esc_html__("Insert contact form", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_contact_form',
				"class" => "trx_sc_collection trx_sc_contact_form",
				"content_element" => true,
				"is_container" => true,
				"as_parent" => array('only' => 'trx_form_item'),
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "custom",
						"heading" => esc_html__("Custom", "grace-church"),
						"description" => esc_html__("Use custom fields or create standard contact form (ignore info from 'Field' tabs)", "grace-church"),
						"class" => "",
						"value" => array( esc_html__('Create custom form', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "style",
						"heading" => esc_html__("Style", "grace-church"),
						"description" => esc_html__("Select style of the contact form", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip(grace_church_get_list_styles(1, 2)),
						"type" => "dropdown"
					),
					array(
						"param_name" => "scheme",
						"heading" => esc_html__("Color scheme", "grace-church"),
						"description" => esc_html__("Select color scheme for this block", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['schemes']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "action",
						"heading" => esc_html__("Action", "grace-church"),
						"description" => esc_html__("Contact form action (URL to handle form data). If empty - use internal action", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Select form alignment", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Title for the block", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "subtitle",
						"heading" => esc_html__("Subtitle", "grace-church"),
						"description" => esc_html__("Subtitle for the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "description",
						"heading" => esc_html__("Description", "grace-church"),
						"description" => esc_html__("Description for the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textarea"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			
			vc_map( array(
				"base" => "trx_form_item",
				"name" => esc_html__("Form item (custom field)", "grace-church"),
				"description" => esc_html__("Custom field for the contact form", "grace-church"),
				"class" => "trx_sc_item trx_sc_form_item",
				'icon' => 'icon_trx_form_item',
				//"allowed_container_element" => 'vc_row',
				"show_settings_on_create" => true,
				"content_element" => true,
				"is_container" => false,
				"as_child" => array('only' => 'trx_contact_form'), // Use only|except attributes to limit parent (separate multiple values with comma)
				"params" => array(
					array(
						"param_name" => "type",
						"heading" => esc_html__("Type", "grace-church"),
						"description" => esc_html__("Select type of the custom field", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['field_types']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "name",
						"heading" => esc_html__("Name", "grace-church"),
						"description" => esc_html__("Name of the custom field", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "value",
						"heading" => esc_html__("Default value", "grace-church"),
						"description" => esc_html__("Default value of the custom field", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "options",
						"heading" => esc_html__("Options", "grace-church"),
						"description" => esc_html__("Field options. For example: big=My daddy|middle=My brother|small=My little sister", "grace-church"),
						'dependency' => array(
							'element' => 'type',
							'value' => array('radio','checkbox','select')
						),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "label",
						"heading" => esc_html__("Label", "grace-church"),
						"description" => esc_html__("Label for the custom field", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "label_position",
						"heading" => esc_html__("Label position", "grace-church"),
						"description" => esc_html__("Label position relative to the field", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['label_positions']),
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Contact_Form extends GRACE_CHURCH_VC_ShortCodeCollection {}
			class WPBakeryShortCode_Trx_Form_Item extends GRACE_CHURCH_VC_ShortCodeItem {}
			
			
			
			
			
			
			
			// Content block on fullscreen page
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_content",
				"name" => esc_html__("Content block", "grace-church"),
				"description" => esc_html__("Container for main content block (use it only on fullscreen pages)", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_content',
				"class" => "trx_sc_collection trx_sc_content",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "scheme",
						"heading" => esc_html__("Color scheme", "grace-church"),
						"description" => esc_html__("Select color scheme for this block", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['schemes']),
						"type" => "dropdown"
					),
					/*
					array(
						"param_name" => "content",
						"heading" => esc_html__("Container content", "grace-church"),
						"description" => esc_html__("Content for section container", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					*/
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom']
				)
			) );
			
			class WPBakeryShortCode_Trx_Content extends GRACE_CHURCH_VC_ShortCodeCollection {}
			
			
			
			
			
			
			
			// Countdown
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_countdown",
				"name" => esc_html__("Countdown", "grace-church"),
				"description" => esc_html__("Insert countdown object", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_countdown',
				"class" => "trx_sc_single trx_sc_countdown",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "date",
						"heading" => esc_html__("Date", "grace-church"),
						"description" => esc_html__("Upcoming date (format: yyyy-mm-dd)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "time",
						"heading" => esc_html__("Time", "grace-church"),
						"description" => esc_html__("Upcoming time (format: HH:mm:ss)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "style",
						"heading" => esc_html__("Style", "grace-church"),
						"description" => esc_html__("Countdown style", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip(grace_church_get_list_styles(1, 2)),
						"type" => "dropdown"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Align counter to left, center or right", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Countdown extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Dropcaps
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_dropcaps",
				"name" => esc_html__("Dropcaps", "grace-church"),
				"description" => esc_html__("Make first letter of the text as dropcaps", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_dropcaps',
				"class" => "trx_sc_single trx_sc_dropcaps",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Style", "grace-church"),
						"description" => esc_html__("Dropcaps style", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip(grace_church_get_list_styles(1, 4)),
						"type" => "dropdown"
					),
					array(
						"param_name" => "content",
						"heading" => esc_html__("Paragraph text", "grace-church"),
						"description" => esc_html__("Paragraph with dropcaps content", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css']
				),
				'js_view' => 'VcTrxTextView'
			
			) );
			
			class WPBakeryShortCode_Trx_Dropcaps extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Emailer
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_emailer",
				"name" => esc_html__("E-mail collector", "grace-church"),
				"description" => esc_html__("Collect e-mails into specified group", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_emailer',
				"class" => "trx_sc_single trx_sc_emailer",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "group",
						"heading" => esc_html__("Group", "grace-church"),
						"description" => esc_html__("The name of group to collect e-mail address", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "open",
						"heading" => esc_html__("Opened", "grace-church"),
						"description" => esc_html__("Initially open the input field on show object", "grace-church"),
						"class" => "",
						"value" => array( esc_html__('Initially opened', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Align field to left, center or right", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Emailer extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Gap
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_gap",
				"name" => esc_html__("Gap", "grace-church"),
				"description" => esc_html__("Insert gap (fullwidth area) in the post content", "grace-church"),
				"category" => esc_html__('Structure', 'js_composer'),
				'icon' => 'icon_trx_gap',
				"class" => "trx_sc_collection trx_sc_gap",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => false,
				"params" => array(
					/*
					array(
						"param_name" => "content",
						"heading" => esc_html__("Gap content", "grace-church"),
						"description" => esc_html__("Gap inner content", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					)
					*/
				)
			) );
			
			class WPBakeryShortCode_Trx_Gap extends GRACE_CHURCH_VC_ShortCodeCollection {}
			
			
			
			
			
			
			
			// Googlemap
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_googlemap",
				"name" => esc_html__("Google map", "grace-church"),
				"description" => esc_html__("Insert Google map with desired address or coordinates", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_googlemap',
				"class" => "trx_sc_collection trx_sc_googlemap",
				"content_element" => true,
				"is_container" => true,
				"as_parent" => array('only' => 'trx_googlemap_marker'),
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "zoom",
						"heading" => esc_html__("Zoom", "grace-church"),
						"description" => esc_html__("Map zoom factor", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "16",
						"type" => "textfield"
					),
					array(
						"param_name" => "style",
						"heading" => esc_html__("Style", "grace-church"),
						"description" => esc_html__("Map custom style", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['googlemap_styles']),
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width('100%'),
					grace_church_vc_height(240),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			vc_map( array(
				"base" => "trx_googlemap_marker",
				"name" => esc_html__("Googlemap marker", "grace-church"),
				"description" => esc_html__("Insert new marker into Google map", "grace-church"),
				"class" => "trx_sc_collection trx_sc_googlemap_marker",
				'icon' => 'icon_trx_googlemap_marker',
				//"allowed_container_element" => 'vc_row',
				"show_settings_on_create" => true,
				"content_element" => true,
				"is_container" => true,
				"as_child" => array('only' => 'trx_googlemap'), // Use only|except attributes to limit parent (separate multiple values with comma)
				"params" => array(
					array(
						"param_name" => "address",
						"heading" => esc_html__("Address", "grace-church"),
						"description" => esc_html__("Address of this marker", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "latlng",
						"heading" => esc_html__("Latitude and Longtitude", "grace-church"),
						"description" => esc_html__("Comma separated marker's coorditanes (instead Address)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Title for this marker", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "point",
						"heading" => esc_html__("URL for marker image file", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site for this marker. If empty - use default marker", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id']
				)
			) );
			
			class WPBakeryShortCode_Trx_Googlemap extends GRACE_CHURCH_VC_ShortCodeCollection {}
			class WPBakeryShortCode_Trx_Googlemap_Marker extends GRACE_CHURCH_VC_ShortCodeCollection {}
			
			
			
			
			
			
			
			
			
			// Highlight
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_highlight",
				"name" => esc_html__("Highlight text", "grace-church"),
				"description" => esc_html__("Highlight text with selected color, background color and other styles", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_highlight',
				"class" => "trx_sc_single trx_sc_highlight",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "type",
						"heading" => esc_html__("Type", "grace-church"),
						"description" => esc_html__("Highlight type", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array(
								__('Custom', 'grace-church') => 0,
								__('Type 1', 'grace-church') => 1,
								__('Type 2', 'grace-church') => 2,
								__('Type 3', 'grace-church') => 3
							),
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Text color", "grace-church"),
						"description" => esc_html__("Color for the highlighted text", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Background color", "grace-church"),
						"description" => esc_html__("Background color for the highlighted text", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "font_size",
						"heading" => esc_html__("Font size", "grace-church"),
						"description" => esc_html__("Font size for the highlighted text (default - in pixels, allows any CSS units of measure)", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "content",
						"heading" => esc_html__("Highlight text", "grace-church"),
						"description" => esc_html__("Content for highlight", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css']
				),
				'js_view' => 'VcTrxTextView'
			) );
			
			class WPBakeryShortCode_Trx_Highlight extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			// Icon
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_icon",
				"name" => esc_html__("Icon", "grace-church"),
				"description" => esc_html__("Insert the icon", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_icon',
				"class" => "trx_sc_single trx_sc_icon",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "icon",
						"heading" => esc_html__("Icon", "grace-church"),
						"description" => esc_html__("Select icon class from Fontello icons set", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Text color", "grace-church"),
						"description" => esc_html__("Icon's color", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Background color", "grace-church"),
						"description" => esc_html__("Background color for the icon", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_shape",
						"heading" => esc_html__("Background shape", "grace-church"),
						"description" => esc_html__("Shape of the icon background", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array(
							__('None', 'grace-church') => 'none',
							__('Round', 'grace-church') => 'round',
							__('Square', 'grace-church') => 'square'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "font_size",
						"heading" => esc_html__("Font size", "grace-church"),
						"description" => esc_html__("Icon's font size", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "font_weight",
						"heading" => esc_html__("Font weight", "grace-church"),
						"description" => esc_html__("Icon's font weight", "grace-church"),
						"class" => "",
						"value" => array(
							__('Default', 'grace-church') => 'inherit',
							__('Thin (100)', 'grace-church') => '100',
							__('Light (300)', 'grace-church') => '300',
							__('Normal (400)', 'grace-church') => '400',
							__('Bold (700)', 'grace-church') => '700'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Icon's alignment", "grace-church"),
						"description" => esc_html__("Align icon to left, center or right", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "link",
						"heading" => esc_html__("Link URL", "grace-church"),
						"description" => esc_html__("Link URL from this icon (if not empty)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
			) );
			
			class WPBakeryShortCode_Trx_Icon extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Image
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_image",
				"name" => esc_html__("Image", "grace-church"),
				"description" => esc_html__("Insert image", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_image',
				"class" => "trx_sc_single trx_sc_image",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "url",
						"heading" => esc_html__("Select image", "grace-church"),
						"description" => esc_html__("Select image from library", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Image alignment", "grace-church"),
						"description" => esc_html__("Align image to left or right side", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['float']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "shape",
						"heading" => esc_html__("Image shape", "grace-church"),
						"description" => esc_html__("Shape of the image: square or round", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array(
							__('Square', 'grace-church') => 'square',
							__('Round', 'grace-church') => 'round'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Image's title", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "icon",
						"heading" => esc_html__("Title's icon", "grace-church"),
						"description" => esc_html__("Select icon for the title from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "link",
						"heading" => esc_html__("Link", "grace-church"),
						"description" => esc_html__("The link URL from the image", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Image extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Infobox
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_infobox",
				"name" => esc_html__("Infobox", "grace-church"),
				"description" => esc_html__("Box with info or error message", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_infobox',
				"class" => "trx_sc_container trx_sc_infobox",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Style", "grace-church"),
						"description" => esc_html__("Infobox style", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array(
								__('Regular', 'grace-church') => 'regular',
								__('Info', 'grace-church') => 'info',
								__('Success', 'grace-church') => 'success',
								__('Error', 'grace-church') => 'error',
								__('Result', 'grace-church') => 'result'
							),
						"type" => "dropdown"
					),
					array(
						"param_name" => "closeable",
						"heading" => esc_html__("Closeable", "grace-church"),
						"description" => esc_html__("Create closeable box (with close button)", "grace-church"),
						"class" => "",
						"value" => array( esc_html__('Close button', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "icon",
						"heading" => esc_html__("Custom icon", "grace-church"),
						"description" => esc_html__("Select icon for the infobox from Fontello icons set. If empty - use default icon", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Text color", "grace-church"),
						"description" => esc_html__("Any color for the text and headers", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Background color", "grace-church"),
						"description" => esc_html__("Any background color for this infobox", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					/*
					array(
						"param_name" => "content",
						"heading" => esc_html__("Message text", "grace-church"),
						"description" => esc_html__("Message for the infobox", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					*/
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'js_view' => 'VcTrxTextContainerView'
			) );
			
			class WPBakeryShortCode_Trx_Infobox extends GRACE_CHURCH_VC_ShortCodeContainer {}
			
			
			
			
			
			
			
			// Line
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_line",
				"name" => esc_html__("Line", "grace-church"),
				"description" => esc_html__("Insert line (delimiter)", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				"class" => "trx_sc_single trx_sc_line",
				'icon' => 'icon_trx_line',
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Style", "grace-church"),
						"description" => esc_html__("Line style", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array(
								__('Solid', 'grace-church') => 'solid',
								__('Dashed', 'grace-church') => 'dashed',
								__('Dotted', 'grace-church') => 'dotted',
								__('Double', 'grace-church') => 'double',
								__('Shadow', 'grace-church') => 'shadow'
							),
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Line color", "grace-church"),
						"description" => esc_html__("Line color", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Line extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// List
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_list",
				"name" => esc_html__("List", "grace-church"),
				"description" => esc_html__("List items with specific bullets", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				"class" => "trx_sc_collection trx_sc_list",
				'icon' => 'icon_trx_list',
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => false,
				"as_parent" => array('only' => 'trx_list_item'),
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Bullet's style", "grace-church"),
						"description" => esc_html__("Bullet's style for each list item", "grace-church"),
						"class" => "",
						"admin_label" => true,
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['list_styles']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Color", "grace-church"),
						"description" => esc_html__("List items color", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "icon",
						"heading" => esc_html__("List icon", "grace-church"),
						"description" => esc_html__("Select list icon from Fontello icons set (only for style=Iconed)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						'dependency' => array(
							'element' => 'style',
							'value' => array('iconed')
						),
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "icon_color",
						"heading" => esc_html__("Icon color", "grace-church"),
						"description" => esc_html__("List icons color", "grace-church"),
						"class" => "",
						'dependency' => array(
							'element' => 'style',
							'value' => array('iconed')
						),
						"value" => "",
						"type" => "colorpicker"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'default_content' => '
					[trx_list_item]' . esc_html__( 'Item 1', 'grace-church' ) . '[/trx_list_item]
					[trx_list_item]' . esc_html__( 'Item 2', 'grace-church' ) . '[/trx_list_item]
				'
			) );
			
			
			vc_map( array(
				"base" => "trx_list_item",
				"name" => esc_html__("List item", "grace-church"),
				"description" => esc_html__("List item with specific bullet", "grace-church"),
				"class" => "trx_sc_single trx_sc_list_item",
				"show_settings_on_create" => true,
				"content_element" => true,
				"is_container" => false,
				'icon' => 'icon_trx_list_item',
				"as_child" => array('only' => 'trx_list'), // Use only|except attributes to limit parent (separate multiple values with comma)
				"as_parent" => array('except' => 'trx_list'),
				"params" => array(
					array(
						"param_name" => "title",
						"heading" => esc_html__("List item title", "grace-church"),
						"description" => esc_html__("Title for the current list item (show it as tooltip)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "link",
						"heading" => esc_html__("Link URL", "grace-church"),
						"description" => esc_html__("Link URL for the current list item", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Link', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "target",
						"heading" => esc_html__("Link target", "grace-church"),
						"description" => esc_html__("Link target for the current list item", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Link', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Color", "grace-church"),
						"description" => esc_html__("Text color for this item", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "icon",
						"heading" => esc_html__("List item icon", "grace-church"),
						"description" => esc_html__("Select list item icon from Fontello icons set (only for style=Iconed)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "icon_color",
						"heading" => esc_html__("Icon color", "grace-church"),
						"description" => esc_html__("Icon color for this item", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "content",
						"heading" => esc_html__("List item text", "grace-church"),
						"description" => esc_html__("Current list item content", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css']
				),
				'js_view' => 'VcTrxTextView'
			
			) );
			
			class WPBakeryShortCode_Trx_List extends GRACE_CHURCH_VC_ShortCodeCollection {}
			class WPBakeryShortCode_Trx_List_Item extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			
			
			// Number
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_number",
				"name" => esc_html__("Number", "grace-church"),
				"description" => esc_html__("Insert number or any word as set of separated characters", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				"class" => "trx_sc_single trx_sc_number",
				'icon' => 'icon_trx_number',
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "value",
						"heading" => esc_html__("Value", "grace-church"),
						"description" => esc_html__("Number or any word to separate", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Select block alignment", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Number extends GRACE_CHURCH_VC_ShortCodeSingle {}


			
			
			
			
			
			// Parallax
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_parallax",
				"name" => esc_html__("Parallax", "grace-church"),
				"description" => esc_html__("Create the parallax container (with asinc background image)", "grace-church"),
				"category" => esc_html__('Structure', 'js_composer'),
				'icon' => 'icon_trx_parallax',
				"class" => "trx_sc_collection trx_sc_parallax",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "gap",
						"heading" => esc_html__("Create gap", "grace-church"),
						"description" => esc_html__("Create gap around parallax container (not need in fullscreen pages)", "grace-church"),
						"class" => "",
						"value" => array( esc_html__('Create gap', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "dir",
						"heading" => esc_html__("Direction", "grace-church"),
						"description" => esc_html__("Scroll direction for the parallax background", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array(
								__('Up', 'grace-church') => 'up',
								__('Down', 'grace-church') => 'down'
							),
						"type" => "dropdown"
					),
					array(
						"param_name" => "speed",
						"heading" => esc_html__("Speed", "grace-church"),
						"description" => esc_html__("Parallax background motion speed (from 0.0 to 1.0)", "grace-church"),
						"class" => "",
						"value" => "0.3",
						"type" => "textfield"
					),
					array(
						"param_name" => "scheme",
						"heading" => esc_html__("Color scheme", "grace-church"),
						"description" => esc_html__("Select color scheme for this block", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['schemes']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Text color", "grace-church"),
						"description" => esc_html__("Select color for text object inside parallax block", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Backgroud color", "grace-church"),
						"description" => esc_html__("Select color for parallax background", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_image",
						"heading" => esc_html__("Background image", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site for the parallax background", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "bg_image_x",
						"heading" => esc_html__("Image X position", "grace-church"),
						"description" => esc_html__("Parallax background X position (in percents)", "grace-church"),
						"class" => "",
						"value" => "50%",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_video",
						"heading" => esc_html__("Video background", "grace-church"),
						"description" => esc_html__("Paste URL for video file to show it as parallax background", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_video_ratio",
						"heading" => esc_html__("Video ratio", "grace-church"),
						"description" => esc_html__("Specify ratio of the video background. For example: 16:9 (default), 4:3, etc.", "grace-church"),
						"class" => "",
						"value" => "16:9",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_overlay",
						"heading" => esc_html__("Overlay", "grace-church"),
						"description" => esc_html__("Overlay color opacity (from 0.0 to 1.0)", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_texture",
						"heading" => esc_html__("Texture", "grace-church"),
						"description" => esc_html__("Texture style from 1 to 11. Empty or 0 - without texture.", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					/*
					array(
						"param_name" => "content",
						"heading" => esc_html__("Content", "grace-church"),
						"description" => esc_html__("Content for the parallax container", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					*/
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Parallax extends GRACE_CHURCH_VC_ShortCodeCollection {}
			
			
			
			
			
			
			// Popup
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_popup",
				"name" => esc_html__("Popup window", "grace-church"),
				"description" => esc_html__("Container for any html-block with desired class and style for popup window", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_popup',
				"class" => "trx_sc_collection trx_sc_popup",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"params" => array(
					/*
					array(
						"param_name" => "content",
						"heading" => esc_html__("Container content", "grace-church"),
						"description" => esc_html__("Content for popup container", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					*/
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Popup extends GRACE_CHURCH_VC_ShortCodeCollection {}
			
			
			
			
			
			
			
			// Price
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_price",
				"name" => esc_html__("Price", "grace-church"),
				"description" => esc_html__("Insert price with decoration", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_price',
				"class" => "trx_sc_single trx_sc_price",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "money",
						"heading" => esc_html__("Money", "grace-church"),
						"description" => esc_html__("Money value (dot or comma separated)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "currency",
						"heading" => esc_html__("Currency symbol", "grace-church"),
						"description" => esc_html__("Currency character", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "$",
						"type" => "textfield"
					),
					array(
						"param_name" => "period",
						"heading" => esc_html__("Period", "grace-church"),
						"description" => esc_html__("Period text (if need). For example: monthly, daily, etc.", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Align price to left or right side", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['float']),
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Price extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Price block
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_price_block",
				"name" => esc_html__("Price block", "grace-church"),
				"description" => esc_html__("Insert price block with title, price and description", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_price_block',
				"class" => "trx_sc_single trx_sc_price_block",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Block title", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "link",
						"heading" => esc_html__("Link URL", "grace-church"),
						"description" => esc_html__("URL for link from button (at bottom of the block)", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "link_text",
						"heading" => esc_html__("Link text", "grace-church"),
						"description" => esc_html__("Text (caption) for the link button (at bottom of the block). If empty - button not showed", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "icon",
						"heading" => esc_html__("Icon", "grace-church"),
						"description" => esc_html__("Select icon from Fontello icons set (placed before/instead price)", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "money",
						"heading" => esc_html__("Money", "grace-church"),
						"description" => esc_html__("Money value (dot or comma separated)", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Money', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "currency",
						"heading" => esc_html__("Currency symbol", "grace-church"),
						"description" => esc_html__("Currency character", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Money', 'grace-church'),
						"class" => "",
						"value" => "$",
						"type" => "textfield"
					),
					array(
						"param_name" => "period",
						"heading" => esc_html__("Period", "grace-church"),
						"description" => esc_html__("Period text (if need). For example: monthly, daily, etc.", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Money', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "scheme",
						"heading" => esc_html__("Color scheme", "grace-church"),
						"description" => esc_html__("Select color scheme for this block", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['schemes']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Align price to left or right side", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['float']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "content",
						"heading" => esc_html__("Description", "grace-church"),
						"description" => esc_html__("Description for this price block", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'js_view' => 'VcTrxTextView'
			) );
			
			class WPBakeryShortCode_Trx_PriceBlock extends GRACE_CHURCH_VC_ShortCodeSingle {}

			
			
			
			
			// Quote
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_quote",
				"name" => esc_html__("Quote", "grace-church"),
				"description" => esc_html__("Quote text", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_quote',
				"class" => "trx_sc_single trx_sc_quote",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "cite",
						"heading" => esc_html__("Quote cite", "grace-church"),
						"description" => esc_html__("URL for the quote cite link", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title (author)", "grace-church"),
						"description" => esc_html__("Quote title (author name)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
                    array(
                        "param_name" => "style",
                        "heading" => esc_html__("Style Quote", "grace-church"),
                        "description" => esc_html__("Select a transparent background if you want to write a quote on the image", "grace-church"),
                        "value" => array(
                            esc_html__('Default', 'grace-church') => "",
                            esc_html__('Transparent', 'grace-church') => "transparent"
                        ),
                        "type" => "dropdown"
                    ),
					array(
						"param_name" => "content",
						"heading" => esc_html__("Quote content", "grace-church"),
						"description" => esc_html__("Quote content", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),

                    array(
                        "param_name" => "bg_color",
                        "heading" => esc_html__("Background color", "grace-church"),
                        "description" => esc_html__("Any background color for this section", "grace-church"),
                        "group" => esc_html__('Colors and Images', 'grace-church'),
                        'dependency' => array(
                            'element' => 'style',
                            'value' => array('transparent')
                        ),
                        "class" => "",
                        "value" => "",
                        "type" => "colorpicker"
                    ),
                    array(
                        "param_name" => "bg_image",
                        "heading" => esc_html__("Background image URL", "grace-church"),
                        "description" => esc_html__("Select background image from library for this section", "grace-church"),
                        "group" => esc_html__('Colors and Images', 'grace-church'),
                        'dependency' => array(
                            'element' => 'style',
                            'value' => array('transparent')
                        ),
                        "class" => "",
                        "value" => "",
                        "type" => "attach_image"
                    ),
                    array(
                        "param_name" => "bg_overlay",
                        "heading" => esc_html__("Overlay", "grace-church"),
                        "description" => esc_html__("Overlay color opacity (from 0.0 to 1.0)", "grace-church"),
                        "group" => esc_html__('Colors and Images', 'grace-church'),
                        'dependency' => array(
                            'element' => 'style',
                            'value' => array('transparent')
                        ),
                        "class" => "",
                        "value" => "",
                        "type" => "textfield"
                    ),

					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'js_view' => 'VcTrxTextView'
			) );
			
			class WPBakeryShortCode_Trx_Quote extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Reviews
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_reviews",
				"name" => esc_html__("Reviews", "grace-church"),
				"description" => esc_html__("Insert reviews block in the single post", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_reviews',
				"class" => "trx_sc_single trx_sc_reviews",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Align counter to left, center or right", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Reviews extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Search
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_search",
				"name" => esc_html__("Search form", "grace-church"),
				"description" => esc_html__("Insert search form", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_search',
				"class" => "trx_sc_single trx_sc_search",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Style", "grace-church"),
						"description" => esc_html__("Select style to display search field", "grace-church"),
						"class" => "",
						"value" => array(
							__('Regular', 'grace-church') => "regular",
							__('Flat', 'grace-church') => "flat"
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "state",
						"heading" => esc_html__("State", "grace-church"),
						"description" => esc_html__("Select search field initial state", "grace-church"),
						"class" => "",
						"value" => array(
							__('Fixed', 'grace-church')  => "fixed",
							__('Opened', 'grace-church') => "opened",
							__('Closed', 'grace-church') => "closed"
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Title (placeholder) for the search field", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => esc_html__("Search &hellip;", 'grace-church'),
						"type" => "textfield"
					),
					array(
						"param_name" => "ajax",
						"heading" => esc_html__("AJAX", "grace-church"),
						"description" => esc_html__("Search via AJAX or reload page", "grace-church"),
						"class" => "",
						"value" => array( esc_html__('Use AJAX search', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Search extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Section
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_section",
				"name" => esc_html__("Section container", "grace-church"),
				"description" => esc_html__("Container for any block ([block] analog - to enable nesting)", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				"class" => "trx_sc_collection trx_sc_section",
				'icon' => 'icon_trx_block',
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "dedicated",
						"heading" => esc_html__("Dedicated", "grace-church"),
						"description" => esc_html__("Use this block as dedicated content - show it before post title on single page", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array( esc_html__('Use as dedicated content', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Select block alignment", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "columns",
						"heading" => esc_html__("Columns emulation", "grace-church"),
						"description" => esc_html__("Select width for columns emulation", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['columns']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "pan",
						"heading" => esc_html__("Use pan effect", "grace-church"),
						"description" => esc_html__("Use pan effect to show section content", "grace-church"),
						"group" => esc_html__('Scroll', 'grace-church'),
						"class" => "",
						"value" => array( esc_html__('Content scroller', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "scroll",
						"heading" => esc_html__("Use scroller", "grace-church"),
						"description" => esc_html__("Use scroller to show section content", "grace-church"),
						"group" => esc_html__('Scroll', 'grace-church'),
						"admin_label" => true,
						"class" => "",
						"value" => array( esc_html__('Content scroller', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "scroll_dir",
						"heading" => esc_html__("Scroll and Pan direction", "grace-church"),
						"description" => esc_html__("Scroll and Pan direction (if Use scroller = yes or Pan = yes)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"group" => esc_html__('Scroll', 'grace-church'),
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['dir']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "scroll_controls",
						"heading" => esc_html__("Scroll controls", "grace-church"),
						"description" => esc_html__("Show scroll controls (if Use scroller = yes)", "grace-church"),
						"class" => "",
						"group" => esc_html__('Scroll', 'grace-church'),
						'dependency' => array(
							'element' => 'scroll',
							'not_empty' => true
						),
						"value" => array( esc_html__('Show scroll controls', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "scheme",
						"heading" => esc_html__("Color scheme", "grace-church"),
						"description" => esc_html__("Select color scheme for this block", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['schemes']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Fore color", "grace-church"),
						"description" => esc_html__("Any color for objects in this section", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Background color", "grace-church"),
						"description" => esc_html__("Any background color for this section", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_image",
						"heading" => esc_html__("Background image URL", "grace-church"),
						"description" => esc_html__("Select background image from library for this section", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "bg_overlay",
						"heading" => esc_html__("Overlay", "grace-church"),
						"description" => esc_html__("Overlay color opacity (from 0.0 to 1.0)", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_texture",
						"heading" => esc_html__("Texture", "grace-church"),
						"description" => esc_html__("Texture style from 1 to 11. Empty or 0 - without texture.", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "font_size",
						"heading" => esc_html__("Font size", "grace-church"),
						"description" => esc_html__("Font size of the text (default - in pixels, allows any CSS units of measure)", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "font_weight",
						"heading" => esc_html__("Font weight", "grace-church"),
						"description" => esc_html__("Font weight of the text", "grace-church"),
						"class" => "",
						"value" => array(
							__('Default', 'grace-church') => 'inherit',
							__('Thin (100)', 'grace-church') => '100',
							__('Light (300)', 'grace-church') => '300',
							__('Normal (400)', 'grace-church') => '400',
							__('Bold (700)', 'grace-church') => '700'
						),
						"type" => "dropdown"
					),
					/*
					array(
						"param_name" => "content",
						"heading" => esc_html__("Container content", "grace-church"),
						"description" => esc_html__("Content for section container", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					*/
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Section extends GRACE_CHURCH_VC_ShortCodeCollection {}
			
			
			
			
			
			
			
			// Skills
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_skills",
				"name" => esc_html__("Skills", "grace-church"),
				"description" => esc_html__("Insert skills diagramm", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_skills',
				"class" => "trx_sc_collection trx_sc_skills",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"as_parent" => array('only' => 'trx_skills_item'),
				"params" => array(
					array(
						"param_name" => "max_value",
						"heading" => esc_html__("Max value", "grace-church"),
						"description" => esc_html__("Max value for skills items", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "100",
						"type" => "textfield"
					),
					array(
						"param_name" => "type",
						"heading" => esc_html__("Skills type", "grace-church"),
						"description" => esc_html__("Select type of skills block", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array(
							__('Bar', 'grace-church') => 'bar',
							__('Pie chart', 'grace-church') => 'pie',
							__('Counter', 'grace-church') => 'counter',
							__('Arc', 'grace-church') => 'arc'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "layout",
						"heading" => esc_html__("Skills layout", "grace-church"),
						"description" => esc_html__("Select layout of skills block", "grace-church"),
						"admin_label" => true,
						'dependency' => array(
							'element' => 'type',
							'value' => array('counter','bar','pie')
						),
						"class" => "",
						"value" => array(
							__('Rows', 'grace-church') => 'rows',
							__('Columns', 'grace-church') => 'columns'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "dir",
						"heading" => esc_html__("Direction", "grace-church"),
						"description" => esc_html__("Select direction of skills block", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['dir']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "style",
						"heading" => esc_html__("Counters style", "grace-church"),
						"description" => esc_html__("Select style of skills items (only for type=counter)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip(grace_church_get_list_styles(1, 4)),
						'dependency' => array(
							'element' => 'type',
							'value' => array('counter')
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "columns",
						"heading" => esc_html__("Columns count", "grace-church"),
						"description" => esc_html__("Skills columns count (required)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Color", "grace-church"),
						"description" => esc_html__("Color for all skills items", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Background color", "grace-church"),
						"description" => esc_html__("Background color for all skills items (only for type=pie)", "grace-church"),
						'dependency' => array(
							'element' => 'type',
							'value' => array('pie')
						),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "border_color",
						"heading" => esc_html__("Border color", "grace-church"),
						"description" => esc_html__("Border color for all skills items (only for type=pie)", "grace-church"),
						'dependency' => array(
							'element' => 'type',
							'value' => array('pie')
						),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Align skills block to left or right side", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['float']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "arc_caption",
						"heading" => esc_html__("Arc caption", "grace-church"),
						"description" => esc_html__("Arc caption - text in the center of the diagram", "grace-church"),
						'dependency' => array(
							'element' => 'type',
							'value' => array('arc')
						),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "pie_compact",
						"heading" => esc_html__("Pie compact", "grace-church"),
						"description" => esc_html__("Show all skills in one diagram or as separate diagrams", "grace-church"),
						'dependency' => array(
							'element' => 'type',
							'value' => array('pie')
						),
						"class" => "",
						"value" => array( esc_html__('Show all skills in one diagram', 'grace-church') => 'on'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "pie_cutout",
						"heading" => esc_html__("Pie cutout", "grace-church"),
						"description" => esc_html__("Pie cutout (0-99). 0 - without cutout, 99 - max cutout", "grace-church"),
						'dependency' => array(
							'element' => 'type',
							'value' => array('pie')
						),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Title for the block", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "subtitle",
						"heading" => esc_html__("Subtitle", "grace-church"),
						"description" => esc_html__("Subtitle for the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "description",
						"heading" => esc_html__("Description", "grace-church"),
						"description" => esc_html__("Description for the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textarea"
					),
					array(
						"param_name" => "link",
						"heading" => esc_html__("Button URL", "grace-church"),
						"description" => esc_html__("Link URL for the button at the bottom of the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "link_caption",
						"heading" => esc_html__("Button caption", "grace-church"),
						"description" => esc_html__("Caption for the button at the bottom of the block", "grace-church"),
						"group" => esc_html__('Captions', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			
			vc_map( array(
				"base" => "trx_skills_item",
				"name" => esc_html__("Skill", "grace-church"),
				"description" => esc_html__("Skills item", "grace-church"),
				"show_settings_on_create" => true,
				"class" => "trx_sc_single trx_sc_skills_item",
				"content_element" => true,
				"is_container" => false,
				"as_child" => array('only' => 'trx_skills'),
				"as_parent" => array('except' => 'trx_skills'),
				"params" => array(
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Title for the current skills item", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "value",
						"heading" => esc_html__("Value", "grace-church"),
						"description" => esc_html__("Value for the current skills item", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Color", "grace-church"),
						"description" => esc_html__("Color for current skills item", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Background color", "grace-church"),
						"description" => esc_html__("Background color for current skills item (only for type=pie)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "border_color",
						"heading" => esc_html__("Border color", "grace-church"),
						"description" => esc_html__("Border color for current skills item (only for type=pie)", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "style",
						"heading" => esc_html__("Counter style", "grace-church"),
						"description" => esc_html__("Select style for the current skills item (only for type=counter)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip(grace_church_get_list_styles(1, 4)),
						"type" => "dropdown"
					),
					array(
						"param_name" => "icon",
						"heading" => esc_html__("Counter icon", "grace-church"),
						"description" => esc_html__("Select icon from Fontello icons set, placed before counter (only for type=counter)", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css']
				)
			) );
			
			class WPBakeryShortCode_Trx_Skills extends GRACE_CHURCH_VC_ShortCodeCollection {}
			class WPBakeryShortCode_Trx_Skills_Item extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Slider
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_slider",
				"name" => esc_html__("Slider", "grace-church"),
				"description" => esc_html__("Insert slider", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_slider',
				"class" => "trx_sc_collection trx_sc_slider",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"as_parent" => array('only' => 'trx_slider_item'),
				"params" => array_merge(array(
					array(
						"param_name" => "engine",
						"heading" => esc_html__("Engine", "grace-church"),
						"description" => esc_html__("Select engine for slider. Attention! Swiper is built-in engine, all other engines appears only if corresponding plugings are installed", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['sliders']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Float slider", "grace-church"),
						"description" => esc_html__("Float slider to left or right side", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['float']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "custom",
						"heading" => esc_html__("Custom slides", "grace-church"),
						"description" => esc_html__("Make custom slides from inner shortcodes (prepare it on tabs) or prepare slides from posts thumbnails", "grace-church"),
						"class" => "",
						"value" => array( esc_html__('Custom slides', 'grace-church') => 'yes'),
						"type" => "checkbox"
					)
					),
					grace_church_exists_revslider() ? array(
					array(
						"param_name" => "alias",
						"heading" => esc_html__("Revolution slider alias", "grace-church"),
						"description" => esc_html__("Select Revolution slider to display", "grace-church"),
						"admin_label" => true,
						"class" => "",
						'dependency' => array(
							'element' => 'engine',
							'value' => array('revo')
						),
						"value" => array_flip(grace_church_array_merge(array('none' => esc_html__('- Select slider -', 'grace-church')), $GRACE_CHURCH_GLOBALS['sc_params']['revo_sliders'])),
						"type" => "dropdown"
					)) : array(), array(
					array(
						"param_name" => "cat",
						"heading" => esc_html__("Categories list", "grace-church"),
						"description" => esc_html__("Select category. If empty - show posts from any category or from IDs list", "grace-church"),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => array_flip(grace_church_array_merge(array(0 => esc_html__('- Select category -', 'grace-church')), $GRACE_CHURCH_GLOBALS['sc_params']['categories'])),
						"type" => "dropdown"
					),
					array(
						"param_name" => "count",
						"heading" => esc_html__("Swiper: Number of posts", "grace-church"),
						"description" => esc_html__("How many posts will be displayed? If used IDs - this parameter ignored.", "grace-church"),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => "3",
						"type" => "textfield"
					),
					array(
						"param_name" => "offset",
						"heading" => esc_html__("Swiper: Offset before select posts", "grace-church"),
						"description" => esc_html__("Skip posts before select next part.", "grace-church"),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => "0",
						"type" => "textfield"
					),
					array(
						"param_name" => "orderby",
						"heading" => esc_html__("Swiper: Post sorting", "grace-church"),
						"description" => esc_html__("Select desired posts sorting method", "grace-church"),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['sorting']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "order",
						"heading" => esc_html__("Swiper: Post order", "grace-church"),
						"description" => esc_html__("Select desired posts order", "grace-church"),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "ids",
						"heading" => esc_html__("Swiper: Post IDs list", "grace-church"),
						"description" => esc_html__("Comma separated list of posts ID. If set - parameters above are ignored!", "grace-church"),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "controls",
						"heading" => esc_html__("Swiper: Show slider controls", "grace-church"),
						"description" => esc_html__("Show arrows inside slider", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
                        "std" => "no",
						"value" => array( esc_html__('Show controls', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "pagination",
						"heading" => esc_html__("Swiper: Show slider pagination", "grace-church"),
						"description" => esc_html__("Show bullets or titles to switch slides", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"std" => "no",
						"value" => array(
								__('None', 'grace-church') => 'no',
								__('Dots', 'grace-church') => 'yes',
								__('Side Titles', 'grace-church') => 'full',
								__('Over Titles', 'grace-church') => 'over'
							),
						"type" => "dropdown"
					),
					array(
						"param_name" => "titles",
						"heading" => esc_html__("Swiper: Show titles section", "grace-church"),
						"description" => esc_html__("Show section with post's title and short post's description", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => array(
								__('Not show', 'grace-church') => "no",
								__('Show/Hide info', 'grace-church') => "slide",
								__('Fixed info', 'grace-church') => "fixed"
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "descriptions",
						"heading" => esc_html__("Swiper: Post descriptions", "grace-church"),
						"description" => esc_html__("Show post's excerpt max length (characters)", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => "0",
						"type" => "textfield"
					),
					array(
						"param_name" => "links",
						"heading" => esc_html__("Swiper: Post's title as link", "grace-church"),
						"description" => esc_html__("Make links from post's titles", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => array( esc_html__('Titles as a links', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "crop",
						"heading" => esc_html__("Swiper: Crop images", "grace-church"),
						"description" => esc_html__("Crop images in each slide or live it unchanged", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => array( esc_html__('Crop images', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "autoheight",
						"heading" => esc_html__("Swiper: Autoheight", "grace-church"),
						"description" => esc_html__("Change whole slider's height (make it equal current slide's height)", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => array( esc_html__('Autoheight', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					array(
						"param_name" => "slides_per_view",
						"heading" => esc_html__("Swiper: Slides per view", "grace-church"),
						"description" => esc_html__("Slides per view showed in this slider", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Details', 'grace-church'),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => "1",
						"type" => "textfield"
					),
					array(
						"param_name" => "slides_space",
						"heading" => esc_html__("Swiper: Space between slides", "grace-church"),
						"description" => esc_html__("Size of space (in px) between slides", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Details', 'grace-church'),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => "0",
						"type" => "textfield"
					),
					array(
						"param_name" => "interval",
						"heading" => esc_html__("Swiper: Slides change interval", "grace-church"),
						"description" => esc_html__("Slides change interval (in milliseconds: 1000ms = 1s)", "grace-church"),
						"group" => esc_html__('Details', 'grace-church'),
						'dependency' => array(
							'element' => 'engine',
							'value' => array('swiper')
						),
						"class" => "",
						"value" => "5000",
						"type" => "textfield"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				))
			) );
			
			
			vc_map( array(
				"base" => "trx_slider_item",
				"name" => esc_html__("Slide", "grace-church"),
				"description" => esc_html__("Slider item - single slide", "grace-church"),
				"show_settings_on_create" => true,
				"content_element" => true,
				"is_container" => false,
				'icon' => 'icon_trx_slider_item',
				"as_child" => array('only' => 'trx_slider'),
				"as_parent" => array('except' => 'trx_slider'),
				"params" => array(
					array(
						"param_name" => "src",
						"heading" => esc_html__("URL (source) for image file", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site for the current slide", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css']
				)
			) );
			
			class WPBakeryShortCode_Trx_Slider extends GRACE_CHURCH_VC_ShortCodeCollection {}
			class WPBakeryShortCode_Trx_Slider_Item extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Socials
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_socials",
				"name" => esc_html__("Social icons", "grace-church"),
				"description" => esc_html__("Custom social icons", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_socials',
				"class" => "trx_sc_collection trx_sc_socials",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"as_parent" => array('only' => 'trx_social_item'),
				"params" => array_merge(array(
					array(
						"param_name" => "type",
						"heading" => esc_html__("Icon's type", "grace-church"),
						"description" => esc_html__("Type of the icons - images or font icons", "grace-church"),
						"class" => "",
						"std" => grace_church_get_theme_setting('socials_type'),
						"value" => array(
							__('Icons', 'grace-church') => 'icons',
							__('Images', 'grace-church') => 'images'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "size",
						"heading" => esc_html__("Icon's size", "grace-church"),
						"description" => esc_html__("Size of the icons", "grace-church"),
						"class" => "",
						"std" => "small",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['sizes']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "shape",
						"heading" => esc_html__("Icon's shape", "grace-church"),
						"description" => esc_html__("Shape of the icons", "grace-church"),
						"class" => "",
						"std" => "square",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['shapes']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "socials",
						"heading" => esc_html__("Manual socials list", "grace-church"),
						"description" => esc_html__("Custom list of social networks. For example: twitter=http://twitter.com/my_profile|facebook=http://facebooc.com/my_profile. If empty - use socials from Theme options.", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "custom",
						"heading" => esc_html__("Custom socials", "grace-church"),
						"description" => esc_html__("Make custom icons from inner shortcodes (prepare it on tabs)", "grace-church"),
						"class" => "",
						"value" => array( esc_html__('Custom socials', 'grace-church') => 'yes'),
						"type" => "checkbox"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				))
			) );
			
			
			vc_map( array(
				"base" => "trx_social_item",
				"name" => esc_html__("Custom social item", "grace-church"),
				"description" => esc_html__("Custom social item: name, profile url and icon url", "grace-church"),
				"show_settings_on_create" => true,
				"content_element" => true,
				"is_container" => false,
				'icon' => 'icon_trx_social_item',
				"as_child" => array('only' => 'trx_socials'),
				"as_parent" => array('except' => 'trx_socials'),
				"params" => array(
					array(
						"param_name" => "name",
						"heading" => esc_html__("Social name", "grace-church"),
						"description" => esc_html__("Name (slug) of the social network (twitter, facebook, linkedin, etc.)", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "url",
						"heading" => esc_html__("Your profile URL", "grace-church"),
						"description" => esc_html__("URL of your profile in specified social network", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "icon",
						"heading" => esc_html__("URL (source) for icon file", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site for the current social icon", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					)
				)
			) );
			
			class WPBakeryShortCode_Trx_Socials extends GRACE_CHURCH_VC_ShortCodeCollection {}
			class WPBakeryShortCode_Trx_Social_Item extends GRACE_CHURCH_VC_ShortCodeSingle {}
			

			
			
			
			
			
			// Table
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_table",
				"name" => esc_html__("Table", "grace-church"),
				"description" => esc_html__("Insert a table", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_table',
				"class" => "trx_sc_container trx_sc_table",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "align",
						"heading" => esc_html__("Cells content alignment", "grace-church"),
						"description" => esc_html__("Select alignment for each table cell", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "content",
						"heading" => esc_html__("Table content", "grace-church"),
						"description" => esc_html__("Content, created with any table-generator", "grace-church"),
						"class" => "",
						"value" => "Paste here table content, generated on one of many public internet resources, for example: http://www.impressivewebs.com/html-table-code-generator/ or http://html-tables.com/",
						"type" => "textarea_html"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'js_view' => 'VcTrxTextContainerView'
			) );
			
			class WPBakeryShortCode_Trx_Table extends GRACE_CHURCH_VC_ShortCodeContainer {}
			
			
			
			
			
			
			
			// Tabs
			//-------------------------------------------------------------------------------------
			
			$tab_id_1 = 'sc_tab_'.time() . '_1_' . rand( 0, 100 );
			$tab_id_2 = 'sc_tab_'.time() . '_2_' . rand( 0, 100 );
			vc_map( array(
				"base" => "trx_tabs",
				"name" => esc_html__("Tabs", "grace-church"),
				"description" => esc_html__("Tabs", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_tabs',
				"class" => "trx_sc_collection trx_sc_tabs",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => false,
				"as_parent" => array('only' => 'trx_tab'),
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Tabs style", "grace-church"),
						"description" => esc_html__("Select style of tabs items", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip(grace_church_get_list_styles(1, 2)),
						"type" => "dropdown"
					),
					array(
						"param_name" => "initial",
						"heading" => esc_html__("Initially opened tab", "grace-church"),
						"description" => esc_html__("Number of initially opened tab", "grace-church"),
						"class" => "",
						"value" => 1,
						"type" => "textfield"
					),
					array(
						"param_name" => "scroll",
						"heading" => esc_html__("Scroller", "grace-church"),
						"description" => esc_html__("Use scroller to show tab content (height parameter required)", "grace-church"),
						"class" => "",
						"value" => array("Use scroller" => "yes" ),
						"type" => "checkbox"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'default_content' => '
					[trx_tab title="' . esc_html__( 'Tab 1', 'grace-church' ) . '" tab_id="'.esc_attr($tab_id_1).'"][/trx_tab]
					[trx_tab title="' . esc_html__( 'Tab 2', 'grace-church' ) . '" tab_id="'.esc_attr($tab_id_2).'"][/trx_tab]
				',
				"custom_markup" => '
					<div class="wpb_tabs_holder wpb_holder vc_container_for_children">
						<ul class="tabs_controls">
						</ul>
						%content%
					</div>
				',
				'js_view' => 'VcTrxTabsView'
			) );
			
			
			vc_map( array(
				"base" => "trx_tab",
				"name" => esc_html__("Tab item", "grace-church"),
				"description" => esc_html__("Single tab item", "grace-church"),
				"show_settings_on_create" => true,
				"class" => "trx_sc_collection trx_sc_tab",
				"content_element" => true,
				"is_container" => true,
				'icon' => 'icon_trx_tab',
				"as_child" => array('only' => 'trx_tabs'),
				"as_parent" => array('except' => 'trx_tabs'),
				"params" => array(
					array(
						"param_name" => "title",
						"heading" => esc_html__("Tab title", "grace-church"),
						"description" => esc_html__("Title for current tab", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "tab_id",
						"heading" => esc_html__("Tab ID", "grace-church"),
						"description" => esc_html__("ID for current tab (required). Please, start it from letter.", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css']
				),
			  'js_view' => 'VcTrxTabView'
			) );
			class WPBakeryShortCode_Trx_Tabs extends GRACE_CHURCH_VC_ShortCodeTabs {}
			class WPBakeryShortCode_Trx_Tab extends GRACE_CHURCH_VC_ShortCodeTab {}
			
			
			
			
			
			
			
			// Title
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_title",
				"name" => esc_html__("Title", "grace-church"),
				"description" => esc_html__("Create header tag (1-6 level) with many styles", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_title',
				"class" => "trx_sc_single trx_sc_title",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "content",
						"heading" => esc_html__("Title content", "grace-church"),
						"description" => esc_html__("Title content", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textarea_html"
					),
					array(
						"param_name" => "type",
						"heading" => esc_html__("Title type", "grace-church"),
						"description" => esc_html__("Title type (header level)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array(
							__('Header 1', 'grace-church') => '1',
							__('Header 2', 'grace-church') => '2',
							__('Header 3', 'grace-church') => '3',
							__('Header 4', 'grace-church') => '4',
							__('Header 5', 'grace-church') => '5',
							__('Header 6', 'grace-church') => '6'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "style",
						"heading" => esc_html__("Title style", "grace-church"),
						"description" => esc_html__("Title style: only text (regular) or with icon/image (iconed)", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array(
							__('Regular', 'grace-church') => 'regular',
							__('Underline', 'grace-church') => 'underline',
							__('Divider', 'grace-church') => 'divider',
							__('With icon (image)', 'grace-church') => 'iconed'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Title text alignment", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "font_size",
						"heading" => esc_html__("Font size", "grace-church"),
						"description" => esc_html__("Custom font size. If empty - use theme default", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "font_weight",
						"heading" => esc_html__("Font weight", "grace-church"),
						"description" => esc_html__("Custom font weight. If empty or inherit - use theme default", "grace-church"),
						"class" => "",
						"value" => array(
							__('Default', 'grace-church') => 'inherit',
							__('Thin (100)', 'grace-church') => '100',
							__('Light (300)', 'grace-church') => '300',
							__('Normal (400)', 'grace-church') => '400',
							__('Semibold (600)', 'grace-church') => '600',
							__('Bold (700)', 'grace-church') => '700',
							__('Black (900)', 'grace-church') => '900'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "color",
						"heading" => esc_html__("Title color", "grace-church"),
						"description" => esc_html__("Select color for the title", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "icon",
						"heading" => esc_html__("Title font icon", "grace-church"),
						"description" => esc_html__("Select font icon for the title from Fontello icons set (if style=iconed)", "grace-church"),
						"class" => "",
						"group" => esc_html__('Icon &amp; Image', 'grace-church'),
						'dependency' => array(
							'element' => 'style',
							'value' => array('iconed')
						),
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "image",
						"heading" => esc_html__("or image icon", "grace-church"),
						"description" => esc_html__("Select image icon for the title instead icon above (if style=iconed)", "grace-church"),
						"class" => "",
						"group" => esc_html__('Icon &amp; Image', 'grace-church'),
						'dependency' => array(
							'element' => 'style',
							'value' => array('iconed')
						),
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['images'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "picture",
						"heading" => esc_html__("or select uploaded image", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site (if style=iconed)", "grace-church"),
						"group" => esc_html__('Icon &amp; Image', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "image_size",
						"heading" => esc_html__("Image (picture) size", "grace-church"),
						"description" => esc_html__("Select image (picture) size (if style=iconed)", "grace-church"),
						"group" => esc_html__('Icon &amp; Image', 'grace-church'),
						"class" => "",
						"value" => array(
							__('Small', 'grace-church') => 'small',
							__('Medium', 'grace-church') => 'medium',
							__('Large', 'grace-church') => 'large'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "position",
						"heading" => esc_html__("Icon (image) position", "grace-church"),
						"description" => esc_html__("Select icon (image) position (if style=iconed)", "grace-church"),
						"group" => esc_html__('Icon &amp; Image', 'grace-church'),
						"class" => "",
						"value" => array(
							__('Top', 'grace-church') => 'top',
							__('Left', 'grace-church') => 'left'
						),
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'js_view' => 'VcTrxTextView'
			) );
			
			class WPBakeryShortCode_Trx_Title extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Toggles
			//-------------------------------------------------------------------------------------
				
			vc_map( array(
				"base" => "trx_toggles",
				"name" => esc_html__("Toggles", "grace-church"),
				"description" => esc_html__("Toggles items", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_toggles',
				"class" => "trx_sc_collection trx_sc_toggles",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => false,
				"as_parent" => array('only' => 'trx_toggles_item'),
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Toggles style", "grace-church"),
						"description" => esc_html__("Select style for display toggles", "grace-church"),
						"class" => "",
						"admin_label" => true,
						"value" => array_flip(grace_church_get_list_styles(1, 2)),
						"type" => "dropdown"
					),
					array(
						"param_name" => "counter",
						"heading" => esc_html__("Counter", "grace-church"),
						"description" => esc_html__("Display counter before each toggles title", "grace-church"),
						"class" => "",
						"value" => array("Add item numbers before each element" => "on" ),
						"type" => "checkbox"
					),
					array(
						"param_name" => "icon_closed",
						"heading" => esc_html__("Icon while closed", "grace-church"),
						"description" => esc_html__("Select icon for the closed toggles item from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "icon_opened",
						"heading" => esc_html__("Icon while opened", "grace-church"),
						"description" => esc_html__("Select icon for the opened toggles item from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
				'default_content' => '
					[trx_toggles_item title="' . esc_html__( 'Item 1 title', 'grace-church' ) . '"][/trx_toggles_item]
					[trx_toggles_item title="' . esc_html__( 'Item 2 title', 'grace-church' ) . '"][/trx_toggles_item]
				',
				"custom_markup" => '
					<div class="wpb_accordion_holder wpb_holder clearfix vc_container_for_children">
						%content%
					</div>
					<div class="tab_controls">
						<button class="add_tab" title="'.__("Add item", "grace-church").'">'.__("Add item", "grace-church").'</button>
					</div>
				',
				'js_view' => 'VcTrxTogglesView'
			) );
			
			
			vc_map( array(
				"base" => "trx_toggles_item",
				"name" => esc_html__("Toggles item", "grace-church"),
				"description" => esc_html__("Single toggles item", "grace-church"),
				"show_settings_on_create" => true,
				"content_element" => true,
				"is_container" => true,
				'icon' => 'icon_trx_toggles_item',
				"as_child" => array('only' => 'trx_toggles'),
				"as_parent" => array('except' => 'trx_toggles'),
				"params" => array(
					array(
						"param_name" => "title",
						"heading" => esc_html__("Title", "grace-church"),
						"description" => esc_html__("Title for current toggles item", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "open",
						"heading" => esc_html__("Open on show", "grace-church"),
						"description" => esc_html__("Open current toggle item on show", "grace-church"),
						"class" => "",
						"value" => array("Opened" => "yes" ),
						"type" => "checkbox"
					),
					array(
						"param_name" => "icon_closed",
						"heading" => esc_html__("Icon while closed", "grace-church"),
						"description" => esc_html__("Select icon for the closed toggles item from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					array(
						"param_name" => "icon_opened",
						"heading" => esc_html__("Icon while opened", "grace-church"),
						"description" => esc_html__("Select icon for the opened toggles item from Fontello icons set", "grace-church"),
						"class" => "",
						"value" => $GRACE_CHURCH_GLOBALS['sc_params']['icons'],
						"type" => "dropdown"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css']
				),
				'js_view' => 'VcTrxTogglesTabView'
			) );
			class WPBakeryShortCode_Trx_Toggles extends GRACE_CHURCH_VC_ShortCodeToggles {}
			class WPBakeryShortCode_Trx_Toggles_Item extends GRACE_CHURCH_VC_ShortCodeTogglesItem {}
			
			
			
			
			
			
			// Twitter
			//-------------------------------------------------------------------------------------

			vc_map( array(
				"base" => "trx_twitter",
				"name" => esc_html__("Twitter", "grace-church"),
				"description" => esc_html__("Insert twitter feed into post (page)", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_twitter',
				"class" => "trx_sc_single trx_sc_twitter",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "user",
						"heading" => esc_html__("Twitter Username", "grace-church"),
						"description" => esc_html__("Your username in the twitter account. If empty - get it from Theme Options.", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "consumer_key",
						"heading" => esc_html__("Consumer Key", "grace-church"),
						"description" => esc_html__("Consumer Key from the twitter account", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "consumer_secret",
						"heading" => esc_html__("Consumer Secret", "grace-church"),
						"description" => esc_html__("Consumer Secret from the twitter account", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "token_key",
						"heading" => esc_html__("Token Key", "grace-church"),
						"description" => esc_html__("Token Key from the twitter account", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "token_secret",
						"heading" => esc_html__("Token Secret", "grace-church"),
						"description" => esc_html__("Token Secret from the twitter account", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "count",
						"heading" => esc_html__("Tweets number", "grace-church"),
						"description" => esc_html__("Number tweets to show", "grace-church"),
						"class" => "",
						"divider" => true,
						"value" => 3,
						"type" => "textfield"
					),
					array(
						"param_name" => "controls",
						"heading" => esc_html__("Show arrows", "grace-church"),
						"description" => esc_html__("Show control buttons", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['yes_no']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "interval",
						"heading" => esc_html__("Tweets change interval", "grace-church"),
						"description" => esc_html__("Tweets change interval (in milliseconds: 1000ms = 1s)", "grace-church"),
						"class" => "",
						"value" => "7000",
						"type" => "textfield"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Alignment of the tweets block", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "autoheight",
						"heading" => esc_html__("Autoheight", "grace-church"),
						"description" => esc_html__("Change whole slider's height (make it equal current slide's height)", "grace-church"),
						"class" => "",
						"value" => array("Autoheight" => "yes" ),
						"type" => "checkbox"
					),
					array(
						"param_name" => "scheme",
						"heading" => esc_html__("Color scheme", "grace-church"),
						"description" => esc_html__("Select color scheme for this block", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['schemes']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "bg_color",
						"heading" => esc_html__("Background color", "grace-church"),
						"description" => esc_html__("Any background color for this section", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "colorpicker"
					),
					array(
						"param_name" => "bg_image",
						"heading" => esc_html__("Background image URL", "grace-church"),
						"description" => esc_html__("Select background image from library for this section", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "bg_overlay",
						"heading" => esc_html__("Overlay", "grace-church"),
						"description" => esc_html__("Overlay color opacity (from 0.0 to 1.0)", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_texture",
						"heading" => esc_html__("Texture", "grace-church"),
						"description" => esc_html__("Texture style from 1 to 11. Empty or 0 - without texture.", "grace-church"),
						"group" => esc_html__('Colors and Images', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				),
			) );
			
			class WPBakeryShortCode_Trx_Twitter extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Video
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_video",
				"name" => esc_html__("Video", "grace-church"),
				"description" => esc_html__("Insert video player", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_video',
				"class" => "trx_sc_single trx_sc_video",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "url",
						"heading" => esc_html__("URL for video file", "grace-church"),
						"description" => esc_html__("Paste URL for video file", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "ratio",
						"heading" => esc_html__("Ratio", "grace-church"),
						"description" => esc_html__("Select ratio for display video", "grace-church"),
						"class" => "",
						"value" => array(
							__('16:9', 'grace-church') => "16:9",
							__('4:3', 'grace-church') => "4:3"
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "autoplay",
						"heading" => esc_html__("Autoplay video", "grace-church"),
						"description" => esc_html__("Autoplay video on page load", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array("Autoplay" => "on" ),
						"type" => "checkbox"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Select block alignment", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "image",
						"heading" => esc_html__("Cover image", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site for video preview", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "bg_image",
						"heading" => esc_html__("Background image", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site for video background. Attention! If you use background image - specify paddings below from background margins to video block in percents!", "grace-church"),
						"group" => esc_html__('Background', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "bg_top",
						"heading" => esc_html__("Top offset", "grace-church"),
						"description" => esc_html__("Top offset (padding) from background image to video block (in percent). For example: 3%", "grace-church"),
						"group" => esc_html__('Background', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_bottom",
						"heading" => esc_html__("Bottom offset", "grace-church"),
						"description" => esc_html__("Bottom offset (padding) from background image to video block (in percent). For example: 3%", "grace-church"),
						"group" => esc_html__('Background', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_left",
						"heading" => esc_html__("Left offset", "grace-church"),
						"description" => esc_html__("Left offset (padding) from background image to video block (in percent). For example: 20%", "grace-church"),
						"group" => esc_html__('Background', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_right",
						"heading" => esc_html__("Right offset", "grace-church"),
						"description" => esc_html__("Right offset (padding) from background image to video block (in percent). For example: 12%", "grace-church"),
						"group" => esc_html__('Background', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Video extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
			
			
			
			// Zoom
			//-------------------------------------------------------------------------------------
			
			vc_map( array(
				"base" => "trx_zoom",
				"name" => esc_html__("Zoom", "grace-church"),
				"description" => esc_html__("Insert the image with zoom/lens effect", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_zoom',
				"class" => "trx_sc_single trx_sc_zoom",
				"content_element" => true,
				"is_container" => false,
				"show_settings_on_create" => true,
				"params" => array(
					array(
						"param_name" => "effect",
						"heading" => esc_html__("Effect", "grace-church"),
						"description" => esc_html__("Select effect to display overlapping image", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"std" => "zoom",
						"value" => array(
							__('Lens', 'grace-church') => 'lens',
							__('Zoom', 'grace-church') => 'zoom'
						),
						"type" => "dropdown"
					),
					array(
						"param_name" => "url",
						"heading" => esc_html__("Main image", "grace-church"),
						"description" => esc_html__("Select or upload main image", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "over",
						"heading" => esc_html__("Overlaping image", "grace-church"),
						"description" => esc_html__("Select or upload overlaping image", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Float zoom to left or right side", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['float']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "bg_image",
						"heading" => esc_html__("Background image", "grace-church"),
						"description" => esc_html__("Select or upload image or write URL from other site for zoom background. Attention! If you use background image - specify paddings below from background margins to video block in percents!", "grace-church"),
						"group" => esc_html__('Background', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "bg_top",
						"heading" => esc_html__("Top offset", "grace-church"),
						"description" => esc_html__("Top offset (padding) from background image to zoom block (in percent). For example: 3%", "grace-church"),
						"group" => esc_html__('Background', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_bottom",
						"heading" => esc_html__("Bottom offset", "grace-church"),
						"description" => esc_html__("Bottom offset (padding) from background image to zoom block (in percent). For example: 3%", "grace-church"),
						"group" => esc_html__('Background', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_left",
						"heading" => esc_html__("Left offset", "grace-church"),
						"description" => esc_html__("Left offset (padding) from background image to zoom block (in percent). For example: 20%", "grace-church"),
						"group" => esc_html__('Background', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "bg_right",
						"heading" => esc_html__("Right offset", "grace-church"),
						"description" => esc_html__("Right offset (padding) from background image to zoom block (in percent). For example: 12%", "grace-church"),
						"group" => esc_html__('Background', 'grace-church'),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css'],
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right']
				)
			) );
			
			class WPBakeryShortCode_Trx_Zoom extends GRACE_CHURCH_VC_ShortCodeSingle {}
			

			do_action('grace_church_action_shortcodes_list_vc');
			
			
			if (false && grace_church_exists_woocommerce()) {
			
				// WooCommerce - Cart
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "woocommerce_cart",
					"name" => esc_html__("Cart", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show cart page", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_wooc_cart',
					"class" => "trx_sc_alone trx_sc_woocommerce_cart",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => false,
					"params" => array(
						array(
							"param_name" => "dummy",
							"heading" => esc_html__("Dummy data", "grace-church"),
							"description" => esc_html__("Dummy data - not used in shortcodes", "grace-church"),
							"class" => "",
							"value" => "",
							"type" => "textfield"
						)
					)
				) );
				
				class WPBakeryShortCode_Woocommerce_Cart extends GRACE_CHURCH_VC_ShortCodeAlone {}
			
			
				// WooCommerce - Checkout
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "woocommerce_checkout",
					"name" => esc_html__("Checkout", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show checkout page", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_wooc_checkout',
					"class" => "trx_sc_alone trx_sc_woocommerce_checkout",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => false,
					"params" => array(
						array(
							"param_name" => "dummy",
							"heading" => esc_html__("Dummy data", "grace-church"),
							"description" => esc_html__("Dummy data - not used in shortcodes", "grace-church"),
							"class" => "",
							"value" => "",
							"type" => "textfield"
						)
					)
				) );
				
				class WPBakeryShortCode_Woocommerce_Checkout extends GRACE_CHURCH_VC_ShortCodeAlone {}
			
			
				// WooCommerce - My Account
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "woocommerce_my_account",
					"name" => esc_html__("My Account", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show my account page", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_wooc_my_account',
					"class" => "trx_sc_alone trx_sc_woocommerce_my_account",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => false,
					"params" => array(
						array(
							"param_name" => "dummy",
							"heading" => esc_html__("Dummy data", "grace-church"),
							"description" => esc_html__("Dummy data - not used in shortcodes", "grace-church"),
							"class" => "",
							"value" => "",
							"type" => "textfield"
						)
					)
				) );
				
				class WPBakeryShortCode_Woocommerce_My_Account extends GRACE_CHURCH_VC_ShortCodeAlone {}
			
			
				// WooCommerce - Order Tracking
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "woocommerce_order_tracking",
					"name" => esc_html__("Order Tracking", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show order tracking page", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_wooc_order_tracking',
					"class" => "trx_sc_alone trx_sc_woocommerce_order_tracking",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => false,
					"params" => array(
						array(
							"param_name" => "dummy",
							"heading" => esc_html__("Dummy data", "grace-church"),
							"description" => esc_html__("Dummy data - not used in shortcodes", "grace-church"),
							"class" => "",
							"value" => "",
							"type" => "textfield"
						)
					)
				) );
				
				class WPBakeryShortCode_Woocommerce_Order_Tracking extends GRACE_CHURCH_VC_ShortCodeAlone {}
			
			
				// WooCommerce - Shop Messages
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "shop_messages",
					"name" => esc_html__("Shop Messages", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show shop messages", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_wooc_shop_messages',
					"class" => "trx_sc_alone trx_sc_shop_messages",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => false,
					"params" => array(
						array(
							"param_name" => "dummy",
							"heading" => esc_html__("Dummy data", "grace-church"),
							"description" => esc_html__("Dummy data - not used in shortcodes", "grace-church"),
							"class" => "",
							"value" => "",
							"type" => "textfield"
						)
					)
				) );
				
				class WPBakeryShortCode_Shop_Messages extends GRACE_CHURCH_VC_ShortCodeAlone {}
			
			
				// WooCommerce - Product Page
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "product_page",
					"name" => esc_html__("Product Page", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: display single product page", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_product_page',
					"class" => "trx_sc_single trx_sc_product_page",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "sku",
							"heading" => esc_html__("SKU", "grace-church"),
							"description" => esc_html__("SKU code of displayed product", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "id",
							"heading" => esc_html__("ID", "grace-church"),
							"description" => esc_html__("ID of displayed product", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "posts_per_page",
							"heading" => esc_html__("Number", "grace-church"),
							"description" => esc_html__("How many products showed", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "post_type",
							"heading" => esc_html__("Post type", "grace-church"),
							"description" => esc_html__("Post type for the WP query (leave 'product')", "grace-church"),
							"class" => "",
							"value" => "product",
							"type" => "textfield"
						),
						array(
							"param_name" => "post_status",
							"heading" => esc_html__("Post status", "grace-church"),
							"description" => esc_html__("Display posts only with this status", "grace-church"),
							"class" => "",
							"value" => array(
								__('Publish', 'grace-church') => 'publish',
								__('Protected', 'grace-church') => 'protected',
								__('Private', 'grace-church') => 'private',
								__('Pending', 'grace-church') => 'pending',
								__('Draft', 'grace-church') => 'draft'
							),
							"type" => "dropdown"
						)
					)
				) );
				
				class WPBakeryShortCode_Product_Page extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
				// WooCommerce - Product
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "product",
					"name" => esc_html__("Product", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: display one product", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_product',
					"class" => "trx_sc_single trx_sc_product",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "sku",
							"heading" => esc_html__("SKU", "grace-church"),
							"description" => esc_html__("Product's SKU code", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "id",
							"heading" => esc_html__("ID", "grace-church"),
							"description" => esc_html__("Product's ID", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						)
					)
				) );
				
				class WPBakeryShortCode_Product extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
				// WooCommerce - Best Selling Products
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "best_selling_products",
					"name" => esc_html__("Best Selling Products", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show best selling products", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_best_selling_products',
					"class" => "trx_sc_single trx_sc_best_selling_products",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "per_page",
							"heading" => esc_html__("Number", "grace-church"),
							"description" => esc_html__("How many products showed", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "4",
							"type" => "textfield"
						),
						array(
							"param_name" => "columns",
							"heading" => esc_html__("Columns", "grace-church"),
							"description" => esc_html__("How many columns per row use for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						)
					)
				) );
				
				class WPBakeryShortCode_Best_Selling_Products extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
				// WooCommerce - Recent Products
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "recent_products",
					"name" => esc_html__("Recent Products", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show recent products", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_recent_products',
					"class" => "trx_sc_single trx_sc_recent_products",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "per_page",
							"heading" => esc_html__("Number", "grace-church"),
							"description" => esc_html__("How many products showed", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "4",
							"type" => "textfield"
						),
						array(
							"param_name" => "columns",
							"heading" => esc_html__("Columns", "grace-church"),
							"description" => esc_html__("How many columns per row use for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "orderby",
							"heading" => esc_html__("Order by", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array(
								__('Date', 'grace-church') => 'date',
								__('Title', 'grace-church') => 'title'
							),
							"type" => "dropdown"
						),
						array(
							"param_name" => "order",
							"heading" => esc_html__("Order", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
							"type" => "dropdown"
						)
					)
				) );
				
				class WPBakeryShortCode_Recent_Products extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
				// WooCommerce - Related Products
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "related_products",
					"name" => esc_html__("Related Products", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show related products", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_related_products',
					"class" => "trx_sc_single trx_sc_related_products",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "posts_per_page",
							"heading" => esc_html__("Number", "grace-church"),
							"description" => esc_html__("How many products showed", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "4",
							"type" => "textfield"
						),
						array(
							"param_name" => "columns",
							"heading" => esc_html__("Columns", "grace-church"),
							"description" => esc_html__("How many columns per row use for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "orderby",
							"heading" => esc_html__("Order by", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array(
								__('Date', 'grace-church') => 'date',
								__('Title', 'grace-church') => 'title'
							),
							"type" => "dropdown"
						)
					)
				) );
				
				class WPBakeryShortCode_Related_Products extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
				// WooCommerce - Featured Products
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "featured_products",
					"name" => esc_html__("Featured Products", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show featured products", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_featured_products',
					"class" => "trx_sc_single trx_sc_featured_products",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "per_page",
							"heading" => esc_html__("Number", "grace-church"),
							"description" => esc_html__("How many products showed", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "4",
							"type" => "textfield"
						),
						array(
							"param_name" => "columns",
							"heading" => esc_html__("Columns", "grace-church"),
							"description" => esc_html__("How many columns per row use for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "orderby",
							"heading" => esc_html__("Order by", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array(
								__('Date', 'grace-church') => 'date',
								__('Title', 'grace-church') => 'title'
							),
							"type" => "dropdown"
						),
						array(
							"param_name" => "order",
							"heading" => esc_html__("Order", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
							"type" => "dropdown"
						)
					)
				) );
				
				class WPBakeryShortCode_Featured_Products extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
				// WooCommerce - Top Rated Products
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "top_rated_products",
					"name" => esc_html__("Top Rated Products", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show top rated products", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_top_rated_products',
					"class" => "trx_sc_single trx_sc_top_rated_products",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "per_page",
							"heading" => esc_html__("Number", "grace-church"),
							"description" => esc_html__("How many products showed", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "4",
							"type" => "textfield"
						),
						array(
							"param_name" => "columns",
							"heading" => esc_html__("Columns", "grace-church"),
							"description" => esc_html__("How many columns per row use for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "orderby",
							"heading" => esc_html__("Order by", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array(
								__('Date', 'grace-church') => 'date',
								__('Title', 'grace-church') => 'title'
							),
							"type" => "dropdown"
						),
						array(
							"param_name" => "order",
							"heading" => esc_html__("Order", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
							"type" => "dropdown"
						)
					)
				) );
				
				class WPBakeryShortCode_Top_Rated_Products extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
				// WooCommerce - Sale Products
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "sale_products",
					"name" => esc_html__("Sale Products", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: list products on sale", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_sale_products',
					"class" => "trx_sc_single trx_sc_sale_products",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "per_page",
							"heading" => esc_html__("Number", "grace-church"),
							"description" => esc_html__("How many products showed", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "4",
							"type" => "textfield"
						),
						array(
							"param_name" => "columns",
							"heading" => esc_html__("Columns", "grace-church"),
							"description" => esc_html__("How many columns per row use for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "orderby",
							"heading" => esc_html__("Order by", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array(
								__('Date', 'grace-church') => 'date',
								__('Title', 'grace-church') => 'title'
							),
							"type" => "dropdown"
						),
						array(
							"param_name" => "order",
							"heading" => esc_html__("Order", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
							"type" => "dropdown"
						)
					)
				) );
				
				class WPBakeryShortCode_Sale_Products extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
				// WooCommerce - Product Category
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "product_category",
					"name" => esc_html__("Products from category", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: list products in specified category(-ies)", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_product_category',
					"class" => "trx_sc_single trx_sc_product_category",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "per_page",
							"heading" => esc_html__("Number", "grace-church"),
							"description" => esc_html__("How many products showed", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "4",
							"type" => "textfield"
						),
						array(
							"param_name" => "columns",
							"heading" => esc_html__("Columns", "grace-church"),
							"description" => esc_html__("How many columns per row use for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "orderby",
							"heading" => esc_html__("Order by", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array(
								__('Date', 'grace-church') => 'date',
								__('Title', 'grace-church') => 'title'
							),
							"type" => "dropdown"
						),
						array(
							"param_name" => "order",
							"heading" => esc_html__("Order", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
							"type" => "dropdown"
						),
						array(
							"param_name" => "category",
							"heading" => esc_html__("Categories", "grace-church"),
							"description" => esc_html__("Comma separated category slugs", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "operator",
							"heading" => esc_html__("Operator", "grace-church"),
							"description" => esc_html__("Categories operator", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array(
								__('IN', 'grace-church') => 'IN',
								__('NOT IN', 'grace-church') => 'NOT IN',
								__('AND', 'grace-church') => 'AND'
							),
							"type" => "dropdown"
						)
					)
				) );
				
				class WPBakeryShortCode_Product_Category extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
				// WooCommerce - Products
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "products",
					"name" => esc_html__("Products", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: list all products", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_products',
					"class" => "trx_sc_single trx_sc_products",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "skus",
							"heading" => esc_html__("SKUs", "grace-church"),
							"description" => esc_html__("Comma separated SKU codes of products", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "ids",
							"heading" => esc_html__("IDs", "grace-church"),
							"description" => esc_html__("Comma separated ID of products", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "columns",
							"heading" => esc_html__("Columns", "grace-church"),
							"description" => esc_html__("How many columns per row use for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "orderby",
							"heading" => esc_html__("Order by", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array(
								__('Date', 'grace-church') => 'date',
								__('Title', 'grace-church') => 'title'
							),
							"type" => "dropdown"
						),
						array(
							"param_name" => "order",
							"heading" => esc_html__("Order", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
							"type" => "dropdown"
						)
					)
				) );
				
				class WPBakeryShortCode_Products extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
			
				// WooCommerce - Product Attribute
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "product_attribute",
					"name" => esc_html__("Products by Attribute", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show products with specified attribute", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_product_attribute',
					"class" => "trx_sc_single trx_sc_product_attribute",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "per_page",
							"heading" => esc_html__("Number", "grace-church"),
							"description" => esc_html__("How many products showed", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "4",
							"type" => "textfield"
						),
						array(
							"param_name" => "columns",
							"heading" => esc_html__("Columns", "grace-church"),
							"description" => esc_html__("How many columns per row use for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "orderby",
							"heading" => esc_html__("Order by", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array(
								__('Date', 'grace-church') => 'date',
								__('Title', 'grace-church') => 'title'
							),
							"type" => "dropdown"
						),
						array(
							"param_name" => "order",
							"heading" => esc_html__("Order", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
							"type" => "dropdown"
						),
						array(
							"param_name" => "attribute",
							"heading" => esc_html__("Attribute", "grace-church"),
							"description" => esc_html__("Attribute name", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "filter",
							"heading" => esc_html__("Filter", "grace-church"),
							"description" => esc_html__("Attribute value", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						)
					)
				) );
				
				class WPBakeryShortCode_Product_Attribute extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
			
			
				// WooCommerce - Products Categories
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "product_categories",
					"name" => esc_html__("Product Categories", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: show categories with products", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_product_categories',
					"class" => "trx_sc_single trx_sc_product_categories",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "number",
							"heading" => esc_html__("Number", "grace-church"),
							"description" => esc_html__("How many categories showed", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "4",
							"type" => "textfield"
						),
						array(
							"param_name" => "columns",
							"heading" => esc_html__("Columns", "grace-church"),
							"description" => esc_html__("How many columns per row use for categories output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "orderby",
							"heading" => esc_html__("Order by", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array(
								__('Date', 'grace-church') => 'date',
								__('Title', 'grace-church') => 'title'
							),
							"type" => "dropdown"
						),
						array(
							"param_name" => "order",
							"heading" => esc_html__("Order", "grace-church"),
							"description" => esc_html__("Sorting order for products output", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
							"type" => "dropdown"
						),
						array(
							"param_name" => "parent",
							"heading" => esc_html__("Parent", "grace-church"),
							"description" => esc_html__("Parent category slug", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "date",
							"type" => "textfield"
						),
						array(
							"param_name" => "ids",
							"heading" => esc_html__("IDs", "grace-church"),
							"description" => esc_html__("Comma separated ID of products", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "hide_empty",
							"heading" => esc_html__("Hide empty", "grace-church"),
							"description" => esc_html__("Hide empty categories", "grace-church"),
							"class" => "",
							"value" => array("Hide empty" => "1" ),
							"type" => "checkbox"
						)
					)
				) );
				
				class WPBakeryShortCode_Products_Categories extends GRACE_CHURCH_VC_ShortCodeSingle {}
			
				/*
			
				// WooCommerce - Add to cart
				//-------------------------------------------------------------------------------------
				
				vc_map( array(
					"base" => "add_to_cart",
					"name" => esc_html__("Add to cart", "grace-church"),
					"description" => esc_html__("WooCommerce shortcode: Display a single product price + cart button", "grace-church"),
					"category" => esc_html__('WooCommerce', 'js_composer'),
					'icon' => 'icon_trx_add_to_cart',
					"class" => "trx_sc_single trx_sc_add_to_cart",
					"content_element" => true,
					"is_container" => false,
					"show_settings_on_create" => true,
					"params" => array(
						array(
							"param_name" => "id",
							"heading" => esc_html__("ID", "grace-church"),
							"description" => esc_html__("Product's ID", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "sku",
							"heading" => esc_html__("SKU", "grace-church"),
							"description" => esc_html__("Product's SKU code", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "quantity",
							"heading" => esc_html__("Quantity", "grace-church"),
							"description" => esc_html__("How many item add", "grace-church"),
							"admin_label" => true,
							"class" => "",
							"value" => "1",
							"type" => "textfield"
						),
						array(
							"param_name" => "show_price",
							"heading" => esc_html__("Show price", "grace-church"),
							"description" => esc_html__("Show price near button", "grace-church"),
							"class" => "",
							"value" => array("Show price" => "true" ),
							"type" => "checkbox"
						),
						array(
							"param_name" => "class",
							"heading" => esc_html__("Class", "grace-church"),
							"description" => esc_html__("CSS class", "grace-church"),
							"class" => "",
							"value" => "",
							"type" => "textfield"
						),
						array(
							"param_name" => "style",
							"heading" => esc_html__("CSS style", "grace-church"),
							"description" => esc_html__("CSS style for additional decoration", "grace-church"),
							"class" => "",
							"value" => "",
							"type" => "textfield"
						)
					)
				) );
				
				class WPBakeryShortCode_Add_To_Cart extends GRACE_CHURCH_VC_ShortCodeSingle {}
				*/
			}

		}
	}
}
?>