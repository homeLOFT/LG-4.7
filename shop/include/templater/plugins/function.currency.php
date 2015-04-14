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
 * Name:     currency
 * Input:    array
 * Descr:    Replacement for skin/common_files/currency.tpl template
 * -------------------------------------------------------------
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v9 (xcart_4_7_0), 2015-02-17 23:56:28, function.currency.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../../../"); die("Access denied"); }

/*
* Used in tpls and in
* include/func/func.ajax.php:func_ajax_block_provider_commission_data()
*/
function smarty_function_currency($params, &$smarty)
{
    global $config; 

    if (!isset($params['value']))
        return '';

    $value = $params['value'];
    settype($value, 'float');


    $result = '';
    if (!isset($params['plain_text_message'])) {
        $result .= '<span class="currency">';
    }

    if (isset($params['display_sign'])) {
        if ($value >= 0 )
            $result .= '+';
        else    
            $result .= '-';
    }

    $cf_value = func_format_number(abs($value));

    if (
        isset($params['tag_id'])
        && !isset($params['plain_text_message'])
    ) {
        $cf_value = "<span id=\"$params[tag_id]\">$cf_value</span>";
    }

    $result .= str_replace('$', $config['General']['currency_symbol'] , str_replace('x', $cf_value, $config['General']['currency_format']));


    if (!isset($params['plain_text_message'])) {
        $result .= '</span>';
    }

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $result);
        $result = '';
    }

    return $result;
}
?>
