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
 * Common functions for X-Payment connector module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v35 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../../"); die("Access denied"); }

// X-Payment connector requirements codes

define('XPC_REQ_CURL', 1);
define('XPC_REQ_OPENSSL', 2);
define('XPC_REQ_DOM', 4);


define('XPC_SYSERR_CARTID', 1);
define('XPC_SYSERR_URL', 2);
define('XPC_SYSERR_PUBKEY', 4);
define('XPC_SYSERR_PRIVKEY', 8);
define('XPC_SYSERR_PRIVKEYPASS', 16);

define('XPC_WPP_DP', 'PayPal Payments Pro (PayPal API)');
define('XPC_WPPPE_DP', 'PayPal Payments Pro (Payflow API)');

define('XPC_API_EXPIRED', 506);

$xpc_paypal_dp_solutions = array('pro' => XPC_WPP_DP, 'uk' => XPC_WPPPE_DP);

/**
 * Load modules/XPayments_Connector/xpc_func.php script
 */
function func_xpay_func_load()
{
    global $xcart_dir;

    require_once ($xcart_dir . '/modules/XPayments_Connector/xpc_func.php');

    return true;
}

/**
 * Check module system requirements
 *
 * @return boolean Requirements checking result
 */
function xpc_check_requirements()
{
    $code = 0;

    if (!function_exists('curl_init')) {
        $code = $code | XPC_REQ_CURL;
    }

    if (
        !function_exists('openssl_pkey_get_public') || !function_exists('openssl_public_encrypt')
        || !function_exists('openssl_get_privatekey') || !function_exists('openssl_private_decrypt')
        || !function_exists('openssl_free_key')
    ) {
        $code = $code | XPC_REQ_OPENSSL;
    }

    if (!class_exists('DOMDocument')) {
        $code = $code | XPC_REQ_DOM;
    }

    return $code;
}

/**
 * Check profile fields
 * 
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function xpc_check_fields()
{
    $required_fields = array(
        'firstname',
        'address',
        'city',
        'state',
        'country',
        'zipcode',
        'phone',
    );

    $fields = func_get_default_fields('C', 'address_book');

    $warning_required_fields = array();
    $error_required_fields = array();

    foreach ($required_fields as $name) {

        if ('' == $fields[$name]['avail']) {

            $error_required_fields[] = $name;

        }

        if ('' == $fields[$name]['required']) {

            $warning_required_fields[] = $name;

        }

    }

    $warning_required_fields = empty($warning_required_fields) ? false : implode(', ', $warning_required_fields);
    $error_required_fields = empty($error_required_fields) ? false : implode(', ', $error_required_fields);

    return array(
        $warning_required_fields,
        $error_required_fields,
    );
}

/**
 * Process redirect of the main window from a child iframe
 * using JS
 *
 * @params $url location
 *
 * @return string
 * @access public
 * @see    ____func_see____
 */
function func_xpc_iframe_redirect($url)
{
    global $orderids, $xpc_order_ids, $return_url, $bill_output, $action;

    x_session_register('xpc_order_ids');
    x_session_register('return_url');

    
    $xpc_order_ids = $orderids;

    $return_url = preg_replace('/[\x00-\x1f].*$/sm', '', $url); 

    func_define('XC_DISABLE_SESSION_SAVE', true);
    x_session_save();

    if ($bill_output['code'] == 2 && $action != 'return') {
        #
        # Process error, no redirect of the parent window
        #
        define('IS_XPC_IFRAME', 1);
        func_xpc_iframe_finalize($url);

    } else {
        #
        # Process normal return, redirect the parent window
        #
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

<script type="text/javascript">
//<![CDATA[
function func_redirect(return_url) {
    if (window.parent !== window) {
        parent.document.forms['checkout_form'].setAttribute('action', 'payment/cc_xpc_iframe.php?xpc_action=xpc_end');
        parent.document.forms['checkout_form'].submit();
    } else {
        window.location = return_url;
    }
}
//]]>
</script>

</head>
<body onload="javascript: func_redirect('<?php echo $url; ?>');">
Please wait while processing the payment details...
</body>
</html>
<?php

    }

    func_flush();
    func_exit();


    exit();
}


?>
