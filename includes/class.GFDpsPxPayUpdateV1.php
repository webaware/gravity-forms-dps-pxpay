<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
* upgrade from v1.x add-on
*/
class GFDpsPxPayUpdateV1 {

	private $slug;
	private $old_settings_name;
	private $wasUseTest;

	private $update_feeds;
	private $update_transactions;

	const POST_TYPE_FEED			= 'gfdpspxpay_feed';
	const META_CONVERTED			= '_gfdpspxpay_converted';

	/**
	* @param string $slug
	* @param string $old_settings_name
	*/
	public function __construct($slug, $old_settings_name) {
		$this->slug					= $slug;
		$this->old_settings_name	= $old_settings_name;

		$old_settings				= get_option($old_settings_name);

error_log(__METHOD__ . ": old_settings =\n" . print_r($old_settings,1));

		$this->wasUseTest			= !empty($old_settings['useTest']);

		// do settings need updating?
		if (empty($old_settings['upgraded_settings'])) {
			$this->updateSettings();
		}

		// only run feed update process if in the admin
		if (!is_admin()) {
			return;
		}

		// only bother admins / plugin installers / option setters with this stuff
		if (!current_user_can('activate_plugins') && !current_user_can('manage_options')) {
			return;
		}

		// do feeds need updating?
		if (empty($old_settings['upgraded_feeds'])) {
			$this->setupUpdateFeeds();
		}

		// do transactions need updating?
		if (empty($old_settings['upgraded_txns'])) {
			$this->setupUpdateTransactions();
		}
	}

	/**
	* upgrade settings from the old add-on
	* @param array $old_settings
	*/
	protected function updateSettings() {
		$old_settings = get_option($this->old_settings_name);

		$settings = array(
			'userID'	=> rgar($old_settings, 'userID',  ''),
			'userKey'	=> rgar($old_settings, 'userKey', ''),
			'testEnv'	=> rgar($old_settings, 'testEnv', 'UAT'),
			'testID'	=> rgar($old_settings, 'testID',  ''),
			'testKey'	=> rgar($old_settings, 'testKey', ''),
		);

		// if add-on was installed before the test environment setting became available, set default environment to SEC for backwards compatibility
		if (isset($old_settings['userID']) && !isset($old_settings['testEnv'])) {
			$settings['testEnv'] = 'SEC';
		}

		update_option("gravityformsaddon_{$this->slug}_settings", $settings);

		$old_settings['upgraded_settings'] = 1;
		update_option($this->old_settings_name, $old_settings);
	}

	/**
	* set up the process for updating feeds
	*/
	protected function setupUpdateFeeds() {
		// check for feed posts that haven't been converted
		$feeds = $this->getFeedsUnconverted(true);

		if (empty($feeds)) {
			// nothing found, so mark it off and don't check again
			$old_settings = get_option($this->old_settings_name);
			$old_settings['upgraded_feeds'] = 1;
			update_option($this->old_settings_name, $old_settings);
		}
		else {
			add_action('admin_notices', array($this, 'showUpdate'));
			$this->update_feeds = count($feeds);
		}
	}

	/**
	* set up the process for updating transactions
	*/
	protected function setupUpdateTransactions() {
		// check for transactions that haven't been converted
		$txns = $this->getTxnUnconverted(true);

error_log(__METHOD__ . ": txns =\n" . print_r($txns,1));

		if (empty($txns)) {
			// nothing found, so mark it off and don't check again
			$old_settings = get_option($this->old_settings_name);
			$old_settings['upgraded_txns'] = 1;
			update_option($this->old_settings_name, $old_settings);
		}
		else {
			add_action('admin_notices', array($this, 'showUpdate'));
			$this->update_transactions = count($txns);
		}
	}

	/**
	* get a list of old feeds that haven't been converted yet
	* @param bool $only_ids
	* @return array
	*/
	protected function getFeedsUnconverted($only_ids = false) {
		$args = array(
			'post_type'			=> self::POST_TYPE_FEED,
			'posts_per_page'	=> -1,
			'meta_query'		=> array(
					array(
						'key'			=> self::META_CONVERTED,
						'compare'		=> 'NOT EXISTS',
					),
			),
		);

		if ($only_ids) {
			$args['fields'] = 'ids';
		}

		$query = new WP_Query($args);

		return $query->posts;
	}

	/**
	* get a list of old transactions that haven't been converted yet
	* @param bool $only_ids
	* @return array
	*/
	protected function getTxnUnconverted($only_ids = false) {
		// TODO: get transactions, not feeds!
		$args = array(
			'post_type'			=> self::POST_TYPE_FEED,
			'posts_per_page'	=> -1,
			'meta_query'		=> array(
					array(
						'key'			=> self::META_CONVERTED,
						'compare'		=> 'NOT EXISTS',
					),
			),
		);

		if ($only_ids) {
			$args['fields'] = 'ids';
		}

		$query = new WP_Query($args);

		return $query->posts;
	}

	/**
	* show feed update prompt
	*/
	public function showUpdate() {
		$min = SCRIPT_DEBUG ? '' : '.min';
		$ver = SCRIPT_DEBUG ? time() : GFDPSPXPAY_PLUGIN_VERSION;

		wp_enqueue_script('gfdpspxpay_updatev1', plugins_url("js/admin-update-v1$min.js", GFDPSPXPAY_PLUGIN_FILE), array('jquery'), $ver, true);

		include GFDPSPXPAY_PLUGIN_ROOT . 'views/admin-update-v1.php';
	}

}
