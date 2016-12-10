<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<?php if ($authCode): ?>
<div class="gf_payment_detail">
	<?php echo esc_html_x('Auth Code:', 'entry details', 'gravity-forms-dps-pxpay') ?>
	<span id="gfdpspxpay_authcode"><?php echo esc_html($authCode); ?></span>
</div>
<?php endif; ?>

