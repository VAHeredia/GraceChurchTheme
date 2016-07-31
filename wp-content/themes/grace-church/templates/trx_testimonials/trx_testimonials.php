<?php
// Autoload layouts in this folder
$name = pathinfo(__FILE__);
grace_church_autoload_folder( 'templates/'.trim($name['filename']) );
?>