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
 * Templater plugin
 * -------------------------------------------------------------
 * Type:     function
 * Name:     xoffers_promo
 * Purpose:  generate list of random promo blocks
 * -------------------------------------------------------------
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v20 (xcart_4_7_0), 2015-02-17 23:56:28, function.xoffers_promo.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../../"); die("Access denied"); }

function smarty_function_xoffers_promo($params, &$smarty)
{
    static $check_vars = array ('product_offers', 'category_offers');
    global $logged_userid, $login_type;

    if (empty($params['mode']) || $params['mode'] != 'random')
        return;

    foreach ($check_vars as $v) {
        $_v = $smarty->getTemplateVars($v);
        if (!empty($_v))
            return;
    }

    $avail_offers = func_get_offers($logged_userid, $login_type, false);

    if (empty($avail_offers))
        return;

    $tmp = array();
    foreach ($avail_offers as $v) {
        if ($v['show_short_promo'] != 'Y')
            continue;

        $tmp[] = $v['offerid'];
    }

    if (empty($tmp))
        return;

    shuffle($tmp);

    $info_offers = func_get_sorted_offers($tmp);
    $smarty->assign('new_offers', $info_offers);
    $template = 'modules/Special_Offers/customer/new_offers_short_list.tpl';
    $smarty->smarty->already_included_tpls[$template] = ++$smarty->smarty->inclusion_depth;
    $smarty->display($template);
    $smarty->smarty->inclusion_depth--;
}

?>
