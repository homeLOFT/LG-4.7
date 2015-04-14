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
 * Bestsellers
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v19 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_SESSION_START')) { header("Location: ../../"); die("Access denied"); }

function func_get_bestsellers($template_in) {
    global $config, $sql_tbl, $user_account, $active_modules, $cat, $smarty;

    // Increase to select more bestsellers
    $threshold_deviation = 1;
    x_load('product');

    if (
        !is_numeric($config['Bestsellers']['number_of_bestsellers'])
        || $config['Bestsellers']['number_of_bestsellers'] < 0
    ) {
        return array();
    }

    $membershipid = intval(@$user_account['membershipid']);

    /**
     * Get products data for current category and store it into $ products array
     */
    $cat = isset($cat) ? intval($cat) : 0;

    $search_query = '';

    $threshold = 0;
    $cat_ids = array();
    $categories_thresholds = array();

    if ($cat) {

        if ($smarty->getTemplateVars('main') == 'catalog') {
            $_categories = func_get_categories_list_preset('short_list=FALSE', $cat);
            if (!empty($_categories)) {
                $cat_ids = array_keys($_categories);
            }
        } else {
            $cat_ids = func_query_column("SELECT categoryid FROM $sql_tbl[categories] WHERE parentid='$cat'");
        }
        $cat_ids[] = $cat;

        $sql_cats_condition = implode("','", $cat_ids);
        $categories_thresholds = func_query_hash("SELECT ctb.categoryid, ctb.threshold_sales_stats FROM $sql_tbl[category_threshold_bestsellers] ctb WHERE ctb.categoryid IN ('$sql_cats_condition') AND ctb.membershipid='$membershipid'", 'categoryid', FALSE, TRUE);
        if (!empty($categories_thresholds)) {
            $threshold = intval($categories_thresholds[$cat]);
        } else {
            $categories_thresholds = array();
        }

        $search_query = " AND $sql_tbl[products_categories].categoryid IN ('$sql_cats_condition')";

        if (count($categories_thresholds) < count($cat_ids)) {
            $repaired_cats = XCProductSalesStats::repairIntegrityThresholds($cat_ids, array_keys($categories_thresholds), $membershipid);
            $categories_thresholds = $categories_thresholds + $repaired_cats;
        }
    } else {
        if (empty($membershipid)) {
            $threshold = intval($config['home_threshold_sales_stats']);
        } else {
            $threshold = intval(func_query_first_cell("SELECT ctb.threshold_sales_stats FROM $sql_tbl[category_threshold_bestsellers] ctb WHERE ctb.categoryid=0 AND ctb.membershipid='$membershipid'"));
        }

        $categories_thresholds[0] = $threshold;
    }

    $query['use_group_by'] = TRUE;

    $threshold = max(0, $threshold - $threshold_deviation);
    if ($threshold > 0) {
        $search_query .= " AND $sql_tbl[product_sales_stats].sales_stats >= '" . $threshold . "'";
    }

    if (!empty($search_query)) {
        $query['query'] = $search_query;
    }
    $query['skip_tables'] = XCSearchProducts::getSkipTablesByTemplate(XCSearchProducts::SKIP_ALL_POSSIBLE);
    $query['skip_tables'][] = 'products_lng_current';
    $query['fields'] = array("$sql_tbl[product_sales_stats].sales_stats");

    // First step: get productids without product's data
    $bestsellers_ids = func_search_products(
        $query,
        $membershipid,
        "$sql_tbl[product_sales_stats].sales_stats DESC",
        $config['Bestsellers']['number_of_bestsellers'] * XCProductSalesStats::DIRTY_LIMIT_MULT
    );

    $bestsellers_ids = !empty($bestsellers_ids) ? $bestsellers_ids : array();
    $res = XCProductSalesStats::adjustThresholds($bestsellers_ids, array($cat=>$categories_thresholds[$cat]), $membershipid);
    if (!empty($bestsellers_ids)) {
        $allow_cache = (count($bestsellers_ids) == $config['Bestsellers']['number_of_bestsellers'] * XCProductSalesStats::DIRTY_LIMIT_MULT);
        $bestsellers_ids = array_slice($bestsellers_ids, 0, $config['Bestsellers']['number_of_bestsellers']);
        array_walk($bestsellers_ids, create_function('&$val, $key', '$val = $val["productid"];')); #nolint

        $query = array(
            'skip_tables' => XCSearchProducts::getSkipTablesByTemplate($template_in),
            'query' => " AND $sql_tbl[products].productid IN ('" . implode("','", $bestsellers_ids) . "')"
        );

        // second step: Get products data, do not check products availability using these tables
        $query['skip_tables'] = array_merge($query['skip_tables'], XCSearchProducts::$tblsToCheckAvailability); #nolint

        $bestsellers = func_search_products(
            $query,
            $membershipid,
            'skip_orderby',
            $config['Bestsellers']['number_of_bestsellers']
        );

        // PHP sort in memory to avoid filesorting in mysql
        if (!empty($bestsellers)) {
            $res = array();
            $bestsellers_ids = array_flip($bestsellers_ids);
            foreach($bestsellers as $bs_product) {
                if (isset($bestsellers_ids[$bs_product['productid']])) {
                    $res[$bestsellers_ids[$bs_product['productid']]] = $bs_product;
                } else {
                    assert('FALSE /* '.__FUNCTION__.': For some reason bestseller product is skipped */');
                }
            }
            ksort($res);

            $bestsellers = $res;
        }

    } else {
        $bestsellers = array();
    }

    // Do not cache wrong results allow_cache
    return $bestsellers;
}

function func_tpl_get_bestsellers() {
    static $res;

    if (isset($res))
        return $res;

    $res = func_get_bestsellers('modules/Bestsellers/bestsellers.tpl');
    return $res;
}


?>
