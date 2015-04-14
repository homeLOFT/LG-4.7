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
 * Lanuage functions for the installation script
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v41 (xcart_4_7_0), 2015-02-17 23:56:28, install_lng.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

$available_install_languages = array();
$install_lng_defs = array ();

/**
 * Declaration of $install_languages begins in appropriate language files (e.g. install_lng_XX.php)
 */
$install_languages = array ();

$_lng_files = get_dirents_mask($xcart_dir.'/include',
    '!^install_lng_..\.php$!S');

foreach ($_lng_files as $dirent=>$dirent_matches) {
    require $xcart_dir.'/include/'.$dirent;
}

$available_install_languages = array_keys($install_languages);

function lng_get()
{
    global $install_languages;
    global $install_language_code;

    $args = func_get_args();

    if (count($args) == 0) return '';
    $index = array_shift($args);

    if (isset($install_languages[$install_language_code][$index]))
        $result = $install_languages[$install_language_code][$index];
    else
    if (isset($install_languages['US'][$index]))
        $result = $install_languages['US'][$index];
    else {
        return $index;
    }

    $replace_to = array();
    for ($i = 0, $cnt = count($args); $i < $cnt; $i+=2) {
        $replace_to[$args[$i]] = $args[$i+1];
    }

    if (!empty($replace_to))
        foreach ($replace_to as $k=>$v)
            $result = str_replace("{{".$k."}}", $v, $result);

    // Remove unassigned variables from language template
    $result = preg_replace('/{{[a-z0-9_]+}}/s', '', $result);
    return $result;
}

function echo_lng()
{
    $args = func_get_args();
    echo call_user_func_array('lng_get', $args);
}

function echo_lng_quote()
{
    $args = func_get_args();
    echo htmlspecialchars(call_user_func_array('lng_get', $args));
}

function echo_lng_js()
{
    $args = func_get_args();
    $data = addslashes(call_user_func_array('lng_get', $args));
    $data = str_replace("\r","\\r", $data);
    $data = str_replace("\n","\\n", $data);
    echo $data;
}

?>
