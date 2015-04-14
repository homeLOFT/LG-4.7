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
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v23 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

/**
 * Func display wrappers
 */
function func_fb_shop_display($src, $_echo = false) {
    global $smarty;

    $smarty->compile_check = false;
    $src = func_display($src, $smarty, $_echo);

    return $src;
}

/**
 * Currency format wrapper
 */
function func_fb_shop_currency($value, $alter = false) {

    global $config;

    if (!isset($value))
        return '';

    settype($value, 'float');

    $result = '';

    $symbol = 'currency_symbol';
    $format = 'currency_format';

    if ($alter) {

        if (empty($config['General']['alter_currency_symbol'])) {
            return '';
        }

        $value = $value * $config['General']['alter_currency_rate'];
        $symbol = 'alter_currency_symbol';
        $format = 'alter_currency_format';
    }

    $cf_value = func_format_number(abs($value));

    $result .= str_replace('$', $config['General'][$symbol], str_replace('x', $cf_value, $config['General'][$format]));

    return $result;
}

/**
 * Crop image dimensions by limit
 */
function func_fb_shop_crop_dimensions($x, $y, $limit_x, $limit_y) {

    if ($x <= 0 || $y <= 0)
        return array($x, $y);

    if ($limit_x > 0 && $limit_y > 0) {

        $kx = $x > $limit_x ? $limit_x / $x : 1;
        $ky = $y > $limit_y ? $limit_y / $y : 1;
        $k = $kx < $ky ? $kx : $ky;
    } elseif ($limit_x > 0) {

        $k = $x > $limit_x ? $limit_x / $x : 1;
    } elseif ($limit_y > 0) {

        $k = $y > $limit_y ? $limit_y / $y : 1;
    } else {

        $k = 1;
    }

    return array(round($k * $x, 0), round($k * $y, 0));
}

/**
 * Preparing products (or categories) list for appearance
 * If $prefix is "C" - it works for categories
 * in other case for $prefix is "P - it does work for products
 * 
 * This function uses in array_walk
 */
function func_fb_shop_prepare_products(&$item, $key, $prefix = 'T') {

    global $shop_configuration, $sql_tbl, $current_location, $expired;

    x_load('files');

    /**
     * Determine if it'll work for categories or products
     */
    $needle = ($prefix == 'C') ? 'image_path' : 'tmbn_url';
    $sign = ($prefix == 'C') ? 'categoryid' : 'productid';

    $_path_sign = (func_fb_shop_constant('IS_XCART_42')) ? 1 : '';


    /**
     * Getting image for item
     */
    if (!is_url($item[$needle])) {
        $item[$needle] = func_get_image_url($item[$sign], $prefix, $_path_sign);

        if (!is_url($item[$needle]) && $prefix == 'T') {
            $prefix = 'P';
            $item[$needle] = func_get_image_url($item[$sign], $prefix, $_path_sign);
        }

        if (!is_url($item[$needle]) && strpos($item[$needle], 'image.php') != false) {
            $item[$needle] = $current_location . '/' . $item[$needle];
        }

        if (!is_url($item[$needle])) {
            $item[$needle] = 'tpls/tab/images/default_image.png';
            $item['default_image'] = true;
        }
    }

    /**
     * Getting correct sizes for item's image
     */
    if (!$item['image_x'] && !$item['image_y']) {
        list($item['image_x'], $item['image_y']) = @array_values(func_query_first("SELECT image_x, image_y FROM " . $sql_tbl['images_' . $prefix] . " WHERE id = '$item[$sign]'"));
    }

    $needle_x = ($prefix == 'C') ? $shop_configuration['cat_tmbn_width'] : $shop_configuration['prod_tmbn_width'];
    $needle_y = ($prefix == 'C') ? $shop_configuration['cat_tmbn_height'] : $shop_configuration['prod_tmbn_height'];

    if (!$item['image_x'] && !$item['image_y']) {
        $item['image_x'] = $item['tmbn_x'] = $needle_x;
        $item['image_y'] = $item['tmbn_y'] = $needle_y;
    } else {
        list($item['tmbn_x'], $item['tmbn_y']) = func_fb_shop_crop_dimensions($item['image_x'], $item['image_y'], $needle_x, $needle_y);
    }

    /**
     * Getting "Appearance" data for a product
     */
    if ($prefix != 'C') {

        $item['appearance'] = func_fb_shop_get_appearance_data($item);

        $item['categoryid'] = func_query_first_cell("SELECT categoryid FROM $sql_tbl[products_categories] WHERE productid = '$item[productid]' AND main = 'Y'");

        if ($expired) {

            global $smarty, $customer_dir, $config;

            $_taxed_price = $item['taxed_price'];

            $smarty->assign('value', $_taxed_price);
            $item['taxed_price'] = func_fb_shop_display($customer_dir . '/currency.tpl');

            if ($config['General']['alter_currency_symbol']) {
                $smarty->assign('alter_currency_value', $_taxed_price);
                $item['alter_price'] = func_fb_shop_display($customer_dir . '/alter_currency_value.tpl');
            }

            if ($item['appearance']['has_market_price'] && $item['appearance']['market_price_discount'] > 0) {
                $smarty->assign('value', $item['list_price']);
                $item['list_price'] = func_fb_shop_display($customer_dir . '/currency.tpl');
            }

            unset($_taxed_price);
        }
        $item['url'] = (($config['SEO']['clean_urls_enabled'] == 'Y') ? func_clean_url_get('P', $item['productid']) : $current_location . '/product.php?productid=' . $item['productid']);
    }
}

