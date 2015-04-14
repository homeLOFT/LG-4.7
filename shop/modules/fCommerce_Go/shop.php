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
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v9 (xcart_4_7_0), 2015-02-17 23:56:28, shop.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */
if (!defined('XCART_START') && !defined('FB_TAB_START')) {
    header('Location: ../../');
    die('Access denied');
}

/**
 * Config variables
 */
$smarty_skin_dir = ($smarty_skin_dir) ? $smarty_skin_dir : '/skin1';

$customer_dir = $xcart_dir . $smarty_skin_dir . '/modules/fCommerce_Go/customer';
$smarty->assign('customer_dir', $customer_dir);

$smarty->assign('ImagesDir', $current_location . $smarty_skin_dir . '/images');
$smarty->assign('SkinDir', $current_location . $smarty_skin_dir);

$shop_configuration = unserialize(stripslashes(func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name = 'fb_shop_admin_configuration'")));

if (empty($shop_configuration)) {
    $shop_configuration = array(
        'bestsellers' => ($active_modules['Bestsellers'] ? 'Y' : ''),
        'featured' => 'Y',
        'cat_icons' => 'Y',
        'cat_tmbn_width' => 90,
        'cat_tmbn_height' => 90,
        'prod_tmbn_width' => 120,
        'prod_tmbn_height' => 120,
        'prod_image_width' => 200,
        'prod_image_height' => 200,
        'per_page' => 6,
        'show_cat_descr' => 'Y',
    );
}

$shop_configuration['soft'] = 'xcart';
$shop_configuration['session'] = array($XCART_SESSION_NAME, $XCARTSESSID);

if (empty($expired)) {

    /**
     * Categories tab menu
     */
    $categories_filter = false;

    $cat = isset($cat) ? intval($cat) : 0;

    if (!empty($shop_configuration['categories_menu'])) {
        $categories_filter = $shop_configuration['categories_menu'];
        $shop_configuration['categories_filter'] = $categories_filter;
    }

    /**
     * Categories menu
     */
    if (!$cat || $cat == 0) {
        define('GET_ALL_CATEGORIES', true);
    }

    if (defined('IS_XCART_44')) {
        x_load('category');
    } else {
        require $xcart_dir . "/include/categories.php";

        if ($cat) {
            extract(func_get_categories_list($cat, true, "level"));
            $categories = $subcategories;
        }
    }

    if ($fb_mode != 'search') {
        $shop_configuration['categories_menu'] = func_fb_shop_prepare_categories($all_categories, $categories, $categories_filter, $cat);
    }

    if ($cat == 0) {
        /**
         * Header text
         */
        if ($fb_mode != 'search') {

            $shop_configuration['header_text'] = stripslashes(func_query_first_cell("SELECT value FROM $sql_tbl[languages] WHERE name = 'txt_fb_shop_header_text' AND code = '$store_language'"));

            $shop_configuration['header_text_liked'] = stripslashes(func_query_first_cell("SELECT value FROM $sql_tbl[languages] WHERE name = 'txt_fb_shop_header_text_liked' AND code = '$store_language'"));

            /**
             * Custom styles
             */
            if (!$_POST['ajax'] && @file_exists($xcart_dir . '/files/fcommerce_skin.css')) {

                $regex = array(
                    "`^([\t\s]+)`ism" => '',
                    "`([:;}{]{1})([\t\s]+)(\S)`ism" => '$1$3',
                    "`(\S)([\t\s]+)([:;}{]{1})`ism" => '$1$3',
                    "`\/\*(.+?)\*\/`ism" => "",
                    "`([\n|\A|;]+)\s//(.+?)[\n\r]`ism" => "$1\n",
                    "`(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+`ism" => "\n"
                );

                $shop_configuration['custom_styles'] = trim(str_replace("\r\n", "\n", preg_replace(array_keys($regex), $regex, file_get_contents($xcart_dir . '/files/fcommerce_skin.css'))), "\r\n");
            }

            /**
             *  HTML <head> includings
             */

            $skin_dir = $smarty->getTemplateVars('SkinDir');
            $shop_configuration['html_head'] = array(
                func_fb_shop_display($customer_dir . '/head_includings.tpl'),
                '<script type="text/javascript" src="' . $skin_dir . '/' . (defined('IS_XCART_44') ? 'js/' : '') . 'common.js"></script>',
                '<script type="text/javascript" src="' . $skin_dir . '/modules/Product_Options/func.js"></script>',
            );
        }
    } else {

        $shop_configuration['requested_category'] = func_get_category_data($cat);

        if ($shop_configuration['requested_category']) {
            $shop_configuration['requested_category']['category'] = $shop_configuration['requested_category']['category'];
            $shop_configuration['requested_category']['description'] = $shop_configuration['requested_category']['description'];
        }

        $smarty->assign('cat', $cat);
    }
}

/**
 * Language variables
 */
$shop_configuration['lng_vars'] = array(
    'home' => func_get_langvar_by_name('lbl_fb_home', false, false, true),
    'view_cart' => func_get_langvar_by_name('lbl_view_cart', false, false, true),
    'add_to_wl' => func_get_langvar_by_name('lbl_add_to_wl', false, false, true),
    'close' => func_get_langvar_by_name('lbl_close', false, false, true),
    'your_cart' => func_get_langvar_by_name('lbl_fb_your_cart', false, false, true),
    'continue_shopping' => func_get_langvar_by_name('lbl_fb_continue_shopping', false, false, true),
    'search_results' => func_get_langvar_by_name('lbl_search_results', false, false, true),
    'browse_by_category' => func_get_langvar_by_name('lbl_fb_browse_by_category', false, false, true),
    'search_for_products' => func_get_langvar_by_name('lbl_fb_search_for_products', false, false, true),
    'save_price' => func_get_langvar_by_name('lbl_save_price', false, false, true),
    'details' => func_get_langvar_by_name('lbl_details', false, false, true),
);

$shop_configuration['site_title'] = (!empty($lbl_site_name) ? $lbl_site_name : $config["Company"]["company_name"]);
$shop_configuration['mode'] = $fb_mode;

$smarty->assign('data', $shop_configuration);

/**
 * Cart processing
 */
if (isset($cart_mode) && empty($expired)) {

    $minicart_changed = true;

    if ($cart_mode == 'add') {

        $price = !empty($price) ? $price : false;

        func_fb_shop_add2cart($productid, $amount, $product_options, $price);
    } elseif ($cart_mode == 'delete' && $cartid) {

        $productid = func_fb_shop_delete_from_cart($cartid);
    }
}

$fb_case = (($fb_mode == 'product_details') ? 'product_details.php' : 'products_list.php');

require $xcart_dir . '/modules/fCommerce_Go/' . $fb_case;

if (isset($get_minicart) && empty($expired)) {
    $shop_configuration['minicart'] = func_fb_shop_show_minicart();
    $shop_configuration['minicart_total_products'] = $smarty->getTemplateVars('minicart_total_products');
}

$shop_configuration['default_charset'] = $smarty->getTemplateVars('default_charset');
?>
