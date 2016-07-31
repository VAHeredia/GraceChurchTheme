<?php 
if (in_array('contact_info', $top_panel_top_components) && ($contact_info=trim(grace_church_get_custom_option('contact_info')))!='') {
	?>
	<div class="top_panel_top_contact_area">
		<?php echo force_balance_tags($contact_info); ?>
	</div>
	<?php
}
?>

<?php
if (in_array('open_hours', $top_panel_top_components) && ($open_hours=trim(grace_church_get_custom_option('contact_open_hours')))!='') {
	?>
	<div class="top_panel_top_open_hours icon-clock"><?php echo force_balance_tags($open_hours); ?></div>
	<?php
}
?>

<div class="top_panel_top_user_area">
	<?php
	if (in_array('socials', $top_panel_top_components) && grace_church_get_custom_option('show_socials')=='yes') {
		?>
		<div class="top_panel_top_socials">
			<?php echo grace_church_sc_socials(array('size'=>'tiny')); ?>
		</div>
		<?php
	}

	if (in_array('search', $top_panel_top_components) && grace_church_get_custom_option('show_search')=='yes') {
		?>
		<div class="top_panel_top_search"><?php echo grace_church_sc_search(array('state'=>'fixed')); ?></div>
		<?php
	}

	global $GRACE_CHURCH_GLOBALS;
	if (empty($GRACE_CHURCH_GLOBALS['menu_user']))
		$GRACE_CHURCH_GLOBALS['menu_user'] = grace_church_get_nav_menu('menu_user');
	if (empty($GRACE_CHURCH_GLOBALS['menu_user'])) {
		?>
		<ul id="menu_user" class="menu_user_nav">
		<?php
	} else {
		$menu = grace_church_substr($GRACE_CHURCH_GLOBALS['menu_user'], 0, grace_church_strlen($GRACE_CHURCH_GLOBALS['menu_user'])-5);
		$pos = grace_church_strpos($menu, '<ul');
		if ($pos!==false) $menu = grace_church_substr($menu, 0, $pos+3) . ' class="menu_user_nav"' . grace_church_substr($menu, $pos+3);
		echo str_replace('class=""', '', $menu);
	}
	

	if (in_array('currency', $top_panel_top_components) && grace_church_is_woocommerce_page() && grace_church_get_custom_option('show_currency')=='yes') {
		?>
		<li class="menu_user_currency">
			<a href="#">$</a>
			<ul>
				<li><a href="#"><b>&#36;</b> <?php esc_html_e('Dollar', 'grace-church'); ?></a></li>
				<li><a href="#"><b>&euro;</b> <?php esc_html_e('Euro', 'grace-church'); ?></a></li>
				<li><a href="#"><b>&pound;</b> <?php esc_html_e('Pounds', 'grace-church'); ?></a></li>
			</ul>
		</li>
		<?php
	}

	if (in_array('language', $top_panel_top_components) && grace_church_get_custom_option('show_languages')=='yes' && function_exists('icl_get_languages')) {
		$languages = icl_get_languages('skip_missing=1');
		if (!empty($languages) && is_array($languages)) {
			$lang_list = '';
			$lang_active = '';
			foreach ($languages as $lang) {
				$lang_title = esc_attr($lang['translated_name']);	//esc_attr($lang['native_name']);
				if ($lang['active']) {
					$lang_active = $lang_title;
				}
				$lang_list .= "\n"
					.'<li><a rel="alternate" hreflang="' . esc_attr($lang['language_code']) . '" href="' . esc_url(apply_filters('WPML_filter_link', $lang['url'], $lang)) . '">'
						.'<img src="' . esc_url($lang['country_flag_url']) . '" alt="' . esc_attr($lang_title) . '" title="' . esc_attr($lang_title) . '" />'
						. ($lang_title)
					.'</a></li>';
			}
			?>
			<li class="menu_user_language">
				<a href="#"><span><?php echo ($lang_active); ?></span></a>
				<ul><?php echo ($lang_list); ?></ul>
			</li>
			<?php
		}
	}

	if (in_array('bookmarks', $top_panel_top_components) && grace_church_get_custom_option('show_bookmarks')=='yes') {
		// Load core messages
		grace_church_enqueue_messages();
		?>
		<li class="menu_user_bookmarks"><a href="#" class="bookmarks_show icon-star" title="<?php esc_html_e('Show bookmarks', 'grace-church'); ?>"><?php esc_html_e('Bookmarks', 'grace-church'); ?></a>
		<?php 
			$list = grace_church_get_value_gpc('grace_church_bookmarks', '');
			if (!empty($list)) $list = json_decode($list, true);
			?>
			<ul class="bookmarks_list">
				<li><a href="#" class="bookmarks_add icon-star-empty" title="<?php esc_html_e('Add the current page into bookmarks', 'grace-church'); ?>"><?php esc_html_e('Add bookmark', 'grace-church'); ?></a></li>
				<?php 
				if (!empty($list) && is_array($list)) {
					foreach ($list as $bm) {
						echo '<li><a href="'.esc_url($bm['url']).'" class="bookmarks_item">'.($bm['title']).'<span class="bookmarks_delete icon-cancel" title="'. esc_html__('Delete this bookmark', 'grace-church').'"></span></a></li>';
					}
				}
				?>
			</ul>
		</li>
		<?php 
	}

	if (in_array('login', $top_panel_top_components) && grace_church_get_custom_option('show_login')=='yes') {
		if ( !is_user_logged_in() ) {
			// Load core messages
			grace_church_enqueue_messages();
			// Load Popup engine
			grace_church_enqueue_popup();
			// Anyone can register ?
			?>
			<li class="menu_user_register"><a href="#popup_registration" class="popup_link popup_register_link icon-pencil"><?php esc_html_e('Register', 'grace-church'); ?></a><?php
				if (grace_church_get_theme_option('show_login')=='yes') {
					require_once( grace_church_get_file_dir('templates/headers/_parts/register.php') );
				}?></li>
			<li class="menu_user_login"><a href="#popup_login" class="popup_link popup_login_link icon-user"><?php esc_html_e('Login', 'grace-church'); ?></a><?php
				if (grace_church_get_theme_option('show_login')=='yes') {
					require_once( grace_church_get_file_dir('templates/headers/_parts/login.php') );
				}?></li>
			<?php 
		} else {
			$current_user = wp_get_current_user();
			?>
			<li class="menu_user_controls">
				<a href="#"><?php
					$user_avatar = '';
					if ($current_user->user_email) $user_avatar = get_avatar($current_user->user_email, 16*min(2, max(1, grace_church_get_theme_option("retina_ready"))));
					if ($user_avatar) {
						?><span class="user_avatar"><?php echo ($user_avatar); ?></span><?php
					}?><span class="user_name"><?php echo ($current_user->display_name); ?></span></a>
				<ul>
					<?php if (current_user_can('publish_posts')) { ?>
					<li><a href="<?php echo esc_url( home_url('/') ); ?>wp-admin/post-new.php?post_type=post" class="icon icon-doc"><?php esc_html_e('New post', 'grace-church'); ?></a></li>
					<?php } ?>
					<li><a href="<?php echo get_edit_user_link(); ?>" class="icon icon-cog"><?php esc_html_e('Settings', 'grace-church'); ?></a></li>
				</ul>
			</li>
			<li class="menu_user_logout"><a href="<?php echo wp_logout_url( home_url('/') ); ?>" class="icon icon-logout"><?php esc_html_e('Logout', 'grace-church'); ?></a></li>
			<?php 
		}
	}

    if ( grace_church_get_custom_option('show_donate_button')=='yes' && class_exists('PayPalDonations') ) {
            ?>
            <li class="menu_user_donate">
                <?php echo do_shortcode('[paypal-donation]'); ?>
            </li>
        <?php
    }

	if (in_array('cart', $top_panel_top_components) && grace_church_exists_woocommerce() && (grace_church_is_woocommerce_page() && grace_church_get_custom_option('show_cart')=='shop' || grace_church_get_custom_option('show_cart')=='always') && !(is_checkout() || is_cart() || defined('WOOCOMMERCE_CHECKOUT') || defined('WOOCOMMERCE_CART'))) {
		?>
		<li class="menu_user_cart">
			<?php require_once( grace_church_get_file_dir('templates/headers/_parts/contact-info-cart.php') ); ?>
		</li>
		<?php
	}
	?>

	</ul>

</div>