<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
* class for managing form data
*/
class GFDpsPxPayFormData {

	public $total					= 0;
	public $formID					= 0;

	// field mappings to GF form
	public $MerchantReference;				// merchant reference
	public $EmailAddress;					// optional email address
	public $TxnData1;						// optional data #1
	public $TxnData2;						// optional data #2
	public $TxnData3;						// optional data #3

	private $isLastPageFlag			= false;
	private $hasPurchaseFieldsFlag	= false;

	/**
	* initialise instance
	* @param array $form
	* @param GFDpsPxPayFeed $feed
	*/
	public function __construct(&$form, $feed) {
		// check for last page
        $current_page = GFFormDisplay::get_source_page($form['id']);
        $target_page = GFFormDisplay::get_target_page($form, $current_page, rgpost('gform_field_values'));
        $this->isLastPageFlag = ($target_page == 0);

		// load the form data
		$this->formID = $form['id'];
		$this->loadForm($form, $feed);
	}

	/**
	* load the form data we care about from the form array
	* @param array $form
	* @param GFDpsPxPayFeed $feed
	*/
	private function loadForm(&$form, $feed) {
		// pick up feed mappings, set special mappings (form ID, title)
		$inverseMap = $feed->getGfFieldMap();

		$this->setMappedFieldValue($inverseMap, 'title',	$form['title']);
		$this->setMappedFieldValue($inverseMap, 'form',		$form['id']);

		// iterate over fields to collect data
		foreach ($form['fields'] as &$field) {
			$id = (string) $field->id;

			if ($field->type === 'shipping' || $field->type === 'product' || $field->type === 'total') {
				$this->hasPurchaseFieldsFlag = true;
			}

			// check for feed mapping
			if (isset($field->inputs) && is_array($field->inputs)) {
				// compound field
				$values = array();

				foreach($field->inputs as $input) {
					$sub_id = strtr($input['id'], '.', '_');

					// collect sub-field values in case want a compound field as one field value
					$values[] = trim(rgpost('input_' . $sub_id));

					// pass to any fields that want a sub-field
					$this->setMappedFieldValue($inverseMap, (string) $input['id'], rgpost('input_' . $sub_id));
				}

				// see if want the whole field as one field value
				if (isset($inverseMap[$id])) {
					$this->setMappedFieldValue($inverseMap, $id, implode(' ', array_filter($values, 'strlen')));
				}
			}
			else {
				// simple field, just take value
				$this->setMappedFieldValue($inverseMap, $id, rgpost('input_' . $id));
			}
		}

		$entry = GFFormsModel::get_current_lead();
		$this->total = GFCommon::get_order_total($form, $entry);
	}

	/**
	* set PxPay field values
	* @param array $inverseMap inverse map of Gravity Forms fields to feed fields
	* @param string $gfField name of field mapped from Gravity Forms
	* @param string $value
	*/
	private function setMappedFieldValue($inverseMap, $gfField, $value) {
		if (!empty($inverseMap[$gfField])) {
			foreach ($inverseMap[$gfField] as $feedField) {
				$this->$feedField = $value;
			}
		}
	}

	/**
	* check whether we're on the last page of the form
	* @return boolean
	*/
	public function isLastPage() {
		return $this->isLastPageFlag;
	}

	/**
	* check whether form has any product fields (because CC needs something to bill against)
	* @return boolean
	*/
	public function hasPurchaseFields() {
		return $this->hasPurchaseFieldsFlag;
	}

}
