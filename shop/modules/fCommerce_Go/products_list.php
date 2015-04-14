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
 * Home / category page interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v15 (xcart_4_7_0), 2015-02-17 23:56:28, products_list.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */
if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

x_load('product', 'taxes');

$mode = $fb_mode;

if (!$cat && $fb_mode != 'search') {

    if ($shop_configuration['bestsellers'] == 'Y' && $active_modules['Bestsellers'] && empty($expired)) {

        /**
         * Search the bestsellers
         */
        $_query = array();

        $threshold = 0;
        $threshold_deviation = 1;
        $cat_ids = array();
        $categories_thresholds = array();
        $membershipid = @$user_account['membershipid'];
        $search_query = '';

        if ($categories_filter) {

            $cat_ids = $categories_filter;

            $sql_cats_condition = implode("','", $cat_ids);
            $categories_thresholds = func_query_hash("SELECT ctb.categoryid, ctb.threshold_sales_stats FROM $sql_tbl[category_threshold_bestsellers] ctb WHERE ctb.categoryid IN ('$sql_cats_condition') AND ctb.membershipid='$membershipid'", 'categoryid', FALSE, TRUE);
            if (!empty($categories_thresholds)) {
                $threshold = max($categories_thresholds);
            } else {
                $categories_thresholds = array();
            }

            $search_query = " AND $sql_tbl[products_categories].categoryid IN ('$sql_cats_condition')";

            if (count($categories_thresholds) < count($cat_ids)) {
                $repaired_cats = XCProductSalesStats::repairIntegrityThresholds($cat_ids, array_keys($categories_thresholds), $membershipid);
                $categories_thresholds = $categories_thresholds + $repaired_cats;
            }
        } else {
            if (empty($membershipid)) {
                $threshold = intval($config['home_threshold_sales_stats']);
            } else {
                $threshold = intval(func_query_first_cell("SELECT ctb.threshold_sales_stats FROM $sql_tbl[category_threshold_bestsellers] ctb WHERE ctb.categoryid=0 AND ctb.membershipid='$membershipid'"));
            }

            $categories_thresholds[0] = $threshold;
        }

        $threshold = max(0, $threshold - $threshold_deviation);
        if ($threshold > 0) {
            $search_query .= " AND $sql_tbl[product_sales_stats].sales_stats >= '" . $threshold . "'";
        }

        if (!empty($search_query)) {
            $_query['query'] = $search_query;
        }

        $_query['skip_tables'] = XCSearchProducts::getSkipTablesByTemplate(XCSearchProducts::SKIP_ALL_POSSIBLE);

        $bestsellers = func_search_products(
                $_query,
                $membershipid, // membership
                "$sql_tbl[product_sales_stats].sales_stats DESC", // orderby
                $config['Bestsellers']['number_of_bestsellers'], // limit
                TRUE // id_only
        );

        if (is_array($bestsellers)) {

            foreach ($bestsellers as $k => $v) {
                $_to_check[] = $v['productid'];
            }

            $old_search_data = $search_data["products"];
            $old_mode = $mode;
            $old_page = $page;

            $search_data['products'] = array();
            $search_data['products']['productid'] = $_to_check;
            $search_data['products']['sort_field'] = 'sales_stats';
            $search_data['products']['sort_direction'] = 1;


            $REQUEST_METHOD = "GET";
            $mode = "search";
            include $xcart_dir . "/include/search.php";

            $search_data["products"] = $old_search_data;
            $mode = $old_mode;

            if ($products) {
                $bestsellers = $products;
            }

            if (!empty($active_modules["Special_Offers"])) {
                func_offers_check_products($login, $current_area, $bestsellers);
            }

            array_walk($bestsellers, 'func_fb_shop_prepare_products', 'T');
        }

        $smarty->assign('products', $bestsellers);
        $smarty->assign('title', func_get_langvar_by_name('lbl_fb_bestsellers_title'));

        $shop_configuration['bestsellers'] = func_fb_shop_display($customer_dir . '/products.tpl');
    }

    if ($shop_configuration['featured'] == 'Y' || $expired) {

        include $xcart_dir . '/featured_products.php';

        $f_products = $smarty->getTemplateVars('f_products');

        if (is_array($f_products)) {
            array_walk($f_products, 'func_fb_shop_prepare_products', 'T');
        }

        $smarty->assign('products', $f_products);
        $smarty->assign('title', func_get_langvar_by_name('lbl_fb_featured_title'));

        $shop_configuration['featured'] = (isset($expired) ? $f_products : func_fb_shop_display($customer_dir . '/products.tpl'));
    }
} else {

    $old_search_data = isset($search_data['products']) ? $search_data['products'] : '';

    $old_mode = $mode;

    $search_data['products'] = array(
        'categoryid' => $cat,
        'search_in_subcategories' => '',
        'category_main' => 'Y',
        'category_extra' => 'Y',
        'forsale' => 'Y',
    );

    if ($mode == 'search') {

        $search_data['products']['search_in_subcategories'] = 'Y';

        if ($categories_filter) {
            $search_data['products']['_']['where'][] = "$sql_tbl[products_categories].categoryid IN ('" . implode("', '", $categories_filter) . "')";
        }

        if ($config['General']['check_main_category_only'] == 'Y' && !$cat) {
            $search_data['products']['_']['inner_joins']['products_categories'] = array(
                'on' => "$sql_tbl[products_categories].productid = $sql_tbl[products].productid",
            );
        }

        $search_data['products']['by_title'] = 'Y';
        $search_data['products']['by_descr'] = 'Y';
        $search_data['products']['by_sku'] = 'Y';

        if (!empty($substring)) {
            $search_data['products']['substring'] = $substring;
        }
    }


    if (!isset($sort)) {
        $sort = $config['Appearance']['products_order'];
    }

    if (!isset($sort_direction)) {
        $sort_direction = 0;
    }

    $per_page = !empty($per_page) ? $per_page : $shop_configuration['per_page'];

    $config["Appearance"]["products_per_page"] = $objects_per_page = $_GET['objects_per_page'] = $per_page;
    $config['Appearance']['max_nav_pages'] = min(5, $config['Appearance']['max_nav_pages']);

    $REQUEST_METHOD = "GET";
    $mode = 'search';

    include $xcart_dir . '/include/search.php';

    $search_data['products'] = $old_search_data;

    $mode = $old_mode;

    if (!empty($active_modules['Subscriptions'])) {

        include $xcart_dir . '/modules/Subscriptions/subscription.php';
    }

    if (is_array($products)) {

        $shop_configuration['objects_per_page'] = $objects_per_page;
        $shop_configuration['sort'] = $sort;
        $shop_configuration['sort_direction'] = $sort_direction;
        $shop_configuration['sort_line'] = 'sort=' . $sort . '&sort_direction=' . $sort_direction;
        $shop_configuration['current_page'] = $page;
        $shop_configuration['total_items'] = $total_items;
        $shop_configuration['substring'] = $substring;

        /**
         * Correct navigation
         */
        $objects_per_page = intval($objects_per_page);

        if ($objects_per_page < 1)
            $objects_per_page = 10;

        if (!isset($page))
            $page = 0;

        $max_nav_pages = max(intval($config['Appearance']['max_nav_pages']), 1);
        $total_nav_pages = max(($total_nav_pages ? $total_nav_pages : ceil($total_items / $objects_per_page) + 1), 2);
        $page = min(max(intval($page), 1), $total_nav_pages - 1);
        $first_page = $objects_per_page * ($page - 1);

        $start_page = max(ceil($page - ($max_nav_pages / 2)), 1);
        $total_super_pages = (0 == $total_items % $objects_per_page) ? $total_items / $objects_per_page : floor($total_items / $objects_per_page) + 1;

        $total_pages = min($start_page + min($max_nav_pages, $total_super_pages), $total_super_pages + 1);

        if ($total_pages - $start_page < $max_nav_pages)
            $start_page = $max_nav_pages >= $total_pages ? 1 : $total_pages - $max_nav_pages;

        if ($page > 1)
            $smarty->assign('navigation_arrow_left', $page - 1);

        if ($page < $total_super_pages)
            $smarty->assign('navigation_arrow_right', $page + 1);

        $smarty->assign('navigation_max_pages', $max_nav_pages);

        $smarty->assign('navigation_page', $page);
        $smarty->assign('total_pages', $total_pages);
        $smarty->assign('start_page', $start_page);
        $smarty->assign('total_super_pages', $total_super_pages);

        if ($sort_fields) {

            $_lh = func_get_langvar_by_name('lbl_fb_low_high', false, false, true);
            $_hl = func_get_langvar_by_name('lbl_fb_high_low', false, false, true);

            $_az = func_get_langvar_by_name('lbl_fb_a_z', false, false, true);
            $_za = func_get_langvar_by_name('lbl_fb_z_a', false, false, true);

            foreach ($sort_fields as $k => $v) {
                if ($k == 'orderby') {
                    $_sort_fields['sort=' . $k . '&sort_direction=0'] = func_get_langvar_by_name('lbl_sort_by', false, false, true) . $v;
                } else {
                    if ($k == 'price') {
                        $_sort_fields['sort=' . $k . '&sort_direction=0'] = $v . $_lh;
                        $_sort_fields['sort=' . $k . '&sort_direction=1'] = $v . $_hl;
                    } else {
                        $_sort_fields['sort=' . $k . '&sort_direction=0'] = $v . $_az;
                        $_sort_fields['sort=' . $k . '&sort_direction=1'] = $v . $_za;
                    }
                }
            }

            $shop_configuration['sort_fields'] = $_sort_fields;
        }
        
        $smarty->assign('data', $shop_configuration);

        array_walk($products, 'func_fb_shop_prepare_products', 'T');

        $smarty->assign('products', $products);

        $smarty->assign('navigation', true);
    }
    if ($fb_mode == 'search') {
        $smarty->assign('title', $shop_configuration['lng_vars']['search_results']);
    } else {
        $smarty->assign('title', $shop_configuration['requested_category']['category']);
    }

    $shop_configuration['cat_products'] = func_fb_shop_display($customer_dir . '/products.tpl');
}
?>
