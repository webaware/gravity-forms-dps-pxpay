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
	<p class="dps_error"><strong><?php esc_html_e('There was an error with your payment. Please try again.', 'gravity-forms-dps-pxpay'); ?></strong></p>
									   <div class="total">Amount owing: <?php echo GFCommon::to_money($submission_data['payment_amount'])  ?> </div>
									   <a class="button dps_retry_payment_button" href="?<?php echo $retry_link ?>">Retry Payment</a>
	<?php
	// echo $error_msg; 
	?>
	</div>
</div>
