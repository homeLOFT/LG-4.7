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
 * Get necessary language data, process language change action
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v145 (xcart_4_7_0), 2015-02-17 23:56:28, get_language.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

define('GET_LANGUAGE', 1);

x_session_register('old_lng');
x_session_register('is_switched_language');

if (
    !empty($edit_lng)
    && !empty($_GET['old_lng'])
) {

    $asl = $edit_lng;

    $_POST['asl'] = $asl;

    $old_lng = $_GET['old_lng'];

    $QUERY_STRING = func_qs_remove($QUERY_STRING,'edit_lng', 'old_lng');
    $HTTP_REFERER = func_qs_remove($HTTP_REFERER, 'edit_lng', 'old_lng');
}

if (
    !empty($old_lng)
    && !defined('IS_MULTILANGUAGE')
) {

    if ($config['Appearance']['restore_language_interface'] == 'Y') {

        $asl = $old_lng;

        $_POST['asl'] = $asl;

        $HTTP_REFERER = $PHP_SELF."?".$QUERY_STRING;

    }

    $old_lng = '';
}

$e_langs = func_data_cache_get('charsets');

if (!isset($e_langs[$config['default_customer_language']]) && !empty($e_langs) && is_array($e_langs))
    $config['default_customer_language'] = key($e_langs);

if (!isset($e_langs[$config['default_admin_language']]) && !empty($e_langs) && is_array($e_langs))
    $config['default_admin_language'] = key($e_langs);

// Define redirect URL
if (
    (
        isset($is_https_redirect)
        && $is_https_redirect == 'Y'
    )
    || empty($HTTP_REFERER)
    || strpos($HTTP_REFERER, "error=disabled_cookies")
    || !func_is_internal_url($HTTP_REFERER)
) {

    if (
        isset($redirect)
        && !empty($redirect)
        && func_is_internal_url(($HTTPS ? ("https://" . $xcart_https_host) : ("http://" . $xcart_http_host) . $redirect))
    ) {

        $l_redirect = ($HTTPS ? ("https://" . $xcart_https_host) : ("http://" . $xcart_http_host)) . $redirect;

    } else {

        $l_redirect = $PHP_SELF . "?" . $QUERY_STRING;

    }

} else {

    $l_redirect = $HTTP_REFERER;

}

$l_redirect = func_qs_remove(
    $l_redirect,
    'sl',
    $XCART_SESSION_NAME,
    'redirect',
    'is_https_redirect'
);

$predefined_lng_variables = (!empty($smarty->webmaster_mode) || $smarty->debugging)
    ? array(
        'lbl_execution_time',
        'lbl_included_templates_config_files',
        'lbl_xcart_debugging_console',
        'txt_assigned_config_file_variables',
        'txt_assigned_template_variables',
        'txt_no_config_vars_assigned',
        'txt_no_template_variables_assigned',
    )
    : array();

// XMultiCurrency: initialize/update user's selected currency and country
if (!empty($active_modules['XMultiCurrency'])) {
    $_mc_redirect = false;
    include_once $xcart_dir . '/modules/XMultiCurrency/selector.php';
}
// XMultiCurrency

if (!isset($store_language)) {
    $store_language = '';
}

$old_store_language = $store_language;

if ($login) {

    unset($store_language);

}

if (!empty($_GET['sl'])) {
    $store_language = $_GET['sl'];
    $is_switched_language = 'Y';
}

$shop_language = $_lng_from_db = '';

if (
    empty($current_area)
    || $current_area == 'C'
) {

    if (empty($store_language) && !empty($login)) {

        if ($is_switched_language != 'Y') {

            $store_language = $_lng_from_db = func_query_first_cell ("SELECT $sql_tbl[customers].language FROM $sql_tbl[customers], $sql_tbl[languages] WHERE $sql_tbl[customers].id='$logged_userid' AND $sql_tbl[customers].language = $sql_tbl[languages].code");

            if (!isset($do_redirect) && $old_store_language != $store_language)
                $do_redirect = true;

        } else {

            $store_language = $old_store_language;

        }

    }

    if (
        !empty($store_language)
        && isset($e_langs)
        && is_array($e_langs)
        && (
            !is_scalar($store_language)
            || !isset($e_langs[$store_language])
        )
    ) {
        $store_language = '';
    }

    if (empty($store_language))
        $store_language = $config['default_customer_language'];

    if (!isset($e_langs[$store_language])) {

        $store_language = isset($e_langs[$config['default_customer_language']])
            || empty($e_langs)
            || !is_array($e_langs)
            ? $config['default_customer_language']
            : key($e_langs);

    }

    $shop_language = $store_language;

} else {

    x_session_register('current_language');

    if (isset($_POST['asl'])) {

        $res = func_query_first ("SELECT charset FROM $sql_tbl[language_codes] WHERE code = '" . $_POST["asl"] . "'");

        if ($res) {

            $current_language = $_POST['asl'];

        }

        func_header_location($l_redirect);
    }

    if (!isset($current_language) || empty($current_language))
        $current_language = $config['default_admin_language'];

    if (
        is_array($e_langs)
        && !isset($e_langs[$current_language])
    ) {

        if (!isset($e_langs[$config['default_admin_language']])) {

            $current_language = key($e_langs);

            reset($e_langs);

        } else {

            $current_language = $config['default_admin_language'];

        }

    }

    $smarty->assign('current_language', $current_language);

    $shop_language = $current_language;

}

$default_charset = $e_langs[$shop_language];
if (empty($default_charset)) 
    $default_charset = 'UTF-8';

if (function_exists('mb_internal_encoding'))
    mb_internal_encoding($default_charset);

$smarty->assign ('default_charset', $default_charset);

// Override web server Content-Type header
if (!empty($e_langs[$shop_language])) {

    header("Content-Type: text/html; charset=" . $e_langs[$shop_language]);

}

x_session_register('editor_mode');

if (
    $login
    && $_lng_from_db != $shop_language
    && !empty($logged_userid)
) {
    func_array2update(
        'customers',
        array(
            'language' => $shop_language,
        ),
        'id = \'' . $logged_userid . '\''
    );

}

if (@$current_area == 'C' || @$current_area == 'B') {

    // Set cookies
    if (
        !defined('NOCOOKIE')
        && (
            !isset($_COOKIE['store_language'])
            || $store_language != $_COOKIE['store_language']
        )
    ) {
        func_setcookie('store_language', $store_language, XC_TIME + 31536000, false); // for one year
    }
}

$all_languages = func_data_cache_get('languages', array($shop_language));

if (empty($all_languages)) {

    $def_language = @$current_area == 'C'
        ? $config['default_customer_language']
        : $config['default_admin_language'];

    $all_languages = func_data_cache_get('languages', array($def_language));

    if (
        empty($all_languages)
        && !empty($e_langs)
        && is_array($e_langs)
    ) {

        $all_languages = func_data_cache_get('languages', array(key($e_langs)));

        reset($e_langs);

    }

}

if (!empty($active_modules['Feature_Comparison'])) {
    if (empty($shop_language))
        func_set_lng_current('feature_variants_lng_', key($all_languages));
    else    
        func_set_lng_current('feature_variants_lng_', $shop_language);
}

if (empty($shop_language))
    func_set_lng_current('products_lng_', key($all_languages));
else    
    func_set_lng_current('products_lng_', $shop_language);

$n_langs = array ();

if (!empty($all_languages)) {

    ksort($all_languages);

    foreach ($all_languages as $k => $value) {

        if (
            in_array(@$current_area, array('C', 'B'))
            && 'Y' === $value['disabled']
        ) {

            unset($all_languages[$k]);

            continue;

        }

        $var_language = func_get_langvar_by_name('language_' . $value['code']);

        if (!empty($var_language))
            $value['language'] = $var_language;

        $all_languages[$k]['language'] = empty($value['language'])
            ? 'language_' . $value['code']
            : $value['language'];

    }

}

if (
    (
        $current_area == 'C'
        || $current_area == 'B'
    )
    && !empty($_GET['sl'])
    && $old_store_language != $_GET['sl']
    && !defined('IS_ROBOT')
    && (
        !preg_match('/\.html?($|\?)|\/$/s', $l_redirect)
        || preg_match('/^(' . preg_quote($http_location, '/') . '|' . preg_quote($https_location, '/') . ')/', $l_redirect)
    )
) {

    func_header_location($l_redirect);

}
 
// XMultiCurrency: redirect if user have changed currency or country
if (!empty($active_modules['XMultiCurrency'])) {
    func_mc_redirect($l_redirect, $_mc_redirect);
}
// XMultiCurrency

$smarty->assign ('all_languages',     $all_languages);
$smarty->assign ('store_language',    @$store_language);
$smarty->assign ('shop_language',     $shop_language);
$smarty->assign ('all_languages_cnt', sizeof($all_languages));

$config['Company']['location_country_name']       = func_get_country($config['Company']['location_country']);
$config['Company']['location_state_name']         = func_get_state($config['Company']['location_state'], $config['Company']['location_country']);
$config['Company']['location_country_has_states'] = func_is_display_states($config['Company']['location_country']);

$smarty->assignByRef('config', $config);

if ($all_languages[$shop_language]['r2l'] == 'Y') {

    $container_classes[] = 'rtl';

}

// Encode AJAX post/get variables content for further processing
// according to the current charset
if (
    func_is_ajax_request() 
    && strtoupper($default_charset) != 'UTF-8'
) { 

    foreach ($_GET as $__var => $__res) {
        $$__var = $_GET[$__var] = func_convert_encoding($__res, 'UTF-8', $default_charset);
    }
    
    foreach ($_POST as $__var => $__res) {
        $$__var = $_POST[$__var] = func_convert_encoding($__res, 'UTF-8', $default_charset);
    }

}

?>
