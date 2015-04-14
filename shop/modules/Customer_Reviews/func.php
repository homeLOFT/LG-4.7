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
 * Functions for Customer Reviews module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v29 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../../"); die("Access denied"); }

/**
 * Get stars data
 */
function func_get_vote_stars()
{
    global $config;

    static $data = false;

    if (!$data) {
        $data = array(
            'titles' => array(),
            'length' => min(20, max(3, intval($config['Customer_Reviews']['max_stars']))),
            'max' => 100,
            'levels'  => array()
        );

        $data['cost'] = $data['max'] / $data['length'];

        for ($i = 0; $i < $data['length']; $i++) {
            $data['levels'][$i] = ($i + 1) * $data['cost'];
        }
    }

    return $data;
}

/**
 * Check - allow add review or not
 */
function func_is_allow_add_review()
{
    global $config, $login;
    static $cache = null;

    if (is_null($cache))
        $cache = $config['Customer_Reviews']['customer_reviews'] == 'Y' && ($config['Customer_Reviews']['writing_reviews'] == 'A' || ($config['Customer_Reviews']['writing_reviews'] == 'R' && !empty($login)));

    return $cache;
}

/**
 * Check - allow add rate or not
 */
function func_is_allow_add_rate($productid = 0)
{
    global $config, $login, $REMOTE_ADDR, $sql_tbl;
    static $cache = false;

    $productid = max(0, intval($productid));

    if (!isset($cache[$productid])) {
        $cache[$productid] = $config['Customer_Reviews']['customer_voting'] == 'Y' && ($config['Customer_Reviews']['writing_voting'] == 'A' || ($config['Customer_Reviews']['writing_voting'] == 'R' && !empty($login)));
        if ($cache[$productid] && $productid && func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[product_votes] WHERE remote_ip='$REMOTE_ADDR' AND productid='$productid'"))
            $cache[$productid] = false;
    }

    return $cache[$productid];
}

/**
 * Get product rating
 */
function func_get_product_rating($productid)
{
    global $sql_tbl, $REMOTE_ADDR, $config, $login, $stars;

    $productid = intval($productid);
    if ($productid < 1)
        return false;

    $result = array(
        'allow_add_rate' => true,
        'forbidd_reason' => false,
        'total' => 0,
        'rating' => 0,
        'rating_level' => 0,
        'full_stars' => 0,
        'percent' => 0
    );

    $vote_result = func_query_first("SELECT COUNT(remote_ip) AS total, AVG(vote_value) AS rating FROM $sql_tbl[product_votes] WHERE productid = '$productid'");
    if (!$vote_result)
        return $result;

    $result['allow_add_rate'] = func_is_allow_add_rate($productid);
    if (!$result['allow_add_rate']) {
        if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[product_votes] WHERE remote_ip='$REMOTE_ADDR' AND productid='$productid'")) {
            $result['forbidd_reason'] = 'already_added';

        } elseif ($config['Customer_Reviews']['writing_voting'] == 'R' && empty($login)) {
            $result['forbidd_reason'] = 'unlogged';
        }
    }

    if (!isset($stars) || !is_array($stars))
        $stars = func_get_vote_stars();

    $result['total'] = $vote_result["total"];
    $result['rating'] = max(0, $vote_result["total"] == 0 ? 0 : $vote_result["rating"]);
    $result['rating_level'] = round($result['rating'] / $stars['cost'], 2);

    if ($result['rating'] > 0) {
        $result['full_stars'] = floor($result['rating'] / $stars['cost']);
        $result['percent'] = round(($result["rating"] % $stars['cost']) / $stars['cost'] * 100);
    }

    return $result;
}

/**
 * Get Rating bar block
 */
function func_ajax_block_rating_bar()
{
    global $productid, $user_account, $smarty, $config;

    if ($config['Customer_Reviews']['customer_voting'] != 'Y')
        return 1;

    $productid = intval($productid);
    if ($productid < 1)
        return 2;

    x_load('product');

    $product = func_select_product($productid, @$user_account['membershipid'], false, false, false, false);
    if (!$product)
        return 3;

    $smarty->assign('productid', $productid);
    $smarty->assign('stars', func_get_vote_stars());
    $smarty->assign('rating', func_get_product_rating($productid));

    $smarty->assign('vote_bar_standalone', true);
    return func_ajax_trim_div(func_display('modules/Customer_Reviews/vote_bar.tpl', $smarty, false));
}

function func_customer_reviews_init()
{

    if (defined('ADMIN_MODULES_CONTROLLER')) {
        if (function_exists('func_add_event_listener')) {
            func_add_event_listener('module.ajax.toggle', 'func_customer_reviews_on_module_toggle');
        }
    }

}

function func_customer_reviews_on_module_toggle($module_name, $module_new_state)
{

    global $sql_tbl, $active_modules, $smarty;

    if (
        $module_name == 'Customer_Reviews'
        && $module_new_state == true
        && !empty($active_modules['Advanced_Customer_Reviews'])
    ) {
        db_query("UPDATE $sql_tbl[modules] SET active='N' WHERE module_name='Advanced_Customer_Reviews'");
        return 'modules.php';
    }

}

function func_customer_reviews_get_customer_ip()
{
    global $config, $REMOTE_ADDR, $PROXY_IP, $CLIENT_IP;

    $client_ip = $REMOTE_ADDR;

    if (!empty($PROXY_IP) && $config['Customer_Reviews']['use_client_ip'] == 'Y') {
        $client_ip = $CLIENT_IP;
    }

    return $client_ip;
}

?>
