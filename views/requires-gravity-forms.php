<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="notice notice-error">
	<p>
		<?= gf_dpspxpay_external_link(
				esc_html__('Gravity Forms DPS PxPay requires {{a}}Gravity Forms{{/a}} to be installed and activated.', 'gravity-forms-dps-pxpay'),
				'https://webaware.com.au/get-gravity-forms'
			); ?>
	</p>
</div>
