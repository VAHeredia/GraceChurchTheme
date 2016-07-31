<?php
/**
 * Grace-Church Framework: Testimonial post type settings
 *
 * @package	grace_church
 * @since	grace_church 1.0
 */

// Theme init
if (!function_exists('grace_church_testimonial_theme_setup')) {
	add_action( 'grace_church_action_before_init_theme', 'grace_church_testimonial_theme_setup' );
	function grace_church_testimonial_theme_setup() {
	
		// Add item in the admin menu
		add_action('admin_menu',			'grace_church_testimonial_add_meta_box');

		// Save data from meta box
		add_action('save_post',				'grace_church_testimonial_save_data');

		// Add shortcodes [trx_testimonials] and [trx_testimonials_item]
		add_action('grace_church_action_shortcodes_list',		'grace_church_testimonials_reg_shortcodes');
		add_action('grace_church_action_shortcodes_list_vc',	'grace_church_testimonials_reg_shortcodes_vc');

		// Meta box fields
		global $GRACE_CHURCH_GLOBALS;
		$GRACE_CHURCH_GLOBALS['testimonial_meta_box'] = array(
			'id' => 'testimonial-meta-box',
			'title' => esc_html__('Testimonial Details', 'grace-church'),
			'page' => 'testimonial',
			'context' => 'normal',
			'priority' => 'high',
			'fields' => array(
				"testimonial_author" => array(
					"title" => esc_html__('Testimonial author',  'grace-church'),
					"desc" => esc_html__("Name of the testimonial's author", 'grace-church'),
					"class" => "testimonial_author",
					"std" => "",
					"type" => "text"),
				"testimonial_position" => array(
					"title" => esc_html__("Author's position",  'grace-church'),
					"desc" => esc_html__("Position of the testimonial's author", 'grace-church'),
					"class" => "testimonial_author",
					"std" => "",
					"type" => "text"),
				"testimonial_email" => array(
					"title" => esc_html__("Author's e-mail",  'grace-church'),
					"desc" => esc_html__("E-mail of the testimonial's author - need to take Gravatar (if registered)", 'grace-church'),
					"class" => "testimonial_email",
					"std" => "",
					"type" => "text"),
				"testimonial_link" => array(
					"title" => esc_html__('Testimonial link',  'grace-church'),
					"desc" => esc_html__("URL of the testimonial source or author profile page", 'grace-church'),
					"class" => "testimonial_link",
					"std" => "",
					"type" => "text")
			)
		);
		
		if (function_exists('grace_church_require_data')) {
			// Prepare type "Testimonial"
			grace_church_require_data( 'post_type', 'testimonial', array(
				'label'               => esc_html__( 'Testimonial', 'grace-church' ),
				'description'         => esc_html__( 'Testimonial Description', 'grace-church' ),
				'labels'              => array(
					'name'                => _x( 'Testimonials', 'Post Type General Name', 'grace-church' ),
					'singular_name'       => _x( 'Testimonial', 'Post Type Singular Name', 'grace-church' ),
					'menu_name'           => esc_html__( 'Testimonials', 'grace-church' ),
					'parent_item_colon'   => esc_html__( 'Parent Item:', 'grace-church' ),
					'all_items'           => esc_html__( 'All Testimonials', 'grace-church' ),
					'view_item'           => esc_html__( 'View Item', 'grace-church' ),
					'add_new_item'        => esc_html__( 'Add New Testimonial', 'grace-church' ),
					'add_new'             => esc_html__( 'Add New', 'grace-church' ),
					'edit_item'           => esc_html__( 'Edit Item', 'grace-church' ),
					'update_item'         => esc_html__( 'Update Item', 'grace-church' ),
					'search_items'        => esc_html__( 'Search Item', 'grace-church' ),
					'not_found'           => esc_html__( 'Not found', 'grace-church' ),
					'not_found_in_trash'  => esc_html__( 'Not found in Trash', 'grace-church' ),
				),
				'supports'            => array( 'title', 'editor', 'author', 'thumbnail'),
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'menu_icon'			  => 'dashicons-cloud',
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 26,
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'capability_type'     => 'page',
				)
			);
			
			// Prepare taxonomy for testimonial
			grace_church_require_data( 'taxonomy', 'testimonial_group', array(
				'post_type'			=> array( 'testimonial' ),
				'hierarchical'      => true,
				'labels'            => array(
					'name'              => _x( 'Testimonials Group', 'taxonomy general name', 'grace-church' ),
					'singular_name'     => _x( 'Group', 'taxonomy singular name', 'grace-church' ),
					'search_items'      => esc_html__( 'Search Groups', 'grace-church' ),
					'all_items'         => esc_html__( 'All Groups', 'grace-church' ),
					'parent_item'       => esc_html__( 'Parent Group', 'grace-church' ),
					'parent_item_colon' => esc_html__( 'Parent Group:', 'grace-church' ),
					'edit_item'         => esc_html__( 'Edit Group', 'grace-church' ),
					'update_item'       => esc_html__( 'Update Group', 'grace-church' ),
					'add_new_item'      => esc_html__( 'Add New Group', 'grace-church' ),
					'new_item_name'     => esc_html__( 'New Group Name', 'grace-church' ),
					'menu_name'         => esc_html__( 'Testimonial Group', 'grace-church' ),
				),
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
				'rewrite'           => array( 'slug' => 'testimonial_group' ),
				)
			);
		}
	}
}


