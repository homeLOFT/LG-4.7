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
 * Functions for the news management module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v26 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../../"); die("Access denied"); }

function func_news_get($lngcode, $only_first=false, $only_count=false, $limit=false)
{
    global $sql_tbl, $config;

    $query = "
SELECT
    ".($only_count ? "COUNT($sql_tbl[newsletter].listid)": "$sql_tbl[newsletter].*")."
FROM
    $sql_tbl[newslists], $sql_tbl[newsletter]
WHERE
    $sql_tbl[newslists].avail='Y' AND
    $sql_tbl[newslists].show_as_news='Y' AND
    $sql_tbl[newslists].lngcode='$lngcode' AND
    $sql_tbl[newslists].listid=$sql_tbl[newsletter].listid AND
    $sql_tbl[newsletter].show_as_news='Y'
ORDER BY $sql_tbl[newsletter].date DESC";

    if ($limit !== false) {
        $query .= " LIMIT $limit";
    } elseif ($only_first) {
        $query .= " LIMIT 1";
    }

    if ($only_count)
        return func_query_first_cell($query);

    $result = func_query($query);
    if (!is_array($result) || empty($result))
        return false;

    foreach ($result as $k=>$row) {
        $result[$k]['send_date'] += $config["Appearance"]["timezone_offset"];
    }

    return     $only_first ? $result[0] : $result;
}

function insert_news_subscription_allowed($params)
{
    global $sql_tbl;

    return func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[newslists] WHERE avail='Y' AND subscribe='Y' AND lngcode='$params[lngcode]'");
}

function insert_news_exist($params)
{
    global $sql_tbl;

    return func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[newslists] WHERE avail='Y' AND show_as_news='Y' AND lngcode='$params[lngcode]'");
}

?>
