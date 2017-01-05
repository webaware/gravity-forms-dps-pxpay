<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
* class for admin screens
*/
class GFDpsPxPayAdmin {

	public function __construct() {
		add_action('admin_notices', array($this, 'checkPrerequisites'));
		add_filter('plugin_row_meta', array($this, 'pluginDetailsLinks'), 10, 2);

		add_action('wp_ajax_gfdpspxpay_upgradev1', array('GFDpsPxPayUpdateV1', 'ajaxUpgrade'));
	}

	/**
	* check for required prerequisites, tell admin if any are missing
	*/
	public function checkPrerequisites() {
		// only on specific pages
		global $pagenow;
		if ($pagenow !== 'plugins.php' && !self::isGravityFormsPage()) {
			return;
		}

		// only bother admins / plugin installers / option setters with this stuff
		if (!current_user_can('activate_plugins') && !current_user_can('manage_options')) {
			return;
		}

		// need at least PHP 5.2.11 for libxml_disable_entity_loader()
		$php_min = '5.2.11';
		if (version_compare(PHP_VERSION, $php_min, '<')) {
			include GFDPSPXPAY_PLUGIN_ROOT . 'views/requires-php.php';
		}

		// need these PHP extensions too
		$prereqs = array('libxml', 'SimpleXML', 'xmlwriter');
		$missing = array();
		foreach ($prereqs as $ext) {
			if (!extension_loaded($ext)) {
				$missing[] = $ext;
			}
		}
		if (!empty($missing)) {
			include GFDPSPXPAY_PLUGIN_ROOT . 'views/requires-extensions.php';
		}

		// and of course, we need Gravity Forms
		if (!class_exists('GFCommon', false)) {
			include GFDPSPXPAY_PLUGIN_ROOT . 'views/requires-gravity-forms.php';
		}
		elseif (!GFDpsPxPayPlugin::hasMinimumGF()) {
			include GFDPSPXPAY_PLUGIN_ROOT . 'views/requires-gravity-forms-upgrade.php';
		}
	}

	/**
	* test if admin page is a Gravity Forms page
	* @return bool
	*/
	protected static function isGravityFormsPage() {
		$is_gf = false;
		if (class_exists('GFForms', false)) {
			$is_gf = !!(GFForms::get_page());
		}

		return $is_gf;
	}

	/**
	* add plugin details links
	*/
	public static function pluginDetailsLinks($links, $file) {
		if ($file === GFDPSPXPAY_PLUGIN_NAME) {
			$links[] = sprintf('<a href="https://wordpress.org/support/plugin/gravity-forms-dps-pxpay" target="_blank">%s</a>', _x('Get help', 'plugin details links', 'gravity-forms-dps-pxpay'));
			$links[] = sprintf('<a href="https://wordpress.org/plugins/gravity-forms-dps-pxpay/" target="_blank">%s</a>', _x('Rating', 'plugin details links', 'gravity-forms-dps-pxpay'));
			$links[] = sprintf('<a href="https://translate.wordpress.org/projects/wp-plugins/gravity-forms-dps-pxpay" target="_blank">%s</a>', _x('Translate', 'plugin details links', 'gravity-forms-dps-pxpay'));
			$links[] = sprintf('<a href="https://shop.webaware.com.au/donations/?donation_for=Gravity+Forms+DPS+PxPay" target="_blank">%s</a>', _x('Donate', 'plugin details links', 'gravity-forms-dps-pxpay'));
		}

		return $links;
	}

}
