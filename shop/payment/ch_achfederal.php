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
 * ACH Federal 
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v4 (xcart_4_7_0), 2015-02-17 23:56:28, ch_achfederal.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

func_set_time_limit(100);

x_load('http', 'files', 'xml');

$url = ($module_params['testmode'] != 'Y') ? 'https://api.achfederal.com/webservice/V2/gateway.asmx/SaveSingleTransaction' : 'https://api.achfederal.com/webserviceSandbox/v2/gateway.asmx/SaveSingleTransaction';

$ordr = str_replace(" ", '', $module_params['param09']) . join("-", $secure_oid);

$post = array(
    'token' => $module_params['param01'],
    'nachaID' => $module_params['param02'],
    'name' => $userinfo['check_name'],
    'routingnumber' => $userinfo['check_brn'],
    'accountnumber' => $userinfo['check_ban'],
    'transactioncode' => $userinfo['check_type'],
    'sec' => $module_params['param03'],
    'amount' => price_format($cart['total_cost']),
    'description' => substr('Order' . join(",", $secure_oid), 0, 10),
    'individualid' => $ordr,
    'eed' => date("Ymd"),
    'customertracenumber' => $userinfo['id'],
);

$pst = array();
if (!empty($post)) {
    foreach ($post as $k => $v) {
        $pst[] = $k . '=' . urlencode($v);
    }
}

if (defined('ACHFEDERAL_DEBUG')) {
    func_pp_debug_log('achfederal', 'post', print_r($pst, true));
}

list($a, $return) = func_https_request('POST', $url, implode('&', $pst), '', '', "application/x-www-form-urlencoded");

if (preg_match("!<string.*>([^>]+)</string>!", $return, $out)) {
    $res = html_entity_decode(trim($out[1]));
}

if (defined('ACHFEDERAL_DEBUG')) {
    func_pp_debug_log('achfederal', 'return', print_r($res, true));
}

if (!empty($res)) {
    $ord_fields = array('Code', 'Message', 'Value');

    $result = array();
    foreach ($ord_fields as $field) {
        if (preg_match("!<".$field.">([^>]+)</".$field.">!", $res, $out)) {
            $result[$field] = trim($out[1]);
        }
    }

    $bill_output['code'] = (($result['Code'] === '000') ? 1 : 2);
    $bill_output['billmes'] = "TransactionID: $ordr; Code: $result[Code]; Message: $result[Message]" . ($result['Value'] ? ": $result[Value]; " : ";");
} else {
    $bill_output['code'] = 2;
    $bill_output['billmes'] = $return;
}

?>
