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
 * Cryptographic hash class
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Crypt
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v9 (xcart_4_7_0), 2015-02-17 23:56:28, class.XCPasswordHash.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

global $xcart_dir;
require_once $xcart_dir . '/include/lib/PasswordHash.php';

class XCPasswordHash extends PasswordHash { // {{{
    const ITERATION_COUNT_LOG2 = 11;
    const USE_STRONG_HASH = FALSE;
    const HASH_PREFIX = '$XCHash$';

    function __construct() { //{{{

        parent::__construct(self::ITERATION_COUNT_LOG2, self::USE_STRONG_HASH);

    } //}}}

    function get_random_bytes($count) { // {{{ /* Must be public used in func_get_secure_random_key*/

        $output = '';

        if (function_exists('openssl_random_pseudo_bytes')) {
            $output = openssl_random_pseudo_bytes($count, $crypto_strong);
            if (!$crypto_strong) {
                $output = '';
            }
        }

        if (
            strlen($output) != $count
            && defined('X_PHP530_COMPAT')
            && function_exists('mcrypt_create_iv')
        ) {
            $output = mcrypt_create_iv($count, MCRYPT_DEV_URANDOM);
        }

        if (
            strlen($output) != $count
            && @is_readable('/dev/urandom')
            && ($fh = @fopen('/dev/urandom', 'rb'))
        ) {
            $output = fread($fh, $count);
            fclose($fh);
        }

        if (
            strlen($output) != $count
            && X_DEF_OS_WINDOWS
            && class_exists('COM')
        ) {
            try {
                $com_obj = new COM('CAPICOM.Utilities.1');
                $output = base64_decode($com_obj->GetRandom($count, 0));
            } catch (Exception $exp) {
                $output = '';
            }
        }

        if (strlen($output) != $count) {
            $output =  parent::get_random_bytes($count);
        }

        return $output;

    } // }}}

    function HashPassword($password) { //{{{

        $return = parent::HashPassword($password);

        if ($return[0] != '*') {
            $return = self::HASH_PREFIX . $return;
        }

        return $return;

    } //}}}

    function CheckPassword($password, $stored_hash) { //{{{

        if (self::isPasswordHash($stored_hash)) {
            $stored_hash = substr($stored_hash, strlen(self::HASH_PREFIX));
        }

        return parent::CheckPassword($password, $stored_hash);

    } //}}}

    static function isPasswordHash($check_hash) { //{{{

        return (strpos($check_hash, self::HASH_PREFIX) === 0);

    } //}}}

} // class XCPasswordHash }}}

?>
