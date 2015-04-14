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
 * Type:     modifier
 * Name:     escape
 * Purpose:  Escape the string according to escapement type
 * Comment:  Replaces the original Smarty-modifier because of 4.0.6 incompatibility
 * -------------------------------------------------------------
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v39 (xcart_4_7_0), 2015-02-17 23:56:28, modifier.escape.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../../../"); die("Access denied"); }

function smarty_modifier_escape($string, $esc_type = 'html', $char_set = false)
{
    global $default_charset, $current_area;

    if (zerolen($string))
        return $string;

    if (!$char_set)
        $char_set = (isset($default_charset) && ($default_charset != '')) ? $default_charset : "UTF-8";

    switch ($esc_type) {
        case 'html':
            return @htmlspecialchars($string, ENT_QUOTES, $char_set);

        case 'htmlcompat':
            return @htmlspecialchars($string, ENT_COMPAT, $char_set);

        case 'htmlall':
            return @htmlentities($string, ENT_QUOTES, $char_set);

        case 'url':
            return rawurlencode($string);

        case 'urlpathinfo':
            return str_replace('%2F', '/', rawurlencode($string));

        case 'quotes':
            return preg_replace("/(?<!\\\\)'/Ss", "\\'", $string);

        case 'hex':
            $s = '%';
        case 'hexentity':
            if (!$s)
                $s = '&#x';
        case 'decentity':
            if (!$s)
                $s = '&#';
            $f = ($esc_type == 'decentity') ? "ord" : "bin2hex";
            $l = strlen($string);
            $return = '';
            for ($x = 0; $x < $l; $x++)
                $return .= $s.$f(substr($string, $x, 1)).';';

            return $return;

        case 'javascript':
            return strtr($string, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));

        case 'mail':
            return strtr($string, array('@', '.'), array(' [AT] ', ' [DOT] '));

        case 'nonstd':
            $return = '';
            $l = strlen($string);
            for ($i = 0; $i < $l; $i++) {
                $symbol = substr($string, $i, 1);
                $ord = ord($symbol);
                $return .= ($ord >= 126) ? ('&#'.$ord.';') : $symbol;
            }
            return $return;

    }

    return $string;
}
?>
