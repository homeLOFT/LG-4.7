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
 * Module configuration
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v12 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header('Location: ../../'); die('Access denied'); }

global $config, $XCART_SESSION_NAME, $xcart_dir, $sql_tbl;

$sql_tbl['customer_eu_cookie_accesses'] = XC_TBL_PREFIX . 'customer_eu_cookie_accesses';

settype($config['EU_Cookie_Law']['strictly_necessary_cookies'], 'array');
$config['EU_Cookie_Law']['strictly_necessary_cookies'] = 
array_merge($config['EU_Cookie_Law']['strictly_necessary_cookies'],
    array(
        $XCART_SESSION_NAME,
        'eucl_cookie_access',
    )
);


settype($config['EU_Cookie_Law']['functional_cookies'], 'array');

$config['EU_Cookie_Law']['functional_cookies'] = array_merge($config['EU_Cookie_Law']['functional_cookies'],
    array(
        $XCART_SESSION_NAME . 'C_remember',
        $XCART_SESSION_NAME . 'B_remember',
        $XCART_SESSION_NAME . 'A_remember',
        $XCART_SESSION_NAME . 'P_remember',
        'adv_campaignid',
        'adv_campaignid_time',
        'GreetingCookie',
        'store_language',
        'partner_clickid',
        'partner',
        'partner_time',
        'RefererCookie',
        'access_key',
    )
);


$css_files['EU_Cookie_Law'][] = array();
$css_files['EU_Cookie_Law'][] = array('altskin' => TRUE);

$_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . 'EU_Cookie_Law';
/*
 Load module functions
*/
if (!empty($include_func))
    require_once $_module_dir . XC_DS . 'func.php';

?>
