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
 * Product links
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v40 (xcart_4_7_0), 2015-02-17 23:56:28, product_links.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_load('product');

$current_area = 'C';
$login_type = 'C';
$product_info = func_select_product($productid, '',false, false, true);

if ($product_info['product_type'] != 'C') {

if (!empty($active_modules['Magnifier']))
    include $xcart_dir.'/modules/Magnifier/product_magnifier.php';

if (!empty($active_modules['Product_Options']))
    include $xcart_dir.'/modules/Product_Options/customer_options.php';

if(!empty($active_modules['Extra_Fields'])) {
    $extra_fields_provider=$product_info['provider'];
    include $xcart_dir.'/modules/Extra_Fields/extra_fields.php';
}

if(!empty($active_modules['Feature_Comparison']))
    include $xcart_dir.'/modules/Feature_Comparison/product.php';

if (!empty($active_modules['Wholesale_Trading']) && empty($product_info['variantid']))
    include $xcart_dir.'/modules/Wholesale_Trading/product.php';

if (!empty($active_modules['Product_Configurator']) && !empty($_GET['pconf']))
    include $xcart_dir.'/modules/Product_Configurator/slot_product.php';

}

if (!empty($active_modules['Special_Offers']))
    include $xcart_dir.'/modules/Special_Offers/product_offers.php';

$productid = intval($productid);
$location[] = array(func_get_langvar_by_name('lbl_product_links'), "product_links.php?productid=$productid");
$location[] = array($product_info['product'], '');

$smarty->assign('http_customer_location', $http_location . DIR_CUSTOMER);
$smarty->assign('http_host', "http://".$xcart_http_host);

$smarty->assign('product', $product_info);

$smarty->assign('main','product_links');
?>
