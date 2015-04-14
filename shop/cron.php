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
 * Cron jobs execution
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v28 (xcart_4_7_0), 2015-02-17 13:29:01, cron.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('X_CRON', TRUE);
define('SKIP_CHECK_REQUIREMENTS.PHP', TRUE);

if (!defined('X_INTERNAL_CRON')) {

    if (php_sapi_name() != 'cli') {
        header("Location: ./");
        die("Access denied");
    }

    chdir(__DIR__);
    require_once __DIR__.'/auth.php';

    $argv = $_SERVER['argv'];
    array_shift($argv);

    // Get options
    $options = array();

    foreach ($argv as $a) {
        if (preg_match("/--([\w\d_]+)\s*=\s*(['\"]?)(.+)['\"]?$/Ss", trim($a), $match)) {
            $options[strtolower($match[1])] = $match[2] ? stripslashes($match[3]) : $match[3];
        }
    }

    // Check key
    if (!isset($options['key']) || !preg_match("/^[a-zA-Z0-9_]{6,}$/Ss", $options['key']) || $config['General']['cron_key'] != $options['key'])
        exit(1);

    $sowner = get_current_user();
    $processuser = posix_getpwuid(posix_geteuid());
    $processuser = $processuser['name'];

} elseif (!defined('XCART_START')) {

    header("Location: ./");
    die("Access denied");

}

if (!isset($cron_tasks) || !is_array($cron_tasks) || count($cron_tasks) == 0) {
    if (defined('X_INTERNAL_CRON'))
        return;

    exit(2);
}

foreach ($cron_tasks as $index => $t) {
    if (!is_array($t) || !isset($t['function']) || !is_string($t['function']))
        continue;

    if (!defined('X_INTERNAL_CRON')) {
        echo "Task #$index\n";
    }

    if (isset($t['x_load']) && is_string($t['x_load']))
        x_load($t['x_load']);

    if (
        isset($t['include'])
        && is_string($t['include'])
        && file_exists($t['include'])
        && is_readable($t['include'])
    ) {
        include_once $t['include'];
    }

    if (!function_exists($t['function']))
        continue;

    $st = func_microtime();
    if (!defined('X_INTERNAL_CRON')) {
        $log = "Script owner / process user: " . $sowner . " / " . $processuser . "\n";

    } else {
        $log = "Session id: " . $XCARTSESSID . "\n";
    }

    $r = $t['function']();
    $et = func_microtime();
    $log .= "Task duration: from " . date("m/d/y H:i:s", $st) . " to " . date("m/d/y H:i:s", $et) . " (" . round($et - $st, 4) . " sec.)\nTask message:\n\t" . $r;

    x_log_add('CRON', $log);
    if (!defined('X_INTERNAL_CRON')) {
        echo $log;
        echo "\n=========\n";
    }
}

if (!defined('X_INTERNAL_CRON'))
    exit(0);

?>
