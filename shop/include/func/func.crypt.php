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
 * Crypt functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v58 (xcart_4_7_0), 2015-02-17 23:56:28, func.crypt.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

function text_hash($s) {
    global $xcart_dir;

    require_once $xcart_dir . '/include/classes/class.XCPasswordHash.php';

    $t_hasher = new XCPasswordHash();
    $hash = $t_hasher->HashPassword($s);

    return $hash;
}

function text_verify($plain_text, $hash) {
    global $xcart_dir;

    require_once $xcart_dir . '/include/classes/class.XCPasswordHash.php';
    
    assert('preg_match(\'/^' . preg_quote(XCPasswordHash::HASH_PREFIX). '\$2a\$/s\', $hash) /* '.__FUNCTION__.': Wrong or weak hash is used */');
    $t_hasher = new XCPasswordHash();
    $is_correct = $t_hasher->CheckPassword($plain_text, $hash);

    return $is_correct;
}

function text_crypt($s, $type = 'B', $key = false)
{
    global $blowfish, $encryption_types;

    if (strlen($s) == 0)
        return $s;

    if (!in_array((string)$type, $encryption_types))
        $type = 'B';

    $s = trim($s);
    $s .= func_crc32(md5($s));

    if ($type == 'B' || $type == 'C') {
        // Blowfish
        if ($key === false)
            $key = func_get_crypt_key($type);

        if (!$blowfish || empty($key))
            return $s;

        $s = func_bf_crypt($s, $key);

    }

    return $type."-".$s;
}

function text_decrypt($s, $key = false)
{
    global $blowfish;

    if (strlen($s) == 0)
        return $s;

    // Parse crypted data
    $type = func_get_crypt_type($s);
    $result = NULL;
    if ($type === false) {
        x_log_flag('log_decrypt_errors', 'DECRYPT', "Unknown hash type", true);
        return NULL;

    } elseif (substr($s, 1, 1) == '-') {
        $crc32 = true;
        $s = substr($s, 2);

    } else {
        $crc32 = substr($s, 1, 8);
        $s = substr($s, 9);
    }

    // Blowfish
    if ($type == 'B' || $type == 'C') {
        if ($key === false)
            $key = func_get_crypt_key($type);

        if (!$blowfish) {
            x_log_flag('log_decrypt_errors', 'DECRYPT', "The Blowfish service object is missing", true);
            return false;

        } elseif (empty($key)) {
            x_log_flag('log_decrypt_errors', 'DECRYPT', "The key for the selected type ('".$type."') of encryption is missing", true);
            return false;
        }

        $result = trim(func_bf_decrypt($s, $key));

    }

    // CRC32 check
    if ($crc32 === true) {
        // Inner CRC32
        $crc32 = substr($result, -8);
        $result = substr($result, 0, -8);
        if (func_crc32(md5($result)) != $crc32)
            $result = NULL;

    } elseif ($crc32 !== false) {
        // Outer CRC32
        if (func_crc32($result) != $crc32)
            $result = NULL;
    }

    if (is_null($result)) {
        x_log_flag('log_decrypt_errors', 'DECRYPT', "Could not decrypt data", true);
    }

    return $result;
}

/**
 * Get encryptiond/decrtyption key
 */
function func_get_crypt_key($type)
{
    global $blowfish_key, $merchant_password;

    if ($type == 'B') {
        return $blowfish_key;

    } elseif ($type == 'C') {
        x_load('order');
        return func_check_merchant_password() ? $merchant_password : false;
    }

    return false;
}

/**
 * Get crypted string type
 */
function func_get_crypt_type($str)
{
    global $encryption_types;

    $s = substr($str, 0, 1);

    if (!in_array((string)$s, $encryption_types))
        $s = false;

    return $s;
}

/**
 * Check blowfish key
 */
function func_check_blowfish_key()
{
    global $sql_tbl;

    $data = func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name = 'crypted_data'");

    return !empty($data) && text_decrypt($data) === 'TEXT';
}

/**
 * Refresh check blowfish data
 */
function func_refresh_check_blowfish_data($new_blowfish_key = null)
{
    return func_array2insert(
        'config',
        array(
            'name' => 'crypted_data',
            'value' => text_crypt('TEXT', 'B', $new_blowfish_key ? $new_blowfish_key : false),
        ),
        true
    );
}


?>
