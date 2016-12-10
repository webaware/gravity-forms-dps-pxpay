<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
* upgrade from v1.x add-on
*/
class GFDpsPxPayUpdateV1 {

	private $slug;
	private $wasUseTest;

	const OLD_SETTINGS_NAME = 'gfdpspxpay_plugin';

	public function __construct($slug) {
		$this->slug = $slug;

		$old_settings = get_option(self::OLD_SETTINGS_NAME);

		$this->wasUseTest = !empty($old_settings['useTest']);

		// do settings need updating?
		if (empty($old_settings['upgraded_settings'])) {
			$this->updateSettings($old_settings);
		}

		// do feeds need updating?
		if (empty($old_settings['upgraded_feeds'])) {
			$this->setupUpdateFeeds();
		}
	}

	/**
	* upgrade settings from the old add-on
	* @param array $old_settings
	*/
	protected function updateSettings($old_settings) {
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
		update_option(self::OLD_SETTINGS_NAME, $old_settings);
	}

	/**
	* TODO: set up the process for updating feeds
	*/
	protected function setupUpdateFeeds() {
	}

}
