<?php

// Check if shortcodes settings are now used
if ( !function_exists( 'grace_church_shortcodes_is_used' ) ) {
	function grace_church_shortcodes_is_used() {
		return grace_church_options_is_used() 															// All modes when Theme Options are used
			|| (is_admin() && isset($_POST['action']) 
					&& in_array($_POST['action'], array('vc_edit_form', 'wpb_show_edit_form')))		// AJAX query when save post/page
			|| grace_church_vc_is_frontend();															// VC Frontend editor mode
	}
}

// Width and height params
if ( !function_exists( 'grace_church_shortcodes_width' ) ) {
	function grace_church_shortcodes_width($w="") {
		return array(
			"title" => esc_html__("Width", "grace-church"),
			"divider" => true,
			"value" => $w,
			"type" => "text"
		);
	}
}
if ( !function_exists( 'grace_church_shortcodes_height' ) ) {
	function grace_church_shortcodes_height($h='') {
		return array(
			"title" => esc_html__("Height", "grace-church"),
			"desc" => esc_html__("Width (in pixels or percent) and height (only in pixels) of element", "grace-church"),
			"value" => $h,
			"type" => "text"
		);
	}
}

/* Theme setup section
-------------------------------------------------------------------- */

if ( !function_exists( 'grace_church_shortcodes_settings_theme_setup' ) ) {
//	if ( grace_church_vc_is_frontend() )
	if ( (isset($_GET['vc_editable']) && $_GET['vc_editable']=='true') || (isset($_GET['vc_action']) && $_GET['vc_action']=='vc_inline') )
		add_action( 'grace_church_action_before_init_theme', 'grace_church_shortcodes_settings_theme_setup', 20 );
	else
		add_action( 'grace_church_action_after_init_theme', 'grace_church_shortcodes_settings_theme_setup' );
	function grace_church_shortcodes_settings_theme_setup() {
		if (grace_church_shortcodes_is_used()) {
			global $GRACE_CHURCH_GLOBALS;

			// Prepare arrays 
			$GRACE_CHURCH_GLOBALS['sc_params'] = array(
			
				// Current element id
				'id' => array(
					"title" => esc_html__("Element ID", "grace-church"),
					"desc" => esc_html__("ID for current element", "grace-church"),
					"divider" => true,
					"value" => "",
					"type" => "text"
				),
			
				// Current element class
				'class' => array(
					"title" => esc_html__("Element CSS class", "grace-church"),
					"desc" => esc_html__("CSS class for current element (optional)", "grace-church"),
					"value" => "",
					"type" => "text"
				),
			
				// Current element style
				'css' => array(
					"title" => esc_html__("CSS styles", "grace-church"),
					"desc" => esc_html__("Any additional CSS rules (if need)", "grace-church"),
					"value" => "",
					"type" => "text"
				),
			
				// Margins params
				'top' => array(
					"title" => esc_html__("Top margin", "grace-church"),
					"divider" => true,
					"value" => "",
					"type" => "text"
				),
			
				'bottom' => array(
					"title" => esc_html__("Bottom margin", "grace-church"),
					"value" => "",
					"type" => "text"
				),
			
				'left' => array(
					"title" => esc_html__("Left margin", "grace-church"),
					"value" => "",
					"type" => "text"
				),
			
				'right' => array(
					"title" => esc_html__("Right margin", "grace-church"),
					"desc" => esc_html__("Margins around list (in pixels).", "grace-church"),
					"value" => "",
					"type" => "text"
				),
			
				// Switcher choises
				'list_styles' => array(
					'ul'	=> esc_html__('Unordered', 'grace-church'),
					'ol'	=> esc_html__('Ordered', 'grace-church'),
					'iconed'=> esc_html__('Iconed', 'grace-church')
				),
				'yes_no'	=> grace_church_get_list_yesno(),
				'on_off'	=> grace_church_get_list_onoff(),
				'dir' 		=> grace_church_get_list_directions(),
				'align'		=> grace_church_get_list_alignments(),
				'float'		=> grace_church_get_list_floats(),
				'show_hide'	=> grace_church_get_list_showhide(),
				'sorting' 	=> grace_church_get_list_sortings(),
				'ordering' 	=> grace_church_get_list_orderings(),
				'shapes'	=> grace_church_get_list_shapes(),
				'sizes'		=> grace_church_get_list_sizes(),
				'sliders'	=> grace_church_get_list_sliders(),
				'revo_sliders' => grace_church_get_list_revo_sliders(),
				'categories'=> grace_church_get_list_categories(),
				'columns'	=> grace_church_get_list_columns(),
				'images'	=> array_merge(array('none'=>"none"), grace_church_get_list_files("images/icons", "png")),
				'icons'		=> array_merge(array("inherit", "none"), grace_church_get_list_icons()),
				'locations'	=> grace_church_get_list_dedicated_locations(),
				'filters'	=> grace_church_get_list_portfolio_filters(),
				'formats'	=> grace_church_get_list_post_formats_filters(),
				'hovers'	=> grace_church_get_list_hovers(true),
				'hovers_dir'=> grace_church_get_list_hovers_directions(true),
				'schemes'	=> grace_church_get_list_color_schemes(true),
				'animations'=> grace_church_get_list_animations_in(),
				'blogger_styles'	=> grace_church_get_list_templates_blogger(),
				'posts_types'		=> grace_church_get_list_posts_types(),
				'googlemap_styles'	=> grace_church_get_list_googlemap_styles(),
				'field_types'		=> grace_church_get_list_field_types(),
				'label_positions'	=> grace_church_get_list_label_positions()
			);

			$GRACE_CHURCH_GLOBALS['sc_params']['animation'] = array(
				"title" => esc_html__("Animation",  'grace-church'),
				"desc" => esc_html__('Select animation while object enter in the visible area of page',  'grace-church'),
				"value" => "none",
				"type" => "select",
				"options" => $GRACE_CHURCH_GLOBALS['sc_params']['animations']
			);
	
			// Shortcodes list
			//------------------------------------------------------------------
			$GRACE_CHURCH_GLOBALS['shortcodes'] = array(
			
				// Accordion
				"trx_accordion" => array(
					"title" => esc_html__("Accordion", "grace-church"),
					"desc" => esc_html__("Accordion items", "grace-church"),
					"decorate" => true,
					"container" => false,
					"params" => array(
						"style" => array(
							"title" => esc_html__("Accordion style", "grace-church"),
							"desc" => esc_html__("Select style for display accordion", "grace-church"),
							"value" => 1,
							"options" => grace_church_get_list_styles(1, 2),
							"type" => "radio"
						),
						"counter" => array(
							"title" => esc_html__("Counter", "grace-church"),
							"desc" => esc_html__("Display counter before each accordion title", "grace-church"),
							"value" => "off",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['on_off']
						),
						"initial" => array(
							"title" => esc_html__("Initially opened item", "grace-church"),
							"desc" => esc_html__("Number of initially opened item", "grace-church"),
							"value" => 1,
							"min" => 0,
							"type" => "spinner"
						),
						"icon_closed" => array(
							"title" => esc_html__("Icon while closed",  'grace-church'),
							"desc" => esc_html__('Select icon for the closed accordion item from Fontello icons set',  'grace-church'),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"icon_opened" => array(
							"title" => esc_html__("Icon while opened",  'grace-church'),
							"desc" => esc_html__('Select icon for the opened accordion item from Fontello icons set',  'grace-church'),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					),
					"children" => array(
						"name" => "trx_accordion_item",
						"title" => esc_html__("Item", "grace-church"),
						"desc" => esc_html__("Accordion item", "grace-church"),
						"container" => true,
						"params" => array(
							"title" => array(
								"title" => esc_html__("Accordion item title", "grace-church"),
								"desc" => esc_html__("Title for current accordion item", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"icon_closed" => array(
								"title" => esc_html__("Icon while closed",  'grace-church'),
								"desc" => esc_html__('Select icon for the closed accordion item from Fontello icons set',  'grace-church'),
								"value" => "",
								"type" => "icons",
								"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
							),
							"icon_opened" => array(
								"title" => esc_html__("Icon while opened",  'grace-church'),
								"desc" => esc_html__('Select icon for the opened accordion item from Fontello icons set',  'grace-church'),
								"value" => "",
								"type" => "icons",
								"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
							),
							"_content_" => array(
								"title" => esc_html__("Accordion item content", "grace-church"),
								"desc" => esc_html__("Current accordion item content", "grace-church"),
								"rows" => 4,
								"value" => "",
								"type" => "textarea"
							),
							"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
							"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
							"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
						)
					)
				),
			
			
			
			
				// Anchor
				"trx_anchor" => array(
					"title" => esc_html__("Anchor", "grace-church"),
					"desc" => esc_html__("Insert anchor for the TOC (table of content)", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"icon" => array(
							"title" => esc_html__("Anchor's icon",  'grace-church'),
							"desc" => esc_html__('Select icon for the anchor from Fontello icons set',  'grace-church'),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"title" => array(
							"title" => esc_html__("Short title", "grace-church"),
							"desc" => esc_html__("Short title of the anchor (for the table of content)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"description" => array(
							"title" => esc_html__("Long description", "grace-church"),
							"desc" => esc_html__("Description for the popup (then hover on the icon). You can use:<br>'{{' and '}}' - to make the text italic,<br>'((' and '))' - to make the text bold,<br>'||' - to insert line break", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"url" => array(
							"title" => esc_html__("External URL", "grace-church"),
							"desc" => esc_html__("External URL for this TOC item", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"separator" => array(
							"title" => esc_html__("Add separator", "grace-church"),
							"desc" => esc_html__("Add separator under item in the TOC", "grace-church"),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id']
					)
				),
			
			
				// Audio
				"trx_audio" => array(
					"title" => esc_html__("Audio", "grace-church"),
					"desc" => esc_html__("Insert audio player", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"url" => array(
							"title" => esc_html__("URL for audio file", "grace-church"),
							"desc" => esc_html__("URL for audio file", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media",
							"before" => array(
								'title' => esc_html__('Choose audio', 'grace-church'),
								'action' => 'media_upload',
								'type' => 'audio',
								'multiple' => false,
								'linked_field' => '',
								'captions' => array( 	
									'choose' => esc_html__('Choose audio file', 'grace-church'),
									'update' => esc_html__('Select audio file', 'grace-church')
								)
							),
							"after" => array(
								'icon' => 'icon-cancel',
								'action' => 'media_reset'
							)
						),
						"image" => array(
							"title" => esc_html__("Cover image", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site for audio cover", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"title" => array(
							"title" => esc_html__("Title", "grace-church"),
							"desc" => esc_html__("Title of the audio file", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "text"
						),
						"author" => array(
							"title" => esc_html__("Author", "grace-church"),
							"desc" => esc_html__("Author of the audio file", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"type" => array(
							"title" => esc_html__("Type", "grace-church"),
							"desc" => esc_html__("Select type of display", "grace-church"),
							"value" => array(),
                            "options" => array(
                                'default' => esc_html__('Normal', 'grace-church'),
                                'minimal' => esc_html__('Minimal', 'grace-church')
                            ),
							"type" => "checklist"
						),
						"controls" => array(
							"title" => esc_html__("Show controls", "grace-church"),
							"desc" => esc_html__("Show controls in audio player", "grace-church"),
							"divider" => true,
							"size" => "medium",
							"value" => "show",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['show_hide']
						),
						"autoplay" => array(
							"title" => esc_html__("Autoplay audio", "grace-church"),
							"desc" => esc_html__("Autoplay audio on page load", "grace-church"),
							"value" => "off",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['on_off']
						),
						"align" => array(
							"title" => esc_html__("Align", "grace-church"),
							"desc" => esc_html__("Select block alignment", "grace-church"),
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						),
                        "inverse_color_player" => array(
                            "title" => esc_html__("Inverse color", "grace-church"),
                            "desc" => wp_kses( __("Change color to light (for dark background)", "grace-church"), $GRACE_CHURCH_GLOBALS['allowed_tags'] ),
                            "value" => "no",
                            "type" => "switch",
                            "options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
                        ),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Block
				"trx_block" => array(
					"title" => esc_html__("Block container", "grace-church"),
					"desc" => esc_html__("Container for any block ([section] analog - to enable nesting)", "grace-church"),
					"decorate" => true,
					"container" => true,
					"params" => array(
						"dedicated" => array(
							"title" => esc_html__("Dedicated", "grace-church"),
							"desc" => esc_html__("Use this block as dedicated content - show it before post title on single page", "grace-church"),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"align" => array(
							"title" => esc_html__("Align", "grace-church"),
							"desc" => esc_html__("Select block alignment", "grace-church"),
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						),
						"columns" => array(
							"title" => esc_html__("Columns emulation", "grace-church"),
							"desc" => esc_html__("Select width for columns emulation", "grace-church"),
							"value" => "none",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['columns']
						), 
						"pan" => array(
							"title" => esc_html__("Use pan effect", "grace-church"),
							"desc" => esc_html__("Use pan effect to show section content", "grace-church"),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"scroll" => array(
							"title" => esc_html__("Use scroller", "grace-church"),
							"desc" => esc_html__("Use scroller to show section content", "grace-church"),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"scroll_dir" => array(
							"title" => esc_html__("Scroll direction", "grace-church"),
							"desc" => esc_html__("Scroll direction (if Use scroller = yes)", "grace-church"),
							"dependency" => array(
								'scroll' => array('yes')
							),
							"value" => "horizontal",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['dir']
						),
						"scroll_controls" => array(
							"title" => esc_html__("Scroll controls", "grace-church"),
							"desc" => esc_html__("Show scroll controls (if Use scroller = yes)", "grace-church"),
							"dependency" => array(
								'scroll' => array('yes')
							),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"scheme" => array(
							"title" => esc_html__("Color scheme", "grace-church"),
							"desc" => esc_html__("Select color scheme for this block", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['schemes']
						),
						"color" => array(
							"title" => esc_html__("Fore color", "grace-church"),
							"desc" => esc_html__("Any color for objects in this section", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "color"
						),
						"bg_color" => array(
							"title" => esc_html__("Background color", "grace-church"),
							"desc" => esc_html__("Any background color for this section", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"bg_image" => array(
							"title" => esc_html__("Background image URL", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site for the background", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"bg_overlay" => array(
							"title" => esc_html__("Overlay", "grace-church"),
							"desc" => esc_html__("Overlay color opacity (from 0.0 to 1.0)", "grace-church"),
							"min" => "0",
							"max" => "1",
							"step" => "0.1",
							"value" => "0",
							"type" => "spinner"
						),
						"bg_texture" => array(
							"title" => esc_html__("Texture", "grace-church"),
							"desc" => esc_html__("Predefined texture style from 1 to 11. 0 - without texture.", "grace-church"),
							"min" => "0",
							"max" => "11",
							"step" => "1",
							"value" => "0",
							"type" => "spinner"
						),
						"font_size" => array(
							"title" => esc_html__("Font size", "grace-church"),
							"desc" => esc_html__("Font size of the text (default - in pixels, allows any CSS units of measure)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"font_weight" => array(
							"title" => esc_html__("Font weight", "grace-church"),
							"desc" => esc_html__("Font weight of the text", "grace-church"),
							"value" => "",
							"type" => "select",
							"size" => "medium",
							"options" => array(
								'100' => esc_html__('Thin (100)', 'grace-church'),
								'300' => esc_html__('Light (300)', 'grace-church'),
								'400' => esc_html__('Normal (400)', 'grace-church'),
								'700' => esc_html__('Bold (700)', 'grace-church')
							)
						),
						"_content_" => array(
							"title" => esc_html__("Container content", "grace-church"),
							"desc" => esc_html__("Content for section container", "grace-church"),
							"divider" => true,
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Blogger
				"trx_blogger" => array(
					"title" => esc_html__("Blogger", "grace-church"),
					"desc" => esc_html__("Insert posts (pages) in many styles from desired categories or directly from ids", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"title" => array(
							"title" => esc_html__("Title", "grace-church"),
							"desc" => esc_html__("Title for the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"subtitle" => array(
							"title" => esc_html__("Subtitle", "grace-church"),
							"desc" => esc_html__("Subtitle for the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"description" => array(
							"title" => esc_html__("Description", "grace-church"),
							"desc" => esc_html__("Short description for the block", "grace-church"),
							"value" => "",
							"type" => "textarea"
						),
						"style" => array(
							"title" => esc_html__("Posts output style", "grace-church"),
							"desc" => esc_html__("Select desired style for posts output", "grace-church"),
							"value" => "regular",
							"type" => "select",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['blogger_styles']
						),
                        "show_button" => array(
                            "title" => esc_html__("Show Button", "grace-church"),
//                            "desc" => esc_html__("", "grace-church"),
                            "dependency" => array(
                                'style' => 'list'
                            ),
                            "value" => "no",
                            "type" => "switch",
                            "size" => "medium",
                            "options" => array(
                                'yes'   => esc_html__('Show', 'grace-church') ,
                                'no'    => esc_html__('Hide', 'grace-church')
                            ),
                        ),
						"filters" => array(
							"title" => esc_html__("Show filters", "grace-church"),
							"desc" => esc_html__("Use post's tags or categories as filter buttons", "grace-church"),
							"value" => "no",
							"dir" => "horizontal",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['filters']
						),
						"hover" => array(
							"title" => esc_html__("Hover effect", "grace-church"),
							"desc" => esc_html__("Select hover effect (only if style=Portfolio)", "grace-church"),
							"dependency" => array(
								'style' => array('portfolio','grid','square','short','colored')
							),
							"value" => "",
							"type" => "select",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['hovers']
						),
						"hover_dir" => array(
							"title" => esc_html__("Hover direction", "grace-church"),
							"desc" => esc_html__("Select hover direction (only if style=Portfolio and hover=Circle|Square)", "grace-church"),
							"dependency" => array(
								'style' => array('portfolio','grid','square','short','colored'),
								'hover' => array('square','circle')
							),
							"value" => "left_to_right",
							"type" => "select",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['hovers_dir']
						),
						"dir" => array(
							"title" => esc_html__("Posts direction", "grace-church"),
							"desc" => esc_html__("Display posts in horizontal or vertical direction", "grace-church"),
							"value" => "horizontal",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['dir']
						),
						"post_type" => array(
							"title" => esc_html__("Post type", "grace-church"),
							"desc" => esc_html__("Select post type to show", "grace-church"),
							"value" => "post",
							"type" => "select",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['posts_types']
						),
						"ids" => array(
							"title" => esc_html__("Post IDs list", "grace-church"),
							"desc" => esc_html__("Comma separated list of posts ID. If set - parameters above are ignored!", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"cat" => array(
							"title" => esc_html__("Categories list", "grace-church"),
							"desc" => esc_html__("Select the desired categories. If not selected - show posts from any category or from IDs list", "grace-church"),
							"dependency" => array(
								'ids' => array('is_empty'),
								'post_type' => array('refresh')
							),
							"divider" => true,
							"value" => "",
							"type" => "select",
							"style" => "list",
							"multiple" => true,
							"options" => grace_church_array_merge(array(0 => esc_html__('- Select category -', 'grace-church')), $GRACE_CHURCH_GLOBALS['sc_params']['categories'])
						),
						"count" => array(
							"title" => esc_html__("Total posts to show", "grace-church"),
							"desc" => esc_html__("How many posts will be displayed? If used IDs - this parameter ignored.", "grace-church"),
							"dependency" => array(
								'ids' => array('is_empty')
							),
							"value" => 3,
							"min" => 1,
							"max" => 100,
							"type" => "spinner"
						),
						"columns" => array(
							"title" => esc_html__("Columns number", "grace-church"),
							"desc" => esc_html__("How many columns used to show posts? If empty or 0 - equal to posts number", "grace-church"),
							"dependency" => array(
								'dir' => array('horizontal')
							),
							"value" => 3,
							"min" => 1,
							"max" => 100,
							"type" => "spinner"
						),
						"offset" => array(
							"title" => esc_html__("Offset before select posts", "grace-church"),
							"desc" => esc_html__("Skip posts before select next part.", "grace-church"),
							"dependency" => array(
								'ids' => array('is_empty')
							),
							"value" => 0,
							"min" => 0,
							"max" => 100,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Post order by", "grace-church"),
							"desc" => esc_html__("Select desired posts sorting method", "grace-church"),
							"value" => "date",
							"type" => "select",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['sorting']
						),
						"order" => array(
							"title" => esc_html__("Post order", "grace-church"),
							"desc" => esc_html__("Select desired posts order", "grace-church"),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						),
						"only" => array(
							"title" => esc_html__("Select posts only", "grace-church"),
							"desc" => esc_html__("Select posts only with reviews, videos, audios, thumbs or galleries", "grace-church"),
							"value" => "no",
							"type" => "select",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['formats']
						),
						"scroll" => array(
							"title" => esc_html__("Use scroller", "grace-church"),
							"desc" => esc_html__("Use scroller to show all posts", "grace-church"),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"controls" => array(
							"title" => esc_html__("Show slider controls", "grace-church"),
							"desc" => esc_html__("Show arrows to control scroll slider", "grace-church"),
							"dependency" => array(
								'scroll' => array('yes')
							),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"location" => array(
							"title" => esc_html__("Dedicated content location", "grace-church"),
							"desc" => esc_html__("Select position for dedicated content (only for style=excerpt)", "grace-church"),
							"divider" => true,
							"dependency" => array(
								'style' => array('excerpt')
							),
							"value" => "default",
							"type" => "select",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['locations']
						),
						"rating" => array(
							"title" => esc_html__("Show rating stars", "grace-church"),
							"desc" => esc_html__("Show rating stars under post's header", "grace-church"),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"info" => array(
							"title" => esc_html__("Show post info block", "grace-church"),
							"desc" => esc_html__("Show post info block (author, date, tags, etc.)", "grace-church"),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"links" => array(
							"title" => esc_html__("Allow links on the post", "grace-church"),
							"desc" => esc_html__("Allow links on the post from each blogger item", "grace-church"),
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"descr" => array(
							"title" => esc_html__("Description length", "grace-church"),
							"desc" => esc_html__("How many characters are displayed from post excerpt? If 0 - don't show description", "grace-church"),
							"value" => 0,
							"min" => 0,
							"step" => 10,
							"type" => "spinner"
						),
						"readmore" => array(
							"title" => esc_html__("More link text", "grace-church"),
							"desc" => esc_html__("Read more link text. If empty - show 'More', else - used as link text", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"link" => array(
							"title" => esc_html__("Button URL", "grace-church"),
							"desc" => esc_html__("Link URL for the button at the bottom of the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"link_caption" => array(
							"title" => esc_html__("Button caption", "grace-church"),
							"desc" => esc_html__("Caption for the button at the bottom of the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
			
				// Br
				"trx_br" => array(
					"title" => esc_html__("Break", "grace-church"),
					"desc" => esc_html__("Line break with clear floating (if need)", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"clear" => 	array(
							"title" => esc_html__("Clear floating", "grace-church"),
							"desc" => esc_html__("Clear floating (if need)", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"options" => array(
								'none' => esc_html__('None', 'grace-church'),
								'left' => esc_html__('Left', 'grace-church'),
								'right' => esc_html__('Right', 'grace-church'),
								'both' => esc_html__('Both', 'grace-church')
							)
						)
					)
				),
			
			
			
			
				// Button
				"trx_button" => array(
					"title" => esc_html__("Button", "grace-church"),
					"desc" => esc_html__("Button with link", "grace-church"),
					"decorate" => false,
					"container" => true,
					"params" => array(
						"_content_" => array(
							"title" => esc_html__("Caption", "grace-church"),
							"desc" => esc_html__("Button caption", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"type" => array(
							"title" => esc_html__("Button's shape", "grace-church"),
							"desc" => esc_html__("Select button's shape", "grace-church"),
							"value" => "square",
							"size" => "medium",
							"options" => array(
								'square' => esc_html__('Square', 'grace-church'),
								'round' => esc_html__('Round', 'grace-church')
							),
							"type" => "switch"
						), 
						"style" => array(
							"title" => esc_html__("Button's style", "grace-church"),
							"desc" => esc_html__("Select button's style", "grace-church"),
							"value" => "default",
							"dir" => "horizontal",
							"options" => array(
								'filled' => esc_html__('Filled', 'grace-church'),
								'border' => esc_html__('Border', 'grace-church')
							),
							"type" => "checklist"
						), 
						"size" => array(
							"title" => esc_html__("Button's size", "grace-church"),
							"desc" => esc_html__("Select button's size", "grace-church"),
							"value" => "small",
							"dir" => "horizontal",
							"options" => array(
								'small' => esc_html__('Small', 'grace-church'),
								'medium' => esc_html__('Medium', 'grace-church'),
								'large' => esc_html__('Large', 'grace-church')
							),
							"type" => "checklist"
						), 
						"icon" => array(
							"title" => esc_html__("Button's icon",  'grace-church'),
							"desc" => esc_html__('Select icon for the title from Fontello icons set',  'grace-church'),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"color" => array(
							"title" => esc_html__("Button's text color", "grace-church"),
							"desc" => esc_html__("Any color for button's caption", "grace-church"),
							"std" => "",
							"value" => "",
							"type" => "color"
						),
						"bg_color" => array(
							"title" => esc_html__("Button's backcolor", "grace-church"),
							"desc" => esc_html__("Any color for button's background", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"align" => array(
							"title" => esc_html__("Button's alignment", "grace-church"),
							"desc" => esc_html__("Align button to left, center or right", "grace-church"),
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						), 
						"link" => array(
							"title" => esc_html__("Link URL", "grace-church"),
							"desc" => esc_html__("URL for link on button click", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "text"
						),
						"target" => array(
							"title" => esc_html__("Link target", "grace-church"),
							"desc" => esc_html__("Target for link on button click", "grace-church"),
							"dependency" => array(
								'link' => array('not_empty')
							),
							"value" => "",
							"type" => "text"
						),
						"popup" => array(
							"title" => esc_html__("Open link in popup", "grace-church"),
							"desc" => esc_html__("Open link target in popup window", "grace-church"),
							"dependency" => array(
								'link' => array('not_empty')
							),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						), 
						"rel" => array(
							"title" => esc_html__("Rel attribute", "grace-church"),
							"desc" => esc_html__("Rel attribute for button's link (if need)", "grace-church"),
							"dependency" => array(
								'link' => array('not_empty')
							),
							"value" => "",
							"type" => "text"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),




				// Call to Action block
				"trx_call_to_action" => array(
					"title" => esc_html__("Call to action", "grace-church"),
					"desc" => esc_html__("Insert call to action block in your page (post)", "grace-church"),
					"decorate" => true,
					"container" => true,
					"params" => array(
						"title" => array(
							"title" => esc_html__("Title", "grace-church"),
							"desc" => esc_html__("Title for the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"subtitle" => array(
							"title" => esc_html__("Subtitle", "grace-church"),
							"desc" => esc_html__("Subtitle for the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"description" => array(
							"title" => esc_html__("Description", "grace-church"),
							"desc" => esc_html__("Short description for the block", "grace-church"),
							"value" => "",
							"type" => "textarea"
						),
						"style" => array(
							"title" => esc_html__("Style", "grace-church"),
							"desc" => esc_html__("Select style to display block", "grace-church"),
							"value" => "1",
							"type" => "checklist",
							"options" => grace_church_get_list_styles(1, 2)
						),
						"align" => array(
							"title" => esc_html__("Alignment", "grace-church"),
							"desc" => esc_html__("Alignment elements in the block", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						),
						"accent" => array(
							"title" => esc_html__("Accented", "grace-church"),
							"desc" => esc_html__("Fill entire block with Accent1 color from current color scheme", "grace-church"),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"custom" => array(
							"title" => esc_html__("Custom", "grace-church"),
							"desc" => esc_html__("Allow get featured image or video from inner shortcodes (custom) or get it from shortcode parameters below", "grace-church"),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"image" => array(
							"title" => esc_html__("Image", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site to include image into this block", "grace-church"),
							"divider" => true,
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"video" => array(
							"title" => esc_html__("URL for video file", "grace-church"),
							"desc" => esc_html__("Select video from media library or paste URL for video file from other site to include video into this block", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media",
							"before" => array(
								'title' => esc_html__('Choose video', 'grace-church'),
								'action' => 'media_upload',
								'type' => 'video',
								'multiple' => false,
								'linked_field' => '',
								'captions' => array( 	
									'choose' => esc_html__('Choose video file', 'grace-church'),
									'update' => esc_html__('Select video file', 'grace-church')
								)
							),
							"after" => array(
								'icon' => 'icon-cancel',
								'action' => 'media_reset'
							)
						),
						"link" => array(
							"title" => esc_html__("Button URL", "grace-church"),
							"desc" => esc_html__("Link URL for the button at the bottom of the block", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "text"
						),
						"link_caption" => array(
							"title" => esc_html__("Button caption", "grace-church"),
							"desc" => esc_html__("Caption for the button at the bottom of the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"link2" => array(
							"title" => esc_html__("Button 2 URL", "grace-church"),
							"desc" => esc_html__("Link URL for the second button at the bottom of the block", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "text"
						),
						"link2_caption" => array(
							"title" => esc_html__("Button 2 caption", "grace-church"),
							"desc" => esc_html__("Caption for the second button at the bottom of the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
				// Chat
				"trx_chat" => array(
					"title" => esc_html__("Chat", "grace-church"),
					"desc" => esc_html__("Chat message", "grace-church"),
					"decorate" => true,
					"container" => true,
					"params" => array(
						"title" => array(
							"title" => esc_html__("Item title", "grace-church"),
							"desc" => esc_html__("Chat item title", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"photo" => array(
							"title" => esc_html__("Item photo", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site for the item photo (avatar)", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"link" => array(
							"title" => esc_html__("Item link", "grace-church"),
							"desc" => esc_html__("Chat item link", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"_content_" => array(
							"title" => esc_html__("Chat item content", "grace-church"),
							"desc" => esc_html__("Current chat item content", "grace-church"),
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
				// Columns
				"trx_columns" => array(
					"title" => esc_html__("Columns", "grace-church"),
					"desc" => esc_html__("Insert up to 5 columns in your page (post)", "grace-church"),
					"decorate" => true,
					"container" => false,
					"params" => array(
						"fluid" => array(
							"title" => esc_html__("Fluid columns", "grace-church"),
							"desc" => esc_html__("To squeeze the columns when reducing the size of the window (fluid=yes) or to rebuild them (fluid=no)", "grace-church"),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						), 
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					),
					"children" => array(
						"name" => "trx_column_item",
						"title" => esc_html__("Column", "grace-church"),
						"desc" => esc_html__("Column item", "grace-church"),
						"container" => true,
						"params" => array(
							"span" => array(
								"title" => esc_html__("Merge columns", "grace-church"),
								"desc" => esc_html__("Count merged columns from current", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"align" => array(
								"title" => esc_html__("Alignment", "grace-church"),
								"desc" => esc_html__("Alignment text in the column", "grace-church"),
								"value" => "",
								"type" => "checklist",
								"dir" => "horizontal",
								"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
							),
							"color" => array(
								"title" => esc_html__("Fore color", "grace-church"),
								"desc" => esc_html__("Any color for objects in this column", "grace-church"),
								"value" => "",
								"type" => "color"
							),
							"bg_color" => array(
								"title" => esc_html__("Background color", "grace-church"),
								"desc" => esc_html__("Any background color for this column", "grace-church"),
								"value" => "",
								"type" => "color"
							),
							"bg_image" => array(
								"title" => esc_html__("URL for background image file", "grace-church"),
								"desc" => esc_html__("Select or upload image or write URL from other site for the background", "grace-church"),
								"readonly" => false,
								"value" => "",
								"type" => "media"
							),
							"_content_" => array(
								"title" => esc_html__("Column item content", "grace-church"),
								"desc" => esc_html__("Current column item content", "grace-church"),
								"divider" => true,
								"rows" => 4,
								"value" => "",
								"type" => "textarea"
							),
							"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
							"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
							"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
							"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
						)
					)
				),
			
			
			
			
				// Contact form
				"trx_contact_form" => array(
					"title" => esc_html__("Contact form", "grace-church"),
					"desc" => esc_html__("Insert contact form", "grace-church"),
					"decorate" => true,
					"container" => false,
					"params" => array(
						"title" => array(
							"title" => esc_html__("Title", "grace-church"),
							"desc" => esc_html__("Title for the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"subtitle" => array(
							"title" => esc_html__("Subtitle", "grace-church"),
							"desc" => esc_html__("Subtitle for the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"description" => array(
							"title" => esc_html__("Description", "grace-church"),
							"desc" => esc_html__("Short description for the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"style" => array(
							"title" => esc_html__("Style", "grace-church"),
							"desc" => esc_html__("Select style of the contact form", "grace-church"),
							"value" => 1,
							"options" => grace_church_get_list_styles(1, 2),
							"type" => "checklist"
						), 
						"scheme" => array(
							"title" => esc_html__("Color scheme", "grace-church"),
							"desc" => esc_html__("Select color scheme for this block", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['schemes']
						),
						"custom" => array(
							"title" => esc_html__("Custom", "grace-church"),
							"desc" => esc_html__("Use custom fields or create standard contact form (ignore info from 'Field' tabs)", "grace-church"),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						), 
						"action" => array(
							"title" => esc_html__("Action", "grace-church"),
							"desc" => esc_html__("Contact form action (URL to handle form data). If empty - use internal action", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "text"
						),
						"align" => array(
							"title" => esc_html__("Align", "grace-church"),
							"desc" => esc_html__("Select form alignment", "grace-church"),
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						),
						"width" => grace_church_shortcodes_width(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					),
					"children" => array(
						"name" => "trx_form_item",
						"title" => esc_html__("Field", "grace-church"),
						"desc" => esc_html__("Custom field", "grace-church"),
						"container" => false,
						"params" => array(
							"type" => array(
								"title" => esc_html__("Type", "grace-church"),
								"desc" => esc_html__("Type of the custom field", "grace-church"),
								"value" => "text",
								"type" => "checklist",
								"dir" => "horizontal",
								"options" => $GRACE_CHURCH_GLOBALS['sc_params']['field_types']
							), 
							"name" => array(
								"title" => esc_html__("Name", "grace-church"),
								"desc" => esc_html__("Name of the custom field", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"value" => array(
								"title" => esc_html__("Default value", "grace-church"),
								"desc" => esc_html__("Default value of the custom field", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"options" => array(
								"title" => esc_html__("Options", "grace-church"),
								"desc" => esc_html__("Field options. For example: big=My daddy|middle=My brother|small=My little sister", "grace-church"),
								"dependency" => array(
									'type' => array('radio', 'checkbox', 'select')
								),
								"value" => "",
								"type" => "text"
							),
							"label" => array(
								"title" => esc_html__("Label", "grace-church"),
								"desc" => esc_html__("Label for the custom field", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"label_position" => array(
								"title" => esc_html__("Label position", "grace-church"),
								"desc" => esc_html__("Label position relative to the field", "grace-church"),
								"value" => "top",
								"type" => "checklist",
								"dir" => "horizontal",
								"options" => $GRACE_CHURCH_GLOBALS['sc_params']['label_positions']
							), 
							"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
							"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
							"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
							"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
							"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
							"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
							"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
							"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
						)
					)
				),
			
			
			
			
				// Content block on fullscreen page
				"trx_content" => array(
					"title" => esc_html__("Content block", "grace-church"),
					"desc" => esc_html__("Container for main content block with desired class and style (use it only on fullscreen pages)", "grace-church"),
					"decorate" => true,
					"container" => true,
					"params" => array(
						"scheme" => array(
							"title" => esc_html__("Color scheme", "grace-church"),
							"desc" => esc_html__("Select color scheme for this block", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['schemes']
						),
						"_content_" => array(
							"title" => esc_html__("Container content", "grace-church"),
							"desc" => esc_html__("Content for section container", "grace-church"),
							"divider" => true,
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
			
				// Countdown
				"trx_countdown" => array(
					"title" => esc_html__("Countdown", "grace-church"),
					"desc" => esc_html__("Insert countdown object", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"date" => array(
							"title" => esc_html__("Date", "grace-church"),
							"desc" => esc_html__("Upcoming date (format: yyyy-mm-dd)", "grace-church"),
							"value" => "",
							"format" => "yy-mm-dd",
							"type" => "date"
						),
						"time" => array(
							"title" => esc_html__("Time", "grace-church"),
							"desc" => esc_html__("Upcoming time (format: HH:mm:ss)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"style" => array(
							"title" => esc_html__("Style", "grace-church"),
							"desc" => esc_html__("Countdown style", "grace-church"),
							"value" => "1",
							"type" => "checklist",
							"options" => grace_church_get_list_styles(1, 2)
						),
						"align" => array(
							"title" => esc_html__("Alignment", "grace-church"),
							"desc" => esc_html__("Align counter to left, center or right", "grace-church"),
							"divider" => true,
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						), 
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Dropcaps
				"trx_dropcaps" => array(
					"title" => esc_html__("Dropcaps", "grace-church"),
					"desc" => esc_html__("Make first letter as dropcaps", "grace-church"),
					"decorate" => false,
					"container" => true,
					"params" => array(
						"style" => array(
							"title" => esc_html__("Style", "grace-church"),
							"desc" => esc_html__("Dropcaps style", "grace-church"),
							"value" => "1",
							"type" => "checklist",
							"options" => grace_church_get_list_styles(1, 4)
						),
						"_content_" => array(
							"title" => esc_html__("Paragraph content", "grace-church"),
							"desc" => esc_html__("Paragraph with dropcaps content", "grace-church"),
							"divider" => true,
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
			
				// Emailer
				"trx_emailer" => array(
					"title" => esc_html__("E-mail collector", "grace-church"),
					"desc" => esc_html__("Collect the e-mail address into specified group", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"group" => array(
							"title" => esc_html__("Group", "grace-church"),
							"desc" => esc_html__("The name of group to collect e-mail address", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"open" => array(
							"title" => esc_html__("Open", "grace-church"),
							"desc" => esc_html__("Initially open the input field on show object", "grace-church"),
							"divider" => true,
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"align" => array(
							"title" => esc_html__("Alignment", "grace-church"),
							"desc" => esc_html__("Align object to left, center or right", "grace-church"),
							"divider" => true,
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						), 
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
			
				// Gap
				"trx_gap" => array(
					"title" => esc_html__("Gap", "grace-church"),
					"desc" => esc_html__("Insert gap (fullwidth area) in the post content. Attention! Use the gap only in the posts (pages) without left or right sidebar", "grace-church"),
					"decorate" => true,
					"container" => true,
					"params" => array(
						"_content_" => array(
							"title" => esc_html__("Gap content", "grace-church"),
							"desc" => esc_html__("Gap inner content", "grace-church"),
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						)
					)
				),
			
			
			
			
			
				// Google map
				"trx_googlemap" => array(
					"title" => esc_html__("Google map", "grace-church"),
					"desc" => esc_html__("Insert Google map with specified markers", "grace-church"),
					"decorate" => false,
					"container" => true,
					"params" => array(
						"zoom" => array(
							"title" => esc_html__("Zoom", "grace-church"),
							"desc" => esc_html__("Map zoom factor", "grace-church"),
							"divider" => true,
							"value" => 16,
							"min" => 1,
							"max" => 20,
							"type" => "spinner"
						),
						"style" => array(
							"title" => esc_html__("Map style", "grace-church"),
							"desc" => esc_html__("Select map style", "grace-church"),
							"value" => "default",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['googlemap_styles']
						),
						"width" => grace_church_shortcodes_width('100%'),
						"height" => grace_church_shortcodes_height(240),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					),
					"children" => array(
						"name" => "trx_googlemap_marker",
						"title" => esc_html__("Google map marker", "grace-church"),
						"desc" => esc_html__("Google map marker", "grace-church"),
						"decorate" => false,
						"container" => true,
						"params" => array(
							"address" => array(
								"title" => esc_html__("Address", "grace-church"),
								"desc" => esc_html__("Address of this marker", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"latlng" => array(
								"title" => esc_html__("Latitude and Longtitude", "grace-church"),
								"desc" => esc_html__("Comma separated marker's coorditanes (instead Address)", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"point" => array(
								"title" => esc_html__("URL for marker image file", "grace-church"),
								"desc" => esc_html__("Select or upload image or write URL from other site for this marker. If empty - use default marker", "grace-church"),
								"readonly" => false,
								"value" => "",
								"type" => "media"
							),
							"title" => array(
								"title" => esc_html__("Title", "grace-church"),
								"desc" => esc_html__("Title for this marker", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"_content_" => array(
								"title" => esc_html__("Description", "grace-church"),
								"desc" => esc_html__("Description for this marker", "grace-church"),
								"rows" => 4,
								"value" => "",
								"type" => "textarea"
							),
							"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id']
						)
					)
				),
			
			
			
				// Hide or show any block
				"trx_hide" => array(
					"title" => esc_html__("Hide/Show any block", "grace-church"),
					"desc" => esc_html__("Hide or Show any block with desired CSS-selector", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"selector" => array(
							"title" => esc_html__("Selector", "grace-church"),
							"desc" => esc_html__("Any block's CSS-selector", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"hide" => array(
							"title" => esc_html__("Hide or Show", "grace-church"),
							"desc" => esc_html__("New state for the block: hide or show", "grace-church"),
							"value" => "yes",
							"size" => "small",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no'],
							"type" => "switch"
						)
					)
				),
			
			
			
				// Highlght text
				"trx_highlight" => array(
					"title" => esc_html__("Highlight text", "grace-church"),
					"desc" => esc_html__("Highlight text with selected color, background color and other styles", "grace-church"),
					"decorate" => false,
					"container" => true,
					"params" => array(
						"type" => array(
							"title" => esc_html__("Type", "grace-church"),
							"desc" => esc_html__("Highlight type", "grace-church"),
							"value" => "1",
							"type" => "checklist",
							"options" => array(
								0 => esc_html__('Custom', 'grace-church'),
								1 => esc_html__('Type 1', 'grace-church'),
								2 => esc_html__('Type 2', 'grace-church'),
								3 => esc_html__('Type 3', 'grace-church')
							)
						),
						"color" => array(
							"title" => esc_html__("Color", "grace-church"),
							"desc" => esc_html__("Color for the highlighted text", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "color"
						),
						"bg_color" => array(
							"title" => esc_html__("Background color", "grace-church"),
							"desc" => esc_html__("Background color for the highlighted text", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"font_size" => array(
							"title" => esc_html__("Font size", "grace-church"),
							"desc" => esc_html__("Font size of the highlighted text (default - in pixels, allows any CSS units of measure)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"_content_" => array(
							"title" => esc_html__("Highlighting content", "grace-church"),
							"desc" => esc_html__("Content for highlight", "grace-church"),
							"divider" => true,
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Icon
				"trx_icon" => array(
					"title" => esc_html__("Icon", "grace-church"),
					"desc" => esc_html__("Insert icon", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"icon" => array(
							"title" => esc_html__('Icon',  'grace-church'),
							"desc" => esc_html__('Select font icon from the Fontello icons set',  'grace-church'),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"color" => array(
							"title" => esc_html__("Icon's color", "grace-church"),
							"desc" => esc_html__("Icon's color", "grace-church"),
							"dependency" => array(
								'icon' => array('not_empty')
							),
							"value" => "",
							"type" => "color"
						),
						"bg_shape" => array(
							"title" => esc_html__("Background shape", "grace-church"),
							"desc" => esc_html__("Shape of the icon background", "grace-church"),
							"dependency" => array(
								'icon' => array('not_empty')
							),
							"value" => "none",
							"type" => "radio",
							"options" => array(
								'none' => esc_html__('None', 'grace-church'),
								'round' => esc_html__('Round', 'grace-church'),
								'square' => esc_html__('Square', 'grace-church')
							)
						),
						"bg_color" => array(
							"title" => esc_html__("Icon's background color", "grace-church"),
							"desc" => esc_html__("Icon's background color", "grace-church"),
							"dependency" => array(
								'icon' => array('not_empty'),
								'background' => array('round','square')
							),
							"value" => "",
							"type" => "color"
						),
						"font_size" => array(
							"title" => esc_html__("Font size", "grace-church"),
							"desc" => esc_html__("Icon's font size", "grace-church"),
							"dependency" => array(
								'icon' => array('not_empty')
							),
							"value" => "",
							"type" => "spinner",
							"min" => 8,
							"max" => 240
						),
						"font_weight" => array(
							"title" => esc_html__("Font weight", "grace-church"),
							"desc" => esc_html__("Icon font weight", "grace-church"),
							"dependency" => array(
								'icon' => array('not_empty')
							),
							"value" => "",
							"type" => "select",
							"size" => "medium",
							"options" => array(
								'100' => esc_html__('Thin (100)', 'grace-church'),
								'300' => esc_html__('Light (300)', 'grace-church'),
								'400' => esc_html__('Normal (400)', 'grace-church'),
								'700' => esc_html__('Bold (700)', 'grace-church')
							)
						),
						"align" => array(
							"title" => esc_html__("Alignment", "grace-church"),
							"desc" => esc_html__("Icon text alignment", "grace-church"),
							"dependency" => array(
								'icon' => array('not_empty')
							),
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						), 
						"link" => array(
							"title" => esc_html__("Link URL", "grace-church"),
							"desc" => esc_html__("Link URL from this icon (if not empty)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Image
				"trx_image" => array(
					"title" => esc_html__("Image", "grace-church"),
					"desc" => esc_html__("Insert image into your post (page)", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"url" => array(
							"title" => esc_html__("URL for image file", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media",
							"before" => array(
								'sizes' => true		// If you want allow user select thumb size for image. Otherwise, thumb size is ignored - image fullsize used
							)
						),
						"title" => array(
							"title" => esc_html__("Title", "grace-church"),
							"desc" => esc_html__("Image title (if need)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"icon" => array(
							"title" => esc_html__("Icon before title",  'grace-church'),
							"desc" => esc_html__('Select icon for the title from Fontello icons set',  'grace-church'),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"align" => array(
							"title" => esc_html__("Float image", "grace-church"),
							"desc" => esc_html__("Float image to left or right side", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['float']
						), 
						"shape" => array(
							"title" => esc_html__("Image Shape", "grace-church"),
							"desc" => esc_html__("Shape of the image: square (rectangle) or round", "grace-church"),
							"value" => "square",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => array(
								"square" => esc_html__('Square', 'grace-church'),
								"round" => esc_html__('Round', 'grace-church')
							)
						), 
						"link" => array(
							"title" => esc_html__("Link", "grace-church"),
							"desc" => esc_html__("The link URL from the image", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
				// Infobox
				"trx_infobox" => array(
					"title" => esc_html__("Infobox", "grace-church"),
					"desc" => esc_html__("Insert infobox into your post (page)", "grace-church"),
					"decorate" => false,
					"container" => true,
					"params" => array(
						"style" => array(
							"title" => esc_html__("Style", "grace-church"),
							"desc" => esc_html__("Infobox style", "grace-church"),
							"value" => "regular",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => array(
								'regular' => esc_html__('Regular', 'grace-church'),
								'info' => esc_html__('Info', 'grace-church'),
								'success' => esc_html__('Success', 'grace-church'),
								'error' => esc_html__('Error', 'grace-church')
							)
						),
						"closeable" => array(
							"title" => esc_html__("Closeable box", "grace-church"),
							"desc" => esc_html__("Create closeable box (with close button)", "grace-church"),
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"icon" => array(
							"title" => esc_html__("Custom icon",  'grace-church'),
							"desc" => esc_html__('Select icon for the infobox from Fontello icons set. If empty - use default icon',  'grace-church'),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"color" => array(
							"title" => esc_html__("Text color", "grace-church"),
							"desc" => esc_html__("Any color for text and headers", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"bg_color" => array(
							"title" => esc_html__("Background color", "grace-church"),
							"desc" => esc_html__("Any background color for this infobox", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"_content_" => array(
							"title" => esc_html__("Infobox content", "grace-church"),
							"desc" => esc_html__("Content for infobox", "grace-church"),
							"divider" => true,
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
				// Line
				"trx_line" => array(
					"title" => esc_html__("Line", "grace-church"),
					"desc" => esc_html__("Insert Line into your post (page)", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"style" => array(
							"title" => esc_html__("Style", "grace-church"),
							"desc" => esc_html__("Line style", "grace-church"),
							"value" => "solid",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => array(
								'solid' => esc_html__('Solid', 'grace-church'),
								'dashed' => esc_html__('Dashed', 'grace-church'),
								'dotted' => esc_html__('Dotted', 'grace-church'),
								'double' => esc_html__('Double', 'grace-church')
							)
						),
						"color" => array(
							"title" => esc_html__("Color", "grace-church"),
							"desc" => esc_html__("Line color", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// List
				"trx_list" => array(
					"title" => esc_html__("List", "grace-church"),
					"desc" => esc_html__("List items with specific bullets", "grace-church"),
					"decorate" => true,
					"container" => false,
					"params" => array(
						"style" => array(
							"title" => esc_html__("Bullet's style", "grace-church"),
							"desc" => esc_html__("Bullet's style for each list item", "grace-church"),
							"value" => "ul",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['list_styles']
						), 
						"color" => array(
							"title" => esc_html__("Color", "grace-church"),
							"desc" => esc_html__("List items color", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"icon" => array(
							"title" => esc_html__('List icon',  'grace-church'),
							"desc" => esc_html__("Select list icon from Fontello icons set (only for style=Iconed)",  'grace-church'),
							"dependency" => array(
								'style' => array('iconed')
							),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"icon_color" => array(
							"title" => esc_html__("Icon color", "grace-church"),
							"desc" => esc_html__("List icons color", "grace-church"),
							"value" => "",
							"dependency" => array(
								'style' => array('iconed')
							),
							"type" => "color"
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					),
					"children" => array(
						"name" => "trx_list_item",
						"title" => esc_html__("Item", "grace-church"),
						"desc" => esc_html__("List item with specific bullet", "grace-church"),
						"decorate" => false,
						"container" => true,
						"params" => array(
							"_content_" => array(
								"title" => esc_html__("List item content", "grace-church"),
								"desc" => esc_html__("Current list item content", "grace-church"),
								"rows" => 4,
								"value" => "",
								"type" => "textarea"
							),
							"title" => array(
								"title" => esc_html__("List item title", "grace-church"),
								"desc" => esc_html__("Current list item title (show it as tooltip)", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"color" => array(
								"title" => esc_html__("Color", "grace-church"),
								"desc" => esc_html__("Text color for this item", "grace-church"),
								"value" => "",
								"type" => "color"
							),
							"icon" => array(
								"title" => esc_html__('List icon',  'grace-church'),
								"desc" => esc_html__("Select list item icon from Fontello icons set (only for style=Iconed)",  'grace-church'),
								"value" => "",
								"type" => "icons",
								"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
							),
							"icon_color" => array(
								"title" => esc_html__("Icon color", "grace-church"),
								"desc" => esc_html__("Icon color for this item", "grace-church"),
								"value" => "",
								"type" => "color"
							),
							"link" => array(
								"title" => esc_html__("Link URL", "grace-church"),
								"desc" => esc_html__("Link URL for the current list item", "grace-church"),
								"divider" => true,
								"value" => "",
								"type" => "text"
							),
							"target" => array(
								"title" => esc_html__("Link target", "grace-church"),
								"desc" => esc_html__("Link target for the current list item", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
							"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
							"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
						)
					)
				),
			
			
			
				// Number
				"trx_number" => array(
					"title" => esc_html__("Number", "grace-church"),
					"desc" => esc_html__("Insert number or any word as set separate characters", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"value" => array(
							"title" => esc_html__("Value", "grace-church"),
							"desc" => esc_html__("Number or any word", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"align" => array(
							"title" => esc_html__("Align", "grace-church"),
							"desc" => esc_html__("Select block alignment", "grace-church"),
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Parallax
				"trx_parallax" => array(
					"title" => esc_html__("Parallax", "grace-church"),
					"desc" => esc_html__("Create the parallax container (with asinc background image)", "grace-church"),
					"decorate" => false,
					"container" => true,
					"params" => array(
						"gap" => array(
							"title" => esc_html__("Create gap", "grace-church"),
							"desc" => esc_html__("Create gap around parallax container", "grace-church"),
							"value" => "no",
							"size" => "small",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no'],
							"type" => "switch"
						), 
						"dir" => array(
							"title" => esc_html__("Dir", "grace-church"),
							"desc" => esc_html__("Scroll direction for the parallax background", "grace-church"),
							"value" => "up",
							"size" => "medium",
							"options" => array(
								'up' => esc_html__('Up', 'grace-church'),
								'down' => esc_html__('Down', 'grace-church')
							),
							"type" => "switch"
						), 
						"speed" => array(
							"title" => esc_html__("Speed", "grace-church"),
							"desc" => esc_html__("Image motion speed (from 0.0 to 1.0)", "grace-church"),
							"min" => "0",
							"max" => "1",
							"step" => "0.1",
							"value" => "0.3",
							"type" => "spinner"
						),
						"scheme" => array(
							"title" => esc_html__("Color scheme", "grace-church"),
							"desc" => esc_html__("Select color scheme for this block", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['schemes']
						),
						"color" => array(
							"title" => esc_html__("Text color", "grace-church"),
							"desc" => esc_html__("Select color for text object inside parallax block", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "color"
						),
						"bg_color" => array(
							"title" => esc_html__("Background color", "grace-church"),
							"desc" => esc_html__("Select color for parallax background", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"bg_image" => array(
							"title" => esc_html__("Background image", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site for the parallax background", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"bg_image_x" => array(
							"title" => esc_html__("Image X position", "grace-church"),
							"desc" => esc_html__("Image horizontal position (as background of the parallax block) - in percent", "grace-church"),
							"min" => "0",
							"max" => "100",
							"value" => "50",
							"type" => "spinner"
						),
						"bg_video" => array(
							"title" => esc_html__("Video background", "grace-church"),
							"desc" => esc_html__("Select video from media library or paste URL for video file from other site to show it as parallax background", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media",
							"before" => array(
								'title' => esc_html__('Choose video', 'grace-church'),
								'action' => 'media_upload',
								'type' => 'video',
								'multiple' => false,
								'linked_field' => '',
								'captions' => array( 	
									'choose' => esc_html__('Choose video file', 'grace-church'),
									'update' => esc_html__('Select video file', 'grace-church')
								)
							),
							"after" => array(
								'icon' => 'icon-cancel',
								'action' => 'media_reset'
							)
						),
						"bg_video_ratio" => array(
							"title" => esc_html__("Video ratio", "grace-church"),
							"desc" => esc_html__("Specify ratio of the video background. For example: 16:9 (default), 4:3, etc.", "grace-church"),
							"value" => "16:9",
							"type" => "text"
						),
						"bg_overlay" => array(
							"title" => esc_html__("Overlay", "grace-church"),
							"desc" => esc_html__("Overlay color opacity (from 0.0 to 1.0)", "grace-church"),
							"min" => "0",
							"max" => "1",
							"step" => "0.1",
							"value" => "0",
							"type" => "spinner"
						),
						"bg_texture" => array(
							"title" => esc_html__("Texture", "grace-church"),
							"desc" => esc_html__("Predefined texture style from 1 to 11. 0 - without texture.", "grace-church"),
							"min" => "0",
							"max" => "11",
							"step" => "1",
							"value" => "0",
							"type" => "spinner"
						),
						"_content_" => array(
							"title" => esc_html__("Content", "grace-church"),
							"desc" => esc_html__("Content for the parallax container", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "text"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Popup
				"trx_popup" => array(
					"title" => esc_html__("Popup window", "grace-church"),
					"desc" => esc_html__("Container for any html-block with desired class and style for popup window", "grace-church"),
					"decorate" => true,
					"container" => true,
					"params" => array(
						"_content_" => array(
							"title" => esc_html__("Container content", "grace-church"),
							"desc" => esc_html__("Content for section container", "grace-church"),
							"divider" => true,
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Price
				"trx_price" => array(
					"title" => esc_html__("Price", "grace-church"),
					"desc" => esc_html__("Insert price with decoration", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"money" => array(
							"title" => esc_html__("Money", "grace-church"),
							"desc" => esc_html__("Money value (dot or comma separated)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"currency" => array(
							"title" => esc_html__("Currency", "grace-church"),
							"desc" => esc_html__("Currency character", "grace-church"),
							"value" => "$",
							"type" => "text"
						),
						"period" => array(
							"title" => esc_html__("Period", "grace-church"),
							"desc" => esc_html__("Period text (if need). For example: monthly, daily, etc.", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"align" => array(
							"title" => esc_html__("Alignment", "grace-church"),
							"desc" => esc_html__("Align price to left or right side", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['float']
						), 
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
				// Price block
				"trx_price_block" => array(
					"title" => esc_html__("Price block", "grace-church"),
					"desc" => esc_html__("Insert price block with title, price and description", "grace-church"),
					"decorate" => false,
					"container" => true,
					"params" => array(
						"title" => array(
							"title" => esc_html__("Title", "grace-church"),
							"desc" => esc_html__("Block title", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"link" => array(
							"title" => esc_html__("Link URL", "grace-church"),
							"desc" => esc_html__("URL for link from button (at bottom of the block)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"link_text" => array(
							"title" => esc_html__("Link text", "grace-church"),
							"desc" => esc_html__("Text (caption) for the link button (at bottom of the block). If empty - button not showed", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"icon" => array(
							"title" => esc_html__("Icon",  'grace-church'),
							"desc" => esc_html__('Select icon from Fontello icons set (placed before/instead price)',  'grace-church'),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"money" => array(
							"title" => esc_html__("Money", "grace-church"),
							"desc" => esc_html__("Money value (dot or comma separated)", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "text"
						),
						"currency" => array(
							"title" => esc_html__("Currency", "grace-church"),
							"desc" => esc_html__("Currency character", "grace-church"),
							"value" => "$",
							"type" => "text"
						),
						"period" => array(
							"title" => esc_html__("Period", "grace-church"),
							"desc" => esc_html__("Period text (if need). For example: monthly, daily, etc.", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"scheme" => array(
							"title" => esc_html__("Color scheme", "grace-church"),
							"desc" => esc_html__("Select color scheme for this block", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['schemes']
						),
						"align" => array(
							"title" => esc_html__("Alignment", "grace-church"),
							"desc" => esc_html__("Align price to left or right side", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['float']
						), 
						"_content_" => array(
							"title" => esc_html__("Description", "grace-church"),
							"desc" => esc_html__("Description for this price block", "grace-church"),
							"divider" => true,
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Quote
				"trx_quote" => array(
					"title" => esc_html__("Quote", "grace-church"),
					"desc" => esc_html__("Quote text", "grace-church"),
					"decorate" => false,
					"container" => true,
					"params" => array(
						"cite" => array(
							"title" => esc_html__("Quote cite", "grace-church"),
							"desc" => esc_html__("URL for quote cite", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"title" => array(
							"title" => esc_html__("Title (author)", "grace-church"),
							"desc" => esc_html__("Quote title (author name)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"style" => array(
							"title" => esc_html__("Style Quote", "grace-church"),
							"desc" => esc_html__("Select a transparent background if you want to write a quote on the image", "grace-church"),
							"value" => "",
							"options" => array(
                                ""              => esc_html__('Default', 'grace-church'),
                                "transparent"   => esc_html__('Transparent', 'grace-church')
                            ),
							"type" => "checklist"
						),
                    	"_content_" => array(
							"title" => esc_html__("Quote content", "grace-church"),
							"desc" => esc_html__("Quote content", "grace-church"),
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
                        "bg_color" => array(
                            "title" => esc_html__("Background color", "grace-church"),
                            "desc" => esc_html__("Any background color for this section", "grace-church"),
                            "dependency" => array(
                                'style' => array('transparent')
                            ),
                            "value" => "",
                            "type" => "color"
                        ),
                        "bg_image" => array(
                            "title" => esc_html__("Background image URL", "grace-church"),
                            "desc" => esc_html__("Select or upload image or write URL from other site for the background", "grace-church"),
                            "dependency" => array(
                                'style' => array('transparent')
                            ),
                            "readonly" => false,
                            "value" => "",
                            "type" => "media"
                        ),
                        "bg_overlay" => array(
                            "title" => esc_html__("Overlay", "grace-church"),
                            "desc" => esc_html__("Overlay color opacity (from 0.0 to 1.0)", "grace-church"),
                            "dependency" => array(
                                'style' => array('transparent')
                            ),
                            "min" => "0",
                            "max" => "1",
                            "step" => "0.1",
                            "value" => "0",
                            "type" => "spinner"
                        ),
						"width" => grace_church_shortcodes_width(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Reviews
				"trx_reviews" => array(
					"title" => esc_html__("Reviews", "grace-church"),
					"desc" => esc_html__("Insert reviews block in the single post", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"align" => array(
							"title" => esc_html__("Alignment", "grace-church"),
							"desc" => esc_html__("Align counter to left, center or right", "grace-church"),
							"divider" => true,
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						), 
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Search
				"trx_search" => array(
					"title" => esc_html__("Search", "grace-church"),
					"desc" => esc_html__("Show search form", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"style" => array(
							"title" => esc_html__("Style", "grace-church"),
							"desc" => esc_html__("Select style to display search field", "grace-church"),
							"value" => "regular",
							"options" => array(
								"regular" => esc_html__('Regular', 'grace-church'),
								"rounded" => esc_html__('Rounded', 'grace-church')
							),
							"type" => "checklist"
						),
						"state" => array(
							"title" => esc_html__("State", "grace-church"),
							"desc" => esc_html__("Select search field initial state", "grace-church"),
							"value" => "fixed",
							"options" => array(
								"fixed"  => esc_html__('Fixed',  'grace-church'),
								"opened" => esc_html__('Opened', 'grace-church'),
								"closed" => esc_html__('Closed', 'grace-church')
							),
							"type" => "checklist"
						),
						"title" => array(
							"title" => esc_html__("Title", "grace-church"),
							"desc" => esc_html__("Title (placeholder) for the search field", "grace-church"),
							"value" => esc_html__("Search &hellip;", 'grace-church'),
							"type" => "text"
						),
						"ajax" => array(
							"title" => esc_html__("AJAX", "grace-church"),
							"desc" => esc_html__("Search via AJAX or reload page", "grace-church"),
							"value" => "yes",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no'],
							"type" => "switch"
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Section
				"trx_section" => array(
					"title" => esc_html__("Section container", "grace-church"),
					"desc" => esc_html__("Container for any block with desired class and style", "grace-church"),
					"decorate" => true,
					"container" => true,
					"params" => array(
						"dedicated" => array(
							"title" => esc_html__("Dedicated", "grace-church"),
							"desc" => esc_html__("Use this block as dedicated content - show it before post title on single page", "grace-church"),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"align" => array(
							"title" => esc_html__("Align", "grace-church"),
							"desc" => esc_html__("Select block alignment", "grace-church"),
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						),
						"columns" => array(
							"title" => esc_html__("Columns emulation", "grace-church"),
							"desc" => esc_html__("Select width for columns emulation", "grace-church"),
							"value" => "none",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['columns']
						), 
						"pan" => array(
							"title" => esc_html__("Use pan effect", "grace-church"),
							"desc" => esc_html__("Use pan effect to show section content", "grace-church"),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"scroll" => array(
							"title" => esc_html__("Use scroller", "grace-church"),
							"desc" => esc_html__("Use scroller to show section content", "grace-church"),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"scroll_dir" => array(
							"title" => esc_html__("Scroll and Pan direction", "grace-church"),
							"desc" => esc_html__("Scroll and Pan direction (if Use scroller = yes or Pan = yes)", "grace-church"),
							"dependency" => array(
								'pan' => array('yes'),
								'scroll' => array('yes')
							),
							"value" => "horizontal",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['dir']
						),
						"scroll_controls" => array(
							"title" => esc_html__("Scroll controls", "grace-church"),
							"desc" => esc_html__("Show scroll controls (if Use scroller = yes)", "grace-church"),
							"dependency" => array(
								'scroll' => array('yes')
							),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"scheme" => array(
							"title" => esc_html__("Color scheme", "grace-church"),
							"desc" => esc_html__("Select color scheme for this block", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['schemes']
						),
						"color" => array(
							"title" => esc_html__("Fore color", "grace-church"),
							"desc" => esc_html__("Any color for objects in this section", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "color"
						),
						"bg_color" => array(
							"title" => esc_html__("Background color", "grace-church"),
							"desc" => esc_html__("Any background color for this section", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"bg_image" => array(
							"title" => esc_html__("Background image URL", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site for the background", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"bg_overlay" => array(
							"title" => esc_html__("Overlay", "grace-church"),
							"desc" => esc_html__("Overlay color opacity (from 0.0 to 1.0)", "grace-church"),
							"min" => "0",
							"max" => "1",
							"step" => "0.1",
							"value" => "0",
							"type" => "spinner"
						),
						"bg_texture" => array(
							"title" => esc_html__("Texture", "grace-church"),
							"desc" => esc_html__("Predefined texture style from 1 to 11. 0 - without texture.", "grace-church"),
							"min" => "0",
							"max" => "11",
							"step" => "1",
							"value" => "0",
							"type" => "spinner"
						),
						"font_size" => array(
							"title" => esc_html__("Font size", "grace-church"),
							"desc" => esc_html__("Font size of the text (default - in pixels, allows any CSS units of measure)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"font_weight" => array(
							"title" => esc_html__("Font weight", "grace-church"),
							"desc" => esc_html__("Font weight of the text", "grace-church"),
							"value" => "",
							"type" => "select",
							"size" => "medium",
							"options" => array(
								'100' => esc_html__('Thin (100)', 'grace-church'),
								'300' => esc_html__('Light (300)', 'grace-church'),
								'400' => esc_html__('Normal (400)', 'grace-church'),
								'700' => esc_html__('Bold (700)', 'grace-church')
							)
						),
						"_content_" => array(
							"title" => esc_html__("Container content", "grace-church"),
							"desc" => esc_html__("Content for section container", "grace-church"),
							"divider" => true,
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
				// Skills
				"trx_skills" => array(
					"title" => esc_html__("Skills", "grace-church"),
					"desc" => esc_html__("Insert skills diagramm in your page (post)", "grace-church"),
					"decorate" => true,
					"container" => false,
					"params" => array(
						"max_value" => array(
							"title" => esc_html__("Max value", "grace-church"),
							"desc" => esc_html__("Max value for skills items", "grace-church"),
							"value" => 100,
							"min" => 1,
							"type" => "spinner"
						),
						"type" => array(
							"title" => esc_html__("Skills type", "grace-church"),
							"desc" => esc_html__("Select type of skills block", "grace-church"),
							"value" => "bar",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => array(
								'bar' => esc_html__('Bar', 'grace-church'),
								'pie' => esc_html__('Pie chart', 'grace-church'),
								'counter' => esc_html__('Counter', 'grace-church'),
								'arc' => esc_html__('Arc', 'grace-church')
							)
						), 
						"layout" => array(
							"title" => esc_html__("Skills layout", "grace-church"),
							"desc" => esc_html__("Select layout of skills block", "grace-church"),
							"dependency" => array(
								'type' => array('counter','pie','bar')
							),
							"value" => "rows",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => array(
								'rows' => esc_html__('Rows', 'grace-church'),
								'columns' => esc_html__('Columns', 'grace-church')
							)
						),
						"dir" => array(
							"title" => esc_html__("Direction", "grace-church"),
							"desc" => esc_html__("Select direction of skills block", "grace-church"),
							"dependency" => array(
								'type' => array('counter','pie','bar')
							),
							"value" => "horizontal",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['dir']
						), 
						"style" => array(
							"title" => esc_html__("Counters style", "grace-church"),
							"desc" => esc_html__("Select style of skills items (only for type=counter)", "grace-church"),
							"dependency" => array(
								'type' => array('counter')
							),
							"value" => 1,
							"options" => grace_church_get_list_styles(1, 4),
							"type" => "checklist"
						), 
						// "columns" - autodetect, not set manual
						"color" => array(
							"title" => esc_html__("Skills items color", "grace-church"),
							"desc" => esc_html__("Color for all skills items", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "color"
						),
						"bg_color" => array(
							"title" => esc_html__("Background color", "grace-church"),
							"desc" => esc_html__("Background color for all skills items (only for type=pie)", "grace-church"),
							"dependency" => array(
								'type' => array('pie')
							),
							"value" => "",
							"type" => "color"
						),
						"border_color" => array(
							"title" => esc_html__("Border color", "grace-church"),
							"desc" => esc_html__("Border color for all skills items (only for type=pie)", "grace-church"),
							"dependency" => array(
								'type' => array('pie')
							),
							"value" => "",
							"type" => "color"
						),
						"align" => array(
							"title" => esc_html__("Align skills block", "grace-church"),
							"desc" => esc_html__("Align skills block to left or right side", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['float']
						), 
						"arc_caption" => array(
							"title" => esc_html__("Arc Caption", "grace-church"),
							"desc" => esc_html__("Arc caption - text in the center of the diagram", "grace-church"),
							"dependency" => array(
								'type' => array('arc')
							),
							"value" => "",
							"type" => "text"
						),
						"pie_compact" => array(
							"title" => esc_html__("Pie compact", "grace-church"),
							"desc" => esc_html__("Show all skills in one diagram or as separate diagrams", "grace-church"),
							"dependency" => array(
								'type' => array('pie')
							),
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"pie_cutout" => array(
							"title" => esc_html__("Pie cutout", "grace-church"),
							"desc" => esc_html__("Pie cutout (0-99). 0 - without cutout, 99 - max cutout", "grace-church"),
							"dependency" => array(
								'type' => array('pie')
							),
							"value" => 0,
							"min" => 0,
							"max" => 99,
							"type" => "spinner"
						),
						"title" => array(
							"title" => esc_html__("Title", "grace-church"),
							"desc" => esc_html__("Title for the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"subtitle" => array(
							"title" => esc_html__("Subtitle", "grace-church"),
							"desc" => esc_html__("Subtitle for the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"description" => array(
							"title" => esc_html__("Description", "grace-church"),
							"desc" => esc_html__("Short description for the block", "grace-church"),
							"value" => "",
							"type" => "textarea"
						),
						"link" => array(
							"title" => esc_html__("Button URL", "grace-church"),
							"desc" => esc_html__("Link URL for the button at the bottom of the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"link_caption" => array(
							"title" => esc_html__("Button caption", "grace-church"),
							"desc" => esc_html__("Caption for the button at the bottom of the block", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					),
					"children" => array(
						"name" => "trx_skills_item",
						"title" => esc_html__("Skill", "grace-church"),
						"desc" => esc_html__("Skills item", "grace-church"),
						"container" => false,
						"params" => array(
							"title" => array(
								"title" => esc_html__("Title", "grace-church"),
								"desc" => esc_html__("Current skills item title", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"value" => array(
								"title" => esc_html__("Value", "grace-church"),
								"desc" => esc_html__("Current skills level", "grace-church"),
								"value" => 50,
								"min" => 0,
								"step" => 1,
								"type" => "spinner"
							),
							"color" => array(
								"title" => esc_html__("Color", "grace-church"),
								"desc" => esc_html__("Current skills item color", "grace-church"),
								"value" => "",
								"type" => "color"
							),
							"bg_color" => array(
								"title" => esc_html__("Background color", "grace-church"),
								"desc" => esc_html__("Current skills item background color (only for type=pie)", "grace-church"),
								"value" => "",
								"type" => "color"
							),
							"border_color" => array(
								"title" => esc_html__("Border color", "grace-church"),
								"desc" => esc_html__("Current skills item border color (only for type=pie)", "grace-church"),
								"value" => "",
								"type" => "color"
							),
							"style" => array(
								"title" => esc_html__("Counter style", "grace-church"),
								"desc" => esc_html__("Select style for the current skills item (only for type=counter)", "grace-church"),
								"value" => 1,
								"options" => grace_church_get_list_styles(1, 4),
								"type" => "checklist"
							), 
							"icon" => array(
								"title" => esc_html__("Counter icon",  'grace-church'),
								"desc" => esc_html__('Select icon from Fontello icons set, placed above counter (only for type=counter)',  'grace-church'),
								"value" => "",
								"type" => "icons",
								"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
							),
							"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
							"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
							"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
						)
					)
				),
			
			
			
			
				// Slider
				"trx_slider" => array(
					"title" => esc_html__("Slider", "grace-church"),
					"desc" => esc_html__("Insert slider into your post (page)", "grace-church"),
					"decorate" => true,
					"container" => false,
					"params" => array_merge(array(
						"engine" => array(
							"title" => esc_html__("Slider engine", "grace-church"),
							"desc" => esc_html__("Select engine for slider. Attention! Swiper is built-in engine, all other engines appears only if corresponding plugings are installed", "grace-church"),
							"value" => "swiper",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['sliders']
						),
						"align" => array(
							"title" => esc_html__("Float slider", "grace-church"),
							"desc" => esc_html__("Float slider to left or right side", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['float']
						),
						"custom" => array(
							"title" => esc_html__("Custom slides", "grace-church"),
							"desc" => esc_html__("Make custom slides from inner shortcodes (prepare it on tabs) or prepare slides from posts thumbnails", "grace-church"),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						)
						),
						grace_church_exists_revslider() ? array(
						"alias" => array(
							"title" => esc_html__("Revolution slider alias", "grace-church"),
							"desc" => esc_html__("Select Revolution slider to display", "grace-church"),
							"dependency" => array(
								'engine' => array('revo')
							),
							"divider" => true,
							"value" => "",
							"type" => "select",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['revo_sliders']
						)) : array(), array(
						"cat" => array(
							"title" => esc_html__("Swiper: Category list", "grace-church"),
							"desc" => esc_html__("Select category to show post's images. If empty - select posts from any category or from IDs list", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"divider" => true,
							"value" => "",
							"type" => "select",
							"style" => "list",
							"multiple" => true,
							"options" => grace_church_array_merge(array(0 => esc_html__('- Select category -', 'grace-church')), $GRACE_CHURCH_GLOBALS['sc_params']['categories'])
						),
						"count" => array(
							"title" => esc_html__("Swiper: Number of posts", "grace-church"),
							"desc" => esc_html__("How many posts will be displayed? If used IDs - this parameter ignored.", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => 3,
							"min" => 1,
							"max" => 100,
							"type" => "spinner"
						),
						"offset" => array(
							"title" => esc_html__("Swiper: Offset before select posts", "grace-church"),
							"desc" => esc_html__("Skip posts before select next part.", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => 0,
							"min" => 0,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Swiper: Post order by", "grace-church"),
							"desc" => esc_html__("Select desired posts sorting method", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => "date",
							"type" => "select",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['sorting']
						),
						"order" => array(
							"title" => esc_html__("Swiper: Post order", "grace-church"),
							"desc" => esc_html__("Select desired posts order", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						),
						"ids" => array(
							"title" => esc_html__("Swiper: Post IDs list", "grace-church"),
							"desc" => esc_html__("Comma separated list of posts ID. If set - parameters above are ignored!", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => "",
							"type" => "text"
						),
						"controls" => array(
							"title" => esc_html__("Swiper: Show slider controls", "grace-church"),
							"desc" => esc_html__("Show arrows inside slider", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"pagination" => array(
							"title" => esc_html__("Swiper: Show slider pagination", "grace-church"),
							"desc" => esc_html__("Show bullets for switch slides", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => "no",
							"type" => "checklist",
							"options" => array(
								'no'   => esc_html__('None', 'grace-church'),
								'yes'  => esc_html__('Dots', 'grace-church'),
								'full' => esc_html__('Side Titles', 'grace-church'),
								'over' => esc_html__('Over Titles', 'grace-church')
							)
						),
						"titles" => array(
							"title" => esc_html__("Swiper: Show titles section", "grace-church"),
							"desc" => esc_html__("Show section with post's title and short post's description", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"divider" => true,
							"value" => "no",
							"type" => "checklist",
							"options" => array(
								"no"    => esc_html__('Not show', 'grace-church'),
								"slide" => esc_html__('Show/Hide info', 'grace-church'),
								"fixed" => esc_html__('Fixed info', 'grace-church')
							)
						),
						"descriptions" => array(
							"title" => esc_html__("Swiper: Post descriptions", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"desc" => esc_html__("Show post's excerpt max length (characters)", "grace-church"),
							"value" => 0,
							"min" => 0,
							"max" => 1000,
							"step" => 10,
							"type" => "spinner"
						),
						"links" => array(
							"title" => esc_html__("Swiper: Post's title as link", "grace-church"),
							"desc" => esc_html__("Make links from post's titles", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"crop" => array(
							"title" => esc_html__("Swiper: Crop images", "grace-church"),
							"desc" => esc_html__("Crop images in each slide or live it unchanged", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"autoheight" => array(
							"title" => esc_html__("Swiper: Autoheight", "grace-church"),
							"desc" => esc_html__("Change whole slider's height (make it equal current slide's height)", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"slides_per_view" => array(
							"title" => esc_html__("Swiper: Slides per view", "grace-church"),
							"desc" => esc_html__("Slides per view showed in this slider", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => 1,
							"min" => 1,
							"max" => 6,
							"step" => 1,
							"type" => "spinner"
						),
						"slides_space" => array(
							"title" => esc_html__("Swiper: Space between slides", "grace-church"),
							"desc" => esc_html__("Size of space (in px) between slides", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => 0,
							"min" => 0,
							"max" => 100,
							"step" => 10,
							"type" => "spinner"
						),
						"interval" => array(
							"title" => esc_html__("Swiper: Slides change interval", "grace-church"),
							"desc" => esc_html__("Slides change interval (in milliseconds: 1000ms = 1s)", "grace-church"),
							"dependency" => array(
								'engine' => array('swiper')
							),
							"value" => 5000,
							"step" => 500,
							"min" => 0,
							"type" => "spinner"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)),
					"children" => array(
						"name" => "trx_slider_item",
						"title" => esc_html__("Slide", "grace-church"),
						"desc" => esc_html__("Slider item", "grace-church"),
						"container" => false,
						"params" => array(
							"src" => array(
								"title" => esc_html__("URL (source) for image file", "grace-church"),
								"desc" => esc_html__("Select or upload image or write URL from other site for the current slide", "grace-church"),
								"readonly" => false,
								"value" => "",
								"type" => "media"
							),
							"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
							"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
							"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
						)
					)
				),
			
			
			
			
				// Socials
				"trx_socials" => array(
					"title" => esc_html__("Social icons", "grace-church"),
					"desc" => esc_html__("List of social icons (with hovers)", "grace-church"),
					"decorate" => true,
					"container" => false,
					"params" => array(
						"type" => array(
							"title" => esc_html__("Icon's type", "grace-church"),
							"desc" => esc_html__("Type of the icons - images or font icons", "grace-church"),
							"value" => grace_church_get_theme_setting('socials_type'),
							"options" => array(
								'icons' => esc_html__('Icons', 'grace-church'),
								'images' => esc_html__('Images', 'grace-church')
							),
							"type" => "checklist"
						), 
						"size" => array(
							"title" => esc_html__("Icon's size", "grace-church"),
							"desc" => esc_html__("Size of the icons", "grace-church"),
							"value" => "small",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['sizes'],
							"type" => "checklist"
						), 
						"shape" => array(
							"title" => esc_html__("Icon's shape", "grace-church"),
							"desc" => esc_html__("Shape of the icons", "grace-church"),
							"value" => "square",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['shapes'],
							"type" => "checklist"
						), 
						"socials" => array(
							"title" => esc_html__("Manual socials list", "grace-church"),
							"desc" => esc_html__("Custom list of social networks. For example: twitter=http://twitter.com/my_profile|facebook=http://facebooc.com/my_profile. If empty - use socials from Theme options.", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "text"
						),
						"custom" => array(
							"title" => esc_html__("Custom socials", "grace-church"),
							"desc" => esc_html__("Make custom icons from inner shortcodes (prepare it on tabs)", "grace-church"),
							"divider" => true,
							"value" => "no",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no'],
							"type" => "switch"
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					),
					"children" => array(
						"name" => "trx_social_item",
						"title" => esc_html__("Custom social item", "grace-church"),
						"desc" => esc_html__("Custom social item: name, profile url and icon url", "grace-church"),
						"decorate" => false,
						"container" => false,
						"params" => array(
							"name" => array(
								"title" => esc_html__("Social name", "grace-church"),
								"desc" => esc_html__("Name (slug) of the social network (twitter, facebook, linkedin, etc.)", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"url" => array(
								"title" => esc_html__("Your profile URL", "grace-church"),
								"desc" => esc_html__("URL of your profile in specified social network", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"icon" => array(
								"title" => esc_html__("URL (source) for icon file", "grace-church"),
								"desc" => esc_html__("Select or upload image or write URL from other site for the current social icon", "grace-church"),
								"readonly" => false,
								"value" => "",
								"type" => "media"
							)
						)
					)
				),
			
			
			
			
				// Table
				"trx_table" => array(
					"title" => esc_html__("Table", "grace-church"),
					"desc" => esc_html__("Insert a table into post (page). ", "grace-church"),
					"decorate" => true,
					"container" => true,
					"params" => array(
						"align" => array(
							"title" => esc_html__("Content alignment", "grace-church"),
							"desc" => esc_html__("Select alignment for each table cell", "grace-church"),
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						),
						"_content_" => array(
							"title" => esc_html__("Table content", "grace-church"),
							"desc" => esc_html__("Content, created with any table-generator", "grace-church"),
							"divider" => true,
							"rows" => 8,
							"value" => "Paste here table content, generated on one of many public internet resources, for example: http://www.impressivewebs.com/html-table-code-generator/ or http://html-tables.com/",
							"type" => "textarea"
						),
						"width" => grace_church_shortcodes_width(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
			
				// Tabs
				"trx_tabs" => array(
					"title" => esc_html__("Tabs", "grace-church"),
					"desc" => esc_html__("Insert tabs in your page (post)", "grace-church"),
					"decorate" => true,
					"container" => false,
					"params" => array(
						"style" => array(
							"title" => esc_html__("Tabs style", "grace-church"),
							"desc" => esc_html__("Select style for tabs items", "grace-church"),
							"value" => 1,
							"options" => grace_church_get_list_styles(1, 2),
							"type" => "radio"
						),
						"initial" => array(
							"title" => esc_html__("Initially opened tab", "grace-church"),
							"desc" => esc_html__("Number of initially opened tab", "grace-church"),
							"divider" => true,
							"value" => 1,
							"min" => 0,
							"type" => "spinner"
						),
						"scroll" => array(
							"title" => esc_html__("Use scroller", "grace-church"),
							"desc" => esc_html__("Use scroller to show tab content (height parameter required)", "grace-church"),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					),
					"children" => array(
						"name" => "trx_tab",
						"title" => esc_html__("Tab", "grace-church"),
						"desc" => esc_html__("Tab item", "grace-church"),
						"container" => true,
						"params" => array(
							"title" => array(
								"title" => esc_html__("Tab title", "grace-church"),
								"desc" => esc_html__("Current tab title", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"_content_" => array(
								"title" => esc_html__("Tab content", "grace-church"),
								"desc" => esc_html__("Current tab content", "grace-church"),
								"divider" => true,
								"rows" => 4,
								"value" => "",
								"type" => "textarea"
							),
							"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
							"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
							"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
						)
					)
				),
			


				
			
			
				// Title
				"trx_title" => array(
					"title" => esc_html__("Title", "grace-church"),
					"desc" => esc_html__("Create header tag (1-6 level) with many styles", "grace-church"),
					"decorate" => false,
					"container" => true,
					"params" => array(
						"_content_" => array(
							"title" => esc_html__("Title content", "grace-church"),
							"desc" => esc_html__("Title content", "grace-church"),
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"type" => array(
							"title" => esc_html__("Title type", "grace-church"),
							"desc" => esc_html__("Title type (header level)", "grace-church"),
							"divider" => true,
							"value" => "1",
							"type" => "select",
							"options" => array(
								'1' => esc_html__('Header 1', 'grace-church'),
								'2' => esc_html__('Header 2', 'grace-church'),
								'3' => esc_html__('Header 3', 'grace-church'),
								'4' => esc_html__('Header 4', 'grace-church'),
								'5' => esc_html__('Header 5', 'grace-church'),
								'6' => esc_html__('Header 6', 'grace-church'),
							)
						),
						"style" => array(
							"title" => esc_html__("Title style", "grace-church"),
							"desc" => esc_html__("Title style", "grace-church"),
							"value" => "regular",
							"type" => "select",
							"options" => array(
								'regular' => esc_html__('Regular', 'grace-church'),
								'underline' => esc_html__('Underline', 'grace-church'),
								'divider' => esc_html__('Divider', 'grace-church'),
								'iconed' => esc_html__('With icon (image)', 'grace-church')
							)
						),
						"align" => array(
							"title" => esc_html__("Alignment", "grace-church"),
							"desc" => esc_html__("Title text alignment", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						), 
						"font_size" => array(
							"title" => esc_html__("Font_size", "grace-church"),
							"desc" => esc_html__("Custom font size. If empty - use theme default", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"font_weight" => array(
							"title" => esc_html__("Font weight", "grace-church"),
							"desc" => esc_html__("Custom font weight. If empty or inherit - use theme default", "grace-church"),
							"value" => "",
							"type" => "select",
							"size" => "medium",
							"options" => array(
								'inherit' => esc_html__('Default', 'grace-church'),
								'100' => esc_html__('Thin (100)', 'grace-church'),
								'300' => esc_html__('Light (300)', 'grace-church'),
								'400' => esc_html__('Normal (400)', 'grace-church'),
								'600' => esc_html__('Semibold (600)', 'grace-church'),
								'700' => esc_html__('Bold (700)', 'grace-church'),
								'900' => esc_html__('Black (900)', 'grace-church')
							)
						),
						"color" => array(
							"title" => esc_html__("Title color", "grace-church"),
							"desc" => esc_html__("Select color for the title", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"icon" => array(
							"title" => esc_html__('Title font icon',  'grace-church'),
							"desc" => esc_html__("Select font icon for the title from Fontello icons set (if style=iconed)",  'grace-church'),
							"dependency" => array(
								'style' => array('iconed')
							),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"image" => array(
							"title" => esc_html__('or image icon',  'grace-church'),
							"desc" => esc_html__("Select image icon for the title instead icon above (if style=iconed)",  'grace-church'),
							"dependency" => array(
								'style' => array('iconed')
							),
							"value" => "",
							"type" => "images",
							"size" => "small",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['images']
						),
						"picture" => array(
							"title" => esc_html__('or URL for image file', "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site (if style=iconed)", "grace-church"),
							"dependency" => array(
								'style' => array('iconed')
							),
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"image_size" => array(
							"title" => esc_html__('Image (picture) size', "grace-church"),
							"desc" => esc_html__("Select image (picture) size (if style='iconed')", "grace-church"),
							"dependency" => array(
								'style' => array('iconed')
							),
							"value" => "small",
							"type" => "checklist",
							"options" => array(
								'small' => esc_html__('Small', 'grace-church'),
								'medium' => esc_html__('Medium', 'grace-church'),
								'large' => esc_html__('Large', 'grace-church')
							)
						),
						"position" => array(
							"title" => esc_html__('Icon (image) position', "grace-church"),
							"desc" => esc_html__("Select icon (image) position (if style=iconed)", "grace-church"),
							"dependency" => array(
								'style' => array('iconed')
							),
							"value" => "left",
							"type" => "checklist",
							"options" => array(
								'top' => esc_html__('Top', 'grace-church'),
								'left' => esc_html__('Left', 'grace-church')
							)
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
			
				// Toggles
				"trx_toggles" => array(
					"title" => esc_html__("Toggles", "grace-church"),
					"desc" => esc_html__("Toggles items", "grace-church"),
					"decorate" => true,
					"container" => false,
					"params" => array(
						"style" => array(
							"title" => esc_html__("Toggles style", "grace-church"),
							"desc" => esc_html__("Select style for display toggles", "grace-church"),
							"value" => 1,
							"options" => grace_church_get_list_styles(1, 2),
							"type" => "radio"
						),
						"counter" => array(
							"title" => esc_html__("Counter", "grace-church"),
							"desc" => esc_html__("Display counter before each toggles title", "grace-church"),
							"value" => "off",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['on_off']
						),
						"icon_closed" => array(
							"title" => esc_html__("Icon while closed",  'grace-church'),
							"desc" => esc_html__('Select icon for the closed toggles item from Fontello icons set',  'grace-church'),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"icon_opened" => array(
							"title" => esc_html__("Icon while opened",  'grace-church'),
							"desc" => esc_html__('Select icon for the opened toggles item from Fontello icons set',  'grace-church'),
							"value" => "",
							"type" => "icons",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
						),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					),
					"children" => array(
						"name" => "trx_toggles_item",
						"title" => esc_html__("Toggles item", "grace-church"),
						"desc" => esc_html__("Toggles item", "grace-church"),
						"container" => true,
						"params" => array(
							"title" => array(
								"title" => esc_html__("Toggles item title", "grace-church"),
								"desc" => esc_html__("Title for current toggles item", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"open" => array(
								"title" => esc_html__("Open on show", "grace-church"),
								"desc" => esc_html__("Open current toggles item on show", "grace-church"),
								"value" => "no",
								"type" => "switch",
								"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
							),
							"icon_closed" => array(
								"title" => esc_html__("Icon while closed",  'grace-church'),
								"desc" => esc_html__('Select icon for the closed toggles item from Fontello icons set',  'grace-church'),
								"value" => "",
								"type" => "icons",
								"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
							),
							"icon_opened" => array(
								"title" => esc_html__("Icon while opened",  'grace-church'),
								"desc" => esc_html__('Select icon for the opened toggles item from Fontello icons set',  'grace-church'),
								"value" => "",
								"type" => "icons",
								"options" => $GRACE_CHURCH_GLOBALS['sc_params']['icons']
							),
							"_content_" => array(
								"title" => esc_html__("Toggles item content", "grace-church"),
								"desc" => esc_html__("Current toggles item content", "grace-church"),
								"rows" => 4,
								"value" => "",
								"type" => "textarea"
							),
							"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
							"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
							"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
						)
					)
				),
			
			
			
			
			
				// Tooltip
				"trx_tooltip" => array(
					"title" => esc_html__("Tooltip", "grace-church"),
					"desc" => esc_html__("Create tooltip for selected text", "grace-church"),
					"decorate" => false,
					"container" => true,
					"params" => array(
						"title" => array(
							"title" => esc_html__("Title", "grace-church"),
							"desc" => esc_html__("Tooltip title (required)", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"_content_" => array(
							"title" => esc_html__("Tipped content", "grace-church"),
							"desc" => esc_html__("Highlighted content with tooltip", "grace-church"),
							"divider" => true,
							"rows" => 4,
							"value" => "",
							"type" => "textarea"
						),
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Twitter
				"trx_twitter" => array(
					"title" => esc_html__("Twitter", "grace-church"),
					"desc" => esc_html__("Insert twitter feed into post (page)", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"user" => array(
							"title" => esc_html__("Twitter Username", "grace-church"),
							"desc" => esc_html__("Your username in the twitter account. If empty - get it from Theme Options.", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"consumer_key" => array(
							"title" => esc_html__("Consumer Key", "grace-church"),
							"desc" => esc_html__("Consumer Key from the twitter account", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"consumer_secret" => array(
							"title" => esc_html__("Consumer Secret", "grace-church"),
							"desc" => esc_html__("Consumer Secret from the twitter account", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"token_key" => array(
							"title" => esc_html__("Token Key", "grace-church"),
							"desc" => esc_html__("Token Key from the twitter account", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"token_secret" => array(
							"title" => esc_html__("Token Secret", "grace-church"),
							"desc" => esc_html__("Token Secret from the twitter account", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"count" => array(
							"title" => esc_html__("Tweets number", "grace-church"),
							"desc" => esc_html__("Tweets number to show", "grace-church"),
							"divider" => true,
							"value" => 3,
							"max" => 20,
							"min" => 1,
							"type" => "spinner"
						),
						"controls" => array(
							"title" => esc_html__("Show arrows", "grace-church"),
							"desc" => esc_html__("Show control buttons", "grace-church"),
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"interval" => array(
							"title" => esc_html__("Tweets change interval", "grace-church"),
							"desc" => esc_html__("Tweets change interval (in milliseconds: 1000ms = 1s)", "grace-church"),
							"value" => 7000,
							"step" => 500,
							"min" => 0,
							"type" => "spinner"
						),
						"align" => array(
							"title" => esc_html__("Alignment", "grace-church"),
							"desc" => esc_html__("Alignment of the tweets block", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						),
						"autoheight" => array(
							"title" => esc_html__("Autoheight", "grace-church"),
							"desc" => esc_html__("Change whole slider's height (make it equal current slide's height)", "grace-church"),
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"scheme" => array(
							"title" => esc_html__("Color scheme", "grace-church"),
							"desc" => esc_html__("Select color scheme for this block", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['schemes']
						),
						"bg_color" => array(
							"title" => esc_html__("Background color", "grace-church"),
							"desc" => esc_html__("Any background color for this section", "grace-church"),
							"value" => "",
							"type" => "color"
						),
						"bg_image" => array(
							"title" => esc_html__("Background image URL", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site for the background", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"bg_overlay" => array(
							"title" => esc_html__("Overlay", "grace-church"),
							"desc" => esc_html__("Overlay color opacity (from 0.0 to 1.0)", "grace-church"),
							"min" => "0",
							"max" => "1",
							"step" => "0.1",
							"value" => "0",
							"type" => "spinner"
						),
						"bg_texture" => array(
							"title" => esc_html__("Texture", "grace-church"),
							"desc" => esc_html__("Predefined texture style from 1 to 11. 0 - without texture.", "grace-church"),
							"min" => "0",
							"max" => "11",
							"step" => "1",
							"value" => "0",
							"type" => "spinner"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
				// Video
				"trx_video" => array(
					"title" => esc_html__("Video", "grace-church"),
					"desc" => esc_html__("Insert video player", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"url" => array(
							"title" => esc_html__("URL for video file", "grace-church"),
							"desc" => esc_html__("Select video from media library or paste URL for video file from other site", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media",
							"before" => array(
								'title' => esc_html__('Choose video', 'grace-church'),
								'action' => 'media_upload',
								'type' => 'video',
								'multiple' => false,
								'linked_field' => '',
								'captions' => array( 	
									'choose' => esc_html__('Choose video file', 'grace-church'),
									'update' => esc_html__('Select video file', 'grace-church')
								)
							),
							"after" => array(
								'icon' => 'icon-cancel',
								'action' => 'media_reset'
							)
						),
						"ratio" => array(
							"title" => esc_html__("Ratio", "grace-church"),
							"desc" => esc_html__("Ratio of the video", "grace-church"),
							"value" => "16:9",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => array(
								"16:9" => esc_html__("16:9", 'grace-church'),
								"4:3" => esc_html__("4:3", 'grace-church')
							)
						),
						"autoplay" => array(
							"title" => esc_html__("Autoplay video", "grace-church"),
							"desc" => esc_html__("Autoplay video on page load", "grace-church"),
							"value" => "off",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['on_off']
						),
						"align" => array(
							"title" => esc_html__("Align", "grace-church"),
							"desc" => esc_html__("Select block alignment", "grace-church"),
							"value" => "none",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						),
						"image" => array(
							"title" => esc_html__("Cover image", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site for video preview", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"bg_image" => array(
							"title" => esc_html__("Background image", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site for video background. Attention! If you use background image - specify paddings below from background margins to video block in percents!", "grace-church"),
							"divider" => true,
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"bg_top" => array(
							"title" => esc_html__("Top offset", "grace-church"),
							"desc" => esc_html__("Top offset (padding) inside background image to video block (in percent). For example: 3%", "grace-church"),
							"dependency" => array(
								'bg_image' => array('not_empty')
							),
							"value" => "",
							"type" => "text"
						),
						"bg_bottom" => array(
							"title" => esc_html__("Bottom offset", "grace-church"),
							"desc" => esc_html__("Bottom offset (padding) inside background image to video block (in percent). For example: 3%", "grace-church"),
							"dependency" => array(
								'bg_image' => array('not_empty')
							),
							"value" => "",
							"type" => "text"
						),
						"bg_left" => array(
							"title" => esc_html__("Left offset", "grace-church"),
							"desc" => esc_html__("Left offset (padding) inside background image to video block (in percent). For example: 20%", "grace-church"),
							"dependency" => array(
								'bg_image' => array('not_empty')
							),
							"value" => "",
							"type" => "text"
						),
						"bg_right" => array(
							"title" => esc_html__("Right offset", "grace-church"),
							"desc" => esc_html__("Right offset (padding) inside background image to video block (in percent). For example: 12%", "grace-church"),
							"dependency" => array(
								'bg_image' => array('not_empty')
							),
							"value" => "",
							"type" => "text"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				),
			
			
			
			
				// Zoom
				"trx_zoom" => array(
					"title" => esc_html__("Zoom", "grace-church"),
					"desc" => esc_html__("Insert the image with zoom/lens effect", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"effect" => array(
							"title" => esc_html__("Effect", "grace-church"),
							"desc" => esc_html__("Select effect to display overlapping image", "grace-church"),
							"value" => "lens",
							"size" => "medium",
							"type" => "switch",
							"options" => array(
								"lens" => esc_html__('Lens', 'grace-church'),
								"zoom" => esc_html__('Zoom', 'grace-church')
							)
						),
						"url" => array(
							"title" => esc_html__("Main image", "grace-church"),
							"desc" => esc_html__("Select or upload main image", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"over" => array(
							"title" => esc_html__("Overlaping image", "grace-church"),
							"desc" => esc_html__("Select or upload overlaping image", "grace-church"),
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"align" => array(
							"title" => esc_html__("Float zoom", "grace-church"),
							"desc" => esc_html__("Float zoom to left or right side", "grace-church"),
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['float']
						), 
						"bg_image" => array(
							"title" => esc_html__("Background image", "grace-church"),
							"desc" => esc_html__("Select or upload image or write URL from other site for zoom block background. Attention! If you use background image - specify paddings below from background margins to zoom block in percents!", "grace-church"),
							"divider" => true,
							"readonly" => false,
							"value" => "",
							"type" => "media"
						),
						"bg_top" => array(
							"title" => esc_html__("Top offset", "grace-church"),
							"desc" => esc_html__("Top offset (padding) inside background image to zoom block (in percent). For example: 3%", "grace-church"),
							"dependency" => array(
								'bg_image' => array('not_empty')
							),
							"value" => "",
							"type" => "text"
						),
						"bg_bottom" => array(
							"title" => esc_html__("Bottom offset", "grace-church"),
							"desc" => esc_html__("Bottom offset (padding) inside background image to zoom block (in percent). For example: 3%", "grace-church"),
							"dependency" => array(
								'bg_image' => array('not_empty')
							),
							"value" => "",
							"type" => "text"
						),
						"bg_left" => array(
							"title" => esc_html__("Left offset", "grace-church"),
							"desc" => esc_html__("Left offset (padding) inside background image to zoom block (in percent). For example: 20%", "grace-church"),
							"dependency" => array(
								'bg_image' => array('not_empty')
							),
							"value" => "",
							"type" => "text"
						),
						"bg_right" => array(
							"title" => esc_html__("Right offset", "grace-church"),
							"desc" => esc_html__("Right offset (padding) inside background image to zoom block (in percent). For example: 12%", "grace-church"),
							"dependency" => array(
								'bg_image' => array('not_empty')
							),
							"value" => "",
							"type" => "text"
						),
						"width" => grace_church_shortcodes_width(),
						"height" => grace_church_shortcodes_height(),
						"top" => $GRACE_CHURCH_GLOBALS['sc_params']['top'],
						"bottom" => $GRACE_CHURCH_GLOBALS['sc_params']['bottom'],
						"left" => $GRACE_CHURCH_GLOBALS['sc_params']['left'],
						"right" => $GRACE_CHURCH_GLOBALS['sc_params']['right'],
						"id" => $GRACE_CHURCH_GLOBALS['sc_params']['id'],
						"class" => $GRACE_CHURCH_GLOBALS['sc_params']['class'],
						"animation" => $GRACE_CHURCH_GLOBALS['sc_params']['animation'],
						"css" => $GRACE_CHURCH_GLOBALS['sc_params']['css']
					)
				)
			);
	
			// Woocommerce Shortcodes list
			//------------------------------------------------------------------
			if (grace_church_exists_woocommerce()) {
				
				// WooCommerce - Cart
				$GRACE_CHURCH_GLOBALS['shortcodes']["woocommerce_cart"] = array(
					"title" => esc_html__("Woocommerce: Cart", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show Cart page", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array()
				);
				
				// WooCommerce - Checkout
				$GRACE_CHURCH_GLOBALS['shortcodes']["woocommerce_checkout"] = array(
					"title" => esc_html__("Woocommerce: Checkout", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show Checkout page", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array()
				);
				
				// WooCommerce - My Account
				$GRACE_CHURCH_GLOBALS['shortcodes']["woocommerce_my_account"] = array(
					"title" => esc_html__("Woocommerce: My Account", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show My Account page", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array()
				);
				
				// WooCommerce - Order Tracking
				$GRACE_CHURCH_GLOBALS['shortcodes']["woocommerce_order_tracking"] = array(
					"title" => esc_html__("Woocommerce: Order Tracking", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show Order Tracking page", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array()
				);
				
				// WooCommerce - Shop Messages
				$GRACE_CHURCH_GLOBALS['shortcodes']["shop_messages"] = array(
					"title" => esc_html__("Woocommerce: Shop Messages", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show shop messages", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array()
				);
				
				// WooCommerce - Product Page
				$GRACE_CHURCH_GLOBALS['shortcodes']["product_page"] = array(
					"title" => esc_html__("Woocommerce: Product Page", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: display single product page", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"sku" => array(
							"title" => esc_html__("SKU", "grace-church"),
							"desc" => esc_html__("SKU code of displayed product", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"id" => array(
							"title" => esc_html__("ID", "grace-church"),
							"desc" => esc_html__("ID of displayed product", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"posts_per_page" => array(
							"title" => esc_html__("Number", "grace-church"),
							"desc" => esc_html__("How many products showed", "grace-church"),
							"value" => "1",
							"min" => 1,
							"type" => "spinner"
						),
						"post_type" => array(
							"title" => esc_html__("Post type", "grace-church"),
							"desc" => esc_html__("Post type for the WP query (leave 'product')", "grace-church"),
							"value" => "product",
							"type" => "text"
						),
						"post_status" => array(
							"title" => esc_html__("Post status", "grace-church"),
							"desc" => esc_html__("Display posts only with this status", "grace-church"),
							"value" => "publish",
							"type" => "select",
							"options" => array(
								"publish" => esc_html__('Publish', 'grace-church'),
								"protected" => esc_html__('Protected', 'grace-church'),
								"private" => esc_html__('Private', 'grace-church'),
								"pending" => esc_html__('Pending', 'grace-church'),
								"draft" => esc_html__('Draft', 'grace-church')
							)
						)
					)
				);
				
				// WooCommerce - Product
				$GRACE_CHURCH_GLOBALS['shortcodes']["product"] = array(
					"title" => esc_html__("Woocommerce: Product", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: display one product", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"sku" => array(
							"title" => esc_html__("SKU", "grace-church"),
							"desc" => esc_html__("SKU code of displayed product", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"id" => array(
							"title" => esc_html__("ID", "grace-church"),
							"desc" => esc_html__("ID of displayed product", "grace-church"),
							"value" => "",
							"type" => "text"
						)
					)
				);
				
				// WooCommerce - Best Selling Products
				$GRACE_CHURCH_GLOBALS['shortcodes']["best_selling_products"] = array(
					"title" => esc_html__("Woocommerce: Best Selling Products", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show best selling products", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"per_page" => array(
							"title" => esc_html__("Number", "grace-church"),
							"desc" => esc_html__("How many products showed", "grace-church"),
							"value" => 4,
							"min" => 1,
							"type" => "spinner"
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns per row use for products output", "grace-church"),
							"value" => 4,
							"min" => 2,
							"max" => 4,
							"type" => "spinner"
						)
					)
				);
				
				// WooCommerce - Recent Products
				$GRACE_CHURCH_GLOBALS['shortcodes']["recent_products"] = array(
					"title" => esc_html__("Woocommerce: Recent Products", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show recent products", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"per_page" => array(
							"title" => esc_html__("Number", "grace-church"),
							"desc" => esc_html__("How many products showed", "grace-church"),
							"value" => 4,
							"min" => 1,
							"type" => "spinner"
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns per row use for products output", "grace-church"),
							"value" => 4,
							"min" => 2,
							"max" => 4,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Order by", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "date",
							"type" => "select",
							"options" => array(
								"date" => esc_html__('Date', 'grace-church'),
								"title" => esc_html__('Title', 'grace-church')
							)
						),
						"order" => array(
							"title" => esc_html__("Order", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						)
					)
				);
				
				// WooCommerce - Related Products
				$GRACE_CHURCH_GLOBALS['shortcodes']["related_products"] = array(
					"title" => esc_html__("Woocommerce: Related Products", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show related products", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"posts_per_page" => array(
							"title" => esc_html__("Number", "grace-church"),
							"desc" => esc_html__("How many products showed", "grace-church"),
							"value" => 4,
							"min" => 1,
							"type" => "spinner"
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns per row use for products output", "grace-church"),
							"value" => 4,
							"min" => 2,
							"max" => 4,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Order by", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "date",
							"type" => "select",
							"options" => array(
								"date" => esc_html__('Date', 'grace-church'),
								"title" => esc_html__('Title', 'grace-church')
							)
						)
					)
				);
				
				// WooCommerce - Featured Products
				$GRACE_CHURCH_GLOBALS['shortcodes']["featured_products"] = array(
					"title" => esc_html__("Woocommerce: Featured Products", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show featured products", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"per_page" => array(
							"title" => esc_html__("Number", "grace-church"),
							"desc" => esc_html__("How many products showed", "grace-church"),
							"value" => 4,
							"min" => 1,
							"type" => "spinner"
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns per row use for products output", "grace-church"),
							"value" => 4,
							"min" => 2,
							"max" => 4,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Order by", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "date",
							"type" => "select",
							"options" => array(
								"date" => esc_html__('Date', 'grace-church'),
								"title" => esc_html__('Title', 'grace-church')
							)
						),
						"order" => array(
							"title" => esc_html__("Order", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						)
					)
				);
				
				// WooCommerce - Top Rated Products
				$GRACE_CHURCH_GLOBALS['shortcodes']["featured_products"] = array(
					"title" => esc_html__("Woocommerce: Top Rated Products", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show top rated products", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"per_page" => array(
							"title" => esc_html__("Number", "grace-church"),
							"desc" => esc_html__("How many products showed", "grace-church"),
							"value" => 4,
							"min" => 1,
							"type" => "spinner"
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns per row use for products output", "grace-church"),
							"value" => 4,
							"min" => 2,
							"max" => 4,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Order by", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "date",
							"type" => "select",
							"options" => array(
								"date" => esc_html__('Date', 'grace-church'),
								"title" => esc_html__('Title', 'grace-church')
							)
						),
						"order" => array(
							"title" => esc_html__("Order", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						)
					)
				);
				
				// WooCommerce - Sale Products
				$GRACE_CHURCH_GLOBALS['shortcodes']["featured_products"] = array(
					"title" => esc_html__("Woocommerce: Sale Products", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: list products on sale", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"per_page" => array(
							"title" => esc_html__("Number", "grace-church"),
							"desc" => esc_html__("How many products showed", "grace-church"),
							"value" => 4,
							"min" => 1,
							"type" => "spinner"
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns per row use for products output", "grace-church"),
							"value" => 4,
							"min" => 2,
							"max" => 4,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Order by", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "date",
							"type" => "select",
							"options" => array(
								"date" => esc_html__('Date', 'grace-church'),
								"title" => esc_html__('Title', 'grace-church')
							)
						),
						"order" => array(
							"title" => esc_html__("Order", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						)
					)
				);
				
				// WooCommerce - Product Category
				$GRACE_CHURCH_GLOBALS['shortcodes']["product_category"] = array(
					"title" => esc_html__("Woocommerce: Products from category", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: list products in specified category(-ies)", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"per_page" => array(
							"title" => esc_html__("Number", "grace-church"),
							"desc" => esc_html__("How many products showed", "grace-church"),
							"value" => 4,
							"min" => 1,
							"type" => "spinner"
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns per row use for products output", "grace-church"),
							"value" => 4,
							"min" => 2,
							"max" => 4,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Order by", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "date",
							"type" => "select",
							"options" => array(
								"date" => esc_html__('Date', 'grace-church'),
								"title" => esc_html__('Title', 'grace-church')
							)
						),
						"order" => array(
							"title" => esc_html__("Order", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						),
						"category" => array(
							"title" => esc_html__("Categories", "grace-church"),
							"desc" => esc_html__("Comma separated category slugs", "grace-church"),
							"value" => '',
							"type" => "text"
						),
						"operator" => array(
							"title" => esc_html__("Operator", "grace-church"),
							"desc" => esc_html__("Categories operator", "grace-church"),
							"value" => "IN",
							"type" => "checklist",
							"size" => "medium",
							"options" => array(
								"IN" => esc_html__('IN', 'grace-church'),
								"NOT IN" => esc_html__('NOT IN', 'grace-church'),
								"AND" => esc_html__('AND', 'grace-church')
							)
						)
					)
				);
				
				// WooCommerce - Products
				$GRACE_CHURCH_GLOBALS['shortcodes']["products"] = array(
					"title" => esc_html__("Woocommerce: Products", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: list all products", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"skus" => array(
							"title" => esc_html__("SKUs", "grace-church"),
							"desc" => esc_html__("Comma separated SKU codes of products", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"ids" => array(
							"title" => esc_html__("IDs", "grace-church"),
							"desc" => esc_html__("Comma separated ID of products", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns per row use for products output", "grace-church"),
							"value" => 4,
							"min" => 2,
							"max" => 4,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Order by", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "date",
							"type" => "select",
							"options" => array(
								"date" => esc_html__('Date', 'grace-church'),
								"title" => esc_html__('Title', 'grace-church')
							)
						),
						"order" => array(
							"title" => esc_html__("Order", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						)
					)
				);
				
				// WooCommerce - Product attribute
				$GRACE_CHURCH_GLOBALS['shortcodes']["product_attribute"] = array(
					"title" => esc_html__("Woocommerce: Products by Attribute", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show products with specified attribute", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"per_page" => array(
							"title" => esc_html__("Number", "grace-church"),
							"desc" => esc_html__("How many products showed", "grace-church"),
							"value" => 4,
							"min" => 1,
							"type" => "spinner"
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns per row use for products output", "grace-church"),
							"value" => 4,
							"min" => 2,
							"max" => 4,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Order by", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "date",
							"type" => "select",
							"options" => array(
								"date" => esc_html__('Date', 'grace-church'),
								"title" => esc_html__('Title', 'grace-church')
							)
						),
						"order" => array(
							"title" => esc_html__("Order", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						),
						"attribute" => array(
							"title" => esc_html__("Attribute", "grace-church"),
							"desc" => esc_html__("Attribute name", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"filter" => array(
							"title" => esc_html__("Filter", "grace-church"),
							"desc" => esc_html__("Attribute value", "grace-church"),
							"value" => "",
							"type" => "text"
						)
					)
				);
				
				// WooCommerce - Products Categories
				$GRACE_CHURCH_GLOBALS['shortcodes']["product_categories"] = array(
					"title" => esc_html__("Woocommerce: Product Categories", "grace-church"),
					"desc" => esc_html__("WooCommerce shortcode: show categories with products", "grace-church"),
					"decorate" => false,
					"container" => false,
					"params" => array(
						"number" => array(
							"title" => esc_html__("Number", "grace-church"),
							"desc" => esc_html__("How many categories showed", "grace-church"),
							"value" => 4,
							"min" => 1,
							"type" => "spinner"
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns per row use for categories output", "grace-church"),
							"value" => 4,
							"min" => 2,
							"max" => 4,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Order by", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "date",
							"type" => "select",
							"options" => array(
								"date" => esc_html__('Date', 'grace-church'),
								"title" => esc_html__('Title', 'grace-church')
							)
						),
						"order" => array(
							"title" => esc_html__("Order", "grace-church"),
							"desc" => esc_html__("Sorting order for products output", "grace-church"),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						),
						"parent" => array(
							"title" => esc_html__("Parent", "grace-church"),
							"desc" => esc_html__("Parent category slug", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"ids" => array(
							"title" => esc_html__("IDs", "grace-church"),
							"desc" => esc_html__("Comma separated ID of products", "grace-church"),
							"value" => "",
							"type" => "text"
						),
						"hide_empty" => array(
							"title" => esc_html__("Hide empty", "grace-church"),
							"desc" => esc_html__("Hide empty categories", "grace-church"),
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						)
					)
				);

			}
			
			do_action('grace_church_action_shortcodes_list');

		}
	}
}
?>