<?php
/**
 * Grace-Church Framework: shortcodes manipulations
 *
 * @package	grace_church
 * @since	grace_church 1.0
 */

// Disable direct call
if ( ! defined( 'ABSPATH' ) ) { exit; }

// Theme init
if (!function_exists('grace_church_sc_theme_setup')) {
	add_action( 'grace_church_action_init_theme', 'grace_church_sc_theme_setup', 1 );
	function grace_church_sc_theme_setup() {
		// Add sc stylesheets
		add_action('grace_church_action_add_styles', 'grace_church_sc_add_styles', 1);
	}
}

if (!function_exists('grace_church_sc_theme_setup2')) {
	add_action( 'grace_church_action_before_init_theme', 'grace_church_sc_theme_setup2' );
	function grace_church_sc_theme_setup2() {

		if ( !is_admin() || isset($_POST['action']) ) {
			// Enable/disable shortcodes in excerpt
			add_filter('the_excerpt', 					'grace_church_sc_excerpt_shortcodes');
	
			// Prepare shortcodes in the content
			if (function_exists('grace_church_sc_prepare_content')) grace_church_sc_prepare_content();
		}

		// Add init script into shortcodes output in VC frontend editor
		add_filter('grace_church_shortcode_output', 'grace_church_sc_add_scripts', 10, 4);

		// AJAX: Send contact form data
		add_action('wp_ajax_send_contact_form',			'grace_church_sc_contact_form_send');
		add_action('wp_ajax_nopriv_send_contact_form',	'grace_church_sc_contact_form_send');

		// Show shortcodes list in admin editor
		add_action('media_buttons',						'grace_church_sc_selector_add_in_toolbar', 11);

	}
}


// Add shortcodes styles
if ( !function_exists( 'grace_church_sc_add_styles' ) ) {
	//add_action('grace_church_action_add_styles', 'grace_church_sc_add_styles', 1);
	function grace_church_sc_add_styles() {
		// Shortcodes
		grace_church_enqueue_style( 'grace_church-shortcodes-style',	grace_church_get_file_url('core/core.shortcodes/shortcodes.css'), array(), null );
	}
}


// Add shortcodes init scripts
if ( !function_exists( 'grace_church_sc_add_scripts' ) ) {
	//add_filter('grace_church_shortcode_output', 'grace_church_sc_add_scripts', 10, 4);
	function grace_church_sc_add_scripts($output, $tag='', $atts=array(), $content='') {

		global $GRACE_CHURCH_GLOBALS;
		
		if (empty($GRACE_CHURCH_GLOBALS['shortcodes_scripts_added'])) {
			$GRACE_CHURCH_GLOBALS['shortcodes_scripts_added'] = true;
			//grace_church_enqueue_style( 'grace_church-shortcodes-style', grace_church_get_file_url('core/core.shortcodes/shortcodes.css'), array(), null );
			grace_church_enqueue_script( 'grace_church-shortcodes-script', grace_church_get_file_url('core/core.shortcodes/shortcodes.js'), array('jquery'), null, true );
		}
		
		return $output;
	}
}


/* Prepare text for shortcodes
-------------------------------------------------------------------------------- */

// Prepare shortcodes in content
if (!function_exists('grace_church_sc_prepare_content')) {
	function grace_church_sc_prepare_content() {
		if (function_exists('grace_church_sc_clear_around')) {
			$filters = array(
				array('grace-church', 'sc', 'clear', 'around'),
				array('widget', 'text'),
				array('the', 'excerpt'),
				array('the', 'content')
			);
			if (grace_church_exists_woocommerce()) {
				$filters[] = array('woocommerce', 'template', 'single', 'excerpt');
				$filters[] = array('woocommerce', 'short', 'description');
			}
			if (is_array($filters) && count($filters) > 0) {
				foreach ($filters as $flt)
					add_filter(join('_', $flt), 'grace_church_sc_clear_around', 1);	// Priority 1 to clear spaces before do_shortcodes()
			}
		}
	}
}

// Enable/Disable shortcodes in the excerpt
if (!function_exists('grace_church_sc_excerpt_shortcodes')) {
	function grace_church_sc_excerpt_shortcodes($content) {
		if (!empty($content)) {
			$content = do_shortcode($content);
			//$content = strip_shortcodes($content);
		}
		return $content;
	}
}



