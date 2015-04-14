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
 * Wishlist interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v38 (xcart_4_7_0), 2015-02-17 23:56:28, display_wishlist.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../../"); die("Access denied"); }

if(
    empty($active_modules['Wishlist'])
    || (
        $current_area != 'A'
        && $current_area != 'P'
    )
) {
    func_403(61);
}

x_session_register('store_search_data_w');

if (!empty($search_data) && $mode == 'search') {
    $store_search_data_w = $search_data;
    func_header_location("wishlists.php?mode=search");
} else {
    $search_data = $store_search_data_w;
}

$provider_condition = (empty($active_modules['Simple_Mode']) && $current_area == 'P') ? " AND $sql_tbl[products].provider = '$logged_userid'" : "";

$smarty->assign('main','wishlists');
$location[] = array(func_get_langvar_by_name('lbl_search_wishlists'), '');

/**
 * Search wishlists
 */
if ($mode == 'search' && !empty($search_data)) {

    $where = array();

    if (!empty($search_data['login'])) {
        $where[] = "($sql_tbl[customers].login LIKE '%$search_data[login]%' OR $sql_tbl[customers].firstname LIKE '%$search_data[login]%' OR $sql_tbl[customers].lastname LIKE '%$search_data[login]%')";
    }

    if (!empty($search_data['sku'])) {
        $where[] = "$sql_tbl[products].productcode = '$search_data[sku]'";
    }

    if (!empty($search_data['productid'])) {
        $where[] = "$sql_tbl[products].productid = '$search_data[productid]'";
    }

    if (!empty($search_data['product'])) {
        $where[] = "($sql_tbl[products_lng_current].product LIKE '%$search_data[product]%' OR $sql_tbl[products_lng_current].descr LIKE '%$search_data[product]%' OR $sql_tbl[products_lng_current].fulldescr LIKE '%$search_data[product]%')";
    }

    $where_str = '';
    if (!empty($where))
        $where_str = " AND ".implode(" AND ", $where);

    $_res = db_query("SELECT COUNT($sql_tbl[wishlist].wishlistid) FROM $sql_tbl[wishlist] INNER JOIN $sql_tbl[products] INNER JOIN $sql_tbl[products_lng_current] ON $sql_tbl[products_lng_current].productid=$sql_tbl[products].productid INNER JOIN $sql_tbl[customers] WHERE $sql_tbl[wishlist].productid = $sql_tbl[products].productid AND $sql_tbl[wishlist].userid = $sql_tbl[customers].id".$where_str.$provider_condition." GROUP BY $sql_tbl[wishlist].userid ORDER BY NULL");
    $total_items = db_num_rows($_res);
    db_free_result($_res);

    $objects_per_page = $config['Appearance']['products_per_page_admin'];
    include $xcart_dir.'/include/navigation.php';

    $wishlists = func_query("SELECT $sql_tbl[wishlist].wishlistid, $sql_tbl[customers].login, $sql_tbl[customers].firstname, $sql_tbl[customers].lastname, $sql_tbl[customers].id AS userid, COUNT($sql_tbl[products].productid) as products_count FROM $sql_tbl[wishlist] INNER JOIN $sql_tbl[products] INNER JOIN $sql_tbl[products_lng_current] ON $sql_tbl[products_lng_current].productid=$sql_tbl[products].productid INNER JOIN $sql_tbl[customers] WHERE $sql_tbl[wishlist].productid = $sql_tbl[products].productid AND $sql_tbl[wishlist].userid = $sql_tbl[customers].id".$where_str.$provider_condition." GROUP BY $sql_tbl[wishlist].userid ORDER BY NULL LIMIT $first_page, $objects_per_page");

    if (!empty($wishlists)) {
        foreach ($wishlists as $k=> $v) {
            $_pcounts = $wishlists[$k]['pcounts'] = func_query_hash("SELECT COUNT($sql_tbl[products].productid) as products_count, $sql_tbl[wishlist].event_id FROM $sql_tbl[wishlist], $sql_tbl[products] WHERE $sql_tbl[wishlist].productid = $sql_tbl[products].productid AND $sql_tbl[wishlist].userid = '$v[userid]' $provider_condition GROUP BY $sql_tbl[wishlist].event_id ORDER BY NULL", "event_id", false, true);

            if (!empty($active_modules['Gift_Registry']) && ( count($_pcounts) > 1 || !in_array(0, array_keys($_pcounts)))) {
                $wishlists[$k]['is_events'] = "Y";
                if (!is_array($eventids))
                    $eventids = array();
                $eventids = array_merge($eventids, array_keys($_pcounts));
            }
        }

        if (!empty($active_modules['Gift_Registry']) && $eventids) {
            $eventids = array_unique($eventids);
            $events = func_query_hash("SELECT event_id, title, userid FROM $sql_tbl[giftreg_events] WHERE event_id IN ('".implode("','", $eventids)."')", "event_id", false);
            $smarty->assign('events', $events);
        }

        $smarty->assign('first_item',$first_page+1);
        $smarty->assign('last_item',$first_page+count($wishlists));
        $smarty->assign('total_items',$total_items);
        $smarty->assign('wishlists',$wishlists);
        $smarty->assign('navigation_script',"wishlists.php?mode=search");

    } elseif (empty($top_message['content'])) {
        $no_results_warning = array('type' => 'W', 'content' => func_get_langvar_by_name("lbl_warning_no_search_results", false, false, true));
        $smarty->assign('top_message', $no_results_warning);
    }

/**
 * Display wishlist
 */
} elseif ($mode == 'wishlist' && $customer) {

    $eventid = (!empty($active_modules['Gift_Registry']) && isset($eventid)) ? abs(intval($eventid)) : 0;

    $wishlist = func_query("SELECT $sql_tbl[wishlist].*, $sql_tbl[products].productcode, $sql_tbl[products_lng_current].product,$sql_tbl[customers].* FROM $sql_tbl[wishlist] INNER JOIN $sql_tbl[products] INNER JOIN $sql_tbl[products_lng_current] ON $sql_tbl[products_lng_current].productid=$sql_tbl[products].productid INNER JOIN $sql_tbl[customers] WHERE $sql_tbl[wishlist].productid = $sql_tbl[products_lng_current].productid AND $sql_tbl[wishlist].userid = $sql_tbl[customers].id AND $sql_tbl[wishlist].userid='$customer' AND $sql_tbl[wishlist].event_id='$eventid' ".$provider_condition);

    if (empty($wishlist))
        func_header_location('wishlists.php');

    foreach ($wishlist as $k => $v) {
        if (!empty($v['options'])) {
            $v['options'] = unserialize($v['options']);
            list($variant, $v['product_options']) = func_get_product_options_data($v['productid'], $v['options'], $v['membershipid']);
            if(!empty($variant))
                $v = func_array_merge($v, $variant);
            $wishlist[$k] = $v;
        }

        if (!empty($active_modules['XPayments_Subscriptions'])) {
            $wishlist[$k] = func_xps_attachPlanToWishlistProduct($wishlist[$k]);
        }
    }

    $location[count($location)-1][1] = 'wishlists.php';
    $location[] = array(func_get_langvar_by_name('lbl_wish_list'), '');

    $smarty->assign('wishlist',$wishlist);
    $smarty->assign('main','wishlist');
}

$smarty->assign('mode',$mode);
$smarty->assign('search_data', func_stripslashes($search_data));

?>
