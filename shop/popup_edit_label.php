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
 * Label editor interface (webmaster mode)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v25 (xcart_4_7_0), 2015-02-17 13:29:01, popup_edit_label.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('QUICK_START', true);

require __DIR__.'/top.inc.php';
require __DIR__.'/init.php';

if (isset($editor_mode)) {
    unset($editor_mode);
}
x_session_register('editor_mode');

if ($editor_mode != 'editor' || empty($_GET['id']) || empty($_GET['_l'])) {
    func_close_window();
}

// For $config.UA.browser and may be other
include_once $xcart_dir . '/include/adaptives.php';

// For $default_charset, $shop_language and may be other 
include $xcart_dir . '/include/get_language.php';

$labelName = addslashes($_GET['id']);
$lng = addslashes($_GET['_l']);
$labelText = func_query_first_cell("SELECT value FROM $sql_tbl[languages] WHERE code='$lng' AND name='$labelName'");

$smarty->assign('labelText', $labelText);
$smarty->assign('labelName', $labelName);

$smarty->webmaster_mode = false;
$smarty->assign('webmaster_mode', '');
$smarty->debugging = false;

if (isset($tarea)) {
    $smarty->assign('tarea', $tarea);
}

// To avoid Notice: Use of undefined constant AREA_TYPE - assumed 'AREA_TYPE' in include/func/func.core.php on line 1245 in func_display
if (!defined('AREA_TYPE')) {
    define('AREA_TYPE', 'C');
}

func_display('main/popup_edit_label.tpl', $smarty);
?>
