<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
* Class for dealing with a DPS Payment Express response
*/
abstract class GFDpsPxPayResponse {

	/**
	* whether it was a successful request
	* @var boolean
	*/
	public $isValid;

	/**
	* load DPS PxPay response data as XML string
	* @param string $response DPS PxPay response as a string (hopefully of XML data)
	* @throws GFDpsPxPayException
	*/
	public function loadResponse($response) {
		// prevent XML injection attacks, and handle errors without warnings
		$oldDisableEntityLoader = libxml_disable_entity_loader(true);
		$oldUseInternalErrors = libxml_use_internal_errors(true);

		try {
			$xml = simplexml_load_string($response);
			if ($xml === false) {
				$errors = array();
				foreach (libxml_get_errors() as $error) {
					$errors[] = $error->message;
				}
				throw new GFDpsPxPayException(implode("\n", $errors));
			}

			// restore old libxml settings
			libxml_disable_entity_loader($oldDisableEntityLoader);
			libxml_use_internal_errors($oldUseInternalErrors);
		}
		catch (Exception $e) {
			// restore old libxml settings
			libxml_disable_entity_loader($oldDisableEntityLoader);
			libxml_use_internal_errors($oldUseInternalErrors);

			throw new GFDpsPxPayException(sprintf(__('Invalid response from Payment Express: %s', 'gravity-forms-dps-pxpay'), $e->getMessage()));
		}

		if (is_null($response)) {
			throw new GFDpsPxPayException($this->getMessageInvalid());
		}

		$this->isValid = ('1' === ((string) $xml['valid']));

		foreach (get_object_vars($xml) as $name => $value) {
			if (property_exists($this, $name)) {
				switch ($name) {

					case 'AmountSettlement':
						$this->AmountSettlement = (float) $value;
						break;

					case 'Success':
						$this->Success = (bool) $value;
						break;

					default:
						$this->$name = (string) $value;
						break;

				}
			}
		}
	}

	/**
	* get 'invalid response' message for specific response class
	* @return string
	*/
	abstract protected function getMessageInvalid();

}
