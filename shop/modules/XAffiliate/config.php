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

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}
/**
 * Global definitions for X-Affiliate module
 */

$addons['XAffiliate'] = true;

$css_files['XAffiliate'][] = array();

$config['available_images']['B'] = "U";
$config['available_images']['L'] = "U";

$sql_tbl['images_B']                         = XC_TBL_PREFIX . 'images_B';
$sql_tbl['images_L']                         = XC_TBL_PREFIX . 'images_L';
$sql_tbl['partner_adv_campaigns']             = XC_TBL_PREFIX . 'partner_adv_campaigns';
$sql_tbl['partner_adv_clicks']                 = XC_TBL_PREFIX . 'partner_adv_clicks';
$sql_tbl['partner_adv_orders']                 = XC_TBL_PREFIX . 'partner_adv_orders';
$sql_tbl['partner_banners']                 = XC_TBL_PREFIX . 'partner_banners';
$sql_tbl['partner_clicks']                     = XC_TBL_PREFIX . 'partner_clicks';
$sql_tbl['partner_commissions']             = XC_TBL_PREFIX . 'partner_commissions';
$sql_tbl['partner_payment']                 = XC_TBL_PREFIX . 'partner_payment';
$sql_tbl['partner_plans']                     = XC_TBL_PREFIX . 'partner_plans';
$sql_tbl['partner_plans_commissions']         = XC_TBL_PREFIX . 'partner_plans_commissions';
$sql_tbl['partner_product_commissions']     = XC_TBL_PREFIX . 'partner_product_commissions';
$sql_tbl['partner_commissions']             = XC_TBL_PREFIX . 'partner_commissions';
$sql_tbl['partner_tier_commissions']         = XC_TBL_PREFIX . 'partner_tier_commissions';
$sql_tbl['partner_views']                     = XC_TBL_PREFIX . 'partner_views';

