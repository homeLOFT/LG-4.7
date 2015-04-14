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
 * Data recryption interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v30 (xcart_4_7_0), 2015-02-17 13:29:01, recrypt.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';

x_load('order','crypt');

/**
 * Check requirements ...
 */

if (!$HTTPS) {
    die(func_get_langvar_by_name('err_protocol_must_https'));
}

if($REQUEST_METHOD != 'POST') {
    die(func_get_langvar_by_name('err_request_must_post'));
}

/**
 * Get #merchant_password from POST parametrs
 */

$merchant_password = $_POST['merchant_password'];

if(!($config['mpassword'] == md5($merchant_password) || text_decrypt($config['mpassword'],$merchant_password) == 'Merchant password test phrase') || !$merchant_password) {
    die(func_get_langvar_by_name('err_mpassword_wrong'));
}

if ($config['mpassword'] == md5($merchant_password)) {
    $config['mpassword'] = text_crypt('Merchant password test phrase','C',$merchant_password);
    func_array2insert('config', array('name' => 'mpassword', 'value' => text_crypt('Merchant password test phrase','C',$merchant_password)), true);
}

/**
 * Recrypt data
 */

sleep(1);
$time = XC_TIME;
if(func_data_recrypt()) {
    $diff = XC_TIME - $time;
    $hours = floor($diff / 3600);
    $mins = floor(($diff - $hours*60) / 60);
    $sec = $diff - $hours*3600 - $mins*60;
    die("Done (".date("m/d/Y H:s")."), elapsed time: $hours:$mins:$sec");
}
?>
