<?php
namespace webaware\gf_dpspxpay;

// error message displayed on failure of payment request
// replaces confirmation text

if (!defined('ABSPATH')) {
	exit;
}
?>
<?= $anchor; ?>
<div id="gform_confirmation_wrapper_<?= esc_attr($form['id']); ?>" class="gform_confirmation_wrapper <?= esc_attr($cssClass); ?>">
	<div id="gform_confirmation_message_<?= esc_attr($form['id']); ?>" class="gform_confirmation_message_<?= esc_attr($form['id']); ?> gform_confirmation_message">
		<p class="gfdpspxpay-failure-message"><strong><?= esc_html($error_msg); ?></strong></p>
		<div class="total"><?php printf(_x('Payment amount: %s', 'retry payment', 'gravity-forms-dps-pxpay'), \GFCommon::to_money($submission_data['payment_amount'])); ?></div>
		<p class="gfdpspxpay-failure-actions">
			<a class="button gfdpspxpay-retry-payment-button" href="<?= esc_url($retry_link); ?>"><?= esc_html_x('Retry Payment', 'retry payment message', 'gravity-forms-dps-pxpay'); ?></a>
			<a class="button gfdpspxpay-cancel-payment-button" href="<?= esc_url($cancel_link); ?>"><?= esc_html_x('Cancel Payment', 'retry payment message', 'gravity-forms-dps-pxpay'); ?></a>
		</p>
	</div>
</div>