/**
 * Wrapper for func_select_product function
 * needed in old X-Cart vesrions
 */
function func_fb_shop_select_product($id, $membershipid, $redirect_if_error = true, $clear_price = false, $always_select = false, $prefered_image_type = 'P') {

    global $logged_userid, $login_type, $current_area, $single_mode, $cart, $current_location;
    global $store_language, $sql_tbl, $config, $active_modules;

    x_load('files', 'taxes', 'image');

    $in_cart = 0;

    $id = intval($id);

    $membershipid = intval($membershipid);

    $p_membershipid_condition = $membershipid_condition = '';

    $membershipid_condition = " AND ($sql_tbl[category_memberships].membershipid = '$membershipid' OR $sql_tbl[category_memberships].membershipid IS NULL) ";
    $p_membershipid_condition = " AND ($sql_tbl[product_memberships].membershipid = '$membershipid' OR $sql_tbl[product_memberships].membershipid IS NULL) ";
    $price_condition = " AND $sql_tbl[quick_prices].membershipid " . ((empty($membershipid) || empty($active_modules['Wholesale_Trading'])) ? "= '0'" : "IN ('$membershipid', '0')") . " AND $sql_tbl[quick_prices].priceid = $sql_tbl[pricing].priceid";


    if (
            !empty($cart)
            && !empty($cart['products'])
    ) {

        foreach ($cart['products'] as $cart_item) {

            if ($cart_item['productid'] == $id) {

                $in_cart += $cart_item['amount'];
            }
        }
    }

    $login_condition = '';

    if (!$single_mode) {
        $login_condition = ((!empty($logged_userid) && $login_type == 'P') ? "AND $sql_tbl[products].provider='$logged_userid'" : "");
    }

    $add_fields = '';

    $join = '';

    $join .= " INNER JOIN $sql_tbl[quick_flags] ON $sql_tbl[products].productid = $sql_tbl[quick_flags].productid";

    if (!empty($active_modules['Feature_Comparison'])) {
        $join .= " LEFT JOIN $sql_tbl[product_features] ON $sql_tbl[product_features].productid = $sql_tbl[products].productid";
        $add_fields .= ", $sql_tbl[product_features].fclassid";
    }

    if (!empty($active_modules['Manufacturers'])) {
        $join .= " LEFT JOIN $sql_tbl[manufacturers] ON $sql_tbl[manufacturers].manufacturerid = $sql_tbl[products].manufacturerid";
        $add_fields .= ", $sql_tbl[manufacturers].manufacturer";
    }

    if (!empty($active_modules['Special_Offers']) && isset($sql_tbl['offer_product_params'])) {
        $join .= " LEFT JOIN $sql_tbl[offer_product_params] ON $sql_tbl[offer_product_params].productid = $sql_tbl[products].productid";
        $add_fields .= ", $sql_tbl[offer_product_params].sp_discount_avail, $sql_tbl[offer_product_params].bonus_points";
    }



    if (empty($membershipid) || empty($active_modules['Wholesale_Trading'])) {
        $membershipid_condition = " = '0'";
    } else {
        $membershipid_condition = " IN ('$membershipid', 0)";
    }

    /**
     * Workaround for the XC4.5.x where the new 'products_lng_current' table was added
     */
    if (strnatcmp($config['version'], '4.5') >= 0) {
        /* XC 4.5.x */
        $add_fields .= ", $sql_tbl[quick_flags].*, $sql_tbl[quick_prices].variantid, $sql_tbl[quick_prices].priceid";

        $join .= " INNER JOIN $sql_tbl[quick_prices] ON $sql_tbl[products].productid = $sql_tbl[quick_prices].productid AND $sql_tbl[quick_prices].membershipid $membershipid_condition ";

        $join .= " INNER JOIN $sql_tbl[products_lng_current] ON $sql_tbl[products_lng_current].productid=$sql_tbl[products].productid ";
    } else {
        /* all the other versions of XC */
        $add_fields .= ", IF($sql_tbl[products_lng].product != '' AND $sql_tbl[products_lng].product IS NOT NULL, $sql_tbl[products_lng].product, $sql_tbl[products].product) as product, IF($sql_tbl[products_lng].descr != '' AND $sql_tbl[products_lng].descr IS NOT NULL, $sql_tbl[products_lng].descr, $sql_tbl[products].descr) as descr, IF($sql_tbl[products_lng].fulldescr != '' AND $sql_tbl[products_lng].fulldescr IS NOT NULL, $sql_tbl[products_lng].fulldescr, $sql_tbl[products].fulldescr) as fulldescr, $sql_tbl[quick_flags].*, $sql_tbl[quick_prices].variantid, $sql_tbl[quick_prices].priceid";

        $join .= " INNER JOIN $sql_tbl[quick_prices] ON $sql_tbl[products].productid = $sql_tbl[quick_prices].productid AND $sql_tbl[quick_prices].membershipid $membershipid_condition LEFT JOIN $sql_tbl[products_lng] ON $sql_tbl[products_lng].code='$store_language' AND $sql_tbl[products_lng].productid = $sql_tbl[products].productid ";
    }

    $join .= " LEFT JOIN $sql_tbl[product_memberships] ON $sql_tbl[product_memberships].productid = $sql_tbl[products].productid";

    if (empty($active_modules['Product_Configurator'])) {
        $login_condition .= " AND $sql_tbl[products].product_type <> 'C' AND $sql_tbl[products].forsale <> 'B' ";
    }

    if ($config['SEO']['clean_urls_enabled']) {
        $join .= " LEFT JOIN $sql_tbl[clean_urls] ON $sql_tbl[clean_urls].resource_type = 'P' AND $sql_tbl[clean_urls].resource_id = $sql_tbl[products].productid";

        $add_fields .= ", $sql_tbl[clean_urls].clean_url, $sql_tbl[clean_urls].mtime";
    }
    /**
     * Workaround for the XC4.5.x where the new 'products_lng_current' table was added
     */
    if (strnatcmp($config['version'], '4.5') >= 0) {

        $product = func_query_first("SELECT $sql_tbl[products].*, $sql_tbl[products].avail-$in_cart AS avail, MIN($sql_tbl[pricing].price) as price $add_fields , $sql_tbl[products_lng_current].* FROM $sql_tbl[pricing] INNER JOIN $sql_tbl[products] ON $sql_tbl[pricing].productid = $sql_tbl[products].productid AND $sql_tbl[products].productid='$id' $join WHERE 1 " . $login_condition . $p_membershipid_condition . $price_condition . " GROUP BY $sql_tbl[products].productid ORDER BY NULL");
    } else {

        $product = func_query_first("SELECT $sql_tbl[products].*, $sql_tbl[products].avail-$in_cart AS avail, MIN($sql_tbl[pricing].price) as price $add_fields FROM $sql_tbl[pricing] INNER JOIN $sql_tbl[products] ON $sql_tbl[pricing].productid = $sql_tbl[products].productid AND $sql_tbl[products].productid='$id' $join WHERE 1 " . $login_condition . $p_membershipid_condition . $price_condition . " GROUP BY $sql_tbl[products].productid ORDER BY NULL");
    }

    $categoryid = func_query_first_cell("SELECT $sql_tbl[products_categories].categoryid FROM $sql_tbl[products_categories] INNER JOIN $sql_tbl[categories] ON $sql_tbl[products_categories].categoryid=$sql_tbl[categories].categoryid LEFT JOIN $sql_tbl[category_memberships] ON $sql_tbl[category_memberships].categoryid = $sql_tbl[categories].categoryid WHERE $sql_tbl[products_categories].productid = '$id' ORDER BY main DESC");

    // Check product's provider Activity
    if (
            !$single_mode
            && !empty($product)
    ) {
        if (defined('IS_XCART_44')) {
            x_load('user'); // For XCUserActivity
            if (!XCUserActivity::isActivatedUser($product['provider']))
                $product = array();
        } else {
            if (!func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[customers] WHERE login = '$product[provider]' AND activity='Y'"))
                $product = array();
        }
    }

    // Error handling

    if (
            !$product
            || !$categoryid
    ) {

        $product_is_exists = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[products] WHERE productid = '$id'") > 0;

        if ($product_is_exists) {
            $product['disabled_product'] = true;
            $product['disabled_product_message'] = 'Access denied';
        } else {
            $product['disabled_product'] = true;
            $product['disabled_product_message'] = 'Product not found';
        }

        return $product;
    }

    $product['productid'] = $id;
    $product['categoryid'] = $categoryid;

    $tmp = func_query_column("SELECT membershipid FROM $sql_tbl[product_memberships] WHERE productid = '$product[productid]'");

    if (!empty($tmp) && is_array($tmp)) {

        $product['membershipids'] = array();

        foreach ($tmp as $v) {
            $product['membershipids'][$v] = 'Y';
        }
    }

    if (!empty($product['variantid']) && !empty($active_modules['Product_Options'])) {

        $tmp = XCVariantsSQL::getVariantById($product['productid'], $product['variantid']);

        if (!empty($tmp)) {

            func_unset($tmp, 'def');

            $product = func_array_merge($product, $tmp);
        } else {

            func_unset($product, 'variantid');
        }
    }

    // Detect product thumbnail and image
    $image_ids = array();

    if (
            !empty($product['variantid'])
            && !empty($active_modules['Product_Options'])
    ) {
        $image_ids['W'] = $product['variantid'];
    }

    $image_ids['P'] = $product['productid'];
    $image_ids['T'] = $product['productid'];

    $image_data = func_fb_shop_get_image_url_by_types($image_ids, $prefered_image_type);

    $product['taxed_price'] = $product['price'];

    // Check if product is not available for sale

    if (empty($active_modules['Egoods']))
        $product['distribution'] = '';

    global $pconf;

    if ($product['forsale'] == 'B' && empty($pconf)) {

        if (
                isset($cart['products'])
                && is_array($cart['products'])
        ) {

            foreach ($cart['products'] as $k => $v) {

                if ($v['productid'] == $product['productid']) {

                    $pconf = $product['productid'];

                    break;
                }
            }
        }

        if (empty($pconf)) {

            x_session_register('configurations');

            global $configurations;

            if (!empty($configurations)) {

                foreach ($configurations as $c) {

                    if (empty($c['steps']) || !is_array($c['steps']))
                        continue;

                    foreach ($c['steps'] as $s) {
                        if (empty($s['slots']) || !is_array($s['slots']))
                            continue;

                        foreach ($s['slots'] as $sl) {
                            if ($sl['productid'] == $product["productid"]) {
                                $pconf = $product['productid'];
                                break;
                            }
                        }
                    }
                }
            }
        }
    }

    if (
            !$always_select
            && (
            $product['forsale'] == 'N'
            || (
            $product['forsale'] == 'B'
            && empty($pconf)
            )
            )
    ) {

        $product_info['disabled_product'] = true;
        $product['disabled_product_message'] = 'Product disabled';

        return $product;
    }

    if (!$clear_price) {

        // Calculate taxes and price including taxes

        global $logged_userid;

        $orig_price = $product['price'];

        $product['taxes'] = func_get_product_taxes($product, $logged_userid);

        // List price corrections
        if (($product['taxed_price'] != $orig_price) && ($product['list_price'] > 0))
            $product['list_price'] = price_format($product['list_price'] * $product['taxed_price'] / $orig_price);
    }

    $product['is_thumbnail'] = func_query_first_cell("SELECT id FROM $sql_tbl[images_T] WHERE id = '$product[productid]'") != false;
    $product['is_pimage'] = func_query_first_cell("SELECT id FROM $sql_tbl[images_P] WHERE id = '$product[productid]'") != false;

    if ($product['is_thumbnail']) {

        list($x, $y) = func_fb_shop_crop_dimensions(
                $image_data['images']['T']['x'], $image_data['images']['T']['y'], $config['images_dimensions']['T']['width'], $config['images_dimensions']['T']['height']
        );

        if (
                $image_data['images']['T']['x'] <= $x
                && $image_data['images']['T']['y'] <= $y
        ) {
            $x = $image_data['images']['T']['x'];
            $y = $image_data['images']['T']['y'];
        }

        $image_data['images']['T']['new_x'] = $x;
        $image_data['images']['T']['new_y'] = $y;
    }

    if ($product['is_pimage']) {

        list($x, $y) = func_fb_shop_crop_dimensions(
                $image_data['images']['P']['x'], $image_data['images']['P']['y'], $config['images_dimensions']['P']['width'], $config['images_dimensions']['P']['height']
        );

        if (
                $image_data['images']['P']['x'] <= $x
                && $image_data['images']['P']['y'] <= $y
        ) {
            $x = $image_data['images']['P']['x'];
            $y = $image_data['images']['P']['y'];
        }

        $image_data['images']['P']['new_x'] = $x;
        $image_data['images']['P']['new_y'] = $y;
    }

    // Add product features
    if (
            !empty($active_modules['Feature_Comparison'])
            && $product['fclassid'] > 0
    ) {
        $product['features'] = func_get_product_features($product['productid']);
    }

    if (
            !empty($active_modules['Special_Offers'])
            && empty($product['sp_discount_avail'])
    ) {
        $product['sp_discount_avail'] = 'Y';
    }

    $product['producttitle'] = $product['product'];

    if (function_exists('func_eol2br')) {
        $product['descr'] = func_eol2br($product['descr']);
        $product['fulldescr'] = func_eol2br($product['fulldescr']);
    }

    if (function_exists('func_get_allow_active_content')) {
        $product['allow_active_content'] = func_get_allow_active_content($product['provider']);
    }

    if (!$product['allow_active_content'] && function_exists('func_xss_free')) {
        $product['descr'] = func_xss_free($product['descr']);
        $product['fulldescr'] = func_xss_free($product['fulldescr']);
    }
    // Get thumbnail's URL (uses only if images stored in FS)

    if (is_array($image_data)) {

        list($image_data['image_x'], $image_data['image_y']) = func_fb_shop_crop_dimensions(
                $image_data['image_x'], $image_data['image_y'], $config['Appearance']['image_width'], $config['Appearance']['image_height']
        );

        $product = array_merge($product, $image_data);
    }

    if (!is_url($product['image_url'])) {
        $product['image_url'] = 'tpls/tab/images/default_image.png';
        $product['default_image'] = true;
        if ($product['default_image']) {
            $product['fb_like_image_url'] = $current_location . '/default_image.gif';
        }
        $product['image_x'] = $config['Appearance']['image_width'];
        $product['image_y'] = $config['Appearance']['image_height'];

        $product['tmbn_url'] = $product['image_url'];
    }

    $product['full_url'] = $current_location . '/product.php?productid=' . $id;

    if ($config['SEO']['clean_urls_enabled'] == 'Y') {
        $product['full_url'] = func_clean_url_get('P', intval($id));
    }

    $product['appearance'] = func_fb_shop_get_appearance_data($product);

    return $product;
}

/**
 * Wrapper for func_get_appearance_data
 * needed in the old X-Cart versions
 */
function func_fb_shop_get_appearance_data($product) {

    global $config, $active_modules, $current_area, $login, $is_comparison_list;

    $appearance = array(
        'empty_stock' => $config['General']['unlimited_products'] != "Y"
        && (
        $product['avail'] <= 0
        || $product['avail'] < $product['min_amount']
        ),
        'has_price' => $product['taxed_price'] > 0
        || (
        !empty($product['variantid'])
        && isset($product['variants_has_price'])
        && !empty($product['variants_has_price'])
        ),
        'has_market_price' => $product['list_price'] > 0
        && $product['taxed_price'] < $product['list_price'],
        'buy_now_enabled' => $current_area == 'C'
        && $config['Appearance']['buynow_button_enabled'] == "Y",
        'buy_now_form_enabled' => $product['price'] > 0
        || (
        !empty($active_modules['Special_Offers'])
        && isset($product['use_special_price'])
        ) || $product['product_type'] == 'C',
        'min_quantity' => max(1, $product['min_amount']),
        'max_quantity' => $config['General']['unlimited_products'] == "Y" ? max($config['Appearance']['max_select_quantity'], $product['min_amount']) : min(max($config['Appearance']['max_select_quantity'], $product['min_amount']), $product['avail']),
        'buy_now_buttons_enabled' => $config['General']['unlimited_products'] == "Y"
        || (
        $product['avail'] > 0
        && $product['avail'] >= $product['min_amount']
        ) || (
        !empty($product['variantid'])
        && $product['avail'] > 0
        ),
        'force_1_amount' => $product['distribution']
        || (
        !empty($active_modules['Subscriptions'])
        && !empty($product['catalogprice'])
        ),
    );

    $appearance['quantity_input_box_enabled'] = $config['Appearance']['show_quantity_as_box'] == 'Y';

    $appearance['is_auction'] = !(
            (
            $appearance['empty_stock']
            && !empty($product['variantid'])
            ) || (
            $product['taxed_price'] != 0
            || (
            !empty($product['variantid'])
            && isset($product['variants_has_price'])
            && $product['variants_has_price']
            ) || (
            !empty($active_modules['Special_Offers'])
            && isset($product['use_special_price'])
            && $product['use_special_price']
            )
            )
            );

    if ($appearance['has_market_price'])
        $appearance['market_price_discount'] = sprintf("%3.0f", 100 - ($product['taxed_price'] / $product['list_price']) * 100);

    $cart_enabled_product_options = isset($product['is_product_options']) && $product['is_product_options'] == 'Y' ? $config['Product_Options']['buynow_with_options_enabled'] != 'Y' : true;

    $cart_enabled_avail = $config['General']['unlimited_products'] == "Y" ? true : $product['avail'] > 0 || empty($product['variantid']) || !$product['variantid'];

    $appearance['buy_now_cart_enabled'] = $appearance['buy_now_form_enabled'] && $cart_enabled_product_options && $cart_enabled_avail;

    $appearance['loop_quantity'] = $appearance['max_quantity'] + 1;

    $appearance['buy_now_add2wl_enabled'] = (
            $login
            || $config['Wishlist']['add2wl_unlogged_user'] == 'Y'
            ) && !empty($active_modules['Wishlist'])
            && $appearance['buy_now_buttons_enabled'];

    // Add to list button
    global $giftreg_events;

    if (
            $appearance['buy_now_add2wl_enabled']
            && (
            (
            !empty($active_modules['Feature_Comparison'])
            && !empty($product['fclassid'])
            ) || (
            !empty($active_modules['Gift_Registry'])
            && isset($giftreg_events)
            && !empty($giftreg_events)
            )
            )
    ) {
        $appearance['dropout_actions'] = array(
            'W' => true,
            'C' => (!empty($active_modules['Feature_Comparison']) && (!empty($product["fclassid"]))),
            'G' => (!empty($active_modules['Gift_Registry']) && !empty($giftreg_events)),
        );
    }

    return $appearance;
}

/**
 * Get images URL by pairs 'type' => 'id'
 */
function func_fb_shop_get_image_url_by_types($ids, $prefered_image_type = false) {
    global $sql_tbl, $config;
    static $result = array();

    $key = md5(serialize($ids) . $prefered_image_type);
    if (isset($result[$key]))
        return $result[$key];

    $data = array('images' => array());

    $query = array();
    foreach ($ids as $type => $id) {
        if (!isset($config['available_images'][$type])) {
            unset($ids[$type]);
            continue;
        }

        $query[] = 'SELECT \'' . $type . '\' as image_type, id, image_path, image_x, image_y FROM ' . $sql_tbl['images_' . $type] . ' WHERE id = \'' . $id . '\'';
    }

    $image_data = func_query_hash(implode(' UNION ', $query), 'image_type', false);

    $return_type = '';

    foreach ($ids as $type => $id) {

        if (
                !isset($image_data[$type])
                || !is_array($image_data[$type])
                || !isset($image_data[$type]['id'])
        ) {

            $data['images'][$type] = array(
                'url' => func_get_default_image($type),
                'x' => $config['setup_images'][$type]['image_x'],
                'y' => $config['setup_images'][$type]['image_y'],
                'is_default' => true,
            );
        } else {

            $d = $image_data[$type];

            $data['images'][$type] = array(
                'url' => func_get_image_url($d['id'], $type, $d['image_path']),
                'x' => $d['image_x'],
                'y' => $d['image_y'],
                'id' => $d['id'],
            );

            if ($return_type == '') {
                $return_type = $type;
            }
        }
    }

    if (
            $return_type != $prefered_image_type
            && isset($image_data[$prefered_image_type])
            && !$data['images'][$prefered_image_type]['is_default']
    ) {
        $return_type = $prefered_image_type;
    }

    // thumbnail and product image are not defined for the product
    if ($return_type === '') {
        $return_type = $prefered_image_type;
    }

    if (
            isset($data['images'][$return_type]['is_default'])
            && $data['images'][$return_type]['is_default'] !== false
    ) {
        foreach ($data['images'] as $image_type => $image_data) {
            if (!$image_data['is_default']) {
                $return_type = $image_type;
                break;
            }
        }
    }

    $data['image_url'] = $data['images'][$return_type]['url'];
    $data['image_x'] = $data['images'][$return_type]['x'];
    $data['image_y'] = $data['images'][$return_type]['y'];
    $data['image_type'] = $return_type;
    if (isset($data['images'][$return_type]['id']))
        $data['image_id'] = $data['images'][$return_type]['id'];

    $result[$key] = $data;

    return $data;
}

/**
 * Cart functions
 */
function func_fb_shop_add2cart($productid, $amount, $product_options = false, $price = false) {

    global $active_modules, $xcart_dir, $cart, $fb_cart, $user_account, $logged_userid, $current_area, $minicart_changed;

    x_load('cart', 'product');

    x_session_register('cart');

    x_session_register('fb_cart');

    if (!defined('IS_XCART_44')) {
        include_once $xcart_dir . "/include/cart_process.php";
    }

    $minicart_changed = true;

    $_hash_01 = func_fb_shop_get_cart_hash($cart);

    $add_product = array(
        'productid' => abs($productid),
        'amount' => abs($amount),
        'product_options' => isset($product_options) ? $product_options : array(),
        'price' => func_convert_number($price)
    );

    if ($active_modules['Special_Offers']) {

        if (isset($prod_amounts) && is_array($prod_amounts)) {
            foreach ($prod_amounts as $item_prod_id => $item_amount) {
                $prod_set_product = array(
                    'productid' => $item_prod_id,
                    'amount' => $item_amount,
                );
                $result = func_add_to_cart($cart, $prod_set_product);
            }
        }

        if ($add_product['price'] == 0 && isset($is_free_product) && $is_free_product == 'Y') {
            $add_product['is_free_product'] = $is_free_product;
        }
    }

    $result = func_add_to_cart($cart, $add_product);

    if ($result) {
        $fb_cart[$result['productindex']] = $productid;
        $fb_cart[$productid] = $result;
    }

    if (!empty($cart['products'])) {
        foreach ($cart['products'] as $k => $prod) {
            if ($prod['productid'] == $productid) {
                $cart['products'][$k]['extra_data']['added_in_facebook'] = true;
            }
        }
    }

    $intershipper_recalc = 'Y';

    // Recalculate cart totals after new item added
    $products = func_products_in_cart(
            $cart, (!empty($user_account['membershipid']) ? $user_account['membershipid'] : ''
            )
    );

    $cart = func_array_merge(
            $cart, func_calculate(
                    $cart, $products, $logged_userid, $current_area, 0
            )
    );


    if ($active_modules['Special_Offers']) {

        if (!empty($cart['not_used_free_products']['A'])) {

            foreach ($cart['not_used_free_products']['A'] as $productid => $amount) {

                if ($amount <= 0)
                    continue;

                if (!isset($cart['sp_deleted_products'][$productid]) || $add_product['productid'] == $productid) {
                    $free_product = array(
                        'productid' => $productid,
                        'amount' => $amount,
                        'price' => 0.00,
                        'is_free_product' => 'Y',
                    );

                    $res = func_add_to_cart($cart, $free_product);
                }
            }
        }

        $func_is_cart_empty = func_is_cart_empty($cart);

        $products = func_products_in_cart($cart, $user_account['membershipid']);

        $cart = func_array_merge(
                $cart, func_calculate(
                        $cart, $products, $logged_userid, $current_area, 0
                )
        );
    }

    $_hash_02 = func_fb_shop_get_cart_hash($cart);

    if (defined('XC_SESSION_DB_SAVE_FCOMMERCE_GO')) {
        x_session_save();
    }

    if ($_hash_01 == $_hash_02) {
        $minicart_changed = false;
    }

    return true;
}

function func_fb_shop_delete_from_cart($productindex) {

    global $active_modules, $config, $xcart_dir, $sql_tbl, $cart, $fb_mode, $fb_cart, $current_area, $logged_userid, $user_account;

    x_load('cart', 'product');

    x_session_register('cart');
    x_session_register('fb_cart');

    if (!defined('IS_XCART_44')) {
        include_once $xcart_dir . "/include/cart_process.php";
    }

    $mode = 'delete';

    if (!empty($active_modules['Product_Configurator'])) {

        include $xcart_dir . '/modules/Product_Configurator/pconf_customer_cart.php';
    }

    $productid = 0;
    $quantity = 0;

    if ($cart['products']) {

        foreach ($cart['products'] as $k => $v) {

            if ($v['cartid'] == $productindex) {

                unset($fb_cart[$productindex]);

                $productid = $v['productid'];
                $quantity = $v['amount'];

                array_splice($cart['products'], $k, 1);

                break;
            }
        }
    }

    if ($active_modules['Special_Offers'] && $productid) {

        $cart['sp_deleted_products'][$productid] = true;
    }

    if ($productid > 0) {

        $intershipper_recalc = 'Y';

        // Recalculate cart totals after updating
        $products = func_products_in_cart($cart, $user_account['membershipid']);

        $cart = func_array_merge(
                $cart, func_calculate(
                        $cart, $products, $logged_userid, $current_area, 0
                )
        );
    }

    if (defined('XC_SESSION_DB_SAVE_FCOMMERCE_GO')) {
        x_session_save();
    }

    if ($fb_mode != 'product_details') {
        unset($productid);
    }

    return $productid;
}

function func_fb_shop_get_cart_hash($cart) {
    global $active_modules, $xcart_dir;

    if (empty($cart['products']))
        return false;

    $hash = array();

    foreach ($cart['products'] as $k => $p) {
        if ($p['hidden'] || !empty($p['pconf_data']))
            continue;

        $po = (!empty($p['options']) && is_array($p['options']) ? serialize($p['options']) : "");
        $key = $p['productid'] . $po . $p['free_price'] . '-a' . doubleval($p['amount']);

        if (isset($p['free_amount'])) {
            # for X-SpecialOffers
            $key .= '-fa' . doubleval($p['free_amount']);
        }

        $hash[$key] = $k;
    }

    if (
            !empty($active_modules["Product_Configurator"])
            && file_exists($xcart_dir . "/modules/Product_Configurator/pconf_customer_cart_normalization.php")
    ) {
        include $xcart_dir . "/modules/Product_Configurator/pconf_customer_cart_normalization.php";
        if (!empty($pconf_hash) && is_array($pconf_hash)) {
            $hash = array_merge($hash, $pconf_hash);
        }
    }

    if (!empty($hash)) {
        $hash = md5(implode('', array_flip($hash)));
    }

    return $hash;
}

function func_fb_shop_show_minicart() {

    global $cart, $user_account, $smarty, $productid, $customer_dir;

    x_session_register('cart');

    x_load('cart');

    $products = func_products_in_cart($cart, (!empty($user_account['membershipid']) ? $user_account['membershipid'] : ''));

    if ($products) {
        $smarty->assign('cart_products', $products);
        $smarty->assign('cart', $cart);
        $smarty->assign('cart_subtotal', $cart['display_subtotal']);
    }

    $smarty->assign('minicart_total_products', @count($products));

    return func_fb_shop_display($customer_dir . '/minicart.tpl');
}

/**
 * Product template filter
 */
function func_fb_shop_product_template_prefilter($tpl_source, &$smarty) {

    if ($smarty->template_resource == 'modules/Product_Options/check_options.tpl') {
        $tpl_source = str_replace('{include file="main/include_js.tpl" src="modules/Product_Options/func.js"}', '', $tpl_source);
        $tpl_source = str_replace('<script type="text/javascript" src="{$SkinDir}/modules/Product_Options/func.js"></script>', '', $tpl_source);
    }

    return $tpl_source;
}

/**
 * Parse product desription content
 */
function func_fb_parse_descr_img_src($matches) {

    global $current_location;

    if (!is_url($matches[3])
            && !strpos($matches[3], 'javascript:')
            && !strpos($matches[3], 'void(')
            && !strpos($matches[3], 'window.open')
    ) {

        $matches[0] = str_replace($matches[3], $current_location . '/' . $matches[3], $matches[0]);
    }

    return $matches[0];
}

?>
