=== Gravity Forms DPS PxPay ===
Contributors: webaware, IstanbulMMV
Plugin Name: Gravity Forms DPS PxPay
Plugin URI: http://shop.webaware.com.au/downloads/gravity-forms-dps-pxpay/
Author URI: http://webaware.com.au/
Donate link: http://shop.webaware.com.au/donations/?donation_for=Gravity+Forms+DPS+PxPay
Tags: gravityforms, gravity forms, gravity, dps, payment express, pxpay, donation, donations, payment, payment gateway, ecommerce, credit cards, new zealand, australia
Requires at least: 3.7.1
Tested up to: 4.4
Stable tag: 1.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate Gravity Forms with the DPS Payment Express PxPay credit card payment gateway

== Description ==

Gravity Forms DPS PxPay adds a credit card payment gateway for [DPS Payment Express PxPay](https://www.paymentexpress.com/Products/Ecommerce/Payment_Express_Hosted) to the [Gravity Forms](http://webaware.com.au/get-gravity-forms) plugin.

* build online donation forms
* build online booking forms
* build simple Buy Now forms

> NB: this plugin extends [Gravity Forms](http://webaware.com.au/get-gravity-forms); you still need to install and activate Gravity Forms!

= Sponsorships =

* creation of this plugin was generously sponsored by [IstanbulMMV](https://profiles.wordpress.org/IstanbulMMV/profile/)

Thanks for sponsoring new features on Gravity Forms DPS PxPay!

= Requirements: =

* Install the [Gravity Forms](http://webaware.com.au/get-gravity-forms) plugin
* Create an account with DPS for [PxPay](https://sec.paymentexpress.com/pxmi/apply)

== Installation ==

1. Install and activate the [Gravity Forms](http://webaware.com.au/get-gravity-forms) plugin
2. Upload the Gravity Forms DPS PxPay plugin to your /wp-content/plugins/ directory.
3. Activate the Gravity Forms DPS PxPay plugin through the 'Plugins' menu in WordPress.
4. Edit the DPS PxPay payment gateway settings to set your DPS PxPay user ID and key

= Building a Gravity Form with Credit Card Payments =

* add one or more Product fields or a Total field to your form. The plugin will automatically detect the values assigned to these pricing fields
* if required, add customer name and contact information fields to your form. These optional fields can be mapped when creating a DPS PxPay feed and their values stored against each transaction in your DPS Payline console
* add a DPS PxPay feed, mapping your form fields to DPS PxPay transaction fields (Merchant Reference, TxnData1, TxnData2, TxnData3)

== Frequently Asked Questions ==

= What is DPS PxPay? =

DPS PxPay is a hosted Credit Card payment gateway. DPS Payment Express is one of Australasia's leading online payments solutions providers.

= Will this plugin work without installing Gravity Forms? =

No. This plugin adds a DPS Payment Express PxPay payment gateway to Gravity Forms so that you can add online payments to your forms. You must purchase and install a copy of the [Gravity Forms](http://webaware.com.au/get-gravity-forms) plugin too.

= What Gravity Forms license do I need? =

Any Gravity Forms license will do. You can use this plugin with the Personal, Business or Developer licenses.

= What is the difference between Normal and Testing (Sandbox) mode? =

Gravity Forms DPS PxPay enables you to store two pairs of User ID and User Key credentials. When you first signup for a PxPay account with DPS you will likely be issued development or testing credentials. Later, when you want to go live with your site, you will need to request a new User ID and User Key from DPS. Sandbox mode enables you to switch between your live and test credentials. If you only have testing credentials, both your User ID and Test ID and User Key and Test Key should be identical. In this instance, Sandbox mode can be switched either On or Off.

=  Where will the customer be directed after they complete their DPS Credit Card transaction? =

Standard Gravity Forms submission logic applies. The customer will either be shown your chosen confirmation message, directed to a nominated page on your website or sent to a custom URL.

= Where do I find the DPS PxPay transaction number? =

Successful transaction details including the DPS PxPay transaction number and bank authcode are shown in the Info box when you view the details of a form entry in the WordPress admin.

= How do I add a confirmed payment amount and transaction number to my Gravity Forms admin or customer email? =

Browse to your Gravity Form, select [Notifications](http://www.gravityhelp.com/documentation/page/Notifications) and use the Insert Merge Tag dropdown (Payment Amount, Transaction Number and Auth Code will appear under Custom at the very bottom of the dropdown list).

NB: these custom merge tags will only work if notifications are only sent after payment is accepted.

= How do I change my currency type? =

Use your Gravity Forms Settings page to select the currency type to pass to DPS. Please ensure your currency type is supported by DPS

= Purchase or Auth? =

DPS PxPay supports two transaction types - Purchase and Auth. The Gravity Forms DPS PxPay plugin only supports the Purchase transaction type

= Can I do recurring payments? =

Not yet.

=  Where can I find dummy Credit Card details for testing purposes? =

[Visit this page](http://www.paymentexpress.com/knowledge_base/faq/developer_faq.html#Testing%20Details)

= I get an SSL error when my form attempts to connect with DPS =

This is a common problem in local testing environments. Please [read this post](http://snippets.webaware.com.au/howto/stop-turning-off-curlopt_ssl_verifypeer-and-fix-your-php-config/) for more information.

= Can I use this plugin on any shared-hosting environment? =

The plugin will run in shared hosting environments, but requires PHP 5 with the following modules enabled (talk to your host). Both are typically available because they are enabled by default in PHP 5, but may be disabled on some shared hosts.

* XMLWriter
* SimpleXML

== Screenshots ==

1. Options screen
2. A sample donation form
3. A list of DPS PxPay feeds
4. A DPS PxPay feed (mapping form fields to DPS PxPay)
5. The sample donation form as it appears on a page
6. A successful entry in Gravity Forms admin

== Filter hooks ==

Developers can use these filter hooks to modify some invoice properties. Each filter receives a string for the field value, and the Gravity Forms form array.

* `gfdpspxpay_invoice_desc` for modifying the invoice description
* `gfdpspxpay_invoice_ref` for modifying the invoice reference
* `gfdpspxpay_invoice_trans_number` for modifying the invoice transaction reference; NB: must be unique for PxPay account!
* `gfdpspxpay_invoice_txndata1` for setting the TxnData1 field
* `gfdpspxpay_invoice_txndata2` for setting the TxnData2 field
* `gfdpspxpay_invoice_txndata3` for setting the TxnData3 field

Developers can run processes on these actions (e.g. load classes required to handle invoice presentation):

* `gfdpspxpay_process_return`
* `gfdpspxpay_process_return_parsed`
* `gfdpspxpay_process_confirmation`
* `gfdpspxpay_process_confirmation_parsed`
* `gfdpspxpay_process_approved`
* `gfdpspxpay_process_failed`

== Contributions ==

* [Fork me on GitHub](https://github.com/webaware/gravity-forms-dps-pxpay/)

== Upgrade Notice ==

= 1.5.1 =

_really_ fixed: race condition when Skip Page 2 enabled; save settings now only requires 'gravityforms_edit_settings'

== Changelog ==

The full changelog can be found [on GitHub](https://github.com/webaware/gravity-forms-dps-pxpay/blob/master/changelog.md). Recent entries:

### 1.5.1, 2015-12-27

* fixed: permission to save settings now only requires 'gravityforms_edit_settings', not 'manage_options'
* _really_ fixed: race condition creating duplicate posts etc. when Skip Page 2 enabled for PxPay custom hosted page

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
