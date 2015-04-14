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
 * Gets wholesale prices data
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v33 (xcart_4_7_0), 2015-02-17 23:56:28, product.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../../"); die("Access denied"); }

x_load('taxes');

/**
 * Generate wholesale pricing table
 */
$wresult = func_query ("SELECT $sql_tbl[pricing].quantity, MIN($sql_tbl[pricing].price) as price FROM $sql_tbl[pricing] WHERE $sql_tbl[pricing].productid='$productid' AND $sql_tbl[pricing].membershipid IN (".intval($user_account['membershipid']).", '0') AND $sql_tbl[pricing].quantity > '1' AND $sql_tbl[pricing].variantid = '0' GROUP BY $sql_tbl[pricing].quantity ORDER BY $sql_tbl[pricing].quantity");

if ($wresult) {

    $last_price = doubleval(func_query_first_cell("SELECT MIN(price) FROM $sql_tbl[pricing] WHERE $sql_tbl[pricing].quantity = '1' AND $sql_tbl[pricing].membershipid IN ('$user_account[membershipid]', '0') AND $sql_tbl[pricing].variantid = '0' AND $sql_tbl[pricing].productid = '$productid'"));

    $last_k = false;

    foreach ($wresult as $wk => $wv) {

        if ($wv['price'] >= $last_price) {

            unset($wresult[$wk]);

            continue;

        }

        $last_price = $wv['price'];

        $_taxes = func_tax_price($wv['price'], $productid);

        $wresult[$wk]['taxed_price'] = $_taxes['taxed_price'];

        if (!empty($_taxes['taxes'])) {
            $wresult[$wk]['taxes'] = $_taxes['taxes'];
        }

        if (
            $last_k !== false
            && isset($wresult[$last_k])
        ) {

            $wresult[$last_k]['next_quantity'] = $wv['quantity'] - 1;

            if ($product_info['min_amount'] > $wresult[$last_k]["next_quantity"]) {

                unset($wresult[$last_k]);

            } elseif ($product_info['min_amount'] > $wresult[$last_k]["quantity"]) {

                $wresult[$last_k]['quantity'] = $product_info['min_amount'];

            }

        }

        $last_k = $wk;

    }

    $wresult = array_values($wresult);

    if (count($wresult) > 0) {

        $wresult[count($wresult) - 1]['next_quantity'] = 0;

        $smarty->assign ('product_wholesale', $wresult);

    }

}

?>
