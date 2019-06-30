# Gravity Forms DPS PxPay
Contributors: webaware
Plugin Name: Gravity Forms DPS PxPay
Plugin URI: https://wordpress.org/plugins/gravity-forms-dps-pxpay/
Author: WebAware
Author URI: https://shop.webaware.com.au/
Donate link: https://shop.webaware.com.au/donations/?donation_for=Gravity+Forms+DPS+PxPay
Tags: gravity forms, dps, payment express, pxpay, donations, payment, payment gateway, ecommerce
Requires at least: 4.9
Tested up to: 5.2
Stable tag: 2.3.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily create online payment forms with Gravity Forms and DPS Payment Express (Windcave) PxPay

## Description

Easily create online payment forms with Gravity Forms and DPS Payment Express (Windcave) PxPay

Gravity Forms DPS PxPay integrates the [DPS Payment Express PxPay credit card payment gateway](https://www.paymentexpress.com/merchant-ecommerce-pxpay) with [Gravity Forms](https://webaware.com.au/get-gravity-forms) advanced form builder.

* build online donation forms
* build online booking forms
* build simple Buy Now forms

> NB: this plugin extends Gravity Forms; you still need to install and activate [Gravity Forms](https://webaware.com.au/get-gravity-forms)!

### Sponsorships

* creation of this plugin was generously sponsored by [IstanbulMMV](https://profiles.wordpress.org/IstanbulMMV/profile/)

Thanks for sponsoring new features on Gravity Forms DPS PxPay!

### Requirements

* Install the [Gravity Forms](https://webaware.com.au/get-gravity-forms) plugin
* Create an account with DPS for [PxPay](https://sec.paymentexpress.com/pxmi/apply)

### Privacy

Information gathered for processing a credit card transaction is transmitted to Payment Express for processing, and in turn, Payment Express passes that information on to your bank. Please review [Payment Express' Privacy Policy](https://www.paymentexpress.com/privacy-policy) for information about how that affects your website's privacy policy. By using this plugin, you are agreeing to the terms of use for Payment Express.

## Frequently Asked Questions

### What is DPS PxPay?

DPS PxPay is a hosted Credit Card payment gateway, accepting payments in New Zealand, Australia, North America, United Kingdom, Ireland, and Singapore.

### Will this plugin work without installing Gravity Forms?

No. This plugin adds a DPS Payment Express PxPay payment gateway to Gravity Forms so that you can add online payments to your forms. You must purchase and install a copy of the [Gravity Forms](https://webaware.com.au/get-gravity-forms) plugin too.

### What Gravity Forms license do I need?

Any Gravity Forms license will do. You can use this plugin with the Basic, Pro, or Elite licenses.

### How do I build a form with credit card payments?

* add one or more Product fields or a Total field to your form. The plugin will automatically detect the values assigned to these pricing fields
* add customer name and contact information fields to your form. These fields can be mapped when creating a DPS PxPay feed
* add a DPS PxPay feed, mapping your form fields to DPS PxPay transaction fields

### What is the difference between Normal and Testing (Sandbox) mode?

Gravity Forms DPS PxPay enables you to store two pairs of User ID and User Key credentials. When you first signup for a PxPay account with DPS you will likely be issued development or testing credentials. Later, when you want to go live with your site, you will need to request a new User ID and User Key from DPS. Sandbox mode enables you to switch between your live and test credentials. If you only have testing credentials, both your User ID and Test ID and User Key and Test Key should be identical. In this instance, Sandbox mode can be switched either On or Off.

Sandbox mode enables you to run tests without using real credit cards or bank accounts. You must use special test credit card details when using the test environment.

### Where can I find dummy Credit Card details for testing purposes?

[Visit this page](https://www.paymentexpress.com/support-merchant-frequently-asked-questions-testing-details)

### Where will the customer be directed after they complete their DPS Credit Card transaction?

Standard Gravity Forms submission logic applies. The customer will either be shown your chosen confirmation message, directed to a nominated page on your website or sent to a custom URL.

### Where do I find the DPS PxPay transaction number?

Successful transaction details including the DPS PxPay transaction number and bank authcode are shown in the Info box when you view the details of a form entry in the WordPress admin.

### How do I add a confirmed payment amount and transaction number to my Gravity Forms admin or customer email?

Browse to your Gravity Form, select [Notifications](https://www.gravityhelp.com/documentation/article/configuring-notifications-in-gravity-forms/) and use the Insert Merge Tag dropdown (Payment Amount, Transaction Number and Auth Code will appear under Custom at the very bottom of the dropdown list).

NB: these custom merge tags will only work for notifications triggered by Payment Completed and Payment Failed events.

### How do I change my currency type?

Use your Gravity Forms Settings page to select the currency type to pass to DPS. Please ensure your currency type is supported by DPS

### Capture or Authorize?

DPS PxPay supports two transaction types - Purchase and Auth. The Gravity Forms DPS PxPay plugin calls them Capture and Authorize, terminology used in most other payment integrations.

Capture processes the payment immediately. Authorize holds the amount on the customer's card for processing later.

Authorize transactions can be completed manually in Payline. Perform a transaction search, and look for its Complete button.

### Can I do recurring payments?

Not yet.

### Can I use Account2Account?

No. Account2Account debits a bank account directly, and Payment Express have told me that they cannot provide a full test environment for me to test in. A2A will never be integrated into the free version of this plugin. It might be integrated into the pro version sometime, but don't hold your breath.

### I get an SSL error when my form attempts to connect with DPS

This is a common problem in local testing environments. Read how to [fix your website SSL configuration](https://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/).

### Can I use this plugin on any shared-hosting environment?

The plugin will run in shared hosting environments, but requires PHP 5 with the following modules enabled (talk to your host). Both are typically available because they are enabled by default in PHP 5, but may be disabled on some shared hosts.

* XMLWriter
* SimpleXML

### Are there any filter hooks?

Developers can use these filter hooks to modify some invoice properties. Each filter receives a string for the field value, and the Gravity Forms form array.

* `gfdpspxpay_invoice_ref` for modifying the invoice reference
* `gfdpspxpay_invoice_trans_number` for modifying the invoice transaction reference; NB: must be unique for PxPay account!
* `gfdpspxpay_invoice_txndata1` for setting the TxnData1 field
* `gfdpspxpay_invoice_txndata2` for setting the TxnData2 field
* `gfdpspxpay_invoice_txndata3` for setting the TxnData3 field

Developers can run processes on these actions (e.g. load classes required to handle invoice presentation):

* `gfdpspxpay_process_confirmation`
* `gfdpspxpay_process_confirmation_parsed`
* `gfdpspxpay_process_approved`
* `gfdpspxpay_process_failed`

## Screenshots

1. Options screen
2. A sample donation form
3. A list of DPS PxPay feeds for a form
4. A DPS PxPay feed (mapping form fields to DPS PxPay)
5. The sample donation form as it appears on a page
6. A successful entry in Gravity Forms admin

## Upgrade Notice

### 2.3.0

support all Gravity Forms add-ons that register delayed action support through the Add-on framework

## Changelog

The full changelog can be found [on GitHub](https://github.com/webaware/gravity-forms-dps-pxpay/blob/master/changelog.md). Recent entries:

### 2.3.0

Released 2019-07-01

* changed: support all Gravity Forms add-ons that register delayed action support through the Add-on framework
