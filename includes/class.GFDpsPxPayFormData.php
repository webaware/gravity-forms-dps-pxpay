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
		if (isset($inverseMap['title'])) {
			$this->{$inverseMap['title']} = $form['title'];
		}
		if (isset($inverseMap['form'])) {
			$this->{$inverseMap['form']} = $form['id'];
		}

		// iterate over fields to collect data
		foreach ($form['fields'] as &$field) {
			$id = (string) $field->id;

			if ($field->type === 'shipping' || $field->type === 'product' || $field->type === 'total') {
				$this->hasPurchaseFieldsFlag = true;
			}

			// check for feed mapping
			if (isset($field->inputs) && is_array($field->inputs)) {
				// compound field, see if want whole field
				if (isset($inverseMap[$id])) {
					// want whole field, concatenate values
					$values = array();
					foreach($field->inputs as $input) {
						$subID = strtr($input['id'], '.', '_');
						$values[] = trim(rgpost("input_{$subID}"));
					}
					$this->{$inverseMap[$id]} = implode(' ', array_filter($values, 'strlen'));
				}
				else {
					// see if want any part-field
					foreach($field->inputs as $input) {
						$key = (string) $input['id'];
						if (isset($inverseMap[$key])) {
							$subID = strtr($input['id'], '.', '_');
							$this->{$inverseMap[$key]} = rgpost("input_{$subID}");
						}
					}
				}
			}
			else {
				// simple field, just take value
				if (isset($inverseMap[$id])) {
					$this->{$inverseMap[$id]} = rgpost("input_{$id}");
				}
			}
		}

		$entry = GFFormsModel::get_current_lead();
		$this->total = GFCommon::get_order_total($form, $entry);
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