if (defined('TOOLS')) {
    $tbl_keys['partner_clicks.userid'] = array(
        'keys' => array('partner_clicks.userid' => 'customers.id'),
        'where' => "customers.usertype = 'B'",
        'fields' => array('clickid','bannerid')
    );
    $tbl_keys['partner_clicks.bannerid'] = array(
        'keys' => array('partner_clicks.bannerid' => 'partner_banners.bannerid'),
        'where' => "partner_banners.bannerid != 0",
        'fields' => array('clickid','userid')
    );
    $tbl_keys['partner_clicks.productid'] = array(
        'keys' => array('partner_clicks.targetid' => 'products.productid'),
        'where' => "partner_clicks.targetid != 0 AND partner_clicks.target = 'P'",
        'fields' => array('clickid','bannerid','userid')
    );
    $tbl_keys['partner_clicks.categoryid'] = array(
        'keys' => array('partner_clicks.targetid' => 'categoryies.categoryid'),
        'where' => "partner_clicks.targetid != 0 AND partner_clicks.target = 'C'",
        'fields' => array('clickid','bannerid','userid')
    );

    if (!empty($active_modules['Manufacturers'])) {
        $tbl_keys['partner_clicks.manufacturerid'] = array(
            'keys' => array('partner_clicks.targetid' => 'manufacturers.manufacturerid'),
            'where' => "partner_clicks.targetid != 0 AND partner_clicks.target = 'M'",
            'fields' => array('clickid','bannerid','userid')
        );
    }

    $tbl_keys['partner_commissions.userid'] = array(
        'keys' => array('partner_commissions.userid' => 'customers.id'),
        'where' => "customers.usertype = 'B'",
        'fields' => array('plan_id')
    );
    $tbl_keys['partner_commissions.plan_id'] = array(
        'keys' => array('partner_commissions.plan_id' => 'partner_plans.plan_id'),
        'fields' => array('userid')
    );
    $tbl_keys['partner_product_commissions.orderid'] = array(
        'keys' => array('partner_product_commissions.orderid' => 'orders.orderid'),
        'fields' => array('itemid','userid')
    );
    $tbl_keys['partner_product_commissions.itemid'] = array(
        'keys' => array('partner_product_commissions.itemid' => 'order_details.itemid'),
        'fields' => array('orderid','userid')
    );
    $tbl_keys['partner_product_commissions.userid'] = array(
        'keys' => array('partner_product_commissions.userid' => 'customers.id'),
        'where' => "customers.usertype = 'B'",
        'fields' => array('orderid','itemid')
    );
    $tbl_keys['partner_payment.userid'] = array(
        'keys' => array('partner_payment.userid' => 'customers.id'),
        'where' => "customers.usertype = 'B'",
        'fields' => array('payment_id','orderid')
    );
    $tbl_keys['partner_payment.orderid'] = array(
        'keys' => array('partner_payment.orderid' => 'orders.orderid'),
        'fields' => array('payment_id','userid')
    );
    $tbl_keys['partner_plans_commissions.plan_id'] = array(
        'keys' => array('partner_plans_commissions.plan_id' => 'partner_plans.plan_id'),
        'fields' => array('commission','commission_type','item_id','item_type')
    );
    $tbl_keys['partner_views.userid'] = array(
        'keys' => array('partner_views.userid' => 'customers.id'),
        'where' => "customers.usertype = 'B'",
        'fields' => array('bannerid','target','targetid')
    );
    $tbl_keys['partner_views.bannerid'] = array(
        'keys' => array('partner_views.bannerid' => 'partner_banners.bannerid'),
        'where' => "partner_views.bannerid != 0",
        'fields' => array('userid','target','targetid')
    );
    $tbl_keys['partner_views.productid'] = array(
        'keys' => array('partner_views.targetid' => 'products.productid'),
        'where' => "partner_views.targetid != 0 AND partner_views.target = 'P'",
        'fields' => array('bannerid','userid')
    );
    $tbl_keys['partner_views.categoryid'] = array(
        'keys' => array('partner_views.targetid' => 'categoryies.categoryid'),
        'where' => "partner_views.targetid != 0 AND partner_views.target = 'C'",
        'fields' => array('bannerid','userid')
    );

    if (!empty($active_modules['Manufacturers'])) {
        $tbl_keys['partner_views.manufacturerid'] = array(
            'keys' => array('partner_views.targetid' => 'manufacturers.manufacturerid'),
            'where' => "partner_views.targetid != 0 AND partner_views.target = 'M'",
            'fields' => array('bannerid','userid')
        );
    }
    $tbl_keys['partner_adv_clicks.campaignid'] = array(
        'keys' => array('partner_adv_clicks.campaignid' => 'partner_adv_campaigns.campaignid')
    );
    $tbl_keys['partner_adv_orders.campaignid'] = array(
        'keys' => array('partner_adv_orders.campaignid' => 'partner_adv_campaigns.campaignid'),
        'fields' => array('orderid')
    );
    $tbl_keys['partner_adv_orders.orderid'] = array(
        'keys' => array('partner_adv_orders.orderid' => 'orders.orderid'),
        'fields' => array('campaignid')
    );
    $tbl_keys['images_B.id'] = array(
        'keys' => array('images_B.id' => 'partner_banners.bannerid'),
        'where' => "partner_banners.banner_type = 'G'",
        'fields' => array('id')
    );
    $tbl_keys['partner_banners.imageid'] = array(
        'keys' => array('partner_banners.bannerid' => 'images_B.id'),
        'where' => "partner_banners.banner_type = 'G'",
        'fields' => array('bannerid')
    );

    $tbl_demo_data['XAffiliate'] = array(
        'partner_adv_campaigns'         => '',
        'partner_adv_clicks'             => '',
        'partner_adv_orders'             => '',
        'partner_banners'                 => '',
        'partner_clicks'                 => '',
        'partner_commissions'             => '',
        'partner_payment'                 => '',
        'partner_plans'                 => '',
        'partner_plans_commissions'     => '',
        'partner_product_commissions'     => '',
        'partner_commissions'             => '',
        'partner_tier_commissions'         => '',
        'partner_views'                 => '',
    );
}

$_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . 'XAffiliate';
/*
 Load module functions
*/
if (!empty($include_func)) {
    require_once $_module_dir . XC_DS . 'func.php';
    if (!empty($include_init)) {
        func_affiliate_init();
    }
}

?>
