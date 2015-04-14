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
 * Name:     math
 * Purpose:  allow mathematical equations
 * -------------------------------------------------------------
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v30 (xcart_4_7_0), 2015-02-17 23:56:28, function.math.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../../../"); die("Access denied"); }

function smarty_function_math($params, &$smarty)
{
    global $mode, $PHP_SELF;
    static $reserved_params = array ('assign', 'equation', 'format');
    static $allowed_funcs = array (
        'ceil','floor','round',
        'int','float','base_convert',
        'abs','max','min','pi','rand','lcg_value',
        'cos','sin','tan','acos','asin','atan',
        'log','log10','exp','pow','sqrt');

    $error_prefix = 'math ``<b>'.htmlspecialchars($params['equation']).'</b>\'\' in ``'.$smarty->template_resource.'\'\': ';

    if (!isset($params['equation'])) {
        trigger_error('Smarty error:function.math.php ' . $error_prefix.'missing equation');
        return;
    }

    $equation = $params['equation'];
    $result = null;
    if (empty($equation)) {
        $result = $equation;
    }
    else {
        if (basename($PHP_SELF) == 'file_edit.php' && $mode == 'preview')
            error_reporting (0);

        if (substr_count($equation,"(") != substr_count($equation,")")) {
            trigger_error('Smarty error:function.math.php ' . $error_prefix.'unbalanced parenthesis');
            return;
        }

        if (!isset($params['skip_func_check'])) {
            // match all vars in equation, make sure all are passed
            preg_match_all("!(?:0x[a-fA-F0-9]+)|([a-zA-Z]+[a-zA-Z0-9_]*)!S",$equation, $match);

            foreach($match[1] as $curr_var) {
                if ($curr_var && !in_array($curr_var, array_keys($params)) && !in_array($curr_var, $allowed_funcs)) {
                    trigger_error('Smarty error:function.math.php ' . $error_prefix."function call $curr_var is not allowed");
                    return;
                }
            }
        }

        $keys_empty = array();
        $keys_not_numeric = array();
        $error = false;

        // substitute parameters in equation
        foreach($params as $key => $val) {
            if (in_array($key, $reserved_params)) continue;

            if (strlen($val)==0) {
                $keys_empty[] = $key;
                $error = true;
                continue;
            }
            if (!is_numeric($val)) {
                $keys_not_numeric[] = $key;
                $error = true;
                continue;
            }

            if (!$error) {
                $equation = preg_replace("!\b$key\b!S",$val, $equation);
            }
        }

        if ($error) {
            $err_arr = array();
            $err_def = array (
                'parameter%s ``<b>%s</b>\'\' %s empty' => $keys_empty,
                'parameter%s ``<b>%s</b>\'\' %s not numeric' => $keys_not_numeric
            );
            foreach ($err_def as $fmt => $keys_arr) {
                $cnt = count($keys_arr);
                if ($cnt < 1) continue;
                $err_arr[] = sprintf( $fmt,
                    ($cnt>1?'s':''),
                    implode('</b>\'\', ``<b>', $keys_arr),
                    ($cnt>1?'are':'is')
                );
            }

            trigger_error('Smarty error:function.math.php ' . $error_prefix.implode('; ', $err_arr));
            return;
        }

        @eval("\$result = ".$equation.";");
    }

    if (!empty($params['format']))
        $result = sprintf($params['format'], $result);

    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $result);
        return '';
    }

    return $result;
}

?>
