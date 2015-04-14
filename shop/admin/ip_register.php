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
 * Admin IP registration (security mechanism)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v25 (xcart_4_7_0), 2015-02-17 13:29:01, ip_register.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';

$key = $_GET['key'];

if (!empty($key)) {

    $objConfigSign = new XCConfigSignature(func_query_first("SELECT " . XCConfigSignature::getSignedFields() . " FROM $sql_tbl[config] WHERE name='ip_register_codes'"));

    if (!is_array($config['ip_register_codes']))
        $config['ip_register_codes'] = unserialize($config['ip_register_codes']);

    if (
        is_array($config['ip_register_codes']) 
        && isset($config['ip_register_codes'][$key]) 
        && $config['ip_register_codes'][$key]['expiry'] > XC_TIME
        && $objConfigSign->checkSignature()
    ) {


        func_register_admin_ip($config['ip_register_codes'][$key]['ip']);

        func_remove_ip_request($key);

        x_session_register('top_message');
        $top_message = array(
            'content' => func_get_langvar_by_name('lbl_access_for_ip_granted')
        );
    } elseif(!$objConfigSign->checkSignature()) {
        x_session_register('top_message');
        $configs = '&nbsp;*&nbsp;' . func_get_langvar_by_name('txt_fake_allowed_ips_detected', NULL, false, true);
        $top_message = array(
            'content' => func_get_langvar_by_name('txt_fake_config_values_blocked', array('configs' => $configs), false, true)
        );
    }
}

func_header_location('home.php');
?>
