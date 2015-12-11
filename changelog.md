# Gravity Forms DPS PxPay

## Changelog

### 1.5.1, soon...

* fixed: permission to save settings now only requires 'gravityforms_edit_settings', not 'manage_options'

### 1.5.0, 2015-09-16

* fixed: race condition creating duplicate posts etc. when Skip Page 2 enabled for PxPay custom hosted page
* added: actions `gfdpspxpay_process_approved` and `gfdpspxpay_process_failed` for hookers adding custom actions upon return from Payment Express
* changed: removed PxPay v1.0 API, only uses PxPay v2.0 now

### 1.4.3, 2015-05-01

* fixed: error reporting when initial request fails, e.g. with API key error
* fixed: error handling logic with redirect as confirmation
* added: some more precautionary XSS prevention steps
* added: action `gfdpspxpay_process_return_parsed` with `$lead`, `$form`, `$feed`
* added: action `gfdpspxpay_process_confirmation_parsed` with `$entry`, `$form`

### 1.4.2, 2014-12-23

* added: hooks `gfdpspxpay_process_return` and `gfdpspxpay_process_confirmation`
* changed: merge tags use currency of transaction for amount display

### 1.4.1, 2014-11-22

* fixed: **IMPORTANT**: forms with no DPS PxPay feeds were sending blank notification emails (sorry Josh!)

### 1.4.0, 2014-11-06

* added: delay user registration until payment is processed
* added: support for PxPay API v2.0, via option (default is v2.0 for new installs)
* added: custom entry meta `authcode` and `payment_gateway` which can be added to listings, used in notification conditions
* fixed: Gravity Forms 1.9 compatibility
* changed: **IMPORTANT**: defaults to only processing delayed notifications, post creation, user rego, on successful payment
* changed: order feeds in admin by name, not by date created
* changed: code formatting, removed some unused variables
* changed: minimum requirements now WordPress 3.7.1, Gravity Forms 1.7

### 1.3.3, 2014-08-15

* added: basic support for Gravity Forms Logging Add-On, to assist support requests

### 1.3.2, 2014-06-25

* fixed: Gravity Forms 1.8.9 Payment Details box on entry details

### 1.3.1, 2014-06-12

* fixed: admin scripts / stylesheet not loading, feed admin broken
* fixed: don't attempt to make a payment when the total is 0, so form entry can still be submitted

### 1.3.0, 2014-06-07

* fixed: hidden products are now correctly handled
* fixed: shipping is now correctly handled
* fixed: RGFormsModel::update_lead() is deprecated in Gravity Forms v1.8.8
* changed: move authcode into Gravity Forms 1.8.8 Payment Details box on entry details
* changed: merge template for payment amount is now formatted as currency
* changed: save transaction reference for failed transactions too
* changed: some code refactoring

### 1.2.1, 2014-05-14

* fixed: products with separate quantity fields fail
* fixed: undefined index 'post_status' when saving feed

### 1.2.0, 2014-01-17

* fixed: transaction ID was not unique between installations with same account
* fixed: settings wouldn't save in WordPress multisite installations
* fixed: Get Help link to support forum
* added: filters for altering PxPay transaction properties
* added: custom merge field for payment status
* changed: DPS PxPay settings page is now a Gravity Forms settings subpage, like other addons
* changed: some code refactoring for maintenance / compatibility

### 1.1.0, 2013-04-26

* fixed: Gravity Forms 1.7 compatibility fixes for deferring the new multiple notifications
* fixed: WordPress SEO setting "Redirect ugly URL's to clean permalinks" breaks many things, including this plugin (but not any more)
* fixed: nonce (number once) handling in settings admin
* added: entries links on feeds admin

### 1.0.1, 2013-04-12

* fixed: amounts greater than 999.99 work correctly (was getting an IU error on Payment Express screen)
* fixed: don't squabble with other plugins for custom merge tags of same name
* fixed: don't stomp on admin page icons for other Gravity Forms plugins

### 1.0.0, 2013-01-25

* initial public release
