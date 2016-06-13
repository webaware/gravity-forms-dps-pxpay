<?php

/**
* custom exception types
*/
class GFDpsPxPayException extends Exception {}
class GFDpsPxPayCurlException extends Exception {}

/**
* class for managing the plugin
*/
class GFDpsPxPayPlugin {
	public $urlBase;									// string: base URL path to files in plugin
	public $options;									// array of plugin options

	protected $validationMessage = '';					// last validation message
	protected $errorMessage = false;					// last transaction error message
	protected $paymentURL = false;						// where to redirect browser for payment

	private $feed = null;								// current feed mapping form fields to payment fields, accessed through getFeed()
	private $formData = null;							// current form data collected from form, accessed through getFormData()

	// end points for the DPS PxPay API
	const PXPAY_APIV2_URL		= 'https://sec.paymentexpress.com/pxaccess/pxpay.aspx';
	const PXPAY_APIV2_TEST_URL	= 'https://uat.paymentexpress.com/pxaccess/pxpay.aspx';

	// end point for return to website
	const PXPAY_RETURN		= 'PXPAYRETURN';

	// minimum versions required
	const MIN_VERSION_GF	= '1.9';

	/**
	* static method for getting the instance of this singleton object
	* @return self
	*/
	public static function getInstance() {
		static $instance = null;

		if (is_null($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	* initialise plugin
	*/
	private function __construct() {
		spl_autoload_register(array(__CLASS__, 'autoload'));

		// grab options, setting new defaults for any that are missing
		$this->initOptions();

		// record plugin URL base
		$this->urlBase = plugin_dir_url(GFDPSPXPAY_PLUGIN_FILE);

		add_action('init', array($this, 'init'));
		add_filter('do_parse_request', array($this, 'processDpsReturn'));	// process DPS PxPay return
		add_action('wp', array($this, 'processFormConfirmation'), 5);		// process redirect to GF confirmation
	}

	/**
	* initialise plug-in options, handling undefined options by setting defaults
	*/
	private function initOptions() {
		$this->options = get_option(GFDPSPXPAY_PLUGIN_OPTIONS, array());

		$defaults = array (
			'userID'			=> '',
			'userKey'			=> '',
			'testID'			=> '',
			'testKey'			=> '',
			'useTest'			=> '0',
			'testEnv'			=> 'UAT',
			'sslVerifyPeer'		=> true,
		);

		// if add-on was installed before test environment setting became available, set default environment to SEC for backwards compatibility
		if (isset($this->options['userID']) && !isset($this->options['testEnv'])) {
			$this->options['testEnv'] = 'SEC';
		}

		$this->options = wp_parse_args($this->options, $defaults);
	}

	/**
	* handle the plugin's init action
	*/
	public function init() {
		// do nothing if Gravity Forms isn't enabled or doesn't meet required minimum version
		if (self::versionCompareGF(self::MIN_VERSION_GF, '>=')) {
			// hook into Gravity Forms
			add_filter('gform_logging_supported', array($this, 'enableLogging'));
			add_filter('gform_validation', array($this, 'gformValidation'));
			add_filter('gform_validation_message', array($this, 'gformValidationMessage'), 10, 2);
			add_filter('gform_confirmation', array($this, 'gformConfirmation'), 1000, 4);
			add_filter('gform_is_delayed_pre_process_feed', array($this, 'gformIsDelayed'), 10, 4);
			add_filter('gform_disable_post_creation', array($this, 'gformDelayPost'), 10, 3);
			add_filter('gform_disable_user_notification', array($this, 'gformDelayUserNotification'), 10, 3);
			add_filter('gform_disable_admin_notification', array($this, 'gformDelayAdminNotification'), 10, 3);
			add_filter('gform_disable_notification', array($this, 'gformDelayNotification'), 10, 4);
			add_action('gform_entry_post_save', array($this, 'gformEntryPostSave'), 10, 2);
			add_filter('gform_custom_merge_tags', array($this, 'gformCustomMergeTags'), 10, 4);
			add_filter('gform_replace_merge_tags', array($this, 'gformReplaceMergeTags'), 10, 7);
			add_filter('gform_entry_meta', array($this, 'gformEntryMeta'), 10, 2);

			// register custom post types
			$this->registerTypeFeed();
		}

		if (is_admin()) {
			// kick off the admin handling
			require GFDPSPXPAY_PLUGIN_ROOT . 'includes/class.GFDpsPxPayAdmin.php';
			new GFDpsPxPayAdmin($this);
		}
	}

	/**
	* register custom post type for PxPay form field mappings
	*/
	protected function registerTypeFeed() {
		// register the post type
		register_post_type(GFDPSPXPAY_TYPE_FEED, array(
			'labels' => array (
				'name'					=> 'DPS PxPay Feeds',
				'singular_name'			=> 'DPS PxPay Feed',
				'add_new_item'			=> 'Add New DPS PxPay Feed',
				'edit_item'				=> 'Edit DPS PxPay Feed',
				'new_item'				=> 'New DPS PxPay Feed',
				'view_item'				=> 'View DPS PxPay Feed',
				'search_items'			=> 'Search DPS PxPay Feeds',
				'not_found'				=> 'No DPS PxPay feeds found',
				'not_found_in_trash'	=> 'No DPS PxPay feeds found in Trash',
				'parent_item_colon'		=> 'Parent DPS PxPay feed',
			),
			'description'				=> 'DPS PxPay Feeds, as a custom post type',
			'public'					=> false,
			'show_ui'					=> true,
			'show_in_menu'				=> false,
			'hierarchical'				=> false,
			'has_archive'				=> false,
			//~ 'capabilities'				=> array (
			//~ ),
			'supports'					=> array('null'),
			'rewrite'					=> false,
		));
	}

	/**
	* filter whether form delays User Registration
	* @param bool $is_delayed
	* @param array $form
	* @param array $entry
	* @param string $addon_slug
	* @return bool
	*/
	public function gformIsDelayed($is_delayed, $form, $entry, $addon_slug) {
		if ($entry['payment_status'] === 'Processing') {
			$feed = $this->getFeed($form['id']);

			if ($feed && $addon_slug === 'gravityformsuserregistration') {
				if (!empty($feed->DelayUserrego)) {
					$is_delayed = true;
					self::log_debug(sprintf('delay user registration: form id %s, lead id %s', $form['id'], $entry['id']));
				}
			}

		}

		return $is_delayed;
	}

	/**
	* filter whether post creation from form is enabled (yet)
	* @param bool $is_disabled
	* @param array $form
	* @param array $lead
	* @return bool
	*/
	public function gformDelayPost($is_disabled, $form, $lead) {
		$feed = $this->getFeed($form['id']);
		$is_disabled = !empty($feed->DelayPost);

		self::log_debug(sprintf('delay post creation: %s; form id %s, lead id %s', $is_disabled ? 'yes' : 'no', $form['id'], $lead['id']));

		return $is_disabled;
	}

	/**
	* deprecated: filter whether form triggers autoresponder (yet)
	* @param bool $is_disabled
	* @param array $form
	* @param array $lead
	* @return bool
	*/
	public function gformDelayUserNotification($is_disabled, $form, $lead) {
		$feed = $this->getFeed($form['id']);
		$is_disabled = !empty($feed->DelayAutorespond);

		$this->log_debug(sprintf('delay user notification: %s; form id %s, lead id %s', $is_disabled ? 'yes' : 'no', $form['id'], $lead['id']));

		return $is_disabled;
	}

	/**
	* deprecated: filter whether form triggers admin notification (yet)
	* @param bool $is_disabled
	* @param array $form
	* @param array $lead
	* @return bool
	*/
	public function gformDelayAdminNotification($is_disabled, $form, $lead) {
		$feed = $this->getFeed($form['id']);
		$is_disabled = !empty($feed->DelayNotify);

		$this->log_debug(sprintf('delay admin notification: %s; form id %s, lead id %s', $is_disabled ? 'yes' : 'no', $form['id'], $lead['id']));

		return $is_disabled;
	}

	/**
	* filter whether form triggers notifications (yet)
	* @param bool $is_disabled
	* @param array $notification
	* @param array $form
	* @param array $lead
	* @return bool
	*/
	public function gformDelayNotification($is_disabled, $notification, $form, $lead) {
		$feed = $this->getFeed($form['id']);

		if ($feed) {
			switch (rgar($notification, 'type')) {
				// old "user" notification
				case 'user':
					if ($feed->DelayAutorespond) {
						$is_disabled = true;
					}
					break;

				// old "admin" notification
				case 'admin':
					if ($feed->DelayNotify) {
						$is_disabled = true;
					}
					break;

				// new since 1.7, add any notification you like
				default:
					if (trim($notification['to']) == '{admin_email}') {
						if ($feed->DelayNotify) {
							$is_disabled = true;
						}
					}
					else {
						if ($feed->DelayAutorespond) {
							$is_disabled = true;
						}
					}
					break;
			}
		}

		$this->log_debug(sprintf('delay notification: %s; form id %s, lead id %s, notification "%s"', $is_disabled ? 'yes' : 'no',
			$form['id'], $lead['id'], $notification['name']));

		return $is_disabled;
	}

	/**
	* process a form validation filter hook; if can find a total, attempt to bill it
	* @param array $data an array with elements is_valid (boolean) and form (array of form elements)
	* @return array
	*/
	public function gformValidation($data) {

		// make sure all other validations passed
		if ($data['is_valid']) {

			$feed = $this->getFeed($data['form']['id']);
			if ($feed) {
				$formData = $this->getFormData($data['form']);

				// make sure form hasn't already been submitted / processed
				if ($this->hasFormBeenProcessed($data['form'])) {
					$data['is_valid'] = false;
					$this->validationMessage .= "Payment already submitted and processed - please close your browser window.\n";
				}

				// make sure that we have something to bill
				// TODO: conditional payments
				//~ else if (!$formData->isCcHidden() && $formData->isLastPage() && is_array($formData->ccField)) {
					if (!$formData->hasPurchaseFields()) {
						$data['is_valid'] = false;
						$this->validationMessage .= "This form has no products or totals; unable to process transaction.\n";
					}
				//~ }
			}
		}

		return $data;
	}

	/**
	* alter the validation message
	* @param string $msg
	* @param array $form
	* @return string
	*/
	public function gformValidationMessage($msg, $form) {
		if ($this->validationMessage) {
			$msg = "<div class='validation_error'>" . nl2br($this->validationMessage) . "</div>";
			$this->validationMessage = false;
		}

		return $msg;
	}

	/**
	* form entry post-submission processing
	* @param array $entry
	* @param array $form
	* @return array
	*/
	public function gformEntryPostSave($entry, $form) {

		// get feed mapping form fields to payment request, run away if not set
		$feed = $this->getFeed($form['id']);
		if (!$feed) {
			return $entry;
		}

		// run away if nothing to charge
		$formData = $this->getFormData($form);
		if (empty($formData->total)) {
			return $entry;
		}

		// generate a unique transactiond ID to avoid collisions, e.g. between different installations using the same PxPay account
		// use last three characters of entry ID as prefix, to avoid collisions with entries created at same microsecond
		// uniqid() generates 13-character string, plus 3 characters from entry ID = 16 characters which is max for field
		$transactionID = uniqid(substr($entry['id'], -3));

		// allow plugins/themes to modify transaction ID; NB: must remain unique for PxPay account!
		$transactionID = apply_filters('gfdpspxpay_invoice_trans_number', $transactionID, $form);

		// build a payment request and execute on API
		$creds = $this->getDpsCredentials($this->options['useTest']);
		$paymentReq = new GFDpsPxPayPayment($creds['userID'], $creds['userKey'], $creds['endpoint']);
		$paymentReq->txnType			= 'Purchase';
		$paymentReq->amount				= $formData->total;
		$paymentReq->currency			= GFCommon::get_currency();
		$paymentReq->transactionNumber	= $transactionID;
		$paymentReq->invoiceReference	= $formData->MerchantReference;
		$paymentReq->option1			= $formData->TxnData1;
		$paymentReq->option2			= $formData->TxnData2;
		$paymentReq->option3			= $formData->TxnData3;
		$paymentReq->invoiceDescription	= $feed->Opt;
		$paymentReq->emailAddress		= $formData->EmailAddress;
		$paymentReq->urlSuccess			= home_url(self::PXPAY_RETURN);
		$paymentReq->urlFail			= home_url(self::PXPAY_RETURN);			// NB: redirection will happen after transaction status is updated

		// allow plugins/themes to modify invoice description and reference, and set option fields
		$paymentReq->invoiceDescription	= apply_filters('gfdpspxpay_invoice_desc', $paymentReq->invoiceDescription, $form);
		$paymentReq->invoiceReference	= apply_filters('gfdpspxpay_invoice_ref', $paymentReq->invoiceReference, $form);
		$paymentReq->option1			= apply_filters('gfdpspxpay_invoice_txndata1', $paymentReq->option1, $form);
		$paymentReq->option2			= apply_filters('gfdpspxpay_invoice_txndata2', $paymentReq->option2, $form);
		$paymentReq->option3			= apply_filters('gfdpspxpay_invoice_txndata3', $paymentReq->option3, $form);

		self::log_debug('========= initiating transaction request');
		self::log_debug(sprintf('%s account, invoice ref: %s, transaction: %s, amount: %s',
			$this->options['useTest'] ? 'test' : 'live',
			$paymentReq->invoiceReference, $paymentReq->transactionNumber, $paymentReq->amount));

		self::log_debug(sprintf('success URL: %s', $paymentReq->urlSuccess));
		self::log_debug(sprintf('failure URL: %s', $paymentReq->urlFail));

		// basic transaction data
		// NB: some are custom meta registered via gform_entry_meta
		$entry['payment_gateway'] = 'gfdpspxpay';
		$entry['authcode'] = '';
		gform_update_meta($entry['id'], 'gfdpspxpay_txn_id', $transactionID);

		// reduce risk of double-submission
		gform_update_meta($entry['id'], 'gfdpspxpay_unique_id', GFFormsModel::get_form_unique_id($form['id']));

		$this->errorMessage = '';

		try {
			$response = $paymentReq->processPayment();

			if ($response->isValid) {
				$entry['payment_status'] = 'Processing';
				$this->paymentURL = $response->paymentURL;
			}
			else {
				$entry['payment_status'] = 'Failed';
				$this->errorMessage = 'Payment Express request invalid.';
				self::log_debug($this->errorMessage);
			}
		}
		catch (GFDpsPxPayException $e) {
			$entry['payment_status'] = 'Failed';
			$this->errorMessage = $e->getMessage();
			self::log_debug($this->errorMessage);
		}

		GFAPI::update_entry($entry);

		return $entry;
	}

	/**
	* on form confirmation, send user's browser to DPS PxPay with required data
	* @param mixed $confirmation text or redirect for form submission
	* @param array $form the form submission data
	* @param array $entry the form entry
	* @param bool $ajax form submission via AJAX
	* @return mixed
	*/
	public function gformConfirmation($confirmation, $form, $entry, $ajax) {
		if ($this->errorMessage) {
			$feed = $this->getFeed($form['id']);
			if ($feed) {
				// create a "confirmation message" in which to display the error
				$default_anchor = count(GFCommon::get_fields_by_type($form, array('page'))) > 0 ? 1 : 0;
				$default_anchor = apply_filters('gform_confirmation_anchor_'.$form['id'], apply_filters('gform_confirmation_anchor', $default_anchor));
				$anchor = $default_anchor ? "<a id='gf_{$form["id"]}' name='gf_{$form["id"]}' class='gform_anchor' ></a>" : '';
				$cssClass = rgar($form, 'cssClass');
				$error_msg = esc_html($this->errorMessage);

				ob_start();
				include GFDPSPXPAY_PLUGIN_ROOT . 'views/error-payment-failure.php';
				$confirmation = ob_get_clean();
			}
		}

		elseif ($this->paymentURL) {
			// NB: GF handles redirect via JavaScript if headers already sent, or AJAX
			$confirmation = array('redirect' => $this->paymentURL);
			self::log_debug('Payment Express request valid, redirecting to: ' . $this->paymentURL);
		}

		// reset transient members
		$this->errorMessage = false;
		$this->paymentURL = false;

		return $confirmation;
	}

	/**
	* return from DPS PxPay website, retrieve and process payment result and redirect to form
	* @param bool $do_parse
	* @return bool
	*/
	public function processDpsReturn($do_parse) {
		// must parse out query params ourselves, to prevent the result param getting dropped / filtered out
		// [speculation: maybe it's an anti-malware filter watching for base64-encoded injection attacks?]
		$parts = parse_url($_SERVER['REQUEST_URI']);
		$path = $parts['path'];
		if (isset($parts['query'])) {
			parse_str($parts['query'], $args);
		}
		else {
			$args = array();
		}

		// check for request path containing our path element, and a result argument
		if (strpos($path, self::PXPAY_RETURN) !== false && isset($args['result'])) {
			$creds = $this->getDpsCredentials($this->options['useTest']);
			$resultReq = new GFDpsPxPayResult($creds['userID'], $creds['userKey'], $creds['endpoint']);
			$resultReq->result = wp_unslash($args['result']);

			try {
				self::log_debug('========= requesting transaction result');
				$response = $resultReq->processResult();

				do_action('gfdpspxpay_process_return');

				if ($response->isValid) {
					global $wpdb;
					$sql = "select lead_id from {$wpdb->prefix}rg_lead_meta where meta_key='gfdpspxpay_txn_id' and meta_value = %s";
					$lead_id = $wpdb->get_var($wpdb->prepare($sql, $response->transactionNumber));
					$lock_id = 'gfdpspxpay_elock_' . $lead_id;

					// must have a lead ID, or nothing to do
					if (empty($lead_id)) {
						throw new GFDpsPxPayException('Invalid entry ID: ' . $lead_id);
					}

					// attempt to lock entry
					$entry_was_locked = get_transient($lock_id);
					if (!$entry_was_locked) {
						set_transient($lock_id, time(), 90);
					}
					else {
						self::log_debug("entry $lead_id was locked");
					}

					$lead = GFFormsModel::get_lead($lead_id);
					$form = GFFormsModel::get_form_meta($lead['form_id']);
					$feed = $this->getFeed($form['id']);

					// capture current state of lead
					$initial_status = $lead['payment_status'];

					do_action('gfdpspxpay_process_return_parsed', $lead, $form, $feed);

					// update lead entry, with success/fail details
					if ($response->success) {
						$lead['payment_status']		= 'Approved';
						$lead['payment_date']		= date('Y-m-d H:i:s');
						$lead['payment_amount']		= $response->amount;
						$lead['transaction_id']		= $response->txnRef;
						$lead['transaction_type']	= 1;	// order
						$lead['authcode']			= $response->authCode;
						if (!empty($response->currencySettlement)) {
							$lead['currency']			= $response->currencySettlement;
						}

						self::log_debug(sprintf('success, date = %s, id = %s, status = %s, amount = %s, authcode = %s',
							$lead['payment_date'], $lead['transaction_id'], $lead['payment_status'],
							$lead['payment_amount'], $response->authCode));
					}
					else {
						$lead['payment_status']		= 'Failed';
						$lead['transaction_id']		= $response->txnRef;
						$lead['transaction_type']	= 1;	// order

						// record empty bank authorisation code, so that we can test for it
						$lead['authcode'] = '';

						self::log_debug(sprintf('failed; %s', $response->statusText));
					}

					if (!$entry_was_locked) {

						// update the entry
						self::log_debug(sprintf('updating entry %d', $lead_id));
						GFAPI::update_entry($lead);

						// if order hasn't been fulfilled, process any deferred actions
						if ($initial_status === 'Processing') {
							self::log_debug('processing deferred actions');

							$this->processDelayed($feed, $lead, $form);

							// allow hookers to trigger their own actions
							$hook_status = $response->success ? 'approved' : 'failed';
							do_action("gfdpspxpay_process_{$hook_status}", $lead, $form, $feed);
						}

					}

					// clear lock if we set one
					if (!$entry_was_locked) {
						delete_transient($lock_id);
					}

					// on failure, redirect to failure page if set, otherwise fall through to redirect back to confirmation page
					if ($lead['payment_status']	== 'Failed') {
						if ($feed->UrlFail) {
							wp_redirect(esc_url_raw($feed->UrlFail));
							exit;
						}
					}

					// redirect to Gravity Forms page, passing form and lead IDs, encoded to deter simple attacks
					$query = "form_id={$lead['form_id']}&lead_id={$lead['id']}";
					$query .= "&hash=" . wp_hash($query);
					$redirect_url = esc_url_raw(add_query_arg(array(self::PXPAY_RETURN => base64_encode($query)), $lead['source_url']));
					wp_redirect($redirect_url);
					exit;
				}
			}
			catch (GFDpsPxPayException $e) {
				// TODO: what now?
				echo nl2br(esc_html($e->getMessage()));
				self::log_error(__METHOD__ . ': ' . $e->getMessage());
				exit;
			}
		}

		return $do_parse;
	}

	/**
	* payment processed and recorded, show confirmation message / page
	*/
	public function processFormConfirmation() {
		// check for redirect to Gravity Forms page with our encoded parameters
		if (isset($_GET[self::PXPAY_RETURN])) {
			do_action('gfdpspxpay_process_confirmation');

			// decode the encoded form and lead parameters
			parse_str(base64_decode($_GET[self::PXPAY_RETURN]), $query);

			// make sure we have a match
			if (wp_hash("form_id={$query['form_id']}&lead_id={$query['lead_id']}") == $query['hash']) {

				// stop WordPress SEO from stripping off our query parameters and redirecting the page
				global $wpseo_front;
				if (isset($wpseo_front)) {
					remove_action('template_redirect', array($wpseo_front, 'clean_permalink'), 1);
				}

				// load form and lead data
				$form = GFFormsModel::get_form_meta($query['form_id']);
				$lead = GFFormsModel::get_lead($query['lead_id']);

				do_action('gfdpspxpay_process_confirmation_parsed', $lead, $form);

				// get confirmation page
				if (!class_exists('GFFormDisplay')) {
					require_once(GFCommon::get_base_path() . '/form_display.php');
				}
				$confirmation = GFFormDisplay::handle_confirmation($form, $lead, false);

				// preload the GF submission, ready for processing the confirmation message
				GFFormDisplay::$submission[$form['id']] = array(
					'is_confirmation'		=> true,
					'confirmation_message'	=> $confirmation,
					'form'					=> $form,
					'lead'					=> $lead,
				);

				// if it's a redirection (page or other URL) then do the redirect now
				if (is_array($confirmation) && isset($confirmation['redirect'])) {
					header('Location: ' . $confirmation['redirect']);
					exit;
				}
			}
		}
	}

	/**
	* process any delayed actions
	* @param GFDpsPxPayFeed $feed
	* @param array $entry
	* @param array $form
	*/
	protected function processDelayed($feed, $entry, $form) {
		// default to only performing delayed actions if payment was successful, unless feed opts to always execute
		// can filter each delayed action to permit / deny execution
		$execute_delayed = ($entry['payment_status'] === 'Approved') || $feed->ExecDelayedAlways;

		if ($feed->DelayPost) {
			if (apply_filters('gfdpspxpay_delayed_post_create', $execute_delayed, $entry, $form, $feed)) {
				$this->log_debug(sprintf('executing delayed post creation; form id %s, lead id %s', $form['id'], $entry['id']));
				GFFormsModel::create_post($form, $entry);
			}
		}

		if ($feed->DelayNotify || $feed->DelayAutorespond) {
			$this->sendDeferredNotifications($feed, $form, $entry, $execute_delayed);
		}

		// record that basic delayed actions have been fulfilled, before attempting things that might fail
		GFFormsModel::update_lead_property($entry['id'], 'is_fulfilled', true);

		if ($execute_delayed) {
			$this->log_debug(sprintf('executing delayed Gravity Forms feeds; form id %s, lead id %s', $form['id'], $entry['id']));
			do_action('gform_paypal_fulfillment', $entry, array(), $entry['transaction_id'], $entry['payment_amount']);
		}
	}

	/**
	* send deferred notifications, handling pre- and post-1.7.0 worlds
	* @param GFDpsPxPayFeed $feed
	* @param array $form the form submission data
	* @param array $lead the form entry
	* @param bool $execute_delayed
	*/
	protected function sendDeferredNotifications($feed, $form, $lead, $execute_delayed) {
		$notifications = GFCommon::get_notifications_to_send("form_submission", $form, $lead);
		foreach ($notifications as $notification) {
			switch (rgar($notification, 'type')) {

				// old "user" notification
				case 'user':
					if ($feed->DelayAutorespond) {
						if (apply_filters('gfdpspxpay_delayed_notification_send', $execute_delayed, $notification, $lead, $form, $feed)) {
							GFCommon::send_notification($notification, $form, $lead);
						}
					}
					break;

				// old "admin" notification
				case 'admin':
					if ($feed->DelayNotify) {
						if (apply_filters('gfdpspxpay_delayed_notification_send', $execute_delayed, $notification, $lead, $form, $feed)) {
							GFCommon::send_notification($notification, $form, $lead);
						}
					}
					break;

				// new since 1.7, add any notification you like
				default:
					if (trim($notification['to']) == '{admin_email}') {
						if ($feed->DelayNotify) {
							if (apply_filters('gfdpspxpay_delayed_notification_send', $execute_delayed, $notification, $lead, $form, $feed)) {
								GFCommon::send_notification($notification, $form, $lead);
							}
						}
					}
					else {
						if ($feed->DelayAutorespond) {
							if (apply_filters('gfdpspxpay_delayed_notification_send', $execute_delayed, $notification, $lead, $form, $feed)) {
								GFCommon::send_notification($notification, $form, $lead);
							}
						}
					}
					break;

			}
		}
	}

	/**
	* add custom merge tags
	* @param array $merge_tags
	* @param int $form_id
	* @param array $fields
	* @param int $element_id
	* @return array
	*/
	public function gformCustomMergeTags($merge_tags, $form_id, $fields, $element_id) {
		if ($form_id && $this->getFeed($form_id)) {
			$merge_tags[] = array('label' => 'Transaction ID', 'tag' => '{transaction_id}');
			$merge_tags[] = array('label' => 'Auth Code', 'tag' => '{authcode}');
			$merge_tags[] = array('label' => 'Payment Amount', 'tag' => '{payment_amount}');
			$merge_tags[] = array('label' => 'Payment Status', 'tag' => '{payment_status}');
		}

		return $merge_tags;
	}

	/**
	* replace custom merge tags
	* @param string $text
	* @param array $form
	* @param array $lead
	* @param bool $url_encode
	* @param bool $esc_html
	* @param bool $nl2br
	* @param string $format
	* @return string
	*/
	public function gformReplaceMergeTags($text, $form, $lead, $url_encode, $esc_html, $nl2br, $format) {
		$gateway = gform_get_meta($lead['id'], 'payment_gateway');
		if ($gateway == 'gfdpspxpay') {
			$authCode = gform_get_meta($lead['id'], 'authcode');

			// format payment amount as currency
			if (isset($lead['payment_amount'])) {
				if (!class_exists('RGCurrency')) {
					require_once(GFCommon::get_base_path() . '/currency.php');
				}
				$currency = new RGCurrency(!empty($lead['currency']) ? $lead['currency'] : GFCommon::get_currency());
				$payment_amount = $currency->to_money($lead['payment_amount']);
			}
			else {
				$payment_amount = '';
			}

			$tags = array (
				'{transaction_id}',
				'{payment_status}',
				'{payment_amount}',
				'{authcode}',
			);
			$values = array (
				isset($lead['transaction_id']) ? $lead['transaction_id'] : '',
				isset($lead['payment_status']) ? $lead['payment_status'] : '',
				$payment_amount,
				!empty($authCode) ? $authCode : '',
			);

			$text = str_replace($tags, $values, $text);
		}

		return $text;
	}

	/**
	* activate and configure custom entry meta
	* @param array $entry_meta
	* @param int $form_id
	* @return array
	*/
	public function gformEntryMeta($entry_meta, $form_id) {

		$entry_meta['payment_gateway'] = array(
			'label'					=> 'Payment Gateway',
			'is_numeric'			=> false,
			'is_default_column'		=> false,
			'filter'				=> array(
											'operators' => array('is', 'isnot')
										),
		);

		$entry_meta['authcode'] = array(
			'label'					=> 'AuthCode',
			'is_numeric'			=> false,
			'is_default_column'		=> false,
			'filter'				=> array(
											'operators' => array('is', 'isnot')
										),
		);

		return $entry_meta;
	}

	/**
	* get DPS credentials for selected operation mode
	* @param bool $useTest
	* @return array
	*/
	protected function getDpsCredentials($useTest) {
		if ($useTest) {
			$creds = array(
				'userID'		=> $this->options['testID'],
				'userKey'		=> $this->options['testKey'],
				'endpoint'		=> $this->options['testEnv'] === 'UAT' ? self::PXPAY_APIV2_TEST_URL : self::PXPAY_APIV2_URL,
			);
		}
		else {
			$creds = array(
				'userID'		=> $this->options['userID'],
				'userKey'		=> $this->options['userKey'],
				'endpoint'		=> self::PXPAY_APIV2_URL,
			);
		}

		return $creds;
	}

	/**
	* check whether this form entry's unique ID has already been used; if so, we've already done a payment attempt.
	* @param array $form
	* @return boolean
	*/
	protected function hasFormBeenProcessed($form) {
		global $wpdb;

		$unique_id = GFFormsModel::get_form_unique_id($form['id']);

		$sql = "select lead_id from {$wpdb->prefix}rg_lead_meta where meta_key='gfdpspxpay_unique_id' and meta_value = %s";
		$lead_id = $wpdb->get_var($wpdb->prepare($sql, $unique_id));

		if ($lead_id) {
			$entry = GFFormsModel::get_lead($this->txResult['lead_id']);
		}

		return !empty($entry['payment_status']);
	}

	/**
	* get feed for form
	* @param int $form_id the submitted form's ID
	* @return GFDpsPxPayFeed
	*/
	protected function getFeed($form_id) {
		if ($this->feed !== false && (empty($this->feed) || $this->feed->FormID != $form_id)) {
			$this->feed = GFDpsPxPayFeed::getFormFeed($form_id);
		}

		return $this->feed;
	}

	/**
	* get form data for form
	* @param array $form the form submission data
	* @return GFDpsPxPayFormData
	*/
	protected function getFormData($form) {
		if (empty($this->formData) || $this->formData->formID != $form['id']) {
			$feed = $this->getFeed($form['id']);
			$this->formData = new GFDpsPxPayFormData($form, $feed);
		}

		return $this->formData;
	}

	/**
	* enable Gravity Forms Logging Add-On support for this plugin
	* @param array $plugins
	* @return array
	*/
	public function enableLogging($plugins){
		$plugins['gfdpspxpay'] = 'Gravity Forms DPS PxPay';

		return $plugins;
	}

	/**
	* write an error log via the Gravity Forms Logging Add-On
	* @param string $message
	*/
	public static function log_error($message){
		if (class_exists('GFLogging')) {
			GFLogging::include_logger();
			GFLogging::log_message('gfdpspxpay', $message, KLogger::ERROR);
		}
	}

	/**
	* write an debug message log via the Gravity Forms Logging Add-On
	* @param string $message
	*/
	public static function log_debug($message){
		if (class_exists('GFLogging')) {
			GFLogging::include_logger();
			GFLogging::log_message('gfdpspxpay', $message, KLogger::DEBUG);
		}
	}

	/**
	* generalise an XML post request
	* @param string $url
	* @param string $request
	* @param bool $sslVerifyPeer whether to validate the SSL certificate
	* @return string
	* @throws GFDpsPxPayCurlException
	*/
	public static function xmlPostRequest($url, $request, $sslVerifyPeer = true) {
		// execute the request, and retrieve the response
		$response = wp_remote_post($url, array(
			'user-agent'	=> 'Gravity Forms DPS PxPay ' . GFDPSPXPAY_PLUGIN_VERSION,
			'sslverify'		=> $sslVerifyPeer,
			'timeout'		=> 60,
			'headers'		=> array(
									'Content-Type'		=> 'text/xml; charset=utf-8',
							   ),
			'body'			=> $request,
		));

		if (is_wp_error($response)) {
			throw new GFDpsPxPayCurlException($response->get_error_message());
		}

		return wp_remote_retrieve_body($response);
	}

	/**
	* compare Gravity Forms version against target
	* @param string $target
	* @param string $operator
	* @return bool
	*/
	public static function versionCompareGF($target, $operator) {
		if (class_exists('GFCommon')) {
			return version_compare(GFCommon::$version, $target, $operator);
		}

		return false;
	}

	/**
	* autoload classes as/when needed
	*
	* @param string $class_name name of class to attempt to load
	*/
	public static function autoload($class_name) {
		static $classMap = array (
			'GFDpsPxPayFeed'						=> 'includes/class.GFDpsPxPayFeed.php',
			'GFDpsPxPayFeedAdmin'					=> 'includes/class.GFDpsPxPayFeedAdmin.php',
			'GFDpsPxPayFormData'					=> 'includes/class.GFDpsPxPayFormData.php',
			'GFDpsPxPayPayment'						=> 'includes/class.GFDpsPxPayPayment.php',
			'GFDpsPxPayResult'						=> 'includes/class.GFDpsPxPayResult.php',
		);

		if (isset($classMap[$class_name])) {
			require GFDPSPXPAY_PLUGIN_ROOT . $classMap[$class_name];
		}
	}

}
