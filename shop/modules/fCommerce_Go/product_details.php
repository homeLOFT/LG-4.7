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
\**************************************************************************** */

/**
 * Home / category page interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v7 (xcart_4_7_0), 2015-02-17 23:56:28, product_details.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

x_load('product', 'templater');

$config['Appearance']['image_width'] = $shop_configuration['prod_image_width'];
$config['Appearance']['image_height'] = $shop_configuration['prod_image_height'];

$productid = !empty($curr_pid) ? intval($curr_pid) : $productid;

/**
 * Put all product info into $product array
 */
$cat = isset($cat) ? abs(intval($cat)) : 0;

$product_info = func_fb_shop_select_product($productid, @$user_account['membershipid']);
//$product_info = func_select_product($productid, @$user_account['membershipid'], false);

func_fb_shop_prepare_products($product_info, false, 'P');

if (!$product_info['disabled_product']) {

    if (!empty($active_modules['Detailed_Product_Images'])) {
        include $xcart_dir . '/modules/Detailed_Product_Images/product_images.php';
        $product_info['detailed_images'] = $images;
    }

    if (!empty($active_modules['Gift_Registry'])) {
        include $xcart_dir . '/modules/Gift_Registry/customer_wlproduct.php';
    }

    if (!empty($active_modules['Product_Options'])) {
        include $xcart_dir . '/modules/Product_Options/customer_options.php';
    }

    if ($product_info['product_type'] != 'C') {

        // If this product is not configurable

        if (
            $config['General']['show_outofstock_products'] != 'Y'
            && empty($product_info['distribution'])
        ) {

            $is_avail = true;

            if (
                $product_info['avail'] <= 0
                && empty($variants)
            ) {

                $is_avail = false;
            } elseif (!empty($variants)) {

                $is_avail = false;

                foreach ($variants as $v) {

                    if ($v['avail'] > 0) {

                        $is_avail = true;

                        break;
                    }
                }
            }

            if (
                    !empty($cart['products'])
                    && !$is_avail
            ) {

                foreach ($cart['products'] as $v) {

                    if ($product_info['productid'] == $v['productid']) {

                        $is_avail = true;

                        break;
                    }
                }
            }

            if (!$is_avail) {

                $product_info['disabled_product'] = true;

                $product_info['disabled_product_message'] = 'Product disabled';
            }
        }

        if (!empty($active_modules['Extra_Fields'])) {

            $extra_fields_provider = $product_info['provider'];

            include $xcart_dir . '/modules/Extra_Fields/extra_fields.php';
        }

        if (!empty($active_modules['Subscriptions'])) {

            $_products = $products;

            $products = array($product_info);

            include_once $xcart_dir . '/modules/Subscriptions/subscription.php';

            $products = $_products;
        }

        if (!empty($active_modules['Feature_Comparison']))
            include $xcart_dir . '/modules/Feature_Comparison/product.php';

        if (!empty($active_modules['Wholesale_Trading']) && empty($product_info['variantid']))
            include $xcart_dir . '/modules/Wholesale_Trading/product.php';

        if (
                !empty($active_modules['Product_Configurator'])
                && !empty($_GET['pconf'])
                && $mode != 'add_vote'
        ) {
            include $xcart_dir . '/modules/Product_Configurator/slot_product.php';
        }
    }

    $smarty->assign('max_image_width', $config['Appearance']['image_width']);
    $smarty->assign('max_image_height', $config['Appearance']['image_height']);


    /** Commented for a while
      if (!empty($active_modules['Special_Offers'])) {
      include $xcart_dir . '/modules/Special_Offers/product_offers.php';
      }
     */

    $product_info['quantity_input_box_enabled'] = ($config['Appearance']['show_quantity_as_box'] == 'Y');

    /**
     * Descriptions parser
     */
    if (!empty($product_info['fulldescr'])) {
        $product_info['fulldescr'] = preg_replace_callback('/< *(a|img)[^>]*(href|src) *= *["\']?([^"\']*)/is', 'func_fb_parse_descr_img_src', $product_info['fulldescr']);
    } elseif (!empty($product_info['descr'])) {
        $product_info['descr'] = preg_replace_callback('/< *(a|img)[^>]*(href|src) *= *["\']?([^"\']*)/is', 'func_fb_parse_descr_img_src', $product_info['descr']);
    }
    
} // endif: $product_info['disabled_product']

$smarty->assign('product', $product_info);

$shop_configuration['product_title'] = $product_info['product'];

$smarty->register_prefilter('func_fb_shop_product_template_prefilter');

$shop_configuration['product'] = str_replace('check_options();', 'check_options(); resize_window();', func_fb_shop_display($customer_dir . '/product.tpl'));
?>
