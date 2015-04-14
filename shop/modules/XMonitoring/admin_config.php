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
 * Module admin config script
 *
 * @category   X-Cart
 * @package    Modules
 * @subpackage X-Monitoring
 * @author     Michael Bugrov <mixon@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v7 (xcart_4_7_0), 2015-02-17 23:56:28, admin_config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

$result = func_xmonitoring_get_status();

$xm_module_info = func_get_langvar_by_name('txt_xmonitoring_settings_help_text', false, false, true);
$xm_expired = func_get_langvar_by_name('txt_xmonitoring_expired', false, false, true);

$xm_plan = 'Unknown';
$xm_days = 'Unknown';

if ($result) {
    
    $xm_plan = $result->actionParams['plan'];
    
    if ($result->actionParams['status'] == 'OK') {
        $xm_days = abs(floor((XC_TIME-strtotime($result->actionParams['expiration'])) / func_constant('SECONDS_PER_DAY')));
        
        $xm_module_info = func_xmonitoring_show_section($xm_module_info, 'subscription_info');
        $xm_module_info = func_xmonitoring_show_section($xm_module_info, 'change_plan');
    }
    else {
        $xm_module_info = func_xmonitoring_show_section($xm_module_info, 'expired_notice');
    }
}

$xm_module_info = str_replace('{{PLAN}}', $xm_plan, $xm_module_info);
$xm_module_info = str_replace('{{DAYS}}', $xm_days, $xm_module_info);
$xm_module_info = str_replace('{{EXPIRED}}', $xm_expired, $xm_module_info);

$configuration[0]['comment'] = $xm_module_info;

?>
