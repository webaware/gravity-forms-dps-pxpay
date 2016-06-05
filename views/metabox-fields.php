
<table class="gfdpspxpay-feed-fields gfdpspxpay-details">

	<tr>
		<th scope="row"><label for="_gfdpspxpay_merchant_ref">Merchant Reference:</label></th>
		<td>
			<select size="1" name="_gfdpspxpay_merchant_ref" id="_gfdpspxpay_merchant_ref">
				<?php if ($fields) echo self::selectFields($feed->MerchantReference, $fields); ?>
			</select> <span class="required" title="required field">*</span>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="_gfdpspxpay_txndata1">TxnData1:</label></th>
		<td>
			<select size="1" name="_gfdpspxpay_txndata1" id="_gfdpspxpay_txndata1">
				<?php if ($fields) echo self::selectFields($feed->TxnData1, $fields); ?>
			</select>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="_gfdpspxpay_txndata2">TxnData2:</label></th>
		<td>
			<select size="1" name="_gfdpspxpay_txndata2" id="_gfdpspxpay_txndata2">
				<?php if ($fields) echo self::selectFields($feed->TxnData2, $fields); ?>
			</select>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="_gfdpspxpay_txndata3">TxnData3:</label></th>
		<td>
			<select size="1" name="_gfdpspxpay_txndata3" id="_gfdpspxpay_txndata3">
				<?php if ($fields) echo self::selectFields($feed->TxnData3, $fields); ?>
			</select>
		</td>
	</tr>

	<tr>
		<th scope="row"><label for="_gfdpspxpay_email">Email Address:</label></th>
		<td>
			<select size="1" name="_gfdpspxpay_email" id="_gfdpspxpay_email">
				<?php if ($fields) echo self::selectFields($feed->EmailAddress, $fields); ?>
			</select>
		</td>
	</tr>

</table>

<p><em>Please note: this information will appear in your DPS Payline console.</em>
	<br /><em>Email Address is currently accepted by DPS but not stored; we hope this will change soon.</em>
	<br /><em>If you need to see Email Address in DPS Payline, please map it to one of the TxnData fields for now.</em>
</p>

