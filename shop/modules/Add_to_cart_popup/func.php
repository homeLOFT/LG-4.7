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
 * Functions for Add To Cart Popup module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v28 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../../"); die("Access denied"); }

function func_add_to_cart_popup($productid, $add_product, $productindex) {

    global $active_modules, $sql_tbl, $xcart_dir, $smarty, $cart, $products, $config, $user_account;

    $added_product = func_select_product($productid, $user_account['membershipid']);

    $imageIds = array(
        'P' => $productid,
        'T' => $productid,
    );

    if (!empty($add_product['product_options'])) {
        list($variant, $product_options) = func_get_product_options_data($productid, $add_product['product_options']);
        $smarty->assign('product_options', $product_options);

        if ($variant['pimageid']) {
            $imageIds['W'] = $variant['variantid'];
        }

        if (is_array($cart) && is_array($cart['products'])) {
            foreach ($cart['products'] as $cartProduct) {
                if ($cartProduct['cartid'] == $productindex) {
                    $added_product['taxed_price'] = $cartProduct['price'];
                }
            }
        }
    }

    $images = func_get_image_url_by_types($imageIds, 'P');

    if (is_array($images) && is_array($images['images'])) {
        foreach (array('W', 'T', 'P') as $type) {
            if (isset($images['images'][$type])) {
                $image = $images['images'][$type];
                if (is_array($image) && empty($image['is_default'])) {
                    list($image['x'], $image['y']) = func_crop_dimensions(
                        $image['x'],
                        $image['y'],
                        $config['Appearance']['thumbnail_width'],
                        $config['Appearance']['thumbnail_height']
                    );

                    $added_product['image_x'] = $image['x'];
                    $added_product['image_y'] = $image['y'];
                    $added_product['image_type'] = $type;
                    $added_product['image_url'] = $image['url'];

                    break;
                }
            }

        }
    }

    $smarty->assign('product', $added_product);
    $smarty->assign('add_product', $add_product);
    $smarty->assign('product_url', func_get_resource_url('P', $productid));

    $limit2 = 3; // Final limit for func_search_products

    $obj = new XCUpsellingProducts(
        $config['Add_to_cart_popup']['a2c_upselling_type'],
        $config['Add_to_cart_popup']['a2c_period_for_also_bought'],
        $limit2
    );
    $pids = $obj->getUpsellingProducts($productid);

    $upselling = array();
    if (!empty($pids)) {
        $_query = array();
        $_query['query'] = " AND $sql_tbl[products].productid IN ('" . implode("','", $pids) . "')";
        $_query['skip_tables'] = XCSearchProducts::getSkipTablesByTemplate('modules/Add_to_cart_popup/product_added.tpl');

        // Get products data, do not check products availability using these tables
        $_query['skip_tables'] = array_merge($_query['skip_tables'], XCSearchProducts::$tblsToCheckAvailability); #nolint
        $_query['fields'] = array("$sql_tbl[products].product_type");
        $upselling = func_search_products(
            $_query,
            (isset($user_account) && isset($user_account['membershipid']))
                ? max(intval($user_account['membershipid']), 0)
                : 0,
            'skip_orderby',
            $limit2
        );

        if (!empty($upselling)) {
            // get extra data
            foreach ($upselling as $k => $u) {
                $u['product_url'] = func_get_resource_url('P', $u['productid']);
                $u['appearance'] = func_get_appearance_data($u);
                $upselling[$k] = $u;
            }

            if (count($upselling) < $limit2) {
                $upselling = array_pad($upselling, $limit2, array());
            }
        }
    }

    $smarty->assignByRef('upselling', $upselling);

    x_load('minicart');
    $smarty->assign(func_get_minicart_totals());

    func_register_ajax_message(
        'productAdded',
        array(
            'content' => func_display('modules/Add_to_cart_popup/product_added.tpl', $smarty, false, true),
            'title'   => $add_product['amount']
                . ' '
                . ($add_product['amount'] == 1
                    ? func_get_langvar_by_name('lbl_item_added_to_cart', false, false, true)
                    : func_get_langvar_by_name('lbl_items_added_to_cart', false, false, true))
        )
    );

}
