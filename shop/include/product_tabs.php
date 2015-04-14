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
 * Define product tabs
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v21 (xcart_4_7_0), 2015-02-17 23:56:28, product_tabs.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header('Location: ../'); die('Access denied'); }

if (isset($printable)) {
    return;
}

$product_tabs = array();

if (!empty($active_modules['Magnifier']) && $config['Magnifier']['magnifier_image_popup'] != 'Y' && !empty($zoomer_images)) {
    $product_tabs[] = array(
        'title'  => func_get_langvar_by_name('lbl_x_magnifier'),
        'tpl'    => 'modules/Magnifier/product_magnifier.tpl',
        'anchor' => 'xmagnifier'
    );
}

if (!empty($active_modules['Special_Offers']) && !empty($product_offers)) {
    $product_tabs[] = array(
        'title'  => func_get_langvar_by_name('lbl_special_offers'),
        'tpl'    => 'modules/Special_Offers/customer/product_offers_short_list.tpl',
        'anchor' => 'soffers'
    );
}

if ($config['Appearance']['send_to_friend_enabled'] == 'Y') {
    $product_tabs[] = array(
        'title'  => func_get_langvar_by_name('lbl_send_to_friend'),
        'tpl'    => 'customer/main/send_to_friend.tpl',
        'anchor' => 'send2friend'
    );
}

if (!empty($active_modules['Detailed_Product_Images']) && $config['Detailed_Product_Images']['det_image_popup'] != 'Y' && !empty($images)) {
    $product_tabs[] = array(
        'title'  => func_get_langvar_by_name('lbl_detailed_images'),
        'tpl'    => 'modules/Detailed_Product_Images/product_images.tpl',
        'anchor' => 'dpimages'
    );
}

if (!empty($active_modules['Upselling_Products']) && !empty($product_links)) {
    $product_tabs[] = array(
        'title'  => func_get_langvar_by_name('lbl_related_products'),
        'tpl'    => 'modules/Upselling_Products/related_products.tpl',
        'anchor' => 'related'
    );
}

if (!empty($active_modules['Recommended_Products']) && !empty($recommends)) {
    $product_tabs[] = array(
        'title'  => func_get_langvar_by_name('txt_recommends_comment'),
        'tpl'    => 'modules/Recommended_Products/recommends.tpl',
        'anchor' => 'recommends'
    );
}

if (
    !empty($active_modules['Customer_Reviews'])
    && empty($active_modules['Advanced_Customer_Reviews'])
    && (
        $config['Customer_Reviews']['customer_reviews'] == 'Y'
        || $config['Customer_Reviews']['customer_voting'] == 'Y'
    )
) {
    $product_tabs[] = array(
        'title'  => func_get_langvar_by_name('lbl_customers_feedback'),
        'tpl'    => 'modules/Customer_Reviews/vote_reviews.tpl',
        'anchor' => 'feedback'
    );
}

if (
    !empty($active_modules['Advanced_Customer_Reviews'])
) {
    func_acr_add_product_tab($product_tabs);
}


if (!empty($product_tabs)) {
    $smarty->assign('product_tabs', $product_tabs);
    if ($config['Appearance']['display_product_tabs'] == 'Y') {
        $smarty->assign('show_as_tabs', true);
    }
}

?>
