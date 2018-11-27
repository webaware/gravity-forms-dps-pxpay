<?php
// error message displayed on failure of payment request
// replaces confirmation text

if (!defined('ABSPATH')) {
	exit;
}
?>
<?php echo $anchor; ?>
<div id="gform_confirmation_wrapper_<?php echo esc_attr($form['id']); ?>" class="gform_confirmation_wrapper <?php echo esc_attr($cssClass); ?>">
	<div id="gform_confirmation_message_<?php echo esc_attr($form['id']); ?>" class="gform_confirmation_message_<?php echo esc_attr($form['id']); ?> gform_confirmation_message">
	<p class="dps_error"><strong><?php echo esc_html($error_msg); ?></strong></p>
	<div class="total"><?php printf(_x('Payment amount: %s', 'retry payment', 'gravity-forms-dps-pxpay'), GFCommon::to_money($submission_data['payment_amount'])); ?></div>
	<a class="button gfdpspxpay-retry-payment-button" href="<?php echo esc_url($retry_link); ?>"><?php esc_html_e('Retry Payment', 'gravity-forms-dps-pxpay'); ?></a>
	</div>
</div>
