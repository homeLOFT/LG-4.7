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
 * Base authentication, defining common variables 
 * and including common scripts
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    033a110b49267b9f80a0e0a4f02573550b310675, v78 (xcart_4_7_0), 2015-02-17 13:37:20, postauth.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: home.php"); die("Access denied"); }

x_session_register('logout_user');
x_session_register('session_failed_transaction');
x_session_register('add_to_cart_time');

x_session_register('always_allow_shop', false);
x_session_register('search_data', array());

$always_allow_shop = !empty($_GET['shopkey'])
    ? $_GET['shopkey'] === $config['General']['shop_closed_key']
    : $always_allow_shop;

$_current_script = basename($PHP_SELF . ($QUERY_STRING ? "?$QUERY_STRING" : ''));
if (
    'Y' === $config['General']['shop_closed']
    && FALSE === $always_allow_shop
    && !func_is_always_allowed_link($_current_script, /*check also if */func_is_ajax_request())
) {

    // Close store front
    // Thanks to rubyaryat for the Shop Closed mod

    if (FALSE === stristr(PHP_SAPI, 'cgi')) {
       header('HTTP/1.0 503 Service Unavailable');
    } else {
       header('Status: 503 Service Unavailable');
    }

    if (!empty($config['General']['shop_closed_retry_after'])) {
        header('Retry-After: '.$config['General']['shop_closed_retry_after']);
    }

    if (
        $shop_evaluation
        && !defined('ALLOW_DEMO')
        && is_readable("$xcart_dir/shop_closed_evaluation.html")
    ) {
        $_shop_closed_file = "$xcart_dir/shop_closed_evaluation.html";
    } else {
        $_shop_closed_file = "$xcart_dir/$shop_closed_file";
    }

    if (is_file($_shop_closed_file) && is_readable($_shop_closed_file)) {
        readfile($_shop_closed_file);
    } else {
        echo func_get_langvar_by_name('txt_shop_temporarily_unaccessible', false, false, true);
    }

    exit();
}

require $xcart_dir . DIR_CUSTOMER . '/https.php';

if (
    !empty($active_modules['Users_online'])
    && !func_is_ajax_request()
) {

    x_session_register('current_url_page');

    $current_url_page = $php_url['url'] . ($php_url['query_string'] ? '?' . $php_url['query_string'] : '');

}

/**
 * Display
 */
x_session_register('wlid');

if (
    isset($_GET['wlid'])
    && $_GET['wlid']
) {
    $wlid = $_GET['wlid'];
}

$smarty->assign('wlid', $wlid);

x_session_register('top_message');

if (
    !empty($top_message)
    && !func_is_ajax_request()
) {

    $top_message['type'] = !empty($top_message['type'])
        ? $top_message['type']
        : 'I';

    $title_list = array(
        'E' => 'lbl_error',
        'W'    => 'lbl_warning',
    );

    $top_message['title'] = func_get_langvar_by_name(
        isset($title_list[$top_message['type']])
            ? $title_list[$top_message['type']]
            : 'lbl_information',
        array(),
        false,
        true
    );

    $smarty->assign('top_message', $top_message);

    $top_message = '';

    if (defined('XC_SESSION_DB_SAVE_POSTAUTH')) {
        x_session_save('top_message');
    }
}

if (isset($cat)) {
    $cat = intval($cat);
}

if (isset($page)) {
    $page = intval($page);
}

if (
    !empty($active_modules['XAffiliate'])
    && !func_is_ajax_request()
) {
    include $xcart_dir . '/include/partner_info.php';
    include $xcart_dir . '/include/adv_info.php';

}


include $xcart_dir . DIR_CUSTOMER . '/referer.php';

include $xcart_dir . '/include/check_useraccount.php';

include $xcart_dir . '/include/get_language.php';

$lbl_site_path = strip_tags(func_get_langvar_by_name('lbl_site_path', '', false, true));

$location = array();

if (!empty($lbl_site_path)) {
    $location[] = array(
        $lbl_site_path,
        'home.php',
    );
}

$smarty->assign('redirect', 'customer');
$smarty->assign('logout_user', $logout_user);
if (isset($printable)) {
    $smarty->assign('printable', $printable);
}

