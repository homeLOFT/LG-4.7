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
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v4 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_SESSION_START')) {
    header('Location: ../');
    die('Access denied');
}

define('KLARNA_CHECKOUT_PAGE', 0);
define('KLARNA_PRODUCT_PAGE', 1);

$css_files['Klarna_Payments'][] = array();

$var_dirs['klarna_pclass_dir'] = $xcart_dir . '/var';

$_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . 'Klarna_Payments';

$config['Klarna_Payments']['lib_dir'] = $_module_dir . '/lib';

require_once $config['Klarna_Payments']['lib_dir'] . '/Klarna.php';
require_once $config['Klarna_Payments']['lib_dir'] . '/transport/xmlrpc-3.0.0.beta/lib/xmlrpc.inc';
require_once $config['Klarna_Payments']['lib_dir'] . '/transport/xmlrpc-3.0.0.beta/lib/xmlrpc_wrappers.inc';

$addons['Klarna_Payments'] = true;

$config['Klarna_Payments']['invoice_payment_enabled'] = (func_query_first_cell($sql = "SELECT active FROM $sql_tbl[payment_methods] WHERE processor_file = 'cc_klarna.php'") == 'Y');
$config['Klarna_Payments']['part_payment_enabled'] = (func_query_first_cell($sql = "SELECT active FROM $sql_tbl[payment_methods] WHERE processor_file = 'cc_klarna_pp.php'") == 'Y');
$config['Klarna_Payments']['invoice_payment_surcharge'] = func_query_first_cell($sql = "SELECT surcharge FROM $sql_tbl[payment_methods] WHERE processor_file = 'cc_klarna.php'");

$config['Klarna_Payments']['klarna_supported_countries'] = array('se', 'de', 'no', 'dk', 'fi', 'nl');

$config['Klarna_Payments']['klarna_avail_countries'] = array();

foreach ($config['Klarna_Payments']['klarna_supported_countries'] as $c) {

    if ($config['Klarna_Payments']['klarna_active_' . $c] == 'Y') {
        $config['Klarna_Payments']['klarna_avail_countries'][] = $c;
    }
}

/*
 Load module functions
*/
if (!empty($include_func)) {
    require_once $_module_dir . XC_DS . 'func.php';
}

/*
 Module initialization
*/
if (!empty($include_init)) {
    require_once $_module_dir . XC_DS . 'init.php';
}

?>
