<?php
// settings form

if (!defined('ABSPATH')) {
	exit;
}
?>

<?php settings_errors(); ?>

<h3><span><i class="fa fa-credit-card"></i> DPS PxPay Settings</span></h3>

<form action="<?php echo admin_url('options.php'); ?>" method="POST">
	<?php settings_fields(GFDPSPXPAY_PLUGIN_OPTIONS); ?>

	<table class="form-table">

		<tr>
			<th scope="row">
				<label for="gfdpspxpay_plugin_userID">User ID</label>
			</th>
			<td>
				<input type="text" class="regular-text" name="gfdpspxpay_plugin[userID]" id="gfdpspxpay_plugin_userID" value="<?php echo esc_attr($options['userID']); ?>" />
			</td>
		</tr>

		<tr>
			<th scope="row">
				<label for="gfdpspxpay_plugin_userKey">User Key</label>
			</th>
			<td>
				<input type="text" class="large-text" name="gfdpspxpay_plugin[userKey]" id="gfdpspxpay_plugin_userKey" value="<?php echo esc_attr($options['userKey']); ?>" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				Use Sandbox (testing)
			</th>
			<td>
				<fieldset>
					<input type="radio" name="gfdpspxpay_plugin[useTest]" id="gfdpspxpay_plugin_useTest_yes" value="1" <?php checked($options['useTest'], '1'); ?> />&nbsp;<label for="gfdpspxpay_plugin_useTest_yes">yes</label>
					&nbsp;&nbsp;<input type="radio" name="gfdpspxpay_plugin[useTest]" id="gfdpspxpay_plugin_useTest_no" value="0" <?php checked($options['useTest'], '0'); ?> />&nbsp;<label for="gfdpspxpay_plugin_useTest_no">no</label>
					<p class="gfdpspxpay-opt-admin-test">Sandbox requires a separate account that has not been activated for live payments.</p>
				</fieldset>

				<fieldset class="gfdpspxpay-opt-admin-test" id="gfdpspxpay_plugin_testEnv">
					<legend>
						Please select which environment
						<?php gform_tooltip('When DPS Payment Express sent you your user ID and password, they will have told you to use either SEC or UAT for your sandbox.'); ?>
					</legend>
					<input type="radio" name="gfdpspxpay_plugin[testEnv]" id="gfdpspxpay_plugin_testEnv_sec" value="SEC" <?php checked($options['testEnv'], 'SEC'); ?> />&nbsp;<label for="gfdpspxpay_plugin_testEnv_sec">SEC</label>
					&nbsp;&nbsp;<input type="radio" name="gfdpspxpay_plugin[testEnv]" id="gfdpspxpay_plugin_testEnv_uat" value="UAT" <?php checked($options['testEnv'], 'UAT'); ?> />&nbsp;<label for="gfdpspxpay_plugin_testEnv_uat">UAT</label>
				</fieldset>
			</td>
		</tr>

		<tr class="gfdpspxpay-opt-admin-test">
			<th scope="row">
				<label for="gfdpspxpay_plugin_testID">Test ID</label>
			</th>
			<td>
				<input type="text" class="regular-text" name="gfdpspxpay_plugin[testID]" id="gfdpspxpay_plugin_testID" value="<?php echo esc_attr($options['testID']); ?>" />
			</td>
		</tr>

		<tr class="gfdpspxpay-opt-admin-test">
			<th scope="row">
				<label for="gfdpspxpay_plugin_testKey">Test Key</label>
			</th>
			<td>
				<input type="text" class="large-text" name="gfdpspxpay_plugin[testKey]" id="gfdpspxpay_plugin_testKey" value="<?php echo esc_attr($options['testKey']); ?>" />
			</td>
		</tr>

	</table>

	<?php submit_button(); ?>
</form>

