<?php
/* vim: set ts=4 sw=4 sts=4 et: */
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart Software license agreement                                           |
| Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>            |
| All rights reserved.                                                        |
+-----------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "COPYRIGHT" |
| FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE  |
| AT THE FOLLOWING URL: http://www.x-cart.com/license.php                     |
|                                                                             |
| THIS AGREEMENT EXPRESSES THE TERMS AND CONDITIONS ON WHICH YOU MAY USE THIS |
| SOFTWARE PROGRAM AND ASSOCIATED DOCUMENTATION THAT QUALITEAM SOFTWARE LTD   |
| (hereinafter referred to as "THE AUTHOR") OF REPUBLIC OF CYPRUS IS          |
| FURNISHING OR MAKING AVAILABLE TO YOU WITH THIS AGREEMENT (COLLECTIVELY,    |
| THE "SOFTWARE"). PLEASE REVIEW THE FOLLOWING TERMS AND CONDITIONS OF THIS   |
| LICENSE AGREEMENT CAREFULLY BEFORE INSTALLING OR USING THE SOFTWARE. BY     |
| INSTALLING, COPYING OR OTHERWISE USING THE SOFTWARE, YOU AND YOUR COMPANY   |
| (COLLECTIVELY, "YOU") ARE ACCEPTING AND AGREEING TO THE TERMS OF THIS       |
| LICENSE AGREEMENT. IF YOU ARE NOT WILLING TO BE BOUND BY THIS AGREEMENT, DO |
| NOT INSTALL OR USE THE SOFTWARE. VARIOUS COPYRIGHTS AND OTHER INTELLECTUAL  |
| PROPERTY RIGHTS PROTECT THE SOFTWARE. THIS AGREEMENT IS A LICENSE AGREEMENT |
| THAT GIVES YOU LIMITED RIGHTS TO USE THE SOFTWARE AND NOT AN AGREEMENT FOR  |
| SALE OR FOR TRANSFER OF TITLE. THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY  |
| GRANTED BY THIS AGREEMENT.                                                  |
+-----------------------------------------------------------------------------+
\*****************************************************************************/

/**
 * Clean URLs dispatcher
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v37 (xcart_4_7_0), 2015-02-17 13:29:01, dispatcher.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/**
 * Step 1: obtain db_connection to handle 404 errors without sessions and modules engines
 */
require_once __DIR__.'/top.inc.php';

// For db and clean urls function
// Somebody cannot hack init.php via $_GET['xc_load_init_step1'] as he cannot create an object via GET request
$xc_load_init_step1 = (object)array('isActive' => true);
require_once $xcart_dir . '/init.php';

// Do not call db_connection in init.php/preauth.php twice for dispatcher.php
$xc_load_init_step1 = null;
define('XC_LOAD_INIT_STEP2', 1);
define('DISPATCHED_REQUEST', 1);

// Some variables must be finally defined before XC_POINT_DB in init.php
assert('isset($xcart_web_dir) && isset($xcart_catalogs) && isset($config) /* xcart_web_dir/xcart_catalogs must be finally defined before XC_POINT_DB in init.php*/');

$request_uri_info = @parse_url(stripslashes(func_get_request_uri()));

if (
    !isset($request_uri_info['path'])
    || zerolen($request_uri_info['path'])
    ) {

    func_clean_urls_page_not_found();
}

$dispatched_request = preg_replace('/^' . preg_quote($xcart_web_dir . DIR_CUSTOMER . '/', '/') . '/', '', $request_uri_info['path']);

$canonical_url_smarty = $dispatched_request;

$dispatched_request = $ext_dispatched_request = rtrim($dispatched_request, '/');
$dispatched_request = preg_replace("/\.html$/i", '', $dispatched_request);
$dispatched_request_end = substr($request_uri_info['path'], -1) == '/' ? '/' : '';

if (zerolen($dispatched_request)) {

    func_clean_urls_page_not_found();
}

if ($dispatched_request == 'clean-url-test') {

    die('Clean URLs system test completed successfully.');
}

// Perform lookup in clean urls table.
$clean_url_data = func_clean_url_lookup_resource($dispatched_request, array('clean_url'));

