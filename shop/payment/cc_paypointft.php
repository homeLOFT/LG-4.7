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
 * PayPoint Fast Track
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Payment interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v27 (xcart_4_7_0), 2015-02-17 13:29:01, cc_paypointft.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ($_SERVER['REQUEST_METHOD'] == "POST" && $_GET['mode'] == 'return') {

    // Return or Cancel
    require __DIR__.'/auth.php';

    if (!func_is_active_payment('cc_paypointft.php'))
        exit;

    if (defined('PAYPOINTFT_DEBUG')) 
        func_pp_debug_log('paypointft', 'B', print_r($_POST, true) . print_r($_GET, true));

    $skey = $strCartID;

    if (empty($intStatus))
        $a = func_query_first("SELECT sessid,trstat,is_callback FROM $sql_tbl[cc_pp3_data] WHERE ref='" . $skey . "'");
    
    if (
        empty($intStatus) 
        && empty($a['is_callback']) 
        && preg_match('/GO\|/s', $a['trstat'])
    ) {
        // User cancels the transaction
        $bill_output['sessid'] = $a['sessid'];
        $bill_output['billmes'] = "Cancelled by user";
        $bill_output['code'] = 2;

        require $xcart_dir.'/payment/payment_ccend.php';

    } else {

        require $xcart_dir.'/payment/payment_ccview.php';
    }

} else {

    // Redirect to gateway server
    if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

    $ordr = $module_params['param06'] . join("-", $secure_oid);
    if (!$duplicate)
        db_query("REPLACE INTO $sql_tbl[cc_pp3_data] (ref,sessid, trstat) VALUES ('".addslashes($ordr)."','".$XCARTSESSID. "','GO|" . implode('|',$secure_oid). "')");

    $fields = array(
        'intInstID' => $module_params["param01"],
        'strCartID' => $ordr,
        'strDesc' => 'Order(s) #' . join("; #", $secure_oid),
        'fltAmount' => $cart["total_cost"],
        'strCurrency' => $module_params["param02"],
        'intAuthMode' => $module_params['use_preauth'] == 'Y' ? 2 : 1,
        'intTestMode' => $module_params["testmode"] == 'Y' ? 1 : 0,
        'strAddress' => substr($userinfo['b_address'] . ' ' . @$userinfo["b_address_2"], 0, 255),
        'strCity' => substr($userinfo['b_city'], 0, 40),
        'strState' => substr($userinfo['b_state'], 0, 40),
        'strPostcode' => substr($userinfo['b_zipcode'], 0, 15),
        'strCountry' => substr($userinfo['b_country'], 0, 2),
        'strTel' => substr($userinfo['phone'], 0, 50),
        'strFax' => substr($userinfo['fax'], 0, 50),
        'strEmail' => substr($userinfo['email'], 0, 100),
        'strCardHolder' => substr($userinfo['b_firstname'] . ' ' . $userinfo["b_lastname"], 0, 20)
    );

    if (defined('PAYPOINTFT_DEBUG')) 
        func_pp_debug_log('paypointft', 'I', print_r($fields, true));

    func_create_payment_form("https://secure.metacharge.com/mcpe/purser", $fields, "PayPoint Fast Track");
}

exit;

?>
