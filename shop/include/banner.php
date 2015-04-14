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
 * Show banner by bannerid
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v30 (xcart_4_7_0), 2015-02-17 23:56:28, banner.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

if (empty($active_modules['XAffiliate']))
    return;

if (empty($type))
    $type = 'js';

$iframe_referer = '';
if ($type == 'iframe')
    $iframe_referer = urlencode($HTTP_REFERER);

/**
 * Get banner data
 */
if (!empty($bid))
    $data = func_query_first("SELECT * FROM $sql_tbl[partner_banners] WHERE bannerid = '$bid'");

x_session_register('login');
if (!empty($preview) && $type == 'preview' && $login) {
    $preview = stripslashes($preview);
    $trans = array_flip(get_html_translation_table());
    $preview = strtr($preview, $trans);
    $data = array(
        'banner_type' => 'M',
        'body' => $preview
    );
}

if (empty($data))
    exit;

/**
 * Add statistic record (banner view)
 */
func_add_banner_view_statistic($partner, $bid, 'skip_check_banner', @$productid, @$categoryid, @$manufacturerid);

include_once $xcart_dir.'/include/get_language.php';
$charset = $smarty->getTemplateVars('default_charset');
$charset_text = $charset ? '; charset=' . $charset : '';

if ($data['banner_type'] == 'P') {

    // Product banner

    x_load('product');
    if (!$productid && !$partner)
        $productid = func_query_first_cell("SELECT productid FROM $sql_tbl[products] ORDER BY RAND()");

    if (!$productid)
        exit;

    $product = func_select_product($productid, $user_account['membershipid']);
    if (!$product)
        exit;

    $smarty->assign('productid', $productid);
    $smarty->assign('product', $product);

} elseif ($data['banner_type'] == 'C') {

    // Category banner

    x_load('category');

    if (!$categoryid && !$partner)
        $categoryid = func_query_first_cell("SELECT categoryid FROM $sql_tbl[categories] ORDER BY RAND()");

    if (!$categoryid)
        exit;

    $category = func_get_category_data($categoryid);
    if (!$category)
        exit;

    $smarty->assign('categoryid', $categoryid);
    $smarty->assign('category', $category);

} elseif ($data['banner_type'] == 'F') {

    // Manufacturer banner

    if (!$manufacturerid && !$partner)
        $manufacturerid = func_query_first_cell("SELECT manufacturerid FROM $sql_tbl[manufacturers] ORDER BY RAND()");

    if (!$manufacturerid)
        exit;

    $manufacturer = func_query_first("SELECT $sql_tbl[manufacturers].*, IF($sql_tbl[images_M].id IS NULL, '', 'Y') as is_image, IFNULL($sql_tbl[manufacturers_lng].manufacturer, $sql_tbl[manufacturers].manufacturer) as manufacturer, IFNULL($sql_tbl[manufacturers_lng].descr, $sql_tbl[manufacturers].descr) as descr FROM $sql_tbl[manufacturers] LEFT JOIN $sql_tbl[manufacturers_lng] ON $sql_tbl[manufacturers_lng].manufacturerid = $sql_tbl[manufacturers].manufacturerid AND $sql_tbl[manufacturers_lng].code = '$shop_language' LEFT JOIN $sql_tbl[images_M] ON $sql_tbl[images_M].id = $sql_tbl[manufacturers].manufacturerid WHERE $sql_tbl[manufacturers].manufacturerid = '$manufacturerid'");
    if (!$manufacturer)
        exit;

    $smarty->assign('manufacturerid', $manufacturerid);
    $smarty->assign('manufacturer', $manufacturer);

}

$smarty->assign('banner', $data);
$smarty->assign('partner', $partner);

$smarty->assign('type', 'html');

$body = trim($smarty->fetch('main/display_banner.tpl'));

if ($type == 'iframe' || $type == 'preview') {

    // IFRAME mode
    $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><title></title></head><body>' . $body . '</body></html>';

} elseif ($type == 'js') {

    // JS mode
    header("Content-type: text/javascript$charset_text");
    $body = "document.write('" . str_replace(array("'", "\n", "\r"), array("\'", ' ', ''), $body) . "');";

} elseif ($type != 'preview') {

    // HTML mode
    header("Content-type: text/html$charset_text");
}

echo $body;

exit;
?>
