<?php
namespace webaware\gf_dpspxpay;

if (!defined('ABSPATH')) {
	exit;
}

// minimum versions required
const MIN_VERSION_GF	= '2.0';

// entry meta keys
const META_UNIQUE_ID					= 'gfdpspxpay_unique_id';	// unique form submission
const META_TRANSACTION_ID				= 'gfdpspxpay_txn_id';		// merchant's transaction ID (invoice number, etc.)
const META_GATEWAY_TXN_ID				= 'gateway_txn_id';			// duplicate of transaction_id to enable passing to integrations (e.g. Zapier)
const META_FEED_ID						= 'gfdpspxpay_feed_id';		// link to feed under which the transaction was processed
const META_AUTHCODE						= 'authcode';				// bank authorisation code

// end points for return to website
const ENDPOINT_RETURN					= '__gfpxpayreturn';
const ENDPOINT_RETURN_TEST				= '__gfpxpayreturntest';	// return from test environment
const ENDPOINT_CONFIRMATION				= '__gfpxpayconfirm';

/**
* custom exception types
*/
class GFDpsPxPayException extends \Exception {}

/**
* compare Gravity Forms version against target
* @param string $target
* @param string $operator
* @return bool
*/
function gform_version_compare($target, $operator) {
	if (class_exists('GFCommon', false)) {
		return version_compare(\GFCommon::$version, $target, $operator);
	}

	return false;
}

/**
* test whether the minimum required Gravity Forms is installed / activated
* @return bool
*/
function has_required_gravityforms() {
	return gform_version_compare(MIN_VERSION_GF, '>=');
}
