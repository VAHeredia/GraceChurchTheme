<?php
// Disable direct call
if ( ! defined( 'ABSPATH' ) ) { exit; }


// Theme init
if (!function_exists('grace_church_importer_theme_setup')) {
    add_action( 'grace_church_action_after_init_theme', 'grace_church_importer_theme_setup' );		// Fire this action after load theme options
    function grace_church_importer_theme_setup() {
        if (is_admin() && current_user_can('import') && grace_church_get_theme_option('admin_dummy_data')=='yes') {
            new grace_church_dummy_data_importer();
        }
    }
}


class grace_church_dummy_data_importer {

    // Theme specific settings
    var $options = array(
        'debug'					=> true,						// Enable debug output
        'enable_importer'		=> true,						// Show Importer section
        'enable_exporter'		=> true,						// Show Exporter section
        'data_type'				=> 'vc',						// Default dummy data type
        'file_with_content'		=> array(
            'no_vc'				=> 'demo/dummy_data.xml',		// Name of the file with demo content without VC wrappers
            'vc'				=> 'demo/dummy_data_vc.xml'		// Name of the file with demo content for Visual Composer
        ),
        'file_with_options'		=> 'demo/theme_options.txt',	// Name of the file with theme options
        'file_with_postmeta'	=> 'demo/theme_postmeta.txt',	// Name of the file with post meta
        'file_with_widgets'		=> 'demo/widgets.txt',			// Name of the file with widgets data
        'file_with_booking'		=> 'demo/booking.txt',			// Name of the file with Booking Calendar data
        'file_with_timeline'	=> 'demo/content_timeline.txt',	// Name of the file with Booking Calendar data
        'folder_with_revsliders'=> 'demo/revslider',			// Name of the folder with revolution sliders data
        'folder_with_essgrids'  => 'demo/essgrid',				// Name of the folder with Essential Grids data
        'domain_dev'			=> 'church.ancorathemes.dnw',	// Domain on developer's server
        'domain_demo'			=> 'gracechurch.ancorathemes.com',	// Domain on demo-server
        'uploads_folder'		=> 'imports',					// Folder with images on demo server
        'upload_attachments'	=> true,						// Upload attachments images
        'import_posts'			=> true,						// Import posts
        'import_to'				=> true,						// Import Theme Options
        'import_widgets'		=> true,						// Import widgets
        'import_booking'		=> true,						// Import Booking Calendar
        'import_timeline'		=> true,						// Import Content TimeLine
        'import_sliders'		=> true,						// Import sliders
        'import_essgrids'		=> true,						// Import Essential Grids
        'overwrite_content'		=> true,						// Overwrite existing content
        'show_on_front'			=> 'page',						// Reading settings
        'page_on_front'			=> 'Home',					    // Homepage title
        'page_for_posts'		=> 'Blog',					    // Blog streampage title
        'menus'					=> array(						// Menus locations and names
            'menu-main'	  => 'Main menu',
            'menu-user'	  => 'User menu',
            'menu-footer' => 'Footer menu',
            'menu-outer'  => 'Main menu'
        ),
        'taxonomies'			=> array(),						// List of required taxonomies: 'post_type' => 'taxonomy', ...
        'required_plugins'		=> array(
            'visual_composer',
            'revslider'
//			'woocommerce'
//			'mega_main_menu'
        ),
        'additional_options'	=> array(						// Additional options slugs (for export plugins settings). Support wildcards, for example 'woocommerce_%'
            // Visual Composer
            'wpb_js_templates',
            // WooCommerce
            'shop_%',
            'woocommerce_%',
            /*
            'shop_catalog_image_size', 'shop_single_image_size', 'shop_thumbnail_image_size',
            'woocommerce_shop_page_display', 'woocommerce_category_archive_display', 'woocommerce_default_catalog_orderby',
            'woocommerce_cart_redirect_after_add', 'woocommerce_enable_ajax_add_to_cart',
            */
            // Mega Menu
            'mega_main_menu_options'
        ),
        'wooc_pages'			=> array(						// Options slugs and pages titles for WooCommerce pages
            'woocommerce_shop_page_id' 				=> 'Shop',
            'woocommerce_cart_page_id' 				=> 'Cart',
            'woocommerce_checkout_page_id' 			=> 'Checkout',
            'woocommerce_pay_page_id' 				=> 'Checkout &#8594; Pay',
            'woocommerce_thanks_page_id' 			=> 'Order Received',
            'woocommerce_myaccount_page_id' 		=> 'My Account',
            'woocommerce_edit_address_page_id'		=> 'Edit My Address',
            'woocommerce_view_order_page_id'		=> 'View Order',
            'woocommerce_change_password_page_id'	=> 'Change Password',
            'woocommerce_logout_page_id'			=> 'Logout',
            'woocommerce_lost_password_page_id'		=> 'Lost Password'
        )
    );

    var $error    = '';				// Error message
    var $success  = '';				// Success message
    var $result   = 0;				// Import posts percent (if break inside)
    var $last_slider = 0;			// Last imported slider number

    var $nonce    = '';
    var $export_options = '';
    var $export_templates = '';
    var $export_postmeta = '';
    var $export_widgets = '';
    var $export_booking = '';
    var $export_timeline = '';
    var $uploads_url = '';
    var $uploads_dir = '';
    var $import_log = '';
    var $import_last_id = 0;

    //-----------------------------------------------------------------------------------
    // Constuctor
    //-----------------------------------------------------------------------------------
    function __construct() {
        $this->options = apply_filters('grace_church_filter_importer_options', $this->options);
        $this->nonce = wp_create_nonce(get_admin_url());
        $uploads_info = wp_upload_dir();
        $this->uploads_dir = $uploads_info['basedir'];
        $this->uploads_url = $uploads_info['baseurl'];
        if ($this->options['debug']) define('IMPORT_DEBUG', true);
        $this->import_log = grace_church_get_file_dir('core/core.importer/importer.log');
        $log = explode('|', grace_church_fgc($this->import_log));
        $this->import_last_id = (int) $log[0];
        $this->result = empty($log[1]) ? 0 : (int) $log[1];
        $this->last_slider = empty($log[2]) ? '' : $log[2];
        add_action('admin_menu', array($this, 'admin_menu_item'));
    }

