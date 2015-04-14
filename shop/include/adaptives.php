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
 * Adaptives module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v65 (xcart_4_7_0), 2015-02-17 23:56:28, adaptives.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

x_session_register('is_location');
x_session_register('adaptives');

function func_parse_user_agent($HTTP_USER_AGENT) { // {{{
    $ua = false;
    // HTTP_USER_AGENT analyze...
    $preg = $ppreg = $__ver = array();

    if (preg_match("/^([\w\d_]+)[\/ ]+([^(]+)\s+\((.+)\)(?:\s+(.+))?$/Ss", $HTTP_USER_AGENT, $preg)) {
        array_shift($preg);
        $preg = func_array_map('trim', $preg);

        // Fix for Opera 10.00 version

        if ($preg[0] == 'Opera' && $preg[1] == '9.80' && preg_match("/ Version\/(.+)$/Ss", $HTTP_USER_AGENT, $__ver)) {
            $preg[1] = $__ver[1];
        }

        $ua = array(
            'browser'  => $preg[0],
            'version'  => (float)$preg[1],
            'hard'     => 'i386',
            'language' => "en-US"
        );

        // Browser platform detect
        $platforms = array(
            'Win32'         => 'Win32',
            'FreeBSD'       => 'FreeBSD',
            'Linux'         => 'Linux',
            'Mac_PowerPC'   => 'MacPPC',
            'Windows NT'    => 'Win32',
            'Windows 95'    => 'Win32',
            'Windows 98'    => 'Win32',
            'Windows 2000'  => 'Win32',
            'Windows XP'    => 'Win32',
            'Macintosh'     => 'MacPPC',
            'iPhone'        => 'iPhone',
            'PPC Mac OS'    => 'MacPPC',
            'J2ME/MIDP'     => 'J2ME Midlet'
        );

        $ua['platform'] = 'Win32';

        foreach($platforms as $p => $pn) {

            if(strpos($preg[2],$p) !== false) {

                $ua['platform'] = $pn;

                if($ua['platform'] != 'Win32') {

                    $p = preg_quote($p, '/');
                    if(preg_match('/' . $p . " ([^;]+)/Ss", $preg[2], $ppreg)) {
                        $ua['hard'] = $ppreg[1];
                    }

                } else {

                    $ua['hard'] = 'i386';

                }
                break;
            }
        }

        // Local language detect
        if( isset( $preg[2])) {
            if(preg_match("/ (\w{2}-\w{2})/Ss", $preg[2], $ppreg)) {
                $ua['language'] = $ppreg[1];
            }
        } else {
            $ua['language'] = NULL;
        }
        
        // Browser detect
        if($ua['browser'] == 'Mozilla' && ($ua['platform'] == 'MacPPC' || $ua['platform'] == 'Win32') && (preg_match("/MSIE ([\d\.]+);/USis",$preg[2],$ppreg) || empty($preg[3]))) {
            // IE <= 10
            $ua['browser'] = 'MSIE';
            $ua['version'] = $ppreg[1];
            
        } elseif ($ua['browser'] == 'Mozilla' && (!empty($preg[3]) && $preg[3] == 'like Gecko') && preg_match("/Windows(.*)rv:([\d\.]{2,})/Sis",$preg[2],$ppreg)) {
            // IE >= 11
            $ua['browser'] = 'MSIE';
            $ua['version'] = $ppreg[2];

        } elseif(!empty($preg[3])) {

            $browsers = array(
                'KHTML'     => 'Konqueror',
                'Epiphany'  => 'Epiphany',
                'Opera'     => 'Opera',
                'Flock'     => 'Flock',
                'Firefox'   => 'Firefox',
                'Netscape'  => 'Netscape',
                'FireBird'  => 'Firefox',
                'Phoenix'   => 'Firefox',
                'Chrome'    => 'Chrome',
                'Safari'    => 'Safari'
            );

            foreach($browsers as $b => $bn) {
                if(preg_match('/'.$b."[\/ ]+([\d\.]+)/Sis", $preg[3],$ppreg)) {
                    $ua['browser'] = $bn;
                    $ua['version'] = $ppreg[1];
                    break;
                }
            }
        }
    }

    return $ua;
} // }}}

$_ua = func_parse_user_agent($HTTP_USER_AGENT);
if (!empty($_ua)) {
    $config['UA'] = $_ua;
    unset($_ua);
}

// If request from ROBOT...
if (defined('IS_ROBOT')) {

    $adaptives = array(
        'is_first_start'    => '',
        'isDOM'             => 'Y',
        'isJS'              => 'Y',
        'isStrict'          => 'Y',
        'isJava'            => '',
        'browser'           => 'MSIE',
        'version'           => '6',
        'platform'          => 'Win32',
        'isCookie'          => 'Y',
        'screen_x'          => '1024',
        'screen_y'          => '768',
        'isFlash'           => '',
    );

} else {

    if (isset($adaptive_restart))
        $adaptives = array();

    if (!empty($config['UA']))
        $adaptives = func_array_merge($adaptives, $config['UA']);

    // Start request
    if (
        (
            !isset($adaptives['is_first_start'])
            || (
                $adaptives['is_first_start'] == 'Y'
                && $is_location == 'Y'
            )
        )
        && !empty($REQUEST_METHOD)
        && $REQUEST_METHOD == 'GET'
    ) {

        $is_location = '';

        $adaptives = array(
            'is_first_start' => 'Y'
        );

        if (defined('XC_SESSION_DB_SAVE_ADAPTIVES')) {
            x_session_save('adaptives', 'is_location');
        }

    // Else ...
    } else {

        $adaptives['is_first_start'] = '';

        if (defined('XC_SESSION_DB_SAVE_ADAPTIVES')) {
            x_session_save('adaptives');
        }

    }

}

// Save adaptives array to the config array
$config['Adaptives'] = $adaptives;

?>
