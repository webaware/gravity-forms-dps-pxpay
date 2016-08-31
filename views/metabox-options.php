<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<p><label><input type="checkbox" name="_gfdpspxpay_delay_notify" value="1" <?php checked($feed->DelayNotify); ?> />
 Send admin notification only when payment is processed</label></p>
<p><label><input type="checkbox" name="_gfdpspxpay_delay_autorespond" value="1" <?php checked($feed->DelayAutorespond); ?> />
 Send user notification only when payment is processed</label></p>
<p><label><input type="checkbox" name="_gfdpspxpay_delay_post" value="1" <?php checked($feed->DelayPost); ?> />
 Create post only when payment is processed</label></p>
<p><label><input type="checkbox" name="_gfdpspxpay_delay_userrego" value="1" <?php checked($feed->DelayUserrego); ?> />
 Register user only when payment is processed</label></p>

<fieldset>

	<p><label><input type="radio" name="gfdpspxpay_delay_exec_option" value="success" <?php checked(!$feed->ExecDelayedAlways && !$feed->IgnoreDelayedNoFeed); ?> />
	 Execute delayed actions for successful payment, or if no payment is required</label></p>

	<p><label><input type="radio" name="gfdpspxpay_delay_exec_option" value="always" <?php checked($feed->ExecDelayedAlways); ?> />
	 Always execute delayed actions, regardless of payment status</label></p>

	<p><label><input type="radio" name="gfdpspxpay_delay_exec_option" value="ignore_nofeed" <?php checked($feed->IgnoreDelayedNoFeed); ?> />
	 Don't execute delayed actions if no payment is required</label></p>

</fieldset>