// Add meta box
if (!function_exists('grace_church_testimonial_add_meta_box')) {
	//add_action('admin_menu', 'grace_church_testimonial_add_meta_box');
	function grace_church_testimonial_add_meta_box() {
		global $GRACE_CHURCH_GLOBALS;
		$mb = $GRACE_CHURCH_GLOBALS['testimonial_meta_box'];
		add_meta_box($mb['id'], $mb['title'], 'grace_church_testimonial_show_meta_box', $mb['page'], $mb['context'], $mb['priority']);
	}
}

// Callback function to show fields in meta box
if (!function_exists('grace_church_testimonial_show_meta_box')) {
	function grace_church_testimonial_show_meta_box() {
		global $post, $GRACE_CHURCH_GLOBALS;

		// Use nonce for verification
        echo '<input type="hidden" name="meta_box_testimonial_nonce" value="', esc_attr($GRACE_CHURCH_GLOBALS['admin_nonce']), '" />';
		
		$data = get_post_meta($post->ID, 'testimonial_data', true);
	
		$fields = $GRACE_CHURCH_GLOBALS['testimonial_meta_box']['fields'];
		?>
		<table class="testimonial_area">
		<?php
		if (is_array($fields) && count($fields) > 0) {
			foreach ($fields as $id=>$field) { 
				$meta = isset($data[$id]) ? $data[$id] : '';
				?>
				<tr class="testimonial_field <?php echo esc_attr($field['class']); ?>" valign="top">
					<td><label for="<?php echo esc_attr($id); ?>"><?php echo esc_attr($field['title']); ?></label></td>
					<td><input type="text" name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($meta); ?>" size="30" />
						<br><small><?php echo esc_attr($field['desc']); ?></small></td>
				</tr>
				<?php
			}
		}
		?>
		</table>
		<?php
	}
}


// Save data from meta box
if (!function_exists('grace_church_testimonial_save_data')) {
	//add_action('save_post', 'grace_church_testimonial_save_data');
	function grace_church_testimonial_save_data($post_id) {
        global $GRACE_CHURCH_GLOBALS;
		// verify nonce
        if (!isset($_POST['meta_box_testimonial_nonce']) || !wp_verify_nonce($_POST['meta_box_testimonial_nonce'], $GRACE_CHURCH_GLOBALS['admin_url'])) {
			return $post_id;
		}

		// check autosave
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return $post_id;
		}

		// check permissions
		if ($_POST['post_type']!='testimonial' || !current_user_can('edit_post', $post_id)) {
			return $post_id;
		}

		$data = array();

		$fields = $GRACE_CHURCH_GLOBALS['testimonial_meta_box']['fields'];

		// Post type specific data handling
		if (is_array($fields) && count($fields) > 0) {
			foreach ($fields as $id=>$field) { 
				if (isset($_POST[$id])) 
					$data[$id] = stripslashes($_POST[$id]);
			}
		}

		update_post_meta($post_id, 'testimonial_data', $data);
	}
}






// ---------------------------------- [trx_testimonials] ---------------------------------------

/*
[trx_testimonials id="unique_id" style="1|2|3"]
	[trx_testimonials_item user="user_login"]Testimonials text[/trx_testimonials_item]
	[trx_testimonials_item email="" name="" position="" photo="photo_url"]Testimonials text[/trx_testimonials]
[/trx_testimonials]
*/

