<?php
/*
Plugin Name: Gravity Forms DPS PxPay
Plugin URI: https://shop.webaware.com.au/downloads/gravity-forms-dps-pxpay/
Description: Easily create online payment forms with Gravity Forms and DPS Payment Express PxPay
Version: 2.2.0-dev
Author: WebAware
Author URI: https://shop.webaware.com.au/
Text Domain: gravity-forms-dps-pxpay
*/

/*
copyright (c) 2013-2018 WebAware Pty Ltd (email : support@webaware.com.au)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

if (!defined('ABSPATH')) {
	exit;
}

define('GFDPSPXPAY_PLUGIN_ROOT', dirname(__FILE__) . '/');
define('GFDPSPXPAY_PLUGIN_NAME', basename(dirname(__FILE__)) . '/' . basename(__FILE__));
define('GFDPSPXPAY_PLUGIN_FILE', __FILE__);
define('GFDPSPXPAY_PLUGIN_VERSION', '2.2.0-dev');

// instantiate the plug-in
require GFDPSPXPAY_PLUGIN_ROOT . 'includes/class.GFDpsPxPayPlugin.php';
GFDpsPxPayPlugin::getInstance();
