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
 * Functions for "Amazon Simple Pay" payment module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v26 (xcart_4_7_0), 2015-02-17 23:56:28, func.cc_asp.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

x_load('http', 'xml');

function func_get_asp_signature($post, $key, $action, $method = 'POST')
{
    $url = parse_url($action);
    $signature = "$method\n$url[host]\n";

    $uri_params = explode('/', $url['path']);
    foreach ($uri_params as $k => $param) {
        $uri_params[$k] = str_replace('%7E', '~', rawurlencode($param));
    }
    $signature .= implode('/', $uri_params). "\n";

    uksort($post, 'strcmp');
    $params = array();
    foreach ($post as $var => $val) {
        $params[] = $var . '=' . str_replace('%7E', '~', rawurlencode($val));
    }
    $signature .= implode('&', $params);

    return base64_encode(hmac_sha1($signature, $key));
}

function func_cc_asp_do($order, $trantype)
{
    global $sql_tbl, $http_location;

    x_load('payment');

    $module_params = func_get_pm_params('cc_asp.php');
    $asp_txnid = $order['order']['extra']['asp_txnid'];

    $extra = array(
        'name'    => 'asp_txnid',
        'value'    => $asp_txnid,
    );

    if (empty($asp_txnid)) {
        return array('Q', func_get_langvar_by_name('txt_delayed_payment_transaction_failed'), $extra);
    }

    $AWS_VERSION = "2008-09-17";

    $get = array(
        'AWSAccessKeyId' => $module_params['param01'],
        'TransactionId' => $asp_txnid,
        'SignatureVersion' => '2',
        'SignatureMethod' => 'HmacSHA1',
        'Timestamp' => gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", XC_TIME),
        'Version' => $AWS_VERSION,
        'Action' => ($trantype == 'S') ? 'Settle' : 'Cancel' 
    );

    $url = ($module_params['testmode'] == 'Y') ? 'https://fps.sandbox.amazonaws.com:443/' : 'https://fps.amazonaws.com:443/';

    $get['Signature'] = urlencode(func_get_asp_signature($get, $module_params['param02']), $url, 'GET');

    $query = array();
    foreach ($get as $var => $val)
        $query[$var] = $var . '=' . $val;

    $url = $url . '?' . implode('&', $query);

    list($a, $return) = func_https_request('GET', $url, '', '', '', 'text/xml', $http_location.'/payment/payment_cc.php');

    $xml = func_xml2hash($return);

    $status = empty($xml['Response']['Errors']['Error']);
    $err_msg = $status ? '' : $xml['Response']['Errors']['Error']['Code'] . ":" . $xml['Response']['Errors']['Error']['Message'];

    return array($status, $err_msg, $extra);
}

/**
 * Perform Amazon Simple Pay Settle transaction
 */
function func_cc_asp_do_capture($order)
{
    return func_cc_asp_do($order, 'S');
}

/**
 * Perform Amazon Simple Pay Cancel trnsaction
 */
function func_cc_asp_do_void($order)
{
    return func_cc_asp_do($order, 'C');
}

/**
 * Verify signature
 */
function func_cc_asp_verify_signature($query)
{
    global $sql_tbl, $http_location;

    $module_params = func_query_first("SELECT * FROM $sql_tbl[ccprocessors] WHERE processor = 'cc_asp.php'");

    $AWS_VERSION = '2008-09-17';

    $get = array(
        'AWSAccessKeyId' => $module_params['param01'],
        'SignatureVersion' => '2',
        'SignatureMethod' => 'HmacSHA1',
        'Timestamp' => gmdate("Y-m-d\TH:i:s.\\0\\0\\0\\Z", XC_TIME),
        'Version' => $AWS_VERSION,
        'Action' => 'VerifySignature',
        'HttpParameters' => rawurlencode(http_build_query($query, '', '&')),
        'UrlEndPoint' => $http_location . '/payment/cc_asp.php'
    );

    $url = ($module_params['testmode'] == 'Y' ? 'https://fps.sandbox.amazonaws.com:443/' : 'https://fps.amazonaws.com:443/');

    $get['Signature'] = func_get_asp_signature($get, $module_params['param02'], $url, 'GET');

    $params = '';
    foreach ($get as $var => $val)
        $params .= ($params==''?'?':'&') . $var . '=' . $val;
    
    $url .= $params;

    list($a, $return) = func_https_request('GET', $url, '', '', '', 'text/xml', $http_location.'/payment/payment_cc.php');

    $xml = func_xml2hash($return);
    if ($xml['VerifySignatureResponse']['VerifySignatureResult']['VerificationStatus'] === 'Success') return true;

    return $xml['Response']['Errors']['Error']['Code'] . ' (' . $xml['Response']['Errors']['Error']['Message'] . ')';
}

?>
