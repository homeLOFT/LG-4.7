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
 * This script shows static page in customer area.
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v43 (xcart_4_7_0), 2015-02-17 13:29:01, pages.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';

if (
    isset($pageid)
    && !empty($pageid)
    && $config['SEO']['clean_urls_enabled'] == 'Y'
    && !defined('DISPATCHED_REQUEST')
    && !func_is_ajax_request()
) {
    func_clean_url_permanent_redirect('S', intval($pageid));
}

include $xcart_dir . '/include/common.php';

x_load('files','pages');

$pages_dir = $xcart_dir . $smarty_skin_dir . "/pages/$store_language/";

$status_condition = empty($identifiers['A'])
    ? "AND active = 'Y'"
    : '';

if (
    !isset($pageid)
    && isset($alias)
) {
    // Try to resolve pageid by alias
    $pageid = func_get_pageid_by_alias($alias);
}

if (
    isset($pageid)
    && is_numeric($pageid)
    && $pageid > 0
) {
    $page_data = func_query_first("SELECT * FROM $sql_tbl[pages] WHERE pageid='".intval($pageid)."' AND level='E' $status_condition");

    $filename = addslashes($page_data['filename']);

    if (
        $filename
        && $page_data['language'] != $store_language
    ) {

        $page_data = func_query_first("SELECT * FROM $sql_tbl[pages] WHERE filename = '$filename' AND level = 'E' AND language = '$store_language' $status_condition");

        if (empty($page_data)) {
            $page_data = func_query_first("SELECT * FROM $sql_tbl[pages] WHERE filename = '$filename' AND level = 'E' AND language = '$config[default_customer_language]' $status_condition");
        }

        if (empty($page_data)) {
            $page_data = func_query_first("SELECT * FROM $sql_tbl[pages] WHERE filename = '$filename' AND level = 'E' AND language = '$config[default_admin_language]' $status_condition");
        }

        if (empty($page_data)) {
            $page_data = func_query_first("SELECT * FROM $sql_tbl[pages] WHERE filename = '$filename' AND level = 'E' $status_condition");
        }

    }

    if (empty($page_data)) {

        $page_is_exists = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[pages] WHERE pageid = '".intval($pageid)."' AND level = 'E'") > 0;

        if ($page_is_exists) {

            func_403(93);

        } else {

            func_page_not_found();

        }

    }

    $pages_dir = $xcart_dir . $smarty_skin_dir . '/pages/' . $page_data['language'] . "/";

    $filename = $pages_dir.$page_data['filename'];

    $page_content = func_file_get($filename, true);

    if ($page_content === false) {

        func_page_not_found();

    }

    $location[isset($open_in_layer) ? 0 : ''] = array($page_data['title'], '');

    $smarty->assign('page_data',      $page_data);
    $smarty->assign('page_content',   $page_content);
    $smarty->assign('meta_page_type', 'E');
    $smarty->assign('meta_page_id',   $page_data['pageid']);
    $smarty->assign('main',           'pages');

}

// Assign the current location line
$smarty->assign('location',           $location);

if (
    isset($is_ajax_request)
    || isset($open_in_layer)
) {

    $smarty->assign('template_name', 'customer/main/pages.tpl');

    func_display('customer/help/popup_info.tpl', $smarty);

} else {

    func_display('customer/home.tpl', $smarty);

}

?>
