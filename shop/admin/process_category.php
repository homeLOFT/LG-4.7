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
 * Process modified categories data
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v74 (xcart_4_7_0), 2015-02-17 13:29:01, process_category.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

x_load('category');

$current_category = func_get_category_data($cat);

func_set_time_limit(86400);

if ($REQUEST_METHOD == 'POST') {

    if ($mode == 'apply') {

        // Update categories list

        if ($posted_data) {
            foreach ($posted_data as $k => $v) {
                $_category_id = intval($k);

                $query_data = array(
                    'order_by' => intval($v['order_by']),
                    'avail' => ($v['avail'] == 'Y' ? 'Y' : 'N')
                );

                if (!empty($active_modules['New_Arrivals'])) {
                    func_new_arrivals_categories_update_list($query_data, $v['show_new_arrivals']);
                }

                func_array2update('categories', $query_data, "categoryid='".$_category_id."'");

                func_reflect_category_availability_changes($_category_id);
            }
            XCProducts_CategoriesChange::repairIntegrity(array_keys($posted_data));
        }

        // Rebuild node indexes
        func_cat_tree_rebuild();

        // Update subcategories counters
        if (!empty($cat_org)) {
            $path = func_get_category_path($cat_org);
            if (!empty($path)) {
                func_recalc_subcat_count($path);
            }
        }

        // Update categories data cache
        // Must be run after func_recalc_product_count/func_cat_tree_rebuild/func_recalc_subcat_count
        if (!empty($active_modules['Flyout_Menus']) && func_fc_use_cache() && $posted_data) {
            func_fc_build_categories(1);
        }

        $top_message['content'] = func_get_langvar_by_name('msg_adm_categories_upd');
        $top_message['type'] = 'I';

        func_header_location("categories.php?cat=$cat_org");

    }
    elseif ($mode == 'update') {

        // Go to modify category

        func_header_location("category_modify.php?cat=$cat");

    }
    elseif ($mode == 'delete') {

        // Delete category

        if ($confirmed == 'Y') {

            // Delete category from database
            // Delete all subcategories and associated products

            require $xcart_dir.'/include/safe_mode.php';

            $parent_categoryid = func_delete_category($cat, 1);

            if (!empty($active_modules['Flyout_Menus']) && func_fc_use_cache()) {
                func_fc_build_categories(1);
            }

            // Delete Clean URLs data.
            db_query("DELETE FROM $sql_tbl[clean_urls] WHERE resource_type = 'C' AND resource_id = '$cat'");
            db_query("DELETE FROM $sql_tbl[clean_urls_history] WHERE resource_type = 'C' AND resource_id = '$cat'");

            $top_message['content'] = func_get_langvar_by_name('msg_adm_category_del');
            $top_message['type'] = 'I';

            func_header_location("categories.php?cat=$parent_categoryid");
        }
        else {

            // Go to prepare delete confirmation page

            func_header_location("process_category.php?cat=$cat&mode=delete");
        }
    }
}

if ($mode == 'add') {

    // Add new category

    func_header_location("category_modify.php?$QUERY_STRING");
}

if ($mode == 'delete' && $confirmed != 'Y') {

    // Prepare the delete confirmation page

    $location[] = array(func_get_langvar_by_name('lbl_categories_management'), 'categories.php');
    $location[] = array(func_get_langvar_by_name('lbl_delete_category'), '');

    x_load('category');
    $current_category = func_get_category_data($cat);
    $subcats = func_query("SELECT categoryid, category FROM $sql_tbl[categories] WHERE lpos BETWEEN " . $current_category['lpos'] . " AND " .  $current_category['rpos']);

    if (!is_array($subcats)) {
        $subcats = array();
    }

    if (is_array($subcats)) {
        foreach ($subcats as $k=>$v) {
            $subcats[$k]['products'] = func_query("
            SELECT $sql_tbl[products].productid, $sql_tbl[products].productcode, $sql_tbl[products_lng_current].product
              FROM $sql_tbl[products_categories]
             INNER JOIN $sql_tbl[products]
                ON $sql_tbl[products_categories].categoryid = '$v[categoryid]'
               AND $sql_tbl[products_categories].productid  = $sql_tbl[products].productid
               AND $sql_tbl[products_categories].main       = 'Y'
             INNER JOIN $sql_tbl[products_lng_current]
             ON $sql_tbl[products_lng_current].productid=$sql_tbl[products].productid");

            $subcats[$k]['products_count'] = (is_array($subcats[$k]['products']) ? count($subcats[$k]['products']) : 0);
        }
    }

    $smarty->assign('subcats', $subcats);
    $smarty->assign('main','category_delete_confirmation');

    // Assign the current location line
    $smarty->assign('location', $location);

    // Assign the section navigation data
    $dialog_tools_data = array('help' => true);
    $smarty->assign('dialog_tools_data', $dialog_tools_data);

    if (is_readable($xcart_dir.'/modules/gold_display.php')) {
        include $xcart_dir.'/modules/gold_display.php';
    }
    func_display('admin/home.tpl',$smarty);
}

?>