if (
    empty($clean_url_data)
    || !is_array($clean_url_data)
    || !isset($clean_url_data['resource_type'])
    || !isset($clean_url_data['resource_id'])
    ) {

    // We got no matches in clean urls table. Let's check if the URL exists in URLs history.
    $history_url_data = func_clean_url_history_lookup_resource($dispatched_request);

    if (
        !empty($history_url_data)
        && is_array($history_url_data)
        && isset($history_url_data['resource_type'])
        && isset($history_url_data['resource_id'])
    ) {

        $redirect_url = func_get_resource_url($history_url_data['resource_type'], $history_url_data['resource_id']);

        if ($redirect_url) {
            // We needed some sessions variable for func_header_location
            require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'preauth.php';
            func_header_location($redirect_url, true, 301);
        }
    }

    if (empty($redirect_url)) {
        func_clean_urls_page_not_found();
    }
}

switch ($config['SEO']['clean_urls_ext_'.strtolower($clean_url_data['resource_type'])]) {
    case '.html':
        $redirect_to_canonical_url = !preg_match("/\.html$/Ssi", $ext_dispatched_request) || $dispatched_request_end == '/';
        break;
    case '/':
        $redirect_to_canonical_url = preg_match("/\.html$/Ssi", $ext_dispatched_request) || $dispatched_request_end != '/';
        break;
    default:
        $redirect_to_canonical_url = false;
}

if (!$redirect_to_canonical_url) 
    $redirect_to_canonical_url = $clean_url_data['clean_url'] != $dispatched_request;

// Perform permanent redirect to the corresponding dynamic page 
// if Clean URLs functionality is disabled
// - or -
// perform permanent redirect to the canonical URL if the path is incorrect.
if ($config['SEO']['clean_urls_enabled'] != 'Y' || $redirect_to_canonical_url) {

    $redirect_url = func_get_resource_url($clean_url_data['resource_type'], $clean_url_data['resource_id'], $QUERY_STRING);

    if ($redirect_url) {
        assert("false/*$GLOBALS[HTTP_REFERER], $QUERY_STRING, $dispatched_request, $request_uri_info[path]::Correct is $clean_url_data[clean_url]".$config['SEO']['clean_urls_ext_'.strtolower($clean_url_data['resource_type'])]."*/");
        // We needed some sessions variable for func_header_location
        require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'preauth.php';
        func_header_location($redirect_url, true, 301);
    }

    func_clean_urls_page_not_found();
}

/**
 * Step 2: Load full X-Cart engine for content pages
 */
switch ($clean_url_data['resource_type']) {
case 'C':
case 'P':
case 'M':
case 'S':
    require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'preauth.php';
    $smarty->assign('canonical_url', $canonical_url_smarty);
}

switch ($clean_url_data['resource_type']) {

case 'C':
    // Category page case
    $_GET['cat'] = $cat = intval($clean_url_data['resource_id']);
    $QUERY_STRING = 'cat=' . $cat . (!empty($QUERY_STRING) ? '&' . $QUERY_STRING : '');
    $PHP_SELF = dirname($PHP_SELF).'/home.php';

    require $xcart_dir.DIR_CUSTOMER.'/home.php';
    break;

case 'P':
    // Product page case
    $_GET['productid'] = $productid = intval($clean_url_data['resource_id']);
    $QUERY_STRING = 'productid=' . $productid . (!empty($QUERY_STRING) ? '&' . $QUERY_STRING : '');
    $PHP_SELF = dirname($PHP_SELF) . '/product.php';

    require $xcart_dir.DIR_CUSTOMER.'/product.php';
    break;

case 'M':
    // Manufacturer page case
    $_GET['manufacturerid'] = $manufacturerid = intval($clean_url_data['resource_id']);
    $QUERY_STRING = 'manufacturerid=' . $manufacturerid . (!empty($QUERY_STRING) ? '&' . $QUERY_STRING : '');
    $PHP_SELF = dirname($PHP_SELF) . '/manufacturers.php';

    require $xcart_dir.DIR_CUSTOMER.'/manufacturers.php';
    break;

case 'S':
    // Static page case
    $_GET['pageid'] = $pageid = intval($clean_url_data['resource_id']);
    $QUERY_STRING = 'pageid=' . $pageid . (!empty($QUERY_STRING) ? '&' . $QUERY_STRING : '');
    $PHP_SELF = dirname($PHP_SELF) . '/pages.php';

    require $xcart_dir.DIR_CUSTOMER.'/pages.php';
    break;

default:

    func_clean_urls_page_not_found();
}


function func_clean_urls_page_not_found() { // {{{
    global $altSkinsInfo, $alt_skin_info, $alt_skin_dir;
    list($altSkinsInfo, $alt_skin_info, $alt_skin_dir) = func_get_alt_skin();
    func_page_not_found();
    exit;
} // }}}

?>
