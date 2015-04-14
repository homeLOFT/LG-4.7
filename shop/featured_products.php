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
 * Get featured products data and store it into $f_products array
 * Get new products data and store it into $new_products array
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v60 (xcart_4_7_0), 2015-02-17 23:56:28, featured_products.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: home.php"); die("Access denied"); }

/**
 * Select from featured products table
 */

if (!empty($user_account)) {
    $user_account['membershipid'] = max(0, intval($user_account['membershipid']));
}

if (empty($search_data)) {
    $search_data = array();
}

$old_search_data = (!empty($search_data['products'])) ? $search_data['products'] : array();

$old_mode = (!empty($mode)) ? $mode : '';

/**
 * Featured products are shown without pagging navigation
 */
$do_not_use_navigation = true;

$search_data['products'] = array();
$search_data['products']['add_page_url'] = "&featured=Y";
$search_data['products']['forsale'] = 'Y';
$search_data['products']['sort_condition'] = "$sql_tbl[featured_products].product_order";

$search_data['products']['_']['inner_joins']['featured_products'] = array(
    'on' => "$sql_tbl[products].productid=$sql_tbl[featured_products].productid AND $sql_tbl[featured_products].avail='Y' AND $sql_tbl[featured_products].categoryid='" . intval($cat) . "'"
);

$REQUEST_METHOD = 'GET';

$mode = 'search';

$_inner_search = true;
include $xcart_dir . '/include/search.php';
$_inner_search = false;

$search_data['products'] = $old_search_data;

$mode = $old_mode;

unset($old_search_data, $old_mode);

$do_not_use_navigation = false;

if ($total_items > 0) {

    $smarty->assign('f_products', $products);

}
?>
