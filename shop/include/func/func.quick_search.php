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
 * Functions for ajax quick search functionality
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v36 (xcart_4_7_0), 2015-02-17 23:56:28, func.quick_search.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

/**
 * Parse search string for quick search facility
 */
function func_parse_quick_search($query)
{
    $types = array(
        'u' => 'users',
        'p' => 'products',
        'o' => 'orders',
    );

    $type = 'all';

    if (preg_match("/^([u|o|p]) (.*)$/", $query, $matches)) {
        $type = $types[$matches[1]];
        $query = trim($matches[2]);
    }

    return array($type, $query);
}

/**
 * Search for orders (using orderid as integer)
 */
function func_get_quick_search_orders ($query, $mode = 'no_search', $orderby = 'orderid', $sort = 'ASC', $first = 0, $limit = 1)
{
    global $sql_tbl, $single_mode, $current_area, $logged_userid;

    if ($orderby === false)
        $orderby = 'orderid';

    switch ($mode) {
        case 'single':
            $orderid = func_query_first_cell($query . " LIMIT 1");

            return $orderid;

        case 'no_search':
            $orderid = intval($query);
            $provider_condition = ($current_area == 'P' && !$single_mode) ? " AND provider='$logged_userid'" : "";

            if ($provider_condition == '') {

                $query = "SELECT orderid, total, userid, status, date FROM $sql_tbl[orders] WHERE orderid='$orderid'";
                $num = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[orders] WHERE orderid='$orderid'");

            } else {

                $query = "SELECT $sql_tbl[orders].orderid, $sql_tbl[orders].total, $sql_tbl[orders].userid, $sql_tbl[orders].status, $sql_tbl[orders].date FROM $sql_tbl[orders] LEFT JOIN $sql_tbl[order_details] ON $sql_tbl[orders].orderid=$sql_tbl[order_details].orderid WHERE $sql_tbl[orders].orderid='$orderid'" . $provider_condition;
                $num = func_query_first_cell("SELECT COUNT($sql_tbl[orders].orderid) FROM $sql_tbl[orders] LEFT JOIN $sql_tbl[order_details] ON $sql_tbl[orders].orderid=$sql_tbl[order_details].orderid WHERE $sql_tbl[orders].orderid='$orderid'" . $provider_condition);

            }

            return array($num, $query);

        case 'search':
            $orderby = (!in_array($orderby, array('orderid', 'total', 'login', 'status', 'date'))) ? 'orderid' : $orderby;
            $orders = func_query($query . " ORDER BY $orderby $sort LIMIT $first, $limit");

            return $orders;
    }

    return false;
}

/**
 * Search for users (using substring for login or firstname or lastname)
 */
function func_get_quick_search_users ($query, $mode = 'no_search', $orderby = 'login', $sort = 'ASC', $first = 0, $limit = 1)
{
    global $sql_tbl, $user_account;

    if ($user_account['flag'] == 'FS')
        return false;

    if ($orderby === false)
        $orderby = 'login';

    switch ($mode) {
        case 'single':
            $result = func_query_first($query . " LIMIT 1");

            return array($result['id'], $result['usertype']);

        case 'no_search':
            $condition = array();
            if (preg_match("/^(.+)(\s+)(.+)$/", $query, $found))
                $condition[] = "firstname LIKE '%".$found[1]."%' AND lastname LIKE '%".$found[3]."%'";
            $condition[] = "firstname LIKE '%$query%'";
            $condition[] = "lastname LIKE '%$query%'";
            $condition[] = "login LIKE '%$query%'";

            $where = implode(" OR ", $condition);

            $query = "SELECT id, login, usertype, firstname, lastname, is_anonymous_customer FROM $sql_tbl[customers] WHERE $where";
            $num = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[customers] WHERE $where");

            return array($num, $query);

        case 'search':
            $orderby = (!in_array($orderby, array('login', 'name', 'usertype', 'is_anonymous_customer'))) ? 'login' : $orderby;
            if ($orderby == 'name')
                $orderby = " lastname $sort, firstname ";

            $users = func_query($query . " ORDER BY $orderby $sort LIMIT $first, $limit");

            return $users;
    }

    return false;
}

/**
 * Search for products (using product name or SKU or productid)
 */
function func_get_quick_search_products ($query, $mode = 'no_search', $orderby = 'product', $sort = 'ASC', $first = 0, $limit = 1)
{
    global $sql_tbl, $current_area, $logged_userid, $single_mode, $user_account, $active_modules;

    if ($user_account['flag'] == 'FS')
        return false;

    if ($orderby === false)
        $orderby = 'product';

    switch ($mode) {
        case 'single':
            $productid = func_query_first_cell($query . " LIMIT 1");

            return $productid;

        case 'no_search':
            $productid = intval($query);
            $condition = array();
            $condition[] = "$sql_tbl[products_lng_current].product LIKE '%$query%'";
            $condition[] = "$sql_tbl[products].productid='$productid'";
            $condition[] = "$sql_tbl[products].productcode LIKE '%$query%'";
            if (!empty($active_modules['POS_System'])) {
                $pos_product = XCPos::getProductVariantByUpc($query);
                if (!empty($pos_product['productid'])) {
                    $condition[] = "$sql_tbl[products].productid='$pos_product[productid]'";
                }
            }

            $where = implode(" OR ", $condition);
            $provider_condition = ($current_area == 'P' && !$single_mode) ? " AND provider='$logged_userid'" : "";

            $query = "SELECT $sql_tbl[products].productid, $sql_tbl[products_lng_current].product, $sql_tbl[products].productcode, $sql_tbl[products].avail, $sql_tbl[pricing].price FROM $sql_tbl[products] INNER JOIN $sql_tbl[products_lng_current] ON $sql_tbl[products_lng_current].productid=$sql_tbl[products].productid LEFT JOIN $sql_tbl[pricing] ON $sql_tbl[products].productid=$sql_tbl[pricing].productid WHERE $sql_tbl[pricing].membershipid = '0' AND $sql_tbl[pricing].quantity = '1' AND $sql_tbl[pricing].variantid = '0' AND ($where)" . $provider_condition;
            $num = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[products] INNER JOIN $sql_tbl[products_lng_current] USING (productid) WHERE ($where)" . $provider_condition);

            return array($num, $query);

        case 'search':
            $orderby = (!in_array($orderby, array('productid', 'productcode', 'product', 'avail', 'price'))) ? 'product' : $orderby;
            $products = func_query($query . " ORDER BY $orderby $sort LIMIT $first, $limit");

            return $products;
    }

    return false;
}
?>
