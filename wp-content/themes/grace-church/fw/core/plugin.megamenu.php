<?php
/* Mega Main Menu support functions
------------------------------------------------------------------------------- */

// Check if MegaMenu installed and activated
if ( !function_exists( 'grace_church_exists_megamenu' ) ) {
	function grace_church_exists_megamenu() {
		return class_exists('mega_main_init');
	}
}
?>