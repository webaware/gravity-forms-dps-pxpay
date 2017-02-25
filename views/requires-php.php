<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="error">
	<p><?php printf(__('Gravity Forms DPS PxPay requires PHP %1$s or higher; your website has PHP %2$s which is <a target="_blank" href="%3$s">old, obsolete, and unsupported</a>.', 'gravity-forms-dps-pxpay'),
			esc_html($php_min), esc_html(PHP_VERSION), 'https://secure.php.net/supported-versions.php'); ?></p>
	<p><?php printf(__('Please upgrade your website hosting. At least PHP %s is recommended.', 'gravity-forms-dps-pxpay'), '7.0'); ?></p>
</div>
