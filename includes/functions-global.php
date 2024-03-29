<?php
// NB: Minimum PHP version for this file is 5.3! No short array notation, no namespaces!

if (!defined('ABSPATH')) {
	exit;
}

/**
* maybe show notice of minimum PHP version failure
*/
function gf_dpspxpay_fail_php_version() {
	if (gf_dpspxpay_can_show_admin_notices()) {
		gf_dpspxpay_load_text_domain();
		include GFDPSPXPAY_PLUGIN_ROOT . 'views/requires-php.php';
	}
}

/**
* test whether we can show admin-related notices
* @return bool
*/
function gf_dpspxpay_can_show_admin_notices() {
	global $pagenow;

	// only on specific pages
	$is_gf_page = class_exists('GFForms', false) ? (bool) GFForms::get_page() : false;
	if ($pagenow !== 'plugins.php' && !$is_gf_page) {
		return false;
	}

	// only bother admins / plugin installers / option setters with this stuff
	if (!current_user_can('activate_plugins') && !current_user_can('manage_options')) {
		return false;
	}

	return true;
}

/**
* load text translations
*/
function gf_dpspxpay_load_text_domain() {
	load_plugin_textdomain('gravity-forms-dps-pxpay');
}

/**
* replace link placeholders with an external link
* @param string $template
* @param string $url
* @return string
*/
function gf_dpspxpay_external_link($template, $url) {
	$search = array(
		'{{a}}',
		'{{/a}}',
	);
	$replace = array(
		sprintf('<a rel="noopener" target="_blank" href="%s">', esc_url($url)),
		'</a>',
	);
	return str_replace($search, $replace, $template);
}