/*
* Optimize ajax POST/GET requests
*/
if (func_is_ajax_request()) {
    return;
}

/*
* =================================================
* THE CODE BELOW WILL NOT EXECUTED IN AJAX REQUESTS
* =================================================
*/

x_load('minicart');
$smarty->assign(func_get_minicart_totals());

if (!empty($active_modules['Interneka'])) {

    include $xcart_dir . '/modules/Interneka/interneka.php';

}

$pages_menu = func_query("SELECT * FROM " . $sql_tbl['pages'] . " WHERE language='" . $store_language . "' AND active='Y' AND level='E' AND show_in_menu='Y' ORDER BY orderby, title");

$smarty->assign('pages_menu', $pages_menu);

$speed_bar = unserialize($config['speed_bar']);
if (!empty($speed_bar)) {// Prepare speed bar vars {{{
    $tmp_labels = array();
    foreach ($speed_bar as $k => $v) {
        if ($v['active'] != 'Y') {
            unset($speed_bar[$k]);
            continue;
        }

        $speed_bar[$k] = func_stripslashes($v);
        $tmp_labels[] = 'speed_bar_' . $v['id'];
    }

    if (!empty($speed_bar)) {
        $_tmp = @parse_url($REQUEST_URI);
        $_tmp['url_to_compare'] = strtolower( 
            trim(preg_replace('/^' . preg_quote($xcart_web_dir, '/') . '/i', '', $_tmp['path'], 1) , '/')
            . ((empty($_tmp['query'])) ? '' : '?' . $_tmp['query'])
        );


        $tmp = func_get_languages_alt($tmp_labels);
        foreach ($speed_bar as $k => $v) {
            if (isset($tmp['speed_bar_' . $v['id']])) {
                $speed_bar[$k]['title'] = $tmp['speed_bar_' . $v['id']];
            }

            if (
                empty($_current_is_found)
                && isset($v['url_to_compare'])
                && in_array($_tmp['url_to_compare'], $v['url_to_compare'])
            ) {
                $speed_bar[$k]['current'] = true;
                $_current_is_found = true;
            }
        }

        $smarty->assign('speed_bar', array_reverse($speed_bar));
    }
}//}}}
unset($speed_bar);

if (!empty($active_modules['Adv_Mailchimp_Subscription'])) {
    func_mailchimp_new_adv_campaign_commission();
}

if (!empty($active_modules['News_Management'])) {

    include $xcart_dir . '/modules/News_Management/news_last.php';

}

if (!empty($active_modules['Feature_Comparison'])) {

    include $xcart_dir . '/modules/Feature_Comparison/comparison_products.php';

    if ($config['Feature_Comparison']['fcomparison_show_product_list'] == 'Y') {

        $comparison_list = func_get_comparison_list();

        $smarty->assign('comparison_list',$comparison_list);

    }

}

if (!empty($active_modules['Survey'])) {

    include_once $xcart_dir . '/modules/Survey/surveys_list.php';

}


assert('!defined("QUICK_START") /* check_new_offers.php cannot be included in QUICK_START mode. Consider to use init.php instead of auth.php https://sd.x-cart.com/view.php?id=143250#743985 */');
if (!empty($active_modules['Special_Offers'])) {
    include_once $xcart_dir . '/modules/Special_Offers/check_new_offers.php';
}

if (!empty($active_modules['Gift_Registry'])) {

    include $xcart_dir . '/modules/Gift_Registry/customer_events.php';

}

if (!empty($active_modules['Klarna_Payments'])) {

    require_once $xcart_dir . '/modules/Klarna_Payments/postinit.php';
    
}

if (!empty($active_modules['Quick_Reorder'])) {
    func_show_quick_reorder_link($logged_userid);
}

// Get Product Notifications data
if (!empty($active_modules['Product_Notifications'])) {
    include $xcart_dir . '/modules/Product_Notifications/init_product_notifications.php';
}


if (!empty($active_modules['EU_Cookie_Law'])) {
    func_eucl_init();
}

if (!empty($active_modules['Advanced_Customer_Reviews'])) {
    func_acr_set_menu();
}

?>