if (!function_exists('grace_church_sc_testimonials')) {
	function grace_church_sc_testimonials($atts, $content=null){
		if (grace_church_in_shortcode_blogger()) return '';
		extract(grace_church_html_decode(shortcode_atts(array(
			// Individual params
			"style" => "testimonials-1",
			"columns" => 1,
			"slider" => "yes",
			"slides_space" => 0,
			"controls" => "no",
			"interval" => "",
			"autoheight" => "no",
			"align" => "",
			"custom" => "no",
			"ids" => "",
			"cat" => "",
			"count" => "3",
			"offset" => "",
			"orderby" => "date",
			"order" => "desc",
			"scheme" => "",
			"bg_color" => "",
			"bg_image" => "",
			"bg_overlay" => "",
			"bg_texture" => "",
			"title" => "",
			"subtitle" => "",
			"description" => "",
			// Common params
			"id" => "",
			"class" => "",
			"animation" => "",
			"css" => "",
			"width" => "",
			"height" => "",
			"top" => "",
			"bottom" => "",
			"left" => "",
			"right" => ""
		), $atts)));

		if (empty($id)) $id = "sc_testimonials_".str_replace('.', '', mt_rand());
		if (empty($width)) $width = "100%";
		if (!empty($height) && grace_church_param_is_on($autoheight)) $autoheight = "no";
		if (empty($interval)) $interval = mt_rand(5000, 10000);
	
		if ($bg_image > 0) {
			$attach = wp_get_attachment_image_src( $bg_image, 'full' );
			if (isset($attach[0]) && $attach[0]!='')
				$bg_image = $attach[0];
		}
	
		if ($bg_overlay > 0) {
			if ($bg_color=='') $bg_color = grace_church_get_scheme_color('bg');
			$rgb = grace_church_hex2rgb($bg_color);
		}
		
		$ms = grace_church_get_css_position_from_values($top, $right, $bottom, $left);
		$ws = grace_church_get_css_position_from_values('', '', '', '', $width);
		$hs = grace_church_get_css_position_from_values('', '', '', '', '', $height);
		$css .= ($ms) . ($hs) . ($ws);

		$count = max(1, (int) $count);
		$columns = max(1, min(12, (int) $columns));
		if (grace_church_param_is_off($custom) && $count < $columns) $columns = $count;
		
		global $GRACE_CHURCH_GLOBALS;
		$GRACE_CHURCH_GLOBALS['sc_testimonials_id'] = $id;
		$GRACE_CHURCH_GLOBALS['sc_testimonials_style'] = $style;
		$GRACE_CHURCH_GLOBALS['sc_testimonials_columns'] = $columns;
		$GRACE_CHURCH_GLOBALS['sc_testimonials_counter'] = 0;
		$GRACE_CHURCH_GLOBALS['sc_testimonials_slider'] = $slider;
		$GRACE_CHURCH_GLOBALS['sc_testimonials_css_wh'] = $ws . $hs;
        $GRACE_CHURCH_GLOBALS['title_testimonials_1'] = $title;


		if (grace_church_param_is_on($slider)) grace_church_enqueue_slider('swiper');
	
		$output = ($bg_color!='' || $bg_image!='' || $bg_overlay>0 || $bg_texture>0 || grace_church_strlen($bg_texture)>2 || ($scheme && !grace_church_param_is_off($scheme) && !grace_church_param_is_inherit($scheme))
					? '<div class="sc_testimonials_wrap sc_section'
							. ($scheme && !grace_church_param_is_off($scheme) && !grace_church_param_is_inherit($scheme) ? ' scheme_'.esc_attr($scheme) : '')
							. '"'
						.' style="'
							. ($bg_color !== '' && $bg_overlay==0 ? 'background-color:' . esc_attr($bg_color) . ';' : '')
							. ($bg_image !== '' ? 'background-image:url(' . esc_url($bg_image) . ');' : '')
							. '"'
						. (!grace_church_param_is_off($animation) ? ' data-animation="'.esc_attr(grace_church_get_animation_classes($animation)).'"' : '')
						. '>'
						. '<div class="sc_section_overlay'.($bg_texture>0 ? ' texture_bg_'.esc_attr($bg_texture) : '') . '"'
								. ' style="' . ($bg_overlay>0 ? 'background-color:rgba('.(int)$rgb['r'].','.(int)$rgb['g'].','.(int)$rgb['b'].','.min(1, max(0, $bg_overlay)).');' : '')
									. (grace_church_strlen($bg_texture)>2 ? 'background-image:url('.esc_url($bg_texture).');' : '')
									. '"'
									. ($bg_overlay > 0 ? ' data-overlay="'.esc_attr($bg_overlay).'" data-bg_color="'.esc_attr($bg_color).'"' : '')
									. '>' 
					: '')
				. '<div' . ($id ? ' id="'.esc_attr($id).'"' : '') 
				. ' class="sc_testimonials sc_testimonials_style_'.esc_attr($style)
 					. ' ' . esc_attr(grace_church_get_template_property($style, 'container_classes'))
 					. (grace_church_param_is_on($slider)
						? ' sc_slider_swiper swiper-slider-container'
							. ' ' . esc_attr(grace_church_get_slider_controls_classes($controls))
							. (grace_church_param_is_on($autoheight) ? ' sc_slider_height_auto' : '')
							. ($hs ? ' sc_slider_height_fixed' : '')
						: '')
					. (!empty($class) ? ' '.esc_attr($class) : '')
					. ($align!='' && $align!='none' ? ' align'.esc_attr($align) : '')
					. '"'
				. ($bg_color=='' && $bg_image=='' && $bg_overlay==0 && ($bg_texture=='' || $bg_texture=='0') && !grace_church_param_is_off($animation) ? ' data-animation="'.esc_attr(grace_church_get_animation_classes($animation)).'"' : '')
				. (!empty($width) && grace_church_strpos($width, '%')===false ? ' data-old-width="' . esc_attr($width) . '"' : '')
				. (!empty($height) && grace_church_strpos($height, '%')===false ? ' data-old-height="' . esc_attr($height) . '"' : '')
				. ((int) $interval > 0 ? ' data-interval="'.esc_attr($interval).'"' : '')
				. ($columns > 1 ? ' data-slides-per-view="' . esc_attr($columns) . '"' : '')
				. ($slides_space > 0 ? ' data-slides-space="' . esc_attr($slides_space) . '"' : '')
				. ($css!='' ? ' style="'.esc_attr($css).'"' : '')
			. '>'
			. (!empty($subtitle) ? '<h6 class="sc_testimonials_subtitle sc_item_subtitle">' . trim(grace_church_strmacros($subtitle)) . '</h6>' : '')
            . (!empty($title) ? '<h2 class="sc_testimonials_title sc_item_title">' . trim(grace_church_strmacros($title)) . '</h2>' : '')
			. (!empty($description) ? '<div class="sc_testimonials_descr sc_item_descr">' . trim(grace_church_strmacros($description)) . '</div>' : '')
			. (grace_church_param_is_on($slider)
				? '<div class="slides swiper-wrapper">' 
				: ($columns > 1 
					? '<div class="sc_columns columns_wrap">' 
					: '')
				);
	
		$content = do_shortcode($content);

		if (grace_church_param_is_on($custom) && $content) {
			$output .= $content;
		} else {
			global $post;
		
			if (!empty($ids)) {
				$posts = explode(',', $ids);
				$count = count($posts);
			}
			
			$args = array(
				'post_type' => 'testimonial',
				'post_status' => 'publish',
				'posts_per_page' => $count,
				'ignore_sticky_posts' => true,
				'order' => $order=='asc' ? 'asc' : 'desc',
			);
		
			if ($offset > 0 && empty($ids)) {
				$args['offset'] = $offset;
			}
		
			$args = grace_church_query_add_sort_order($args, $orderby, $order);
			$args = grace_church_query_add_posts_and_cats($args, $ids, 'testimonial', $cat, 'testimonial_group');
	
			$query = new WP_Query( $args );
	
			$post_number = 0;
				
			while ( $query->have_posts() ) { 
				$query->the_post();
				$post_number++;
				$args = array(
					'layout' => $style,
					'show' => false,
					'number' => $post_number,
					'posts_on_page' => ($count > 0 ? $count : $query->found_posts),
					"descr" => grace_church_get_custom_option('post_excerpt_maxlength'.($columns > 1 ? '_masonry' : '')),
					"orderby" => $orderby,
					'content' => false,
					'terms_list' => false,
					'columns_count' => $columns,
					'slider' => $slider,
					'tag_id' => $id ? $id . '_' . $post_number : '',
					'tag_class' => '',
					'tag_animation' => '',
					'tag_css' => '',
					'tag_css_wh' => $ws . $hs
				);
				$post_data = grace_church_get_post_data($args);
				$post_data['post_content'] = wpautop($post_data['post_content']);	// Add <p> around text and paragraphs. Need separate call because 'content'=>false (see above)
				$post_meta = get_post_meta($post_data['post_id'], 'testimonial_data', true);
				$thumb_sizes = grace_church_get_thumb_sizes(array('layout' => $style));
				$args['author'] = $post_meta['testimonial_author'];
				$args['position'] = $post_meta['testimonial_position'];
				$args['link'] = !empty($post_meta['testimonial_link']) ? $post_meta['testimonial_link'] : '';	//$post_data['post_link'];
				$args['email'] = $post_meta['testimonial_email'];
				$args['photo'] = $post_data['post_thumb'];
				if (empty($args['photo']) && !empty($args['email'])) $args['photo'] = get_avatar($args['email'], $thumb_sizes['w']*min(2, max(1, grace_church_get_theme_option("retina_ready"))));
				$output .= grace_church_show_post_layout($args, $post_data);
			}
			wp_reset_postdata();

		}
	
		if (grace_church_param_is_on($slider)) {
			$output .= '</div>'
				. '<div class="sc_slider_controls_wrap"><a class="sc_slider_prev" href="#"></a><a class="sc_slider_next" href="#"></a></div>'
				. '<div class="sc_slider_pagination_wrap"></div>';
		} else if ($columns > 1) {
			$output .= '</div>';
		}

		$output .= '</div>'
					. ($bg_color!='' || $bg_image!='' || $bg_overlay>0 || $bg_texture>0 || grace_church_strlen($bg_texture)>2
						?  '</div></div>'
						: '');

		return apply_filters('grace_church_shortcode_output', $output, 'trx_testimonials', $atts, $content);
	}
	if (function_exists('grace_church_require_shortcode')) grace_church_require_shortcode('trx_testimonials', 'grace_church_sc_testimonials');
}
	
	
if (!function_exists('grace_church_sc_testimonials_item')) {
	function grace_church_sc_testimonials_item($atts, $content=null){
		if (grace_church_in_shortcode_blogger()) return '';
		extract(grace_church_html_decode(shortcode_atts(array(
			// Individual params
			"author" => "",
			"position" => "",
			"link" => "",
			"photo" => "",
			"email" => "",
			// Common params
			"id" => "",
			"class" => "",
			"css" => "",
		), $atts)));

		global $GRACE_CHURCH_GLOBALS;
		$GRACE_CHURCH_GLOBALS['sc_testimonials_counter']++;
	
		$id = $id ? $id : ($GRACE_CHURCH_GLOBALS['sc_testimonials_id'] ? $GRACE_CHURCH_GLOBALS['sc_testimonials_id'] . '_' . $GRACE_CHURCH_GLOBALS['sc_testimonials_counter'] : '');
	
		$thumb_sizes = grace_church_get_thumb_sizes(array('layout' => $GRACE_CHURCH_GLOBALS['sc_testimonials_style']));

		if (empty($photo)) {
			if (!empty($email))
				$photo = get_avatar($email, $thumb_sizes['w']*min(2, max(1, grace_church_get_theme_option("retina_ready"))));
		} else {
			if ($photo > 0) {
				$attach = wp_get_attachment_image_src( $photo, 'full' );
				if (isset($attach[0]) && $attach[0]!='')
					$photo = $attach[0];
			}
			$photo = grace_church_get_resized_image_tag($photo, $thumb_sizes['w'], $thumb_sizes['h']);
		}

		$post_data = array(
			'post_content' => do_shortcode($content)
		);
		$args = array(
			'layout' => $GRACE_CHURCH_GLOBALS['sc_testimonials_style'],
			'number' => $GRACE_CHURCH_GLOBALS['sc_testimonials_counter'],
			'columns_count' => $GRACE_CHURCH_GLOBALS['sc_testimonials_columns'],
			'slider' => $GRACE_CHURCH_GLOBALS['sc_testimonials_slider'],
			'show' => false,
			'descr'  => 0,
			'tag_id' => $id,
			'tag_class' => $class,
			'tag_animation' => '',
			'tag_css' => $css,
			'tag_css_wh' => $GRACE_CHURCH_GLOBALS['sc_testimonials_css_wh'],
			'author' => $author,
			'position' => $position,
			'link' => $link,
			'email' => $email,
			'photo' => $photo
		);
		$output = grace_church_show_post_layout($args, $post_data);

		return apply_filters('grace_church_shortcode_output', $output, 'trx_testimonials_item', $atts, $content);
	}
	if (function_exists('grace_church_require_shortcode')) grace_church_require_shortcode('trx_testimonials_item', 'grace_church_sc_testimonials_item');
}
// ---------------------------------- [/trx_testimonials] ---------------------------------------



