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
 * Configuration script
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v14 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

$addons['TaxCloud'] = true;
$css_files['TaxCloud'][] = array();

// Module directory
$taxcloud_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . 'TaxCloud';

// Debug option: set to true to log all requests and responses
$taxcloudLogAllRequests = true;

// WSDL file for Soap
$taxcloud_wsdl = file_exists($taxcloud_module_dir . XC_DS . 'sdk' . XC_DS . 'Taxcloud.wsdl.xml')
    ? $taxcloud_module_dir . XC_DS . 'sdk' . XC_DS . 'Taxcloud.wsdl.xml'
    : "https://api.taxcloud.net/1.0/TaxCloud.asmx?wsdl";


// Ignore address validation errors
$taxcloud_ignore_address_validation_error = true;

// Disable several taxes options
$config['Taxes']['display_taxed_order_totals'] = 'N';
$config['Taxes']['display_cart_products_tax_rates'] = 'N';
$config['Taxes']['tax_operation_scheme'] = 'TAX_SCHEME_GENERAL'; // @see XCTaxesDefs class for available values
$config['Taxes']['allow_user_modify_tax_number'] = 'N';


// SQL table
$sql_tbl['taxcloud_cache'] = XC_TBL_PREFIX . 'taxcloud_cache';

// Load module functions
if (!empty($include_func)) {
    require_once $taxcloud_module_dir . XC_DS . 'func.php';
}

