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
 * Called from prepare.php
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v30 (xcart_4_7_0), 2015-02-17 23:56:28, https_detect.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header("Location: ../"); die("Access denied"); }

$HTTPS_RELAY = false;

$HTTPS = (
    (
        isset($_SERVER['HTTPS'])
        && stristr($_SERVER['HTTPS'], 'on')
    ) || (
        isset($_SERVER['HTTPS'])
        && $_SERVER['HTTPS'] == 1
    ) || (
        isset($_SERVER['SERVER_PORT'])
        && $_SERVER['SERVER_PORT'] == 443
    ) || (
        isset($_SERVER['SCRIPT_URI'])
        && is_string($_SERVER['SCRIPT_URI'])
        && !strncmp($_SERVER['SCRIPT_URI'], 'https://', 8)
    ) || (
        isset($_SERVER['HTTP_X_FORWARDED_PROTO'])
        && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'
    )
);

/**
 * Uncomment the code below if $HTTPS isn't detected correctly
 * (this may happen on some systems)
 */

// $HTTPS = $HTTPS || (isset($_SERVER['HTTP_FRONT_END_HTTPS']) && (stristr($_SERVER['HTTP_FRONT_END_HTTPS'], 'on') || $_SERVER['HTTP_FRONT_END_HTTPS'] == 1));

// ========================================================= //
// Please place your custom detection code below these lines //
// ========================================================= //
//
//
// If you wish to set X-Cart to work through an HTTPS proxy, define the proxy
// IP address here and set the variable $HTTPS to 'true'. X-Cart will match all
// the IP addresses it will receive with incoming requests against the IP
// address specified here and thus will be able to define whether a request is
// coming from HTTPS proxy or not.
// If the web path used for work via HTTPS proxy differs from the path used for
// work via HTTP (for example, HTTP xcart web root: '/xcart/'; HTTPS xcart web
// root: '/~example/xcart/'), you also need to set the variable $HTTPS_RELAY to
// 'true'.
// Please find an example of processing such a situation below (In the example,
// the HTTPS proxy IP address is 192.160.1.1):
//
// if ($_SERVER['REMOTE_ADDR'] == '192.160.1.1') {
//     $HTTPS_RELAY = true;
//     $HTTPS = true;
// }

?>
