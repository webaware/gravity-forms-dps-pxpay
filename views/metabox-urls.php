<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

		<p><label for="_gfdpspxpay_url_fail">URL to redirect to on transaction failure:</label><br />
			<input type="url" class="large-text" name="_gfdpspxpay_url_fail" id="_gfdpspxpay_url_fail" value="<?php echo esc_attr($feed->UrlFail); ?>" /></p>

		<p><em>Please note: standard Gravity Forms submission logic applies if the DPS transaction is successful.</em></p>