    //-----------------------------------------------------------------------------------
    // Admin Interface
    //-----------------------------------------------------------------------------------
    function admin_menu_item() {
        if ( current_user_can( 'manage_options' ) ) {
            // In this case menu item is add in admin menu 'Appearance'
            //add_theme_page( esc_html__('Install Dummy Data', 'grace-church'), esc_html__('Install Dummy Data', 'grace-church'), 'edit_theme_options', 'trx_importer', array($this, 'build_page'));

            // In this case menu item is add in admin menu 'Tools'
            //add_management_page( esc_html__('Theme Demo', 'grace-church'), esc_html__('Theme Demo', 'grace-church'), 'manage_options', 'trx_importer', array($this, 'build_page'));

            // In this case menu item is add in admin menu 'Theme Options'
            add_submenu_page('grace_church_options', esc_html__('Install Dummy Data', 'grace-church'), esc_html__('Install Dummy Data', 'grace-church'), 'manage_options', 'trx_importer', array($this, 'build_page'));
        }
    }


    //-----------------------------------------------------------------------------------
    // Build the Main Page
    //-----------------------------------------------------------------------------------
    function build_page() {

        $after_importer = false;

        do {
            if ( isset($_POST['importer_action']) ) {
                if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], get_admin_url() ) ) {
                    $this->error = esc_html__('Incorrect WP-nonce data! Operation canceled!', 'grace-church');
                    break;
                }
                if ($this->checkRequiredPlugins()) {
                    $this->options['overwrite_content']	= $_POST['importer_action']=='overwrite';
                    $this->options['data_type'] 		= $_POST['data_type']=='vc' ? 'vc' : 'no_vc';
                    $this->options['upload_attachments']= isset($_POST['importer_upload']);
                    $this->options['import_posts']		= isset($_POST['importer_posts']);
                    $this->options['import_to']			= isset($_POST['importer_to']);
                    $this->options['import_widgets']	= isset($_POST['importer_widgets']);
                    $this->options['import_booking']	= isset($_POST['importer_booking']);
                    $this->options['import_timeline']	= isset($_POST['importer_timeline']);
                    $this->options['import_sliders']	= isset($_POST['importer_sliders']);
                    $this->options['import_essgrids']	= isset($_POST['importer_essgrids']);
                    $this->import_last_id = (int) $_POST['last_id'];
                    ?>
                    <div class="trx_importer_log">
                        <?php
                        $this->importer();
                        $after_importer = true;
                        ?>
                        <script type="text/javascript">
                            jQuery(document).ready(function() {
                                jQuery('.trx_importer_log').remove();
                                <?php if ($this->import_last_id > 0 || (!empty($this->last_slider) && $this->options['import_sliders'])) { ?>
                                setTimeout(function() {
                                    jQuery('#trx_importer_continue').trigger('click');
                                }, 3000);
                                <?php } ?>
                            });
                        </script>
                    </div>
                <?php
                }
            } else if ( isset($_POST['exporter_action']) ) {
                if ( !isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], get_admin_url() ) ) {
                    $this->error = esc_html__('Incorrect WP-nonce data! Operation canceled!', 'grace-church');
                    break;
                }
                $this->exporter();
            }
        } while (false);
        ?>
        <div class="trx_importer">
            <div class="trx_importer_result">
                <?php if (!empty($this->error)) { ?>
                    <p>&nbsp;</p>
                    <div class="error">
                        <p><?php echo ($this->error); ?></p>
                    </div>
                    <p>&nbsp;</p>
                <?php } ?>
                <?php if (!empty($this->success)) { ?>
                    <p>&nbsp;</p>
                    <div class="updated">
                        <p><?php echo ($this->success); ?></p>
                    </div>
                    <p>&nbsp;</p>
                <?php } ?>
            </div>

            <?php if (empty($this->success) && $this->options['enable_importer']) {
                global $GRACE_CHURCH_GLOBALS;
                ?>
                <div class="trx_importer_section"<?php echo ($after_importer ? ' style="display:none;"' : ''); ?>>
                    <h2 class="trx_title"><?php esc_html_e('Grace-Church Importer', 'grace-church'); ?></h2>
                    <p><b><?php esc_html_e('Attention! Important info:', 'grace-church'); ?></b></p>
                    <ol>
                        <li><?php esc_html_e('Data import can take a long time (sometimes more than 10 minutes) - please wait until the end of the procedure, do not navigate away from the page.', 'grace-church'); ?></li>
                        <li><?php esc_html_e('Web-servers set the time limit for the execution of php-scripts. Therefore, the import process will be split into parts. Upon completion of each part - the import will resume automatically!', 'grace-church'); ?></li>
                        <li><?php esc_html_e('We recommend that you select the first option to import (with the replacement of existing content) - so you get a complete copy of our demo site', 'grace-church'); ?></li>
                        <li><?php esc_html_e('We also encourage you to leave the enabled check box "Upload attachments" - to download the demo version of the images', 'grace-church'); ?></li>
                    </ol>

                    <form id="trx_importer_form" action="#" method="post">

                        <input type="hidden" value="<?php echo esc_attr($this->nonce); ?>" name="nonce" />
                        <input type="hidden" value="0" name="last_id" />

                        <p>
                            <input type="radio" <?php echo ($this->options['overwrite_content'] ? 'checked="checked"' : ''); ?> value="overwrite" name="importer_action" id="importer_action_over" /><label for="importer_action_over"><?php esc_html_e('Overwrite existing content', 'grace-church'); ?></label><br>
                            <?php wp_kses( _e('In this case <b>all existing content will be erased</b>! But you get full copy of the our demo site <b>(recommended)</b>.', 'grace-church'), $GRACE_CHURCH_GLOBALS['allowed_tags']); ?>
                        </p>

                        <p>
                            <input type="radio" <?php echo !$this->options['overwrite_content'] ? 'checked="checked"' : ''; ?> value="append" name="importer_action" id="importer_action_append" /><label for="importer_action_append"><?php esc_html_e('Append to existing content', 'grace-church'); ?></label><br>
                            <?php esc_html_e('In this case demo data append to the existing content! Warning! In many cases you do not have exact copy of the demo site.', 'grace-church'); ?>
                        </p>

                        <p><b><?php esc_html_e('Select the data to import:', 'grace-church'); ?></b></p>
                        <p>
                            <?php
                            $checked = 'checked="checked"';
                            if (!empty($this->options['file_with_content']['vc']) && file_exists(grace_church_get_file_dir($this->options['file_with_content']['vc']))) {
                                ?>
                                <input type="radio" <?php echo ($this->options['data_type']=='vc' ? $checked : ''); ?> value="vc" name="data_type" id="data_type_vc" /><label for="data_type_vc"><?php esc_html_e('Import data for edit in the Visual Composer', 'grace-church'); ?></label><br>
                                <?php
                                if ($this->options['data_type']=='vc') $checked = '';
                            }
                            if (!empty($this->options['file_with_content']['no_vc']) && file_exists(grace_church_get_file_dir($this->options['file_with_content']['no_vc']))) {
                                ?>
                                <input type="radio" <?php echo ($this->options['data_type']=='no_vc' || $checked ? $checked : ''); ?> value="no_vc" name="data_type" id="data_type_no_vc" /><label for="data_type_no_vc"><?php esc_html_e('Import data without Visual Composer wrappers', 'grace-church'); ?></label>
                            <?php
                            }
                            ?>
                        </p>
                        <p>
                            <input type="checkbox" <?php echo ($this->options['import_posts'] ? 'checked="checked"' : ''); ?> value="1" name="importer_posts" id="importer_posts" /> <label for="importer_posts"><?php esc_html_e('Import posts', 'grace-church'); ?></label><br>
                            <input type="checkbox" <?php echo ($this->options['upload_attachments'] ? 'checked="checked"' : ''); ?> value="1" name="importer_upload" id="importer_upload" /> <label for="importer_upload"><?php esc_html_e('Upload attachments', 'grace-church'); ?></label>
                        </p>
                        <p>
                            <input type="checkbox" <?php echo ($this->options['import_to'] ? 'checked="checked"' : ''); ?> value="1" name="importer_to" id="importer_to" /> <label for="importer_to"><?php esc_html_e('Import Theme Options', 'grace-church'); ?></label><br>
                            <input type="checkbox" <?php echo ($this->options['import_widgets'] ? 'checked="checked"' : ''); ?> value="1" name="importer_widgets" id="importer_widgets" /> <label for="importer_widgets"><?php esc_html_e('Import Widgets', 'grace-church'); ?></label><br>
                            <?php if (grace_church_exists_booking()) { ?>
                                <input type="checkbox" <?php echo ($this->options['import_booking'] ? 'checked="checked"' : ''); ?> value="1" name="importer_booking" id="importer_booking" /> <label for="importer_booking"><?php esc_html_e('Import Booking', 'grace-church'); ?></label><br>
                            <?php } ?>
                            <?php if ( is_plugin_active('content_timeline/content_timeline.php') ) { ?>
                                <input type="checkbox" <?php echo ($this->options['import_timeline'] ? 'checked="checked"' : ''); ?> value="1" name="importer_timeline" id="importer_timeline" /> <label for="importer_timeline"><?php esc_html_e('Import Content TimeLine', 'grace-church'); ?></label><br>
                            <?php } ?>
                            <input type="checkbox" <?php echo ($this->options['import_sliders'] ? 'checked="checked"' : ''); ?> value="1" name="importer_sliders" id="importer_sliders" /> <label for="importer_sliders"><?php esc_html_e('Import Sliders', 'grace-church'); ?></label><br>
                            <input type="checkbox" <?php echo ($this->options['import_essgrids'] ? 'checked="checked"' : ''); ?> value="1" name="importer_essgrids" id="importer_essgrids" /> <label for="importer_essgrids"><?php esc_html_e('Import Ess.Grids', 'grace-church'); ?></label>
                        </p>

                        <div class="trx_buttons">
                            <?php if ($this->import_last_id > 0 || (!empty($this->last_slider) && $this->options['import_sliders'])) { ?>
                                <h4 class="trx_importer_complete"><?php sprintf( esc_html__('Import posts completed by %s', 'grace-church'), $this->result.'%'); ?></h4>
                                <input type="submit" value="<?php printf( esc_html__('Continue import (from ID=%s)', 'grace-church'), $this->import_last_id); ?>" onClick="this.form.last_id.value='<?php echo esc_attr($this->import_last_id); ?>'" id="trx_importer_continue">
                                <input type="submit" value="<?php esc_html_e('Start import again', 'grace-church'); ?>">
                            <?php } else { ?>
                                <input type="submit" value="<?php esc_html_e('Start import', 'grace-church'); ?>">
                            <?php } ?>
                        </div>
                    </form>
                </div>
            <?php } ?>

            <?php if (empty($this->success) && $this->options['enable_exporter']) { ?>
                <div class="trx_exporter_section"<?php echo ($after_importer ? ' style="display:none;"' : ''); ?>>
                    <h2 class="trx_title"><?php esc_html_e('Grace-Church Exporter', 'grace-church'); ?></h2>
                    <form id="trx_exporter_form" action="#" method="post">

                        <input type="hidden" value="<?php echo esc_attr($this->nonce); ?>" name="nonce" />
                        <input type="hidden" value="all" name="exporter_action" />


                        <div class="trx_buttons">
                            <?php if ($this->export_options != '') { ?>

                                <table border="0" cellpadding="6">
                                    <tr>
                                        <th align="left"><?php esc_html_e('Theme Options', 'grace-church'); ?></th>
                                        <td><?php grace_church_fpc(grace_church_get_file_dir('core/core.importer/export/theme_options.txt'), $this->export_options); ?>
                                            <a download="theme_options.txt" href="<?php echo esc_url(grace_church_get_file_url('core/core.importer/export/theme_options.txt')); ?>"><?php esc_html_e('Download', 'grace-church'); ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th align="left"><?php esc_html_e('Templates Options', 'grace-church'); ?></th>
                                        <td><?php grace_church_fpc(grace_church_get_file_dir('core/core.importer/export/templates_options.txt'), $this->export_templates); ?>
                                            <a download="templates_options.txt" href="<?php echo esc_url(grace_church_get_file_url('core/core.importer/export/templates_options.txt')); ?>"><?php esc_html_e('Download', 'grace-church'); ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th align="left"><?php esc_html_e('Widgets', 'grace-church'); ?></th>
                                        <td><?php grace_church_fpc(grace_church_get_file_dir('core/core.importer/export/widgets.txt'), $this->export_widgets); ?>
                                            <a download="widgets.txt" href="<?php echo esc_url(grace_church_get_file_url('core/core.importer/export/widgets.txt')); ?>"><?php esc_html_e('Download', 'grace-church'); ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th align="left"><?php esc_html_e('Content TimeLine', 'grace-church'); ?></th>
                                        <td><?php grace_church_fpc(grace_church_get_file_dir('core/core.importer/export/content_timeline.txt'), $this->export_timeline); ?>
                                            <a download="content_timeline.txt" href="<?php echo esc_url(grace_church_get_file_url('core/core.importer/export/content_timeline.txt')); ?>"><?php esc_html_e('Download', 'grace-church'); ?></a>
                                        </td>
                                    </tr>

                                    <?php do_action('grace_church_action_importer_export_fields', $this); ?>

                                </table>

                            <?php } else { ?>

                                <input type="submit" value="<?php esc_attr_e('Export Theme Options', 'grace-church'); ?>">

                            <?php } ?>
                        </div>

                    </form>
                </div>
            <?php } ?>
        </div>
    <?php
    }


    //-----------------------------------------------------------------------------------
    // Export dummy data
    //-----------------------------------------------------------------------------------
    function exporter() {
        global $wpdb;
        $suppress = $wpdb->suppress_errors();

        // Export theme and categories options and VC templates
        $rows = $wpdb->get_results( "SELECT option_name, option_value FROM " . esc_sql($wpdb->options) . " WHERE option_name LIKE 'grace_church_options%'" );
        $options = array();
        if (is_array($rows) && count($rows) > 0) {
            foreach ($rows as $row) {
                $options[$row->option_name] = $this->prepare_uploads(unserialize($row->option_value));
            }
        }
        // Export additional options
        if (is_array($this->options['additional_options']) && count($this->options['additional_options']) > 0) {
            foreach ($this->options['additional_options'] as $opt) {
                $rows = $wpdb->get_results( "SELECT option_name, option_value FROM " . esc_sql($wpdb->options) . " WHERE option_name LIKE '" . esc_sql($opt) . "'" );
                if (is_array($rows) && count($rows) > 0) {
                    foreach ($rows as $row) {
                        $options[$row->option_name] = maybe_unserialize($row->option_value);
                    }
                }
            }
        }
        $this->export_options = serialize($this->prepare_domains($options));

        // Export templates options
        $rows = $wpdb->get_results( "SELECT option_name, option_value FROM " . esc_sql($wpdb->options) . " WHERE option_name LIKE 'grace_church_options_template_%'" );
        $options = array();
        if (is_array($rows) && count($rows) > 0) {
            foreach ($rows as $row) {
                $options[$row->option_name] = $this->prepare_uploads(unserialize($row->option_value));
            }
        }
        $this->export_templates = serialize($this->prepare_domains($options));

        // Export widgets
        $rows = $wpdb->get_results( "SELECT option_name, option_value FROM " . esc_sql($wpdb->options) . " WHERE option_name = 'sidebars_widgets' OR option_name LIKE 'widget_%'" );
        $options = array();
        if (is_array($rows) && count($rows) > 0) {
            foreach ($rows as $row) {
                $options[$row->option_name] = $this->prepare_uploads(unserialize($row->option_value));
            }
        }
        $this->export_widgets = serialize($this->prepare_domains($options));

        // Export Booking Calendar
        if (grace_church_exists_booking()) {
            $options = array();
            $rows = $wpdb->get_results( "SELECT * FROM ".esc_sql($wpdb->prefix)."booking_calendars", ARRAY_A );
            $options['booking_calendars'] = $rows;
            $rows = $wpdb->get_results( "SELECT * FROM ".esc_sql($wpdb->prefix)."booking_categories", ARRAY_A );
            $options['booking_categories'] = $rows;
            $rows = $wpdb->get_results( "SELECT * FROM ".esc_sql($wpdb->prefix)."booking_config", ARRAY_A );
            $options['booking_config'] = $rows;
            $rows = $wpdb->get_results( "SELECT * FROM ".esc_sql($wpdb->prefix)."booking_reservation", ARRAY_A );
            $options['booking_reservation'] = $rows;
            $rows = $wpdb->get_results( "SELECT * FROM ".esc_sql($wpdb->prefix)."booking_slots", ARRAY_A );
            $options['booking_slots'] = $rows;
            $this->export_booking = serialize($options);
        }

        //*+ Export Content TimeLine
        if ( is_plugin_active('content_timeline/content_timeline.php') ) {
            $options = array();
            $rows = $wpdb->get_results( "SELECT * FROM ".esc_sql($wpdb->prefix)."ctimelines", ARRAY_A );
            $options['ctimelines'] = $rows;
            $this->export_timeline = serialize($options);
        }

        $wpdb->suppress_errors( $suppress );
    }


    //-----------------------------------------------------------------------------------
    // Import dummy data
    //-----------------------------------------------------------------------------------
    function importer() {
        ?>
        <p>&nbsp;</p>
        <div class="error">
            <h4><?php echo esc_html__('Import progress:', 'grace-church') . ' <span id="import_progress_value">' . (!empty($this->last_slider) && $this->options['import_sliders'] ? 99 : $this->result) . '</span>%'; ?></h4>
            <p><?php echo esc_html__('Status:', 'grace-church'); ?> <span id="import_progress_status"></span></p>
            <p><?php echo esc_html__('Data import can take a long time (sometimes more than 10 minutes)!', 'grace-church')
                    . '<br>' . esc_html__('Please wait until the end of the procedure, do not navigate away from the page!', 'grace-church'); ?></p>
        </div>
        <p>&nbsp;</p>
        <?php
        // Import posts, pages, menu items, etc.
        $result = 100;
        if ($this->options['import_posts'] && (empty($this->last_slider) || !$this->options['import_sliders'])) {
            // Load WP Importer class
            if ( !defined('WP_LOAD_IMPORTERS') ) define('WP_LOAD_IMPORTERS', true); // we are loading importers
            if ( !class_exists('WP_Import') ) {
                require(grace_church_get_file_dir('core/core.importer/wordpress-importer.php'));
            }
            if ( class_exists( 'WP_Import' ) ) {
                $result = $this->import_posts();
                if ($result >= 100) {
                    if (in_array('woocommerce', $this->options['required_plugins']))
                        $this->setup_woocommerce_pages();
                    $this->setup_menus();
                } else {
                    $log = explode('|', grace_church_fgc($this->import_log));
                    $this->import_last_id = (int) $log[0];
                }
            }
        }

        // Import Theme Options
        if ($result>=100 && $this->options['import_to'] && (empty($this->last_slider) || !$this->options['import_sliders'])) {
            grace_church_options_reset();
            $this->import_theme_options();
        }

        // Import Widgets
        if ($result>=100 && $this->options['import_widgets'] && (empty($this->last_slider) || !$this->options['import_sliders']))
            $this->import_widgets();

        // Import Booking Calendar
        if ($result>=100 && $this->options['import_booking'] && (empty($this->last_slider) || !$this->options['import_sliders']))
            $this->import_booking();

        // Import Content TimeLine /*+
        if ($result>=100 && $this->options['import_timeline'] && (empty($this->last_slider) || !$this->options['import_sliders']))
            $this->import_timeline();

        // Import Ess.Grids
        if ($result>=100 && $this->options['import_essgrids'] && (empty($this->last_slider) || !$this->options['import_sliders']))
            $this->import_essgrids();

        // Import Sliders
        if ($result>=100 && $this->options['import_sliders'])
            $this->import_sliders();

        // Setup Front page and Blog page
        if ($result>=100 && $this->options['import_posts'] && (empty($this->last_slider) || !$this->options['import_sliders'])) {
            // Set reading options
            $home_page = get_page_by_title( $this->options['page_on_front'] );
            $posts_page = get_page_by_title( $this->options['page_for_posts'] );
            if ($home_page->ID && $posts_page->ID) {
                update_option('show_on_front', $this->options['show_on_front']);
                update_option('page_on_front', $home_page->ID); 	// Front Page
                update_option('page_for_posts', $posts_page->ID);	// Blog Page
            }

            // Flush rules after install
            flush_rewrite_rules();
        }
        // finally redirect to success page
        if ($result >= 100 && (empty($this->last_slider) || !$this->options['import_sliders'])) {
            $this->success = esc_html__('Congratulations! Import demo data finished successfull!', 'grace-church');
        } else {
            $this->error = '<h4>' . sprintf( esc_html__('Import progress: %s.', 'grace-church'), max(99, $result).'%') . '</h4>'
                . esc_html__('Due to the expiration of the time limit for the execution of scripts on your server, the import process is interrupted!', 'grace-church')
                . '<br>' . esc_html__('After 3 seconds, the import will continue automatically!', 'grace-church');
            $this->result = $result;
        }
    }

    //==========================================================================================
    // Utilities
    //==========================================================================================

    // Check for required plugings
    function checkRequiredPlugins() {
        $not_installed = '';
        if (in_array('visual_composer', $this->options['required_plugins']) && $_POST['data_type']=='vc' && !grace_church_exists_visual_composer() )
            $not_installed .= '<br>Visual Composer';
        if (in_array('woocommerce', $this->options['required_plugins']) && !grace_church_exists_woocommerce() )
            $not_installed .= '<br>WooCommerce';
        if (in_array('revslider', $this->options['required_plugins']) && !grace_church_exists_revslider())
            $not_installed .= '<br>Revolution Slider';
        if (in_array('instagram', $this->options['required_plugins']) && !grace_church_exists_instagram())
            $not_installed .= '<br>Instagram Widget';
        if (in_array('mega_main_menu', $this->options['required_plugins']) && !grace_church_exists_megamenu() )
            $not_installed .= '<br>Mega Main Menu';
        $not_installed = apply_filters('grace_church_filter_importer_required_plugins', $not_installed);
        if ($not_installed) {
            $this->error = '<b>'. esc_html__('Attention! For correct installation of the demo data, you must install and activate the following plugins: ', 'grace-church').'</b>'.($not_installed);
            $this->options['enable_importer'] = false;
            $this->options['enable_exporter'] = false;
            return false;
        }
        return true;
    }


    // Import XML file with posts data
    function import_posts() {
        if (empty($this->options['file_with_content'][$this->options['data_type']])) return;
        echo ($this->import_last_id == 0
            ? '<h3>'. esc_html__('Start Import', 'grace-church').'</h3>'
            : '<h3>'.sprintf( esc_html__('Continue Import from ID=%d', 'grace-church'), $this->import_last_id).'</h3>');
        echo '<b>' . esc_html__('Import Posts (pages, menus, attachments, etc) ...', 'grace-church').'</b><br>'; flush();
        $theme_xml = grace_church_get_file_dir($this->options['file_with_content'][$this->options['data_type']]);
        $importer = new WP_Import();
        $importer->fetch_attachments = $this->options['upload_attachments'];
        $importer->overwrite = $this->options['overwrite_content'];
        $importer->debug = $this->options['debug'];
        $importer->uploads_folder = $this->options['uploads_folder'];
        $importer->demo_url = 'http://' . $this->options['domain_demo'] . '/';
        $importer->start_from_id = $this->import_last_id;
        $importer->import_log = $this->import_log;
        if ($this->import_last_id == 0) $this->clear_tables();
        $this->prepare_taxonomies();
        if (!$this->options['debug']) ob_start();
        $result = $importer->import($theme_xml);
        if (!$this->options['debug']) ob_end_clean();
        if ($result>=100) grace_church_fpc($this->import_log, '');
        return $result;
    }


    // Delete all data from tables
    function clear_tables() {
        global $wpdb;
        if ($this->options['overwrite_content']) {
            echo '<br><b>'. esc_html__('Clear tables ...', 'grace-church').'</b><br>'; flush();
            if ($this->options['import_posts']) {
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->comments));
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "comments".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->commentmeta));
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "commentmeta".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->postmeta));
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "postmeta".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->posts));
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "posts".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->terms));
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "terms".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->term_relationships));
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "term_relationships".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->term_taxonomy));
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "term_taxonomy".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
            }
            if ($this->options['import_sliders'] && grace_church_exists_revslider()) {
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "revslider_sliders");
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "revslider_sliders".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "revslider_slides");
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "revslider_slides".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix) . "revslider_static_slides");
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "revslider_static_slides".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
            }
            if ($this->options['import_booking'] && grace_church_exists_booking()) {
                $rows = $wpdb->query( "TRUNCATE TABLE ".esc_sql($wpdb->prefix)."booking_calendars" );
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "booking_calendars".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $rows = $wpdb->query( "TRUNCATE TABLE ".esc_sql($wpdb->prefix)."booking_categories");
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "booking_categories".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $rows = $wpdb->query( "TRUNCATE TABLE ".esc_sql($wpdb->prefix)."booking_config");
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "booking_config".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $rows = $wpdb->query( "TRUNCATE TABLE ".esc_sql($wpdb->prefix)."booking_reservation");
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "booking_reservation".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
                $rows = $wpdb->query( "TRUNCATE TABLE ".esc_sql($wpdb->prefix)."booking_slots" );
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "booking_slots".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
            }
            if ($this->options['import_timeline'] && is_plugin_active('content_timeline/content_timeline.php') ) {
                $rows = $wpdb->query( "TRUNCATE TABLE ".esc_sql($wpdb->prefix)."ctimelines" );
                if ( is_wp_error( $res ) ) echo esc_html__( 'Failed truncate table "ctimelines".', 'grace-church' ) . ' ' . ($res->get_error_message()) . '<br />';
            }
        }
    }


    // Prepare additional taxes
    function prepare_taxonomies() {
        if (!function_exists('grace_church_require_data')) return;
        if (isset($this->options['taxonomies']) && is_array($this->options['taxonomies']) && count($this->options['taxonomies']) > 0) {
            foreach ($this->options['taxonomies'] as $type=>$tax) {
                grace_church_require_data( 'taxonomy', $tax, array(
                        'post_type'			=> array( $type ),
                        'hierarchical'		=> false,
                        'query_var'			=> $tax,
                        'rewrite'			=> true,
                        'public'			=> false,
                        'show_ui'			=> false,
                        'show_admin_column'	=> false,
                        '_builtin'			=> false
                    )
                );
            }
        }
    }


    // Set WooCommerce pages
    function setup_woocommerce_pages() {
        if (is_array($this->options['wooc_pages']) && count($this->options['wooc_pages']) > 0) {
            foreach ($this->options['wooc_pages'] as $woo_page_name => $woo_page_title) {
                $woopage = get_page_by_title( $woo_page_title );
                if ($woopage->ID) {
                    update_option($woo_page_name, $woopage->ID);
                }
            }
        }
        // We no longer need to install pages
        delete_option( '_wc_needs_pages' );
        delete_transient( '_wc_activation_redirect' );
    }


    // Set imported menus to registered theme locations
    function setup_menus() {
        echo '<script>'
            . 'document.getElementById("import_progress_status").innerHTML = "' . esc_html__('Setup menus ...', 'grace-church') .'";'
            . '</script>';
        echo '<br><b>'. esc_html__('Setup menus ...', 'grace-church').'</b><br>'; flush();
        $locations = get_theme_mod( 'nav_menu_locations' );
        $menus = wp_get_nav_menus();
        if (is_array($menus) && count($menus) > 0) {
            foreach ($menus as $menu) {
                if (is_array($this->options['menus']) && count($this->options['menus']) > 0) {
                    foreach ($this->options['menus'] as $loc=>$name) {
                        if ($menu->name == $name)
                            $locations[$loc] = $menu->term_id;
                    }
                }
            }
            set_theme_mod( 'nav_menu_locations', $locations );
        }
    }


    // Import theme options
    function import_theme_options() {
        if (empty($this->options['file_with_options'])) return;
        echo '<script>'
            . 'document.getElementById("import_progress_status").innerHTML = "' . esc_html__('Import Theme Options ...', 'grace-church') .'";'
            . '</script>';
        echo '<br><b>'. esc_html__('Import Theme Options ...', 'grace-church').'</b><br>'; flush();
        $theme_options_txt = grace_church_fgc(grace_church_get_file_dir($this->options['file_with_options']));
        $data = unserialize( $theme_options_txt );
        // Replace upload url in options
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $k=>$v) {
                if (is_array($v) && count($v) > 0) {
                    foreach ($v as $k1=>$v1) {
                        $v[$k1] = $this->replace_uploads($v1);
                    }
                } else
                    $v = $this->replace_uploads($v);
                if ($k == 'mega_main_menu_options' && isset($v['last_modified']))
                    $v['last_modified'] = time()+30;
                update_option( $k, $v );
            }
        }
        grace_church_load_main_options();
    }


    // Import post meta options
    function import_postmeta() {
        if (empty($this->options['file_with_postmeta'])) return;
        $theme_options_txt = grace_church_fgc(grace_church_get_file_dir($this->options['file_with_postmeta']));
        $data = unserialize( $theme_options_txt );
        // Replace upload url in options
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $k=>$v) {
                if (is_array($v) && count($v) > 0) {
                    foreach ($v as $k1=>$v1) {
                        $v[$k1] = $this->replace_uploads($v1);
                    }
                }
                update_post_meta( $k, $v['key'], $v['value'] );
            }
        }
    }


    // Import widgets
    function import_widgets() {
        if (empty($this->options['file_with_widgets'])) return;
        echo '<script>'
            . 'document.getElementById("import_progress_status").innerHTML = "' . esc_html__('Import Widgets ...', 'grace-church') .'";'
            . '</script>';
        echo '<br><b>'. esc_html__('Import Widgets ...', 'grace-church').'</b><br>'; flush();
        $widgets_txt = grace_church_fgc(grace_church_get_file_dir($this->options['file_with_widgets']));
        $data = unserialize( $widgets_txt );
        if (is_array($data) && count($data) > 0) {
            foreach ($data as $k=>$v) {
                update_option( $k, $this->replace_uploads($v) );
            }
        }
    }


    // Import Booking Calendar
    function import_booking() {
        if (!grace_church_exists_booking()) return;
        if (empty($this->options['file_with_booking'])) return;
        echo '<script>'
            . 'document.getElementById("import_progress_status").innerHTML = "' . esc_html__('Import Booking Calendar ...', 'grace-church') .'";'
            . '</script>';
        echo '<br><b>'. esc_html__('Import Booking Calendar ...', 'grace-church').'</b><br>'; flush();
        $booking_txt = grace_church_fgc(grace_church_get_file_dir($this->options['file_with_booking']));
        $data = unserialize( $booking_txt );
        if (is_array($data) && count($data) > 0) {
            global $wpdb;
            foreach ($data as $table=>$rows) {
                $values = '';
                $fields = '';
                if (is_array($rows) && count($rows) > 0) {
                    foreach ($rows as $row) {
                        $f = '';
                        $v = '';
                        if (is_array($row) && count($row) > 0) {
                            foreach ($row as $field => $value) {
                                $f .= ($f ? ',' : '') . "'" . esc_sql($field) . "'";
                                $v .= ($v ? ',' : '') . "'" . esc_sql($value) . "'";
                            }
                        }
                        if ($fields == '') $fields = '(' . $f . ')';
                        $values .= ($values ? ',' : '') . '(' . $v . ')';
                    }
                }
                // Attention! All items in the variable $values escaped on the loop above - esc_sql($value)
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix . $table));
                $q = "INSERT INTO ".esc_sql($wpdb->prefix . $table)." VALUES {$values}";
                $wpdb->query($q);
            }
        }
    }


    //*+ Import Content TimeLine
    function import_timeline() {
        if (!is_plugin_active('content_timeline/content_timeline.php')) return;
        if (empty($this->options['file_with_timeline'])) return;
        echo '<script>'
            . 'document.getElementById("import_progress_status").innerHTML = "' . esc_html__('Import Content TimeLine ...', 'grace-church') .'";'
            . '</script>';
        echo '<br><b>'. esc_html__('Import Content TimeLine ...', 'grace-church').'</b><br>'; flush();
        $timeline_txt = grace_church_fgc(grace_church_get_file_dir($this->options['file_with_timeline']));
        $data = unserialize( $timeline_txt );
        if (is_array($data) && count($data) > 0) {
            global $wpdb;
            foreach ($data as $table=>$rows) {
                $values = '';
                $fields = '';
                if (is_array($rows) && count($rows) > 0) {
                    foreach ($rows as $row) {
                        $f = '';
                        $v = '';
                        if (is_array($row) && count($row) > 0) {
                            foreach ($row as $field => $value) {
                                $f .= ($f ? ',' : '') . "'" . esc_sql($field) . "'";
                                $v .= ($v ? ',' : '') . "'" . esc_sql($value) . "'";
                            }
                        }
                        if ($fields == '') $fields = '(' . $f . ')';
                        $values .= ($values ? ',' : '') . '(' . $v . ')';
                    }
                }
                // Attention! All items in the variable $values escaped on the loop above - esc_sql($value)
                $res = $wpdb->query("TRUNCATE TABLE " . esc_sql($wpdb->prefix . $table));
                $q = "INSERT INTO ".esc_sql($wpdb->prefix . $table)." VALUES {$values}";
                $wpdb->query($q);
            }
        }
    }


    // Import sliders
    function import_sliders() {
        // Revolution Sliders
        if (grace_church_exists_revslider() && file_exists(WP_PLUGIN_DIR.'/revslider/revslider.php')) {
            require_once(WP_PLUGIN_DIR.'/revslider/revslider.php');
            $dir = grace_church_get_folder_dir($this->options['folder_with_revsliders']);
            if ( is_dir($dir) ) {
                $hdir = @opendir( $dir );
                if ( $hdir ) {
                    echo '<script>'
                        . 'document.getElementById("import_progress_status").innerHTML = "' . esc_html__('Import Revolution sliders ...', 'grace-church') .'";'
                        . '</script>';
                    echo '<br><b>'. esc_html__('Import Revolution sliders ...', 'grace-church').'</b><br>'; flush();
                    $slider = new RevSlider();
                    $counter = 0;
                    while (($file = readdir( $hdir ) ) !== false ) {
                        $counter++;
                        if ($counter <= $this->last_slider) continue;
                        $pi = pathinfo( ($dir) . '/' . ($file) );
                        if ( substr($file, 0, 1) == '.' || is_dir( ($dir) . '/' . ($file) ) || $pi['extension']!='zip' )
                            continue;
                        if ($this->options['debug']) printf( esc_html__('Slider "%s":', 'grace-church'), $file);
                        if (!is_array($_FILES)) $_FILES = array();
                        $_FILES["import_file"] = array("tmp_name" => ($dir) . '/' . ($file));
                        $response = $slider->importSliderFromPost();
                        if ($response["success"] == false) {
                            if ($this->options['debug']) echo ' '. esc_html__('import error:', 'grace-church').'<br>'.grace_church_debug_dump_var($response);
                        } else {
                            if ($this->options['debug']) echo ' '. esc_html__('imported', 'grace-church').'<br>';
                        }
                        flush();
                        break;
                    }
                    @closedir( $hdir );
                    // Write last slider into log
                    grace_church_fpc($this->import_log, $file ? '0|100|'.intval($counter) : '');
                    $this->last_slider = $file ? $counter : 0;
                }
            }
        } else {
            if ($this->options['debug']) { printf( esc_html__('Can not locate Revo plugin: %s', 'grace-church'), WP_PLUGIN_DIR.'/revslider/revslider.php<br>'); flush(); }
        }
    }


    // Import Essential Grids
    function import_essgrids() {
        if (grace_church_exists_essgrids()) {
            $dir = grace_church_get_folder_dir($this->options['folder_with_essgrids']);
            if ( is_dir($dir) ) {
                $hdir = @opendir( $dir );
                if ( $hdir ) {
                    echo '<br><b>'. esc_html__('Import Essential Grids ...', 'grace-church').'</b><br>'; flush();
                    while (($file = readdir( $hdir ) ) !== false ) {
                        $pi = pathinfo( ($dir) . '/' . ($file) );
                        if ( substr($file, 0, 1) == '.' || is_dir( ($dir) . '/' . ($file) ) || $pi['extension']!='json' )
                            continue;
                        if ($this->options['debug']) printf( esc_html__('Ess.Grid "%s":', 'grace-church'), $file);
                        try{
                            $im = new Essential_Grid_Import();
                            $data = json_decode(grace_church_fgc(($dir) . '/' . ($file)), true);
                            // Prepare arrays with overwrite flags
                            $tmp = array();
                            if (is_array($data) && count($data) > 0) {
                                foreach ($data as $k=>$v) {
                                    if ($k=='grids') {			$name = 'grids'; $name_1= 'grid'; $name_id='id'; }
                                    else if ($k=='skins') {		$name = 'skins'; $name_1= 'skin'; $name_id='id'; }
                                    else if ($k=='elements') {	$name = 'elements'; $name_1= 'element'; $name_id='id'; }
                                    else if ($k=='navigation-skins') {	$name = 'navigation-skins'; $name1= 'nav-skin'; $name_id='id'; }
                                    else if ($k=='punch-fonts') {	$name = 'punch-fonts'; $name1= 'punch-fonts'; $name_id='handle'; }
                                    else if ($k=='custom-meta') {	$name = 'custom-meta'; $name1= 'custom-meta'; $name_id='handle'; }
                                    if ($k=='global-css') {
                                        $tmp['import-global-styles'] = "on";
                                        $tmp['global-styles-overwrite'] = "append";
                                    } else {
                                        $tmp['import-'.$name] = "true";
                                        $tmp['import-'.$name.'-'.$name_id] = array();
                                        if (is_array($v) && count($v) > 0) {
                                            foreach ($v as $v1) {
                                                $tmp['import-'.$name.'-'.$name_id][] = $v1[$name_id];
                                                $tmp[$name_1.'-overwrite-'.$name_id] = 'append';
                                            }
                                        }
                                    }
                                }
                            }
                            $im->set_overwrite_data($tmp); //set overwrite data global to class

                            $skins = @$data['skins'];
                            if (!empty($skins) && is_array($skins)){
                                $skins_ids = @$tmp['import-skins-id'];
                                $skins_imported = $im->import_skins($skins, $skins_ids);
                            }

                            $navigation_skins = @$data['navigation-skins'];
                            if (!empty($navigation_skins) && is_array($navigation_skins)){
                                $navigation_skins_ids = @$tmp['import-navigation-skins-id'];
                                $navigation_skins_imported = $im->import_navigation_skins(@$navigation_skins, $navigation_skins_ids);
                            }

                            $grids = @$data['grids'];
                            if (!empty($grids) && is_array($grids)){
                                $grids_ids = @$tmp['import-grids-id'];
                                $grids_imported = $im->import_grids($grids, $grids_ids);
                            }

                            $elements = @$data['elements'];
                            if (!empty($elements) && is_array($elements)){
                                $elements_ids = @$tmp['import-elements-id'];
                                $elements_imported = $im->import_elements(@$elements, $elements_ids);
                            }

                            $custom_metas = @$data['custom-meta'];
                            if (!empty($custom_metas) && is_array($custom_metas)){
                                $custom_metas_handle = @$tmp['import-custom-meta-handle'];
                                $custom_metas_imported = $im->import_custom_meta($custom_metas, $custom_metas_handle);
                            }

                            $custom_fonts = @$data['punch-fonts'];
                            if (!empty($custom_fonts) && is_array($custom_fonts)){
                                $custom_fonts_handle = @$tmp['import-punch-fonts-handle'];
                                $custom_fonts_imported = $im->import_punch_fonts($custom_fonts, $custom_fonts_handle);
                            }

                            if (@$tmp['import-global-styles'] == 'on'){
                                $global_css = @$data['global-css'];
                                $global_styles_imported = $im->import_global_styles($tglobal_css);
                            }

                            if ($this->options['debug']) echo ' '. esc_html__('imported', 'grace-church').'<br>';

                        } catch (Exception $d) {

                            if ($this->options['debug']) echo ' '. esc_html__('import error:', 'grace-church').'<br>'.ddo($response);

                        }

                        flush();
                        break;
                    }
                    @closedir( $hdir );
                }
            }
        } else {
            if ($this->options['debug']) { printf( esc_html__('Can not locate Essential Grid plugin: %s', 'grace-church'), EG_PLUGIN_PATH.'/essential-grid.php<br>'); flush(); }
        }
    }


    // Replace uploads dir to new url
    function replace_uploads($str) {
        /*
        if (is_array($str) && count($str) > 0) {
            foreach ($str as $k=>$v) {
                $str[$k] = $this->replace_uploads($v);
            }
        } else if (is_string($str)) {
            while (($pos = grace_church_strpos($str, "/{$this->options['uploads_folder']}/"))!==false) {
                $pos0 = $pos;
                while ($pos0) {
                    if (grace_church_substr($str, $pos0, 5)=='http:')
                        break;
                    $pos0--;
                }
                $str = ($pos0 > 0 ? grace_church_substr($str, 0, $pos0) : '') . ($this->uploads_url) . grace_church_substr($str, $pos+grace_church_strlen($this->options['uploads_folder'])+1);
            }
        }
        return $str;
        */
        return grace_church_replace_uploads_url($str, $this->options['uploads_folder']);
    }


    // Replace uploads dir to imports then export data
    function prepare_uploads($str) {
        if ($this->options['uploads_folder']=='uploads') return $str;
        if (is_array($str) && count($str) > 0) {
            foreach ($str as $k=>$v) {
                $str[$k] = $this->prepare_uploads($v);
            }
        } else if (is_string($str)) {
            $str = str_replace('/uploads/', "/{$this->options['uploads_folder']}/", $str);
        }
        return $str;
    }

    // Replace dev domain to demo domain then export data
    function prepare_domains($str) {
        if ($this->options['domain_dev']==$this->options['domain_demo']) return $str;
        if (is_array($str) && count($str) > 0) {
            foreach ($str as $k=>$v) {
                $str[$k] = $this->prepare_domains($v);
            }
        } else if (is_string($str)) {
            $str = str_replace($this->options['domain_dev'], $this->options['domain_demo'], $str);
        }
        return $str;
    }
}
?>