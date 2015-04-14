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
 * Module configuration script
 *
 * @category   X-Cart
 * @package    Modules
 * @subpackage X-Monitoring
 * @author     Michael Bugrov <mixon@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v10 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

global $config, $smarty, $xcart_dir, $xc_security_key_general;

#
# Define module table
#
$sql_tbl['monitoring_fsystem'] = XC_TBL_PREFIX . 'xmonitoring_fsystem';
#
# X-Monitoring API constants
#
define('XMONITORING_API_DOMAIN', 'monitoring.x-cart.com');
define('XMONITORING_API_PATH', '/api/v1/processor');
#
# Initialization constants
#
define('XMONITORING_DIR', $xcart_dir . XC_DS . 'modules' . XC_DS . 'XMonitoring');
#
# X-Monitoring API script timeout
#
define('XMONITORING_TIMEOUT', 300);
#
# X-Monitoring reporting
#
define('XMONITORING_REPORT_URL', 'https://secure.x-cart.com/service.php?target=install_feedback_report');
#
# Initialization
#
define('XMONITORING_HASH_FUNC', 'md5');
define('XMONITORING_BASE_KEY', $xc_security_key_general);

# Update include path
set_include_path(get_include_path() . PATH_SEPARATOR . XMONITORING_DIR . '/lib/PEAR');

# Allow substr to be used as smarty modifier
$smarty->changeSecurity(array('php_modifiers' => array('substr')));

/*
 * Load module functions
 */
if (!empty($include_func)) {
    require_once XMONITORING_DIR . XC_DS . 'func.php';
}

/*
 * Module initialization
 */
if (!empty($include_init)) {
    require_once XMONITORING_DIR . XC_DS . 'init.php';
}

?>