/*
// Remove spaces and line breaks between close and open shortcode brackets ][:
[trx_columns]
	[trx_column_item]Column text ...[/trx_column_item]
	[trx_column_item]Column text ...[/trx_column_item]
	[trx_column_item]Column text ...[/trx_column_item]
[/trx_columns]

convert to

[trx_columns][trx_column_item]Column text ...[/trx_column_item][trx_column_item]Column text ...[/trx_column_item][trx_column_item]Column text ...[/trx_column_item][/trx_columns]
*/
if (!function_exists('grace_church_sc_clear_around')) {
	function grace_church_sc_clear_around($content) {
		if (!empty($content)) $content = preg_replace("/\](\s|\n|\r)*\[/", "][", $content);
		return $content;
	}
}






/* Shortcodes support utils
---------------------------------------------------------------------- */

// Grace-Church shortcodes load scripts
if (!function_exists('grace_church_sc_load_scripts')) {
	function grace_church_sc_load_scripts() {
		grace_church_enqueue_script( 'grace_church-shortcodes-script', grace_church_get_file_url('core/core.shortcodes/shortcodes_admin.js'), array('jquery'), null, true );
		grace_church_enqueue_script( 'grace_church-selection-script',  grace_church_get_file_url('js/jquery.selection.js'), array('jquery'), null, true );
	}
}

// Grace-Church shortcodes prepare scripts
if (!function_exists('grace_church_sc_prepare_scripts')) {
    function grace_church_sc_prepare_scripts() {
        global $GRACE_CHURCH_GLOBALS;
        if (!isset($GRACE_CHURCH_GLOBALS['shortcodes_prepared'])) {
            $GRACE_CHURCH_GLOBALS['shortcodes_prepared'] = true;
            $json_parse_func = 'eval';	// 'JSON.parse'
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    try {
                        GRACE_CHURCH_GLOBALS['shortcodes'] = <?php echo trim($json_parse_func); ?>(<?php echo json_encode( grace_church_array_prepare_to_json($GRACE_CHURCH_GLOBALS['shortcodes']) ); ?>);
                    } catch (e) {}
                    GRACE_CHURCH_GLOBALS['shortcodes_cp'] = '<?php echo is_admin() ? (!empty($GRACE_CHURCH_GLOBALS['to_colorpicker']) ? $GRACE_CHURCH_GLOBALS['to_colorpicker'] : 'wp') : 'custom'; ?>';	// wp | tiny | custom
                });
            </script>
        <?php
        }
    }
}

// Show shortcodes list in admin editor
if (!function_exists('grace_church_sc_selector_add_in_toolbar')) {
	//add_action('media_buttons','grace_church_sc_selector_add_in_toolbar', 11);
	function grace_church_sc_selector_add_in_toolbar(){

		if ( !grace_church_options_is_used() ) return;

		grace_church_sc_load_scripts();
		grace_church_sc_prepare_scripts();

		global $GRACE_CHURCH_GLOBALS;

		$shortcodes = $GRACE_CHURCH_GLOBALS['shortcodes'];
		$shortcodes_list = '<select class="sc_selector"><option value="">&nbsp;'. esc_html__('- Select Shortcode -', 'grace-church').'&nbsp;</option>';

		if (is_array($shortcodes) && count($shortcodes) > 0) {
			foreach ($shortcodes as $idx => $sc) {
				$shortcodes_list .= '<option value="'.esc_attr($idx).'" title="'.esc_attr($sc['desc']).'">'.esc_html($sc['title']).'</option>';
			}
		}

		$shortcodes_list .= '</select>';

		echo ($shortcodes_list);
	}
}

// Grace-Church shortcodes builder settings
require_once( grace_church_get_file_dir('core/core.shortcodes/shortcodes_settings.php') );

// VC shortcodes settings
if ( class_exists('WPBakeryShortCode') ) {
	require_once( grace_church_get_file_dir('core/core.shortcodes/shortcodes_vc.php') );
}

// Grace-Church shortcodes implementation
require_once( grace_church_get_file_dir('core/core.shortcodes/shortcodes.php') );
?>