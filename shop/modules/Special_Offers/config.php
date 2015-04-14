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
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v42 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header('Location: ../../'); die('Access denied'); }

global $smarty, $config;

$config['available_images']['S'] = "U";

$addons['Special_Offers'] = true;

$css_files['Special_Offers'][] = array();
$css_files['Special_Offers'][] = array('altskin' => TRUE);

$sql_tbl['offers'] = XC_TBL_PREFIX . 'offers';
$sql_tbl['offers_lng'] = XC_TBL_PREFIX . 'offers_lng';
$sql_tbl['offer_product_sets'] = XC_TBL_PREFIX . 'offer_product_sets';
$sql_tbl['offer_product_params'] = XC_TBL_PREFIX . 'offer_product_params';
$sql_tbl['offer_conditions'] = XC_TBL_PREFIX . 'offer_conditions';
$sql_tbl['offer_condition_params'] = XC_TBL_PREFIX . 'offer_condition_params';
$sql_tbl['offer_bonuses'] = XC_TBL_PREFIX . 'offer_bonuses';
$sql_tbl['offer_bonus_params'] = XC_TBL_PREFIX . 'offer_bonus_params';
$config['special_offers_mark_products'] = true;

$sql_tbl['customer_bonuses'] = XC_TBL_PREFIX . 'customer_bonuses';
$sql_tbl['images_S'] = XC_TBL_PREFIX . 'images_S';
$sql_tbl['condition_memberships'] = XC_TBL_PREFIX . 'condition_memberships';
$sql_tbl['bonus_memberships'] = XC_TBL_PREFIX . 'bonus_memberships';

$sp_offer_types = array('applied', 'free', 'promo');

$sp_total_types = array(
    'ST' => "[".func_get_langvar_by_name('lbl_subtotal')."]",
    'OT' => "[".func_get_langvar_by_name('lbl_subtotal')." - ".func_get_langvar_by_name('lbl_sp_discount')."]",
);
$smarty->assign('sp_total_types', $sp_total_types);

$fake_product_set_id = -1;
$smarty->assign('fake_product_set_id', $fake_product_set_id);

$sp_promo_texts = array(
    'promo_short'            => func_get_langvar_by_name('lbl_sp_promo_text'),
    'promo_long'            => func_get_langvar_by_name('lbl_sp_promo_long'),
    'promo_checkout'        => func_get_langvar_by_name('lbl_sp_promo_checkout'),
    'promo_items_amount'    => func_get_langvar_by_name('lbl_sp_promo_items_amount'),
);
$smarty->assign('sp_promo_texts', $sp_promo_texts);

if (defined('TOOLS')) {
    $tbl_demo_data['Special_Offers'] = array(
        'offer_bonus_params' => '',
        'offer_bonuses' => '',
        'offer_condition_params' => '',
        'offer_conditions' => '',
        'offers' => '',
        'offers_lng' => '',
        'bonus_memberships' => '',
        'condition_memberships' => '',
        'offer_condition_params' => ''
    );

    $tbl_keys['offer_condition_params. test specific geographic location condition type'] = array(
        'keys'         => array(
            'offer_condition_params.param_id' => 'zones.zoneid',
        ),
        'where'     => "offer_condition_params.param_type = 'Z'",
        'fields'     => array(
            'conditionid',
            'param_type',
            'param_id',
            'param_qnty',
        ),
    );
    
    $tbl_keys['offer_condition_params. test category condition type'] = array(
        'keys'         => array(
            'offer_condition_params.param_id' => 'categories.categoryid',
        ),
        'where'     => "offer_condition_params.param_type = 'C'",
        'fields'     => array(
            'conditionid',
            'param_type',
            'param_id',
            'param_qnty',
        ),
    );

    $tbl_keys['offer_condition_params. test product condition type'] = array(
        'keys'         => array(
            'offer_condition_params.param_id' => 'products.productid',
        ),
        'where'     => "offer_condition_params.param_type = 'P'",
        'fields'     => array(
            'conditionid',
            'param_type',
            'param_id',
            'param_qnty',
        ),
    );

    $tbl_keys['offer_bonus_params. test category bonus type'] = array(
        'keys'         => array(
            'offer_bonus_params.param_id' => 'categories.categoryid',
        ),
        'where'     => "offer_bonus_params.param_type = 'C'",
        'fields'     => array(
            'bonusid',
            'param_type',
            'param_id',
            'param_qnty',
        ),
    );

    $tbl_keys['offer_bonus_params. test product bonus type'] = array(
        'keys'         => array(
            'offer_bonus_params.param_id' => 'products.productid',
        ),
        'where'     => "offer_bonus_params.param_type = 'P'",
        'fields'     => array(
            'bonusid',
            'param_type',
            'param_id',
            'param_qnty',
        ),
    );

}

$_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . 'Special_Offers';
/*
 Load module functions
*/
if (!empty($include_func)) {
    require_once $_module_dir . XC_DS . 'func.php';
    if (!empty($include_init)) {
        func_special_offers_init();
    }
}

?>