// Add [trx_testimonials] and [trx_testimonials_item] in the shortcodes list
if (!function_exists('grace_church_testimonials_reg_shortcodes')) {
	//add_filter('grace_church_action_shortcodes_list',	'grace_church_testimonials_reg_shortcodes');
	function grace_church_testimonials_reg_shortcodes() {
		global $GRACE_CHURCH_GLOBALS;
		if (isset($GRACE_CHURCH_GLOBALS['shortcodes'])) {

			$testimonials_groups = grace_church_get_list_terms(false, 'testimonial_group');
			$testimonials_styles = grace_church_get_list_templates('testimonials');
			$controls = grace_church_get_list_slider_controls();

			grace_church_array_insert_before($GRACE_CHURCH_GLOBALS['shortcodes'], 'trx_title', array(
			
				// Testimonials
				"trx_testimonials" => array(
					"title" => esc_html__("Testimonials", "grace-church"),
					"desc" => esc_html__("Insert testimonials into post (page)", "grace-church"),
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
							"type" => "textarea"
						),
						"style" => array(
							"title" => esc_html__("Testimonials style", "grace-church"),
							"desc" => esc_html__("Select style to display testimonials", "grace-church"),
							"value" => "testimonials-1",
							"type" => "select",
							"options" => $testimonials_styles
						),
						"columns" => array(
							"title" => esc_html__("Columns", "grace-church"),
							"desc" => esc_html__("How many columns use to show testimonials", "grace-church"),
                            "dependency" => array(
                                'style' => array('testimonials-2','testimonials-3','testimonials-4')
                            ),
							"value" => 1,
							"min" => 1,
							"max" => 6,
							"step" => 1,
							"type" => "spinner"
						),
						"slider" => array(
							"title" => esc_html__("Slider", "grace-church"),
							"desc" => esc_html__("Use slider to show testimonials", "grace-church"),
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"controls" => array(
							"title" => esc_html__("Controls", "grace-church"),
							"desc" => esc_html__("Slider controls style and position", "grace-church"),
							"dependency" => array(
								'slider' => array('yes')
							),
							"divider" => true,
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $controls
						),
						"slides_space" => array(
							"title" => esc_html__("Space between slides", "grace-church"),
							"desc" => esc_html__("Size of space (in px) between slides", "grace-church"),
							"dependency" => array(
								'slider' => array('yes')
							),
							"value" => 0,
							"min" => 0,
							"max" => 100,
							"step" => 10,
							"type" => "spinner"
						),
						"interval" => array(
							"title" => esc_html__("Slides change interval", "grace-church"),
							"desc" => esc_html__("Slides change interval (in milliseconds: 1000ms = 1s)", "grace-church"),
							"dependency" => array(
								'slider' => array('yes')
							),
							"value" => 7000,
							"step" => 500,
							"min" => 0,
							"type" => "spinner"
						),
						"autoheight" => array(
							"title" => esc_html__("Autoheight", "grace-church"),
							"desc" => esc_html__("Change whole slider's height (make it equal current slide's height)", "grace-church"),
							"dependency" => array(
								'slider' => array('yes')
							),
							"value" => "yes",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"align" => array(
							"title" => esc_html__("Alignment", "grace-church"),
							"desc" => esc_html__("Alignment of the testimonials block", "grace-church"),
							"divider" => true,
							"value" => "",
							"type" => "checklist",
							"dir" => "horizontal",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['align']
						),
						"custom" => array(
							"title" => esc_html__("Custom", "grace-church"),
							"desc" => esc_html__("Allow get testimonials from inner shortcodes (custom) or get it from specified group (cat)", "grace-church"),
							"divider" => true,
							"value" => "no",
							"type" => "switch",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['yes_no']
						),
						"cat" => array(
							"title" => esc_html__("Categories", "grace-church"),
							"desc" => esc_html__("Select categories (groups) to show testimonials. If empty - select testimonials from any category (group) or from IDs list", "grace-church"),
							"dependency" => array(
								'custom' => array('no')
							),
							"divider" => true,
							"value" => "",
							"type" => "select",
							"style" => "list",
							"multiple" => true,
							"options" => grace_church_array_merge(array(0 => esc_html__('- Select category -', 'grace-church')), $testimonials_groups)
						),
						"count" => array(
							"title" => esc_html__("Number of posts", "grace-church"),
							"desc" => esc_html__("How many posts will be displayed? If used IDs - this parameter ignored.", "grace-church"),
							"dependency" => array(
								'custom' => array('no')
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
								'custom' => array('no')
							),
							"value" => 0,
							"min" => 0,
							"type" => "spinner"
						),
						"orderby" => array(
							"title" => esc_html__("Post order by", "grace-church"),
							"desc" => esc_html__("Select desired posts sorting method", "grace-church"),
							"dependency" => array(
								'custom' => array('no')
							),
							"value" => "date",
							"type" => "select",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['sorting']
						),
						"order" => array(
							"title" => esc_html__("Post order", "grace-church"),
							"desc" => esc_html__("Select desired posts order", "grace-church"),
							"dependency" => array(
								'custom' => array('no')
							),
							"value" => "desc",
							"type" => "switch",
							"size" => "big",
							"options" => $GRACE_CHURCH_GLOBALS['sc_params']['ordering']
						),
						"ids" => array(
							"title" => esc_html__("Post IDs list", "grace-church"),
							"desc" => esc_html__("Comma separated list of posts ID. If set - parameters above are ignored!", "grace-church"),
							"dependency" => array(
								'custom' => array('no')
							),
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
					),
					"children" => array(
						"name" => "trx_testimonials_item",
						"title" => esc_html__("Item", "grace-church"),
						"desc" => esc_html__("Testimonials item (custom parameters)", "grace-church"),
						"container" => true,
						"params" => array(
							"author" => array(
								"title" => esc_html__("Author", "grace-church"),
								"desc" => esc_html__("Name of the testimonmials author", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"link" => array(
								"title" => esc_html__("Link", "grace-church"),
								"desc" => esc_html__("Link URL to the testimonmials author page", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"email" => array(
								"title" => esc_html__("E-mail", "grace-church"),
								"desc" => esc_html__("E-mail of the testimonmials author (to get gravatar)", "grace-church"),
								"value" => "",
								"type" => "text"
							),
							"photo" => array(
								"title" => esc_html__("Photo", "grace-church"),
								"desc" => esc_html__("Select or upload photo of testimonmials author or write URL of photo from other site", "grace-church"),
								"value" => "",
								"type" => "media"
							),
							"_content_" => array(
								"title" => esc_html__("Testimonials text", "grace-church"),
								"desc" => esc_html__("Current testimonials text", "grace-church"),
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
				)

			));
		}
	}
}


// Add [trx_testimonials] and [trx_testimonials_item] in the VC shortcodes list
if (!function_exists('grace_church_testimonials_reg_shortcodes_vc')) {
	//add_filter('grace_church_action_shortcodes_list_vc',	'grace_church_testimonials_reg_shortcodes_vc');
	function grace_church_testimonials_reg_shortcodes_vc() {
		global $GRACE_CHURCH_GLOBALS;

		$testimonials_groups = grace_church_get_list_terms(false, 'testimonial_group');
		$testimonials_styles = grace_church_get_list_templates('testimonials');
		$controls			 = grace_church_get_list_slider_controls();
			
		// Testimonials			
		vc_map( array(
				"base" => "trx_testimonials",
				"name" => esc_html__("Testimonials", "grace-church"),
				"description" => esc_html__("Insert testimonials slider", "grace-church"),
				"category" => esc_html__('Content', 'js_composer'),
				'icon' => 'icon_trx_testimonials',
				"class" => "trx_sc_collection trx_sc_testimonials",
				"content_element" => true,
				"is_container" => true,
				"show_settings_on_create" => true,
				"as_parent" => array('only' => 'trx_testimonials_item'),
				"params" => array(
					array(
						"param_name" => "style",
						"heading" => esc_html__("Testimonials style", "grace-church"),
						"description" => esc_html__("Select style to display testimonials", "grace-church"),
						"class" => "",
						"admin_label" => true,
						"value" => array_flip($testimonials_styles),
						"type" => "dropdown"
					),
					array(
						"param_name" => "columns",
						"heading" => esc_html__("Columns", "grace-church"),
						"description" => esc_html__("How many columns use to show testimonials", "grace-church"),
                        'dependency' => array(
                            'element' => 'style',
                            'value' => array('testimonials-2','testimonials-3','testimonials-4')
                        ),
						"admin_label" => true,
						"class" => "",
						"value" => "1",
						"type" => "textfield"
					),
					array(
						"param_name" => "slider",
						"heading" => esc_html__("Slider", "grace-church"),
						"description" => esc_html__("Use slider to show testimonials", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Slider', 'grace-church'),
						"class" => "",
						"std" => "yes",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['yes_no']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "controls",
						"heading" => esc_html__("Controls", "grace-church"),
						"description" => esc_html__("Slider controls style and position", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Slider', 'grace-church'),
						'dependency' => array(
							'element' => 'slider',
							'value' => 'yes'
						),
						"class" => "",
						"std" => "no",
						"value" => array_flip($controls),
						"type" => "dropdown"
					),
					array(
						"param_name" => "slides_space",
						"heading" => esc_html__("Space between slides", "grace-church"),
						"description" => esc_html__("Size of space (in px) between slides", "grace-church"),
						"admin_label" => true,
						"group" => esc_html__('Slider', 'grace-church'),
						'dependency' => array(
							'element' => 'slider',
							'value' => 'yes'
						),
						"class" => "",
						"value" => "0",
						"type" => "textfield"
					),
					array(
						"param_name" => "interval",
						"heading" => esc_html__("Slides change interval", "grace-church"),
						"description" => esc_html__("Slides change interval (in milliseconds: 1000ms = 1s)", "grace-church"),
						"group" => esc_html__('Slider', 'grace-church'),
						'dependency' => array(
							'element' => 'slider',
							'value' => 'yes'
						),
						"class" => "",
						"value" => "7000",
						"type" => "textfield"
					),
					array(
						"param_name" => "autoheight",
						"heading" => esc_html__("Autoheight", "grace-church"),
						"description" => esc_html__("Change whole slider's height (make it equal current slide's height)", "grace-church"),
						"group" => esc_html__('Slider', 'grace-church'),
						'dependency' => array(
							'element' => 'slider',
							'value' => 'yes'
						),
						"class" => "",
						"value" => array("Autoheight" => "yes" ),
						"type" => "checkbox"
					),
					array(
						"param_name" => "align",
						"heading" => esc_html__("Alignment", "grace-church"),
						"description" => esc_html__("Alignment of the testimonials block", "grace-church"),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['align']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "custom",
						"heading" => esc_html__("Custom", "grace-church"),
						"description" => esc_html__("Allow get testimonials from inner shortcodes (custom) or get it from specified group (cat)", "grace-church"),
						"class" => "",
						"value" => array("Custom slides" => "yes" ),
						"type" => "checkbox"
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
						"param_name" => "cat",
						"heading" => esc_html__("Categories", "grace-church"),
						"description" => esc_html__("Select categories (groups) to show testimonials. If empty - select testimonials from any category (group) or from IDs list", "grace-church"),
						"group" => esc_html__('Query', 'grace-church'),
						'dependency' => array(
							'element' => 'custom',
							'is_empty' => true
						),
						"class" => "",
						"value" => array_flip(grace_church_array_merge(array(0 => esc_html__('- Select category -', 'grace-church')), $testimonials_groups)),
						"type" => "dropdown"
					),
					array(
						"param_name" => "count",
						"heading" => esc_html__("Number of posts", "grace-church"),
						"description" => esc_html__("How many posts will be displayed? If used IDs - this parameter ignored.", "grace-church"),
						"group" => esc_html__('Query', 'grace-church'),
						'dependency' => array(
							'element' => 'custom',
							'is_empty' => true
						),
						"class" => "",
						"value" => "3",
						"type" => "textfield"
					),
					array(
						"param_name" => "offset",
						"heading" => esc_html__("Offset before select posts", "grace-church"),
						"description" => esc_html__("Skip posts before select next part.", "grace-church"),
						"group" => esc_html__('Query', 'grace-church'),
						'dependency' => array(
							'element' => 'custom',
							'is_empty' => true
						),
						"class" => "",
						"value" => "0",
						"type" => "textfield"
					),
					array(
						"param_name" => "orderby",
						"heading" => esc_html__("Post sorting", "grace-church"),
						"description" => esc_html__("Select desired posts sorting method", "grace-church"),
						"group" => esc_html__('Query', 'grace-church'),
						'dependency' => array(
							'element' => 'custom',
							'is_empty' => true
						),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['sorting']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "order",
						"heading" => esc_html__("Post order", "grace-church"),
						"description" => esc_html__("Select desired posts order", "grace-church"),
						"group" => esc_html__('Query', 'grace-church'),
						'dependency' => array(
							'element' => 'custom',
							'is_empty' => true
						),
						"class" => "",
						"value" => array_flip($GRACE_CHURCH_GLOBALS['sc_params']['ordering']),
						"type" => "dropdown"
					),
					array(
						"param_name" => "ids",
						"heading" => esc_html__("Post IDs list", "grace-church"),
						"description" => esc_html__("Comma separated list of posts ID. If set - parameters above are ignored!", "grace-church"),
						"group" => esc_html__('Query', 'grace-church'),
						'dependency' => array(
							'element' => 'custom',
							'is_empty' => true
						),
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
					grace_church_vc_width(),
					grace_church_vc_height(),
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_top'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_bottom'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_left'],
					$GRACE_CHURCH_GLOBALS['vc_params']['margin_right'],
					$GRACE_CHURCH_GLOBALS['vc_params']['id'],
					$GRACE_CHURCH_GLOBALS['vc_params']['class'],
					$GRACE_CHURCH_GLOBALS['vc_params']['animation'],
					$GRACE_CHURCH_GLOBALS['vc_params']['css']
				)
		) );
			
			
		vc_map( array(
				"base" => "trx_testimonials_item",
				"name" => esc_html__("Testimonial", "grace-church"),
				"description" => esc_html__("Single testimonials item", "grace-church"),
				"show_settings_on_create" => true,
				"class" => "trx_sc_single trx_sc_testimonials_item",
				"content_element" => true,
				"is_container" => false,
				'icon' => 'icon_trx_testimonials_item',
				"as_child" => array('only' => 'trx_testimonials'),
				"as_parent" => array('except' => 'trx_testimonials'),
				"params" => array(
					array(
						"param_name" => "author",
						"heading" => esc_html__("Author", "grace-church"),
						"description" => esc_html__("Name of the testimonmials author", "grace-church"),
						"admin_label" => true,
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "link",
						"heading" => esc_html__("Link", "grace-church"),
						"description" => esc_html__("Link URL to the testimonmials author page", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "email",
						"heading" => esc_html__("E-mail", "grace-church"),
						"description" => esc_html__("E-mail of the testimonmials author", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "textfield"
					),
					array(
						"param_name" => "photo",
						"heading" => esc_html__("Photo", "grace-church"),
						"description" => esc_html__("Select or upload photo of testimonmials author or write URL of photo from other site", "grace-church"),
						"class" => "",
						"value" => "",
						"type" => "attach_image"
					),
					array(
						"param_name" => "content",
						"heading" => esc_html__("Testimonials text", "grace-church"),
						"description" => esc_html__("Current testimonials text", "grace-church"),
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
			
		class WPBakeryShortCode_Trx_Testimonials extends GRACE_CHURCH_VC_ShortCodeColumns {}
		class WPBakeryShortCode_Trx_Testimonials_Item extends GRACE_CHURCH_VC_ShortCodeSingle {}
		
	}
}
?>