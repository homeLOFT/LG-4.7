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
 * Pop up information
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v38 (xcart_4_7_0), 2015-02-17 23:56:28, popup_info.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_load('files');

/**
 * The array of allowable actions:
 * FDX, UPS, OPT, TSTLBL, IMP
 */

/**
 * $action must contain only [a-zA-Z0-9] chars
 */
$action = (string)$action;
$action = preg_replace("/[^a-zA-Z0-9]/", '', $action);

/**
 * Generate the template name and assign it to Smarty
 */
$template_name = 
    ($current_area == 'C' ? "customer/" : "") 
    . "help/hlp_" . strtolower($action) . ".tpl";

if ($action == 'TSTLBL') {

    require $xcart_dir.'/include/security.php';

    x_session_register('status');
    x_session_register('error');

    if (!empty($status)) {

        $smarty->assign('status', $status);

        $status = false;

    }

    if (!empty($error)) {

        $smarty->assign('error', $error);

        $error = false;

    }

    $smarty->assign('tmp_dir', str_replace("/", XC_DS, $var_dirs['tmp']). XC_DS . 'usps_test_labels' . XC_DS);
}

$tpl_name = func_realpath($xcart_dir . $smarty_skin_dir . XC_DS . $template_name);

if (file_exists($tpl_name)) {

    $smarty->assign('template_name', $template_name);

}

if (func_is_ajax_request()) {
    $default_is_enabled_popup_bottom_tpl = FALSE;
} else {
    $default_is_enabled_popup_bottom_tpl = TRUE;
}

$default_smarty_vars = array(
    'is_enabledPopupBG' => true,
    'is_enabled_popup_bottom_tpl' => $default_is_enabled_popup_bottom_tpl,
    'td_container_class' => '',
    'force_height' => '0',
);

$smarty_vars = array(
    'evaluationPopup' => 
        array(
            'is_enabledPopupBG' => false, 
            'is_enabled_popup_bottom_tpl' => false,
            'td_container_class' => 'class="evaluation-container"',
            'force_height' => '360',
        ),
);

foreach($default_smarty_vars as $varname=>$def_value) {
    if (isset($smarty_vars[$action][$varname]))
        $smarty->assign($varname, $smarty_vars[$action][$varname]);
    else
        $smarty->assign($varname, $def_value);
}


if ($current_area == 'C') {

    func_display('customer/help/popup_info.tpl', $smarty);

} else {

    func_display('help/popup_info.tpl', $smarty);

}

?>
