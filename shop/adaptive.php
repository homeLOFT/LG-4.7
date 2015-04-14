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
 * Save browser enviroment settings
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Customer interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v36 (xcart_4_7_0), 2015-02-17 13:29:01, adaptive.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('QUICK_START', TRUE);
define('SKIP_CHECK_REQUIREMENTS.PHP', TRUE);
define('USE_SIMPLE_SESSION_INTERFACE', TRUE);

require_once __DIR__.'/top.inc.php';
require __DIR__.'/init.php';

header("Content-type: text/javascript");

x_session_register('adaptives');

if(
    !empty($send_browser)
    && $REQUEST_METHOD == 'GET'
) {

    $adaptives['isJS'] = 'Y';

    $arr = explode("|", $send_browser);

    $tmp = array(
        'isDOM',
        'isStrict',
        'isJava',
    );

    for($x = 0; $x < count($tmp); $x++) {

        $adaptives[$tmp[$x]] = $arr[0][$x] == 'Y'
            ? 'Y'
            : '';

    }

    $arr = func_array_map('func_secure_urldecode', $arr);

    $adaptives['browser']   = (isset($adaptives['browser']) && !empty($adaptives['browser']))
        ? $adaptives['browser']
        : $arr[1];

    $adaptives['version']   = (isset($adaptives['version']) && !empty($adaptives['version']))
        ? $adaptives['version']
        : $arr[2];

    $adaptives['platform']  = preg_replace("/^(\S+).*$/S", "\\1", $arr[3]);
    $adaptives['isCookie']  = $arr[4];
    $adaptives['screen_x']  = $arr[5];
    $adaptives['screen_y']  = $arr[6];

    $adaptives['is_first_start'] = '';

    if ($arr[7] == 'C') {
        // Delete expired rows all exept two last years
        if (!mt_rand(0, 600)) {
            // For performance purposes use mt_rand
            $_period = 365*2*SECONDS_PER_DAY;
            db_query("DELETE FROM $sql_tbl[stats_adaptive] WHERE last_date < " . (XC_TIME - $_period));
        }

        db_query("INSERT INTO $sql_tbl[stats_adaptive] 
            (platform,browser,java,js,version,cookie,screen_x,screen_y,count,last_date) VALUES 
            ('$adaptives[platform]','$adaptives[browser]','$adaptives[isJava]','$adaptives[isJS]','$adaptives[version]','$adaptives[isCookie]','$adaptives[screen_x]','$adaptives[screen_y]',1,'" . XC_TIME . "') 
            ON DUPLICATE KEY UPDATE count=count+1,last_date='" . XC_TIME . "'"
        );
    }

    x_session_save('adaptives');
}

// Do not run x_session_save in shutdown function
func_define('XC_DISABLE_SESSION_SAVE', true);

?>
