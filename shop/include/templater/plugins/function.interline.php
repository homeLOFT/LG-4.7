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
 * Name:     interline
 * Input:    class
 *           total
 *           index
 *            additional_class
 * -------------------------------------------------------------
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v28 (xcart_4_7_0), 2015-02-17 23:56:28, function.interline.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../../../"); die("Access denied"); }

function smarty_function_interline($params, &$smarty)
{
    if (
        (
            isset($params['class']) 
            && (
                !is_string($params['class']) 
                || empty($params['class'])
            )
        ) || (
            (
                !isset($params['name']) 
                || !is_string($params['name']) 
                || empty($params['name']) 
            ) && (
                !isset($params['total']) 
                || !is_int($params['total']) 
                || $params['total'] < 1 
                || !isset($params['index']) 
                || !is_int($params['index']) 
                || $params['index'] < 0 
                || $params['index'] > $params['total'] - 1
            )
        )
    ) {
        return '';
    }

    if (isset($params['name'])) {
        assert('!empty($params[foreach_total]) && !empty($params[foreach_iteration]) /* '.__FUNCTION__.': foreach_total and foreach_iteration must be passed when name= is used */');
        $params['total'] = intval($params['foreach_total']);
        $params['index'] = max(0, $params['foreach_iteration'] - 1);
    }

    if (!isset($params['class']) && empty($params['skip_highlight']))
        $params['class'] = 'highlight';

    $class = array();

    if ($params['total'] % 2 == ($params['index'] + 1) % 2)
        $class[] = $params['class'];

    if ($params['index'] == 0)
        $class[] = 'first';

    if ($params['index'] >= $params['total'] - 1)
        $class[] = 'last';

    if (!empty($params['additional_class']))
        $class[] = $params['additional_class'];

    $class = implode(" ", $class);

    if (!empty($class) && (!isset($params['pure']) || !$params['pure']))
        $class = ' class="' . $class . '"';

    if (isset($params['assign'])) {
        $smarty->assign($params['assign'], $class);
        $class = '';
    }

    return $class;
}

?>
