<?php
// this is the uninstall handler
// include unregister_setting, delete_option, and other uninstall behavior here

require_once('wp-plugin-retina.php');

function uninstall_options($name) {
    unregister_setting("${name}_group", $name);
    wp_plugin_retina::remove_options($name);
	global $wpdb;
	$wpdb->query("drop table '".$wpdb->prefix."CAPTCHA_RetinaPost'");
}

uninstall_options('Retinapost_options');

?>