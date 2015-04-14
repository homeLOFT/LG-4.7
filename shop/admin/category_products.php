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
 * Category products page interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v34 (xcart_4_7_0), 2015-02-17 13:29:01, category_products.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[categories] WHERE categoryid='$cat'") == 0) {
    $top_message = array(
        "content" => func_get_langvar_by_name("msg_category_not_exist"),
        "type" => "E"
    );
    func_header_location('categories.php');
}

x_load('category');

if ($current_category = func_get_category_data($cat)) {
    list($current_category['image_x'], $current_category['image_y']) = func_crop_dimensions(
        $current_category['image_x'], $current_category['image_y'],
        $config['Appearance']['thumbnail_width'], $config['Appearance']['thumbnail_height']
    );
    $smarty->assign("current_category", $current_category);

} else {
    assert('!empty($current_category) /*func_get_category_data returns false for existing category*/');
}
/**
 * Assign page location
 */
$location[] = array(func_get_langvar_by_name('lbl_categories_management'), 'categories.php');

$location[] = array(func_get_langvar_by_name('lbl_category_products'), '');

$old_search_data = $search_data['products'];
$old_mode = $mode;

$search_data['products'] = array();
$search_data['products']['categoryid'] = $cat;
$search_data['products']['category_main'] = 'Y';
$search_data['products']['category_extra'] = 'Y';
if (!isset($sort))
    $sort = $search_data['products']['sort_field'] = 'orderby';
if (!isset($sort_direction))
    $search_data['products']['sort_direction'] = 0;

$mode = 'search';

include $xcart_dir.'/include/search.php';

$search_data['products'] = $old_search_data;
$mode = $old_mode;

if (is_array($products)) {
    foreach ($products as $k=>$v) {
        $products[$k] = func_array_merge($v, func_query_first("SELECT main, orderby FROM $sql_tbl[products_categories] WHERE productid='$v[productid]' AND categoryid='$cat'"));
    }
    $smarty->assign('navigation_script', 'category_products.php?cat=' . $cat . '&sort=' . urlencode($sort) . '&sort_direction=' . $sort_direction);
}


$current_category['category'] = func_get_category_path($cat, 'category', true);
$current_category['avail'] = func_query_first_cell("SELECT avail from $sql_tbl[categories] WHERE categoryid='$cat'");

$smarty->assign('products',$products);

require $xcart_dir . DIR_ADMIN . '/location_adjust.php';

$smarty->assign('cat', $cat);
$smarty->assign('current_category', $current_category);

$smarty->assign('main','category_products');

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$dialog_tools_data = array('help' => true);
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);

?>
