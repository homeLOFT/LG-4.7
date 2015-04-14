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
 * Core functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v612 (xcart_4_7_0), 2015-02-17 23:56:28, func.core.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

define('CHECK_UNIQ', true);
define('SKIP_CHECK_UNIQ', !CHECK_UNIQ);

define('USE_CACHE', true);
define('SKIP_CACHE', !USE_CACHE);

define('TEXT_MODE', TRUE);
define('HTML_MODE', !TEXT_MODE);

/******************************************
 * Core interfaces and classes            *
 ******************************************/

/**
 * Singleton interface
 */
interface XC_Singleton_Interface
{
    /**
     * Method to access a singleton
     */
    public static function getInstance();
}

/**
 * Singleton class
 */
abstract class XC_Singleton implements XC_Singleton_Interface
{
    /**
     * Array of instances for all derived classes
     *
     * @var array
     */
    protected static $instances = array();

    /**
     * Disallow direct access to constructor
     */
    protected function __construct() {}

    /**
     * Method to access a class singleton
     *
     * @return Class
     */
    protected static function getClassInstance($className)
    {
        // Create new instance of the object (if it is not already created)
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new $className();
        }

        return self::$instances[$className];
    }
}

abstract class XCEmailFormat {
    const EXTENDED = 'EXTENDED';
}

/******************************************
 * Core functions                         *
 ******************************************/

/**
 * Use this function to load code of functions on demand (include/func/func.*.php)
 */
function x_load()
{
    global $xcart_dir, $xloaded_modules;
    $names = func_get_args();
    foreach ($names as $n) {
        if (isset($xloaded_modules[$n])) {
            continue;
        }

        $n = str_replace('..', '', $n);
        $f = $xcart_dir . '/include/func/func.' . $n . '.php';
        if (file_exists($f)) {
            require_once $f;
        } else {
            assert('FALSE /* '.__FUNCTION__.': x_load tried to load non-existent func file*/');
        }

        $xloaded_modules[$n] = 1;

        // Log file which called the x_load function
        if (defined('DEVELOPMENT_MODE')) {
            $traces = debug_backtrace();
            $xloaded_modules[$n] = $traces[0]['file'] . ':' . $traces[0]['line'];
        }
    }

    return TRUE;
}

function x_load_module()
{
    global $xcart_dir, $active_modules;
    static $xloaded_modules_func = array();
    $names = func_get_args();
    foreach ($names as $n) {
        if (isset($xloaded_modules_func[$n])) {
            continue;
        }

        $n = str_replace('..', '', $n);
        $f = $xcart_dir . '/modules/' . $n . '/func.php';
        if (
            empty($active_modules[$n])
            && file_exists($f)
        ) {
            require_once $f;
        } elseif(empty($active_modules[$n])) {
            assert('FALSE /* '.__FUNCTION__.': x_load tried to load non-existent module func file*/');
        }

        $xloaded_modules_func[$n] = 1;

        // Log file which called the x_load function
        if (defined('DEVELOPMENT_MODE')) {
            $traces = debug_backtrace();
            $xloaded_modules_func[$n] = $traces[0]['file'] . ':' . $traces[0]['line'];
        }
    }

    return TRUE;
}

/**
 * This function replaced standard PHP function header('Location...')
 */
function func_header_location($location, $keep_https = true, $http_error_code = 302, $allow_error_redirect = false, $show_note = true)
{
    global $XCART_SESSION_NAME, $XCARTSESSID, $is_location, $config, $HTTPS, $REQUEST_METHOD, $xcart_catalogs;

    $is_location = 'Y';

    $is_error_script = strpos($location, 'error_message.php');

    if ($is_error_script !== false) {

        $is_relative_link = false;

        if ($is_error_script == 0 || $is_error_script == 1) {
            $is_relative_link = true;
        }

        $link_to_area = null;
        $area_type = defined('AREA_TYPE') ? AREA_TYPE : 'C';

        if (
            !empty($xcart_catalogs)
            && is_array($xcart_catalogs)
            && $is_relative_link
        ) {
            $link_to_area = func_get_area_catalog($area_type);

            if ($is_error_script == 0) {
                $link_to_area .= '/';
            }

            $location = $link_to_area . $location;
        }
    }

    // You cannot redirect from the error message page.

    if (defined('IS_ERROR_MESSAGE') && !$allow_error_redirect) {
        global $id;
        if (isset($id))
            func_show_error_page("Sorry, the shop is inaccessible temporarily. Please try again later.", "Error code: " . $id);
    }

    if (function_exists('x_session_save')) {
        x_session_save();
        func_define('XC_DISABLE_SESSION_SAVE', true);
    }

    func_ajax_finalize();

    if (defined('IS_XPC_IFRAME')) {
        func_xpc_iframe_finalize($location);
    }

    $added = array();

    $supported_http_redirection_codes = array(
        '301' => "301 Moved Permanently",
        '302' => "302 Found",
        '303' => "303 See Other",
        '304' => "304 Not Modified",
        '305' => "305 Use Proxy",
        '307' => "307 Temporary Redirect"
    );

    $location = preg_replace('/[\x00-\x1f].*$/sm', '', $location);
    $location_info = @parse_url($location);

    if (
        !empty($XCARTSESSID)
        && (
            !isset($_COOKIE[$XCART_SESSION_NAME])
            || defined('SESSION_ID_CHANGED')
        )
        && !preg_match('/' . preg_quote($XCART_SESSION_NAME, '/') . "=/i", $location)
        && !defined('IS_ROBOT')
        && (
            empty($location_info)
            ||  !empty($location_info['host'])
        )
        && !defined('IS_ERROR_MESSAGE')
    ) {
        $added[] = $XCART_SESSION_NAME."=".$XCARTSESSID;
    }

    if (
        $keep_https
        && $REQUEST_METHOD == 'POST'
        && $HTTPS
        && strpos($location, 'keep_https=yes') === false
        && $config['Security']['leave_https'] != 'Y'
    ) {
        $added[] = "keep_https=yes";
        // this block is necessary (in addition to https.php) to prevent appearance of secure alert in IE
    }

    if (!empty($added)) {
        $hash = '';

        if (preg_match("/^(.+)#(.+)$/", $location, $match)) {#nolint
            $location = $match[1];#nolint
            $hash = $match[2];#nolint
        }

        $location .= (strpos($location, "?") === false ? "?" : "&") . implode("&", $added);

        if (!empty($hash))
            $location .= "#" . $hash;
    }

    // Opera 8.51 (8.x ?) notes:
    // 1. Opera requires both headers - 'Location' & 'Refresh'. Without 'Location' it displays
    //    HTML code for META redirect
    // 2. 'Refresh' header is required when answering to a POST request

    if (
        !empty($http_error_code)
        && in_array($http_error_code, array_keys($supported_http_redirection_codes))
    ) {
        @header("HTTP/1.1 " . $supported_http_redirection_codes[$http_error_code]);
    }

    @header("Location: " . $location);

    assert('!empty($_SERVER["HTTP_USER_AGENT"]) /* '.__FUNCTION__.': empty HTTP_USER_AGENT */');
    if (
        strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') !== FALSE
        || preg_match("/Microsoft|WebSTAR|Xitami/", getenv('SERVER_SOFTWARE'))
    ) {
        @header("Refresh: 0; URL=" . $location);
    }

    if ($show_note) {
        echo "<br /><br />"
            . func_get_langvar_by_name(
                'txt_header_location_note',
                array(
                    'time' => 2,
                    'location' => str_replace(array('"', "'", '<', '>'), array('&quot;','&#039;', '&lt;', '&gt;'), func_convert_amp($location)),
                ),
                false,
                true,
                true
            );
    }

    echo "<meta http-equiv=\"Refresh\" content=\"0;URL=" . str_replace(array('"', "'", '<', '>'), array('&quot;','&#039;', '&lt;', '&gt;'), $location) ."\" />";

    func_flush();
    exit();
}

/**
 * Calculates weight from user units to grams
 */
function func_weight_in_grams($weight)
{
    global $config;

    return $weight * $config['General']['weight_symbol_grams'];
}

/**
 * Calculates dimension from user units to centimeters
 */
function func_dim_in_centimeters($value)
{
    global $config;

    return $value * $config['General']['dimensions_symbol_cm'];
}

/**
 * Converts weight and dimension units
 * Supported units:
 *   weight: lbs, oz, kg, g
 *   dimensions:  in, cm, dm, m
 */
function func_units_convert($value, $from_unit = 'lbs', $to_unit = 'kg', $precision = null)
{
    $from_unit     = strtolower($from_unit);
    $to_unit     = strtolower($to_unit);

    if (strcmp($from_unit, $to_unit) != 0) {

        $units = array(
            "lbs-oz" => XCPhysics::OUNCES_PER_1LB,
            "lbs-g"  => XCPhysics::GRAMS_PER_1LB,
            "kg-lbs" => XCPhysics::LBS_PER_1KG,
            "kg-oz"  => XCPhysics::OUNCES_PER_1KG,
            "kg-g"   => XCPhysics::GRAMS_PER_1KG,
            "oz-g"   => XCPhysics::GRAMS_PER_1OUNCE,
            "in-cm"  => XCPhysics::CM_PER_1INCH,
            "in-dm"  => XCPhysics::CM_PER_1INCH/10,
            "in-m"   => XCPhysics::CM_PER_1INCH/100,
            "m-cm"   => 100,
            "m-dm"   => 10,
            "dm-cm"  => 10,
            "cm-mm"  => 10,
        );

        $rate = 1.0;

        if (array_key_exists("$from_unit-$to_unit", $units)) {

            $rate = $units["$from_unit-$to_unit"];

        } elseif (array_key_exists("$to_unit-$from_unit", $units)) {

            $rate = $units["$to_unit-$from_unit"];
            $rate = (($rate <= 0) ? 1.0 : (1.0 / $rate));

        }

        $value = $value * $rate;
    }

    return is_null($precision)
        ? ceil($value)
        : round($value, intval($precision));
}

/**
 * Get county by code
 */
function func_get_county ($countyid)
{
    global $sql_tbl;

    $county_name = func_query_first_cell("SELECT county FROM " . $sql_tbl['counties'] . " WHERE countyid='" . intval($countyid) . "'");

    return $county_name
        ? $county_name
        : $countyid;
}
/**
 * Get state by code
 */
function func_get_state ($state_code, $country_code)
{
    global $sql_tbl;

    $state_name = func_query_first_cell("SELECT state FROM " . $sql_tbl['states'] . " WHERE country_code='" . addslashes($country_code) . "' AND code='" . addslashes($state_code) . "'");

    return $state_name
        ? $state_name
        : $state_code;
}

/**
 * Get country by code
 */
function func_get_country ($country_code, $force_code = '')
{
    global $sql_tbl, $shop_language;

    $code = empty($force_code)
        ? $shop_language
        : $force_code;

    $country_name = func_query_first_cell("SELECT value as country FROM " . $sql_tbl['languages'] . " WHERE name='country_" . addslashes($country_code) . "' AND code = '" . $code . "'");

    return $country_name
        ? $country_name
        : $country_code;
}

/**
 * Convert price to 'XXXXX.XX' format
 */
function price_format($price)
{
    return sprintf("%.2f", round((double)$price + 0.00000000001, 2));
}

/**
 * Return number of available products
 */
function insert_productsonline()
{
    global $sql_tbl;

    return func_query_first_cell("SELECT COUNT(*) FROM " . $sql_tbl['products'] . " WHERE forsale!='N'");
}

/**
 * Return number of available items
 */
function insert_itemsonline()
{
    global $sql_tbl;

    return func_query_first_cell("SELECT SUM(avail) FROM " . $sql_tbl['products'] . " WHERE forsale!='N'");
}

/**
 * This function returns true if $cart is empty
 */
function func_is_cart_empty($cart)
{
    return empty($cart) || empty($cart['products']) && empty($cart['giftcerts']);
}

/**
 * Get all cached languages to avoid multiple sql query to xcart_languages table
 */
 function func_get_all_language_vars($lang_code, $lang_name = '')
 {//{{{
    static $all_lng_variables = array();

    if (isset($all_lng_variables[$lang_code])) {
        if (empty($lang_name)) {
            return $all_lng_variables[$lang_code];
        } else {
            return isset($all_lng_variables[$lang_code][$lang_name]) ? $all_lng_variables[$lang_code][$lang_name] : '';
        }
    }

    $lng_res[$lang_code] = func_data_cache_get('get_language_vars', array($lang_code));

    if (!empty($lng_res[$lang_code])) {
        $all_lng_variables[$lang_code] = $lng_res[$lang_code];
    }

    if (empty($lang_name)) {
        return $lng_res[$lang_code];
    } else {
        return $lng_res[$lang_code][$lang_name];
    }
 }//}}}

/**
 * Get value of language variable by its name and usertype
 */
function func_get_langvar_by_name($lang_name, $replace_to = NULL, $force_code = false, $force_output = false, $cancel_wm = false)
{
    global $sql_tbl, $current_area, $config, $shop_language, $editor_mode;
    global $smarty, $user_agent;
    global $predefined_lng_variables;

    $language_code = $shop_language;

    if ($force_code !== false)
        $language_code = $force_code;

    if (!$force_output && $editor_mode == 'editor')
        $force_output = true;

    if (
        $config['General']['use_cached_lng_vars'] == 'Y'
        && !$smarty->webmaster_mode
    ) {
        $use_cached_lng_vars = $force_output = true;
    }

    if ($force_output === false) {
        $predefined_lng_variables[] = $lang_name;
        if ($force_code === false)
            $language_code = "  ";

        $tmp = '';

        if (is_array($replace_to) && !empty($replace_to)) {
            foreach($replace_to as $k => $v) {
                $tmp .= $k . '>' . $v . '<<<';
            }

            $tmp = base64_encode(substr($tmp, 0, -3));
        }

        return '~~~~|' . $lang_name . '|' . $language_code . '|' . $tmp . '|~~~~';
    }

    $select_part_1 = 'SELECT value FROM ' . $sql_tbl['languages'] . ' WHERE code=\'';
    $select_part_2 = '\' AND name=\'' . $lang_name . '\'';

    $result = '';
    if (!empty($language_code)) {
        if (!empty($use_cached_lng_vars)) {
            $result = func_get_all_language_vars($language_code, $lang_name);
        } else {
            $result = func_query_first_cell($select_part_1 . $language_code . $select_part_2);
        }
    }    

    if (empty($result)) {
        $_language_code = $current_area == 'C'
            ? $config['default_customer_language']
            : $config['default_admin_language'];
        if ($_language_code != $language_code) {
            if (!empty($use_cached_lng_vars)) {
                $result = func_get_all_language_vars($_language_code, $lang_name);
            } else {
                $result = func_query_first_cell($select_part_1 . $_language_code . $select_part_2);
            }
        } elseif ($language_code != 'en') {
            if (!empty($use_cached_lng_vars)) {
                $result = func_get_all_language_vars('en', $lang_name);
            } else {
                $result = func_query_first_cell($select_part_1 . 'en' . $select_part_2);
            }
        }
    }

    assert('FALSE !== $result /* '.__FUNCTION__.': lang var not found */');
    if (is_array($replace_to)) {
        foreach ($replace_to as $k => $v) {
            $result = str_replace('{{' . $k . '}}', $v, $result);
        }
    }

    if (
        isset($smarty->webmaster_mode)
        && $smarty->webmaster_mode
        && !$cancel_wm
        && !empty($result)
    ) {
        $result = func_webmaster_label($user_agent, $lang_name, $result);
    }

    return $result;
}

/**
 * Flush output
 */
function func_flush($s = NULL, $display_mode = TEXT_MODE)
{
    if (!is_null($s))
        echo $s;


    $max_length = 0;
    if (preg_match("/Apache(.*)Win/S", getenv('SERVER_SOFTWARE'))) {
        $max_length = 2500;
    } elseif (preg_match("/(.*)MSIE(.*)\)$/S", getenv('HTTP_USER_AGENT'))) {
        $max_length = 256;
    }

    if ($max_length) {

        if ($display_mode === TEXT_MODE) {
            echo str_repeat(" ", $max_length);
        } else {
            $str = '<!-- ';
            $end = ' -->';
            while (strlen($str) < $max_length) {
                $str .= md5(mt_rand());
            }

            $str = substr($str, 0, $max_length - strlen($end)) . $end;
            echo $str;
        }
    }

    @ob_flush();

    flush();
}

/**
 * This function added the ability to redirect a user to another page using HTML meta tags
 * (without using header() function or Javascript)
 */
function func_html_location($url, $time=3) {//{{{
    x_session_save();
    func_define('XC_DISABLE_SESSION_SAVE', true);

    func_ajax_finalize();

    $url = preg_replace('/[\x00-\x1f].*$/sm', '', $url);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Refresh" content="<?php echo $time; ?>;URL=<?php echo str_replace(array('"', "'", '<', '>'), array('&quot;','&#039;', '&lt;', '&gt;'), $url); ?>" />
</head>
<body>
<br /><br />
<?php
    echo func_get_langvar_by_name('txt_header_location_note', array('time' => $time, 'location' => str_replace(array('"', "'", '<', '>'), array('&quot;','&#039;', '&lt;', '&gt;'), func_convert_amp($url))), false, true);
?>
</body>
</html>
<?php

    func_flush();

    func_exit();
}//}}}

/**
 * Process redirect of the main window from a child iframe
 * using JS
 *
 * @params $url location
 *
 * @return string
 * @access public
 * @see    ____func_see____
 */
function func_iframe_redirect($url) {//}}}
    x_session_save();
    func_define('XC_DISABLE_SESSION_SAVE', true);

    $url = preg_replace('/[\x00-\x1f].*$/sm', '', $url);

    if (func_is_ajax_request()) {

        func_register_ajax_message(
            'popupDialogCall',
            array(
                'action' => 'jsCall',
                'toEval'    => "window.location = '$url';"
            )
        );

        func_ajax_finalize();
    }

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
</head>
<body onload="javascript: parent.location = '<?php echo $url; ?>';">
Please wait while processing the payment details...
</body>
</html>
<?php
    func_flush();
    func_exit();
}//}}}

/**
 * exit() wrapper
 */
function func_exit()
{
    if (!defined('INTERNAL_CALL'))
        exit;
}

/**
 * This function returns the language variable value by name and language code
 */
function func_get_languages_alt($name, $lng_code = false, $force_get = false, $last_code = false, $all = false)
{
    global $sql_tbl, $shop_language, $config, $current_area;

    if ($all) {
        if (!is_array($name))
            $name = array($name);

        return func_query_hash("SELECT code, value FROM " . $sql_tbl['languages_alt'] . " WHERE name IN ('" . implode("','", $name) . "')", "code", false, true);
    }

    if ($lng_code === false)
        $lng_code = $shop_language;

    if ($force_get) {
        // Force get language variable(s) content
        $is_array = is_array($name);

        if (!$is_array)
            $name = array($name);

        if ($current_area == 'C' || $current_area == 'B') {

            $lngs = array(
                $lng_code,
                $config['default_customer_language'],
                $config['default_admin_language'],
                $last_code,
            );

        } else {

            $lngs = array(
                $lng_code,
                $config['default_admin_language'],
                $config['default_customer_language'],
                $last_code,
            );

        }

        $lngs = array_unique($lngs);

        $hash = array();

        foreach ($lngs as $lng_code) {

            $where = '';

            if ($lng_code !== false)
                $where = " AND code = '" . $lng_code . "'";

            $res = func_query_hash("SELECT name, value FROM " . $sql_tbl['languages_alt'] . " WHERE name IN ('" . implode("','", $name) . "')" . $where, "name", false, true);

            if (empty($res))
                continue;

            foreach($res as $n => $l) {

                if (!isset($hash[$n])) {

                    $hash[$n] = $l;
                    $idx = array_search($n ,$name);

                    if ($idx !== false)
                        unset($name[$idx]);
                }
            }

            if (empty($name))
                break;
        }

        return !$is_array ? array_shift($hash) : $hash;
    }

    if (is_array($name)) {
        return func_query_hash("SELECT name, value FROM " . $sql_tbl['languages_alt'] . " WHERE code='" . $lng_code . "' AND name IN ('" . implode("','", $name) . "')", "name", false, true);
    }

    return func_query_first_cell("SELECT value FROM " . $sql_tbl['languages_alt'] . " WHERE code='" . $lng_code . "' AND name='" . $name . "'");
}

/**
 * This function quotes arguments for shell command according
 * to the host operation system
 */
function func_shellquote()
{
    static $win_s = '!([\t \&\<\>\?]+)!S';
    static $win_r = '"\\1"';

    $result = '';
    $args = func_get_args();

    foreach ($args as $idx => $arg) {

        $args[$idx] = X_DEF_OS_WINDOWS
            ? preg_replace($win_s, $win_r, $arg)
            : escapeshellarg($arg);

    }

    return implode(' ', $args);
}

/**
 * This function checks the user passwords with default values
 */
function func_check_default_passwords($userid = 0)
{
    global $sql_tbl, $active_modules;

    if (!empty($active_modules['Demo_Mode'])) {
        return array();
    }

    x_load('crypt','user');

    $default_accounts = array();

    $default_accounts['P'] = array(
        'provider',
        'master',
        'root',
    );

    if (empty($active_modules['Simple_Mode']))
        $default_accounts['A'] = array('admin');

    $return = array();

    if ($userid > 0) {

        // Check password security for specified user name

        $account = func_query_first("SELECT login, password FROM " . $sql_tbl['customers'] . " WHERE id='" . $userid . "'");

        if (
            is_array($account)
            && text_verify($account['login'], text_decrypt($account['password']))
        ) {
            $return[] = $account['login'];
        }

    } else {

        // Check password security for all default user names

        foreach ($default_accounts as $usertype => $accounts) {

            foreach ($accounts as $login_) {

                $account = func_query_first("SELECT login, password FROM " . $sql_tbl['customers'] . " WHERE login='" . $login_ . "' AND usertype='" . $usertype . "'");

                if (empty($account) || !is_array($account))
                    continue;

                if (text_verify($account['login'], text_decrypt($account['password']))) {

                    $return[] = $account['login'];

                }

            }

        }

    }

    return $return;
}

/**
 * Smarty->display wrapper
 */
function func_display($tpl, &$templater, $to_display = true, $is_intermediate = false) { // {{{
    global $config;
    global $predefined_lng_variables, $override_lng_code, $shop_language, $user_agent, $__smarty_time, $__smarty_size;
    global $xcart_dir, $xcart_web_dir;
    global $__X_LNG, $login;
    global $current_area, $xcart_catalogs, $xcart_catalogs_secure, $XCART_SESSION_NAME, $XCARTSESSID, $active_modules, $https_location, $current_location;
    global $HTTPS;
    global $all_languages;

    x_session_register('stored_navigation_script');
    global $stored_navigation_script;

    if (
        defined('STORE_NAVIGATION_SCRIPT')
        && 'Y' === constant('STORE_NAVIGATION_SCRIPT')
    ) {
        $_navigation_script = $templater->getTemplateVars('navigation_script');
        $stored_navigation_script = isset($_navigation_script) ? $_navigation_script : 'home.php';
    }

    $templater->assign('stored_navigation_script', $xcart_web_dir . '/' . (!empty($stored_navigation_script) ? $stored_navigation_script : 'home.php'));

    if ($to_display && !$is_intermediate) {

        if (function_exists('x_session_save')) {
            x_session_save();
            x_session_reset();
            func_define('XC_DISABLE_SESSION_SAVE', true);
        }
    }

    static $formid_generated = false;

    if (XCSecurity::FRAME_NOT_ALLOWED) {
        $templater->assign('__frame_not_allowed', true);
    }

    if (
        defined('AREA_TYPE')
        && !empty($login)
        && in_array(constant('AREA_TYPE'), array('A', 'P'))
        && function_exists('func_generate_formid')
        && !$formid_generated
    ) {
        // Generate new form id
        $templater->register_outputfilter('func_substitute_formid');
        $formid_generated = true;
    }

    if (
        isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
    ) {
        $templater->register_outputfilter('func_ajax_convert');
    }

    x_load('templater');

    if (ini_get('pcre.backtrack_limit') < 1000000) {
        assert('FALSE /* '.__FUNCTION__.': your pcre.backtrack_limit is extremly low*/');
        ini_set('pcre.backtrack_limit', 1000000);
    }

    // Correct $location line for proper truncating according to option $config.SEO.page_title_limit and strip_tags usage
    global $location;

    if (!empty($location) && is_array($location)) {
        foreach ($location as $k => $v) {
            if (preg_match("/~~~~\|(.+)\|~~~~/S", $v[0], $match)) {
                $location[$k][0] = func_convert_lang_var($v[0], $templater);
            }
        }
        // Dot not assign location for A zone when breadcrumbs is not assigned
        if (
            func_constant('AREA_TYPE') === 'C'
            || $templater->getTemplateVars('location')
        ) {
            $templater->assign('location', $location);
        }
    }

    if (func_constant('AREA_TYPE') === 'C') {

        $_canonical_url = $templater->getTemplateVars('canonical_url');
        if (!isset($_canonical_url)) {
            $templater->assign('canonical_url', func_get_canonical($templater));
        }

        // Force convert top message text to avoid JS errors if it is multiline
        $_top_msg = $templater->getTemplateVars('top_message');
        if (!empty($_top_msg['content']) && preg_match('/~~~~\|(.+)\|~~~~/S', $_top_msg['content'])) {
            $_top_msg['content'] = func_convert_lang_var($_top_msg['content'], $templater);
            $templater->assign('top_message', $_top_msg);
        }
        unset($_top_msg);

    }

    __add_mark_smarty();

    $lng_code = $override_lng_code
        ? $override_lng_code
        : $shop_language;

    if (
        $config['General']['use_cached_lng_vars'] == 'Y'
        && !$templater->webmaster_mode
        && (
            count($all_languages) == 1
            || defined('X_MYSQL5_COMP_MODE')
        )
    ) {

        if (empty($lng_code)) {
            // WA for scripts which does not load include/get_language.php (like payment/cc_xpc_iframe.php?xpc_action=xpc_popup_show_message)
            $_language_code = empty($current_area) || $current_area == 'C'
                ? $config['default_customer_language']
                : $config['default_admin_language'];
        } else {
            $_language_code = $lng_code;
        }

        $all_cached_lng_variables = func_get_all_language_vars($_language_code);
        $templater->assignByRef('lng', $all_cached_lng_variables);
        $__X_LNG[$shop_language] = $all_cached_lng_variables;
    } else {
        if (!empty($predefined_lng_variables)) {

            $predefined_lng_variables = array_flip($predefined_lng_variables);
            $predefined_vars = array();

            func_get_lang_vars_extra($lng_code, $predefined_lng_variables, $predefined_vars);

            if (!empty($templater->webmaster_mode)) {
                $result = func_webmaster_convert_labels($predefined_vars);
            }

            $_all_lng = func_array_merge($templater->getTemplateVars('lng'), $predefined_vars);
            $templater->assignByRef('lng', $_all_lng);

            $__X_LNG[$shop_language] = isset($__X_LNG[$shop_language])
                ? func_array_merge($__X_LNG[$shop_language], $predefined_vars)
                : $predefined_vars;

            unset($predefined_vars, $predefined_lng_variables);
        }

        $templater->register_postfilter('func_tpl_add_hash');
    }

    if (
        isset($templater->webmaster_mode)
        && $templater->webmaster_mode
    ) {
        if (empty($templater->compile_id_based_on_filters)) {
            $templater->force_compile = true;
        }

        $templater->register_postfilter('func_webmaster_filter');
        $templater->register_outputfilter('func_tpl_webmaster');
    }

    if (
        $config['General']['use_cached_lng_vars'] != 'Y'
        || $templater->webmaster_mode
    ) {
        $templater->register_outputfilter('func_convert_lang_var');
    }

    if ($config['General']['skip_delete_empty_strings'] != 'Y') {
        $templater->register_postfilter('func_postprocess_output');
    }

    // Remove blank lines related to load_defer plugin in speed CSS/JS mode
    $templater->register_prefilter('func_tpl_pre_minify');

    if (func_constant('AREA_TYPE') == 'C') {
        if ($config['SEO']['clean_urls_enabled'] == 'Y')
            $templater->register_outputfilter('func_clean_url_filter_output');

        if ($config['General']['use_cached_templates'] != 'Y')
            $templater->register_prefilter('func_tpl_remove_include_cache');
    }

    if (defined('DEVELOPMENT_MODE'))
        $templater->register_prefilter('func_tpl_warn_about_deprecated');

    // Printable version
    $_main = $templater->getTemplateVars('main');
    if (
        $config['Appearance']['enabled_printable_version'] == 'Y'
        && !empty($_main)
        && (
            (
                $_main == 'catalog'
                && $templater->getTemplateVars('cat')
            )
            || $_main == 'product'
            || $_main == 'pages'
            || (
                $_main == 'comparison'
                && $templater->getTemplateVars('mode') == 'compare_table'
            )
            || (
                $_main == 'choosing'
                && @$_GET['mode'] == 'choose'
            )
        )
    ) {
        if (
            isset($_GET['printable'])
            && $_GET['printable'] == 'Y'
            && $tpl == 'customer/home.tpl'
        ) {
            $templater->assign('printable', true);
            $tpl = 'customer/home_printable.tpl';
        } else {
            // Strip tags from query_string for `printable version` link bt:#94700
            global $php_url;

            $_php_url = $php_url;
            $_php_url['query_string'] = func_qs_remove(strip_tags(urldecode($_php_url['query_string'])) , 'printable');
            $templater->assign('php_url', $_php_url);
            $templater->assign('printable_link_visible', true);
        }
    }

    // Greet visitor module
    if (!empty($active_modules['Greet_Visitor']) && !empty($_COOKIE['GreetingCookie'])) {
        $templater->assign('display_greet_visitor_name', func_htmlspecialchars(stripslashes($_COOKIE['GreetingCookie'])));
    }

    // CSS additional files
    global $css_files;

    if (!empty($css_files) && is_array($css_files)) {
        $templater->assignByRef('css_files', $css_files);
    }

    // CSS additional rules
    global $custom_styles;

    if (!empty($custom_styles) && is_array($custom_styles)) {
        $templater->assignByRef('custom_styles', $custom_styles);
    }

    // Template filename to load if no entry present in common_templates.tpl
    // @example
    // $template_main['sitemap_customer'] = 'modules/Sitemap/customer.tpl';
    // $smarty->assign('main', 'sitemap_customer');
    global $template_main;

    if (!empty($template_main) && is_array($template_main)) {
        $templater->assign('template_main', $template_main);
    }

    global $container_classes;

    // Additional container classes
    if (!empty($container_classes) && is_array($container_classes)) {
        $templater->assignByRef('container_classes', $container_classes);
    }

    // Auth box data
    $slogin_url_add = $slogin_url = '';

    $login_url  = func_get_area_catalog($current_area);
    $slogin_url = func_get_area_catalog($current_area, true);

    if ($current_area == 'C' && $login_url != $slogin_url) {
        $slogin_url_add = '?' . $XCART_SESSION_NAME . '=' . $XCARTSESSID;
    }

    $templater->assign('slogin_url', $slogin_url . '/login.php' . $slogin_url_add);
    $templater->assign('login_url', $login_url . '/login.php');

    $templater->assign(
        'authform_url',
        'Y' === $config['Security']['use_https_login']
            ? $slogin_url . '/login.php' . $slogin_url_add
            : $login_url . '/login.php'
    );

    $templater->assign('auth_usertype', func_get_auth_usertype());
    $templater->assign('is_https_zone', $HTTPS);

    // Session unknown id warning
    if (defined('X_ERR_UNKNOWN_SESSION_ID')) {
        global $xcart_http_host, $xcart_https_host, $HTTPS;

        $host = $HTTPS
            ? $xcart_https_host
            : $xcart_http_host;

        $tmp = array_reverse(explode('.', $host));
        $host_domains = array();

        foreach ($tmp as $k) {
            $tmp2 = array_fill(0, count($tmp), $k);
            foreach ($tmp2 as $i => $v) {
                $host_domains[$i] = '.' . $v . $host_domains[$i];
            }

            array_pop($tmp);
        }

        foreach ($host_domains as $k => $v) {
            $host_domains[$k] = substr($v, 1);
        }

        $templater->assign(
            'top_message',
            array(
                'type'          => 'W',
                'content'       => func_get_langvar_by_name(
                    'txt_session_unknown_id_warning',
                    array(
                        'hosts' => "'" . implode("', '", $host_domains) . "'",
                    )
                ),
            )
        );
    }

    if ($to_display == true) {
        // AJAX finalize
        if (func_is_ajax_request()) {
            $templater->register_outputfilter('func_ajax_templater_finalize');
            $templater->assign('top_message', '');
        }

        $templater->compile_id = $templater->getCompileId();
        $templater->display($tpl);
        $ret = '';
    } else {
        $templater->compile_id = $templater->getCompileId();
        $ret = $templater->fetch($tpl);
    }

    __add_mark_smarty($tpl);

    if ($to_display == true) {
        // Display page content
        func_flush();

        if (AREA_TYPE == 'C') {
            // Call x_session_reset to clear all custom sessions vars before this code
            if (function_exists('x_session_reset')) {
                x_session_reset();
            }
        }
    }

    return $ret;
} // }}}

/**
 * Function for detecting the effective usertype in different X-Cart packages
 */
function func_get_auth_usertype() { // {{{
    global $shop_type, $active_modules, $current_area;

    return $current_area == 'A'
        && (
            in_array($shop_type, array('PRO', 'PLATINUM'))
            && !empty($active_modules['Simple_Mode'])
            || in_array($shop_type, array('GOLD', 'GOLDPLUS'))
        )
        ? 'P'
        : $current_area;
} // }}}

/**
 * Function for fetching language variables values for one code
 */
function func_get_lang_vars($code, &$variables, &$lng)
{
    global $sql_tbl, $memcache;

    if ($memcache) {

        $lng_vars = array();

        foreach ($variables as $key => $val) {

            $lng_vars['inner_lng_' . $code . $key] = $key;

        }

        $data = func_get_mcache_data(array_keys($lng_vars));

        if (
            false !== $data
            && !empty($data)
        ) {

            foreach ($data as $key => $value) {

                $lng[$lng_vars[$key]] = $value;
                unset($variables[$lng_vars[$key]]);

            }

        }

    }

    if (!empty($variables)) {

        $labels = db_query("SELECT name, value FROM " . $sql_tbl['languages'] . " WHERE code = '" . $code . "' AND name IN ('" . implode("','", array_keys($variables)) . "')");

        if ($labels) {

            while ($v = db_fetch_array($labels)) {

                $lng[$v['name']] = $v['value'];
                unset($variables[$v['name']]);

                if ($memcache) {

                    func_store_mcache_data('inner_lng_' . $code . $v['name'], $v['value']);

                }

            }

            db_free_result($labels);

        }

    }

}

/**
 * Extra version of func_get_lang_vars(): try to fetch values of language variables
 * using all possible language codes
 */
function func_get_lang_vars_extra($prefered_lng_code, &$variables, &$lng)
{
    global $current_area, $config;

    if (empty($variables))
        return;

    func_get_lang_vars($prefered_lng_code, $variables, $lng);

    if (empty($variables))
        return;

    $default_language = $current_area == 'C'
        ? $config['default_customer_language']
        : $config['default_admin_language'];

    if ($default_language != $prefered_lng_code) {

        func_get_lang_vars($default_language, $variables, $lng);

        if (empty($variables))
            return;
    }

    if ($default_language != 'en') {
        func_get_lang_vars('en', $variables, $lng);
    }
}

/**
 * Check CC processor's transaction type
 */
function func_check_cc_trans($module_name, $type, $hash = array())
{
    global $sql_tbl;

    $return = false;
    if (empty($hash) && is_array($hash)) {
        $hash = array(
            'P' => 'P',
            'C' => 'C',
            'R' => 'R',
        );
    }

    if (empty($type)) {

        $type = 'P';

    }

    if ($type == 'P') {

        $return = $hash[$type];

    } elseif ($type == 'C') {

        if (func_query_first_cell("SELECT is_check FROM " . $sql_tbl['ccprocessors'] . " WHERE module_name = '" . $module_name . "'"))
            $return = $hash[$type];

    } elseif ($type == 'R') {

        if (func_query_first_cell("SELECT is_refund FROM " . $sql_tbl['ccprocessors'] . " WHERE module_name = '" . $module_name . "'"))
            $return = $hash[$type];

    } elseif (isset($hash[$type])) {

        $return = $hash[$type];

    }

    if (empty($return) && $return !== false)
        $return = false;

    return $return;
}

// Parse string to hash array like:
// x=1|y=2|z=3
// where:
//    str     = x=1|y=2|z=3
//    delim     = |
// convert to:
// array('x' => 1, 'y' => 2, 'z' => 3)

function func_parse_str($str, $delim = '&', $pair_delim = '=', $value_filter = false)
{
    if (empty($str))
        return array();

    $arr = explode($delim, $str);
    $return = array();

    for ($x = 0; $x < count($arr); $x++) {

        $pos = strpos($arr[$x], $pair_delim);

        if ($pos === false) {

            $return[$arr[$x]] = false;

        } elseif ($pos >= 0) {

            $v = substr($arr[$x], $pos+1);

            if (!empty($value_filter))
                $v = $value_filter($v);

            $return[substr($arr[$x], 0, $pos)] = $v;

        }

    }

    return $return;
}

/**
 * Remove parameters from QUERY_STRING by name
 */
function func_qs_remove($qs)
{
    if (func_num_args() <= 1)
        return $qs;

    $args = func_get_args();
    array_shift($args);

    if (count($args) == 0 || (strpos($qs, "=") === false && strpos($qs, "?") === false))
        return $qs;

    // Get scheme://domain/path part
    if (strpos($qs, '?') !== false)
        list($main, $qs) = explode("?", $qs, 2);

    // Get #hash part
    if (strrpos($qs, "#") !== false) {
        $hash = substr($qs, strrpos($qs, "#") + 1);
        $qs = substr($qs, 0, strrpos($qs, "#"));
    }

    // Parse query string
    $arr = func_parse_str($qs);

    // Filter query string
    foreach ($args as $param_name) {

        if (empty($param_name) || !is_string($param_name))
            continue;

        $reg = "/^" . preg_quote($param_name, '/') . "(\[[^\]]*\])*(\Z|=)/S";

        foreach ($arr as $ak => $av) {
            if (preg_match($reg, $ak) || empty($ak)) {
                unset($arr[$ak]);
                break;
            }
        }
    }

    // Assembly return string
    foreach ($arr as $ak => $av) {
        $arr[$ak] = $ak . "=" . $av;
    }

    $qs = implode("&", $arr);

    if (isset($main))
        $qs = $main . (empty($qs) ? '' : ("?" . $qs));

    if (isset($hash))
        $qs .= "#" . $hash;

    return $qs;
}

function func_qs_combine($arr, $qappend = true)
{
    if (empty($arr) || !is_array($arr)) {
        return '';
    }

    $qs = array();
    foreach ($arr as $k => $v) {
        $qs[] = urlencode($k) . '=' . urlencode($v);
    }

    return ($qappend ? '?' : '') . join("&amp;", $qs);
}

/**
 * Serialize data to URL query string
 */
function func_data2url_query($varname, $data, $urlencode = false)
{
    if (is_object($data) || is_resource($data))
        return false;

    if (is_array($data)) {

        $str = array();

        foreach($data as $k => $v) {
            $str[] = func_data2url_query($varname . "[" . $k . "]", $v, $urlencode);
        }

        return implode("&", $str);

    } elseif (is_bool($data) || is_null($data)) {

        return $varname . "=" . ($data ? 1 : 0);

    }

    return $varname . "=" . ($urlencode ? urlencode($data) : $data);
}

/**
 * Return all built-in modules
 */
function func_get_builtin_modules()
{
    global $active_modules, $sql_tbl;

    $built_in_modules = func_query_column("SELECT module_name FROM $sql_tbl[modules] WHERE author='qualiteam' ORDER BY module_name");

    if (!empty($active_modules['Simple_Mode']))
        $built_in_modules[] = 'Simple_Mode';

    return $built_in_modules;
}

/**
 * Get default field's name
 */
function func_get_default_field($name)
{
    $prefix = substr($name, 0, 2);

    if ($prefix == 's_' || $prefix == 'b_') {
        $name = substr($name, 2);
    }

    $name = str_replace(
        array(
            'firstname',
            'lastname',
            'zipcode',
        ),
        array(
            'first_name',
            'last_name',
            'zip_code',
        ),
        $name
    );

    if ($name == 'zip4') {
        return '';
    }

    return func_get_langvar_by_name('lbl_' . $name, false, false, true);
}

/**
 * Get memberships list
 */
function func_get_memberships($area = 'C', $as_hash = false)
{
    global $sql_tbl, $shop_language;

    $query_string = sprintf(
        'SELECT %1$s.membershipid, IFNULL(%2$s.membership, %1$s.membership) as membership FROM %1$s LEFT JOIN %2$s ON %1$s.membershipid = %2$s.membershipid AND %2$s.code = \'%3$s\' WHERE %1$s.active = \'Y\' AND %1$s.area = \'%4$s\' ORDER BY %1$s.orderby',
        $sql_tbl['memberships'],
        $sql_tbl['memberships_lng'],
         $shop_language,
        $area
    );

    return $as_hash
        ? func_query_hash($query_string, 'membershipid', false)
        : func_query($query_string);
}

/**
 * Detect membershipid by membership name
 */
function func_detect_membership($membership = '', $type = false)
{
    global $sql_tbl;

    if (empty($membership))
        return 0;

    $where = '';

    if ($type != false)
        $where = " AND area = '" . $type . "'";

    $membership = addslashes($membership);

    $id = func_query_first_cell("SELECT membershipid FROM " . $sql_tbl['memberships'] . " WHERE membership = '" . $membership . "'" . $where);

    return $id ? $id : 0;
}

/**
 * The function is merging arrays by keys
 * Ex.:
 * array(5 => 'y') = func_array_merge_assoc(array(5 => 'x'), array(5 => 'y'));
 */
function func_array_merge_assoc()
{
    if (!func_num_args())
        return array();

    $args = func_get_args();

    $result = array();

    foreach ($args as $val) {

        if (!is_array($val) || empty($val))
            continue;

        foreach ($val as $k => $v)
            $result[$k] = $v;
    }

    return $result;
}

function func_membership_update($type, $id, $membershipids, $field = false)
{
    global $sql_tbl;

    $tbl = $sql_tbl[$type . '_memberships'];

    if (empty($tbl) || empty($id))
        return false;

    if ($field === false)
        $field = $type . 'id';

    db_query("DELETE FROM " . $tbl . " WHERE " . $field . " = '" . $id . "'");

    if (!empty($membershipids)) {

        if (!in_array(-1, $membershipids)) {

            foreach ($membershipids as $v) {

                db_query("INSERT INTO " . $tbl . " VALUES ('" . $id . "','" . $v . "')");

            }

        }

    }

    return true;
}

function func_get_titles($active = true)
{
    global $sql_tbl;

    $active_condition = $active ? "WHERE active = 'Y'" : "";

    $titles = func_query("SELECT * FROM " . $sql_tbl['titles'] . " " . $active_condition . " ORDER BY orderby, title");

    if (!empty($titles)) {

        foreach ($titles as $k => $v) {

            $name = func_get_languages_alt('title_' . $v['titleid']);

            $titles[$k]['title_orig'] = $v['title'];

            if (!empty($name)) {
                $titles[$k]['title'] = $name;
            }

        }

    }

    return $titles;
}

function func_detect_title($title,$language = NULL)
{
    global $sql_tbl,$config;

    if (empty($title))
        return false;

    $titleid = func_query_first_cell("SELECT titleid FROM " . $sql_tbl['titles'] . " WHERE title = '" . $title . "'");

    if (!$titleid) {

    // Detect multi language title

        $full_text = func_query_first_cell($sql = "SELECT name FROM " . $sql_tbl['languages_alt'] . " WHERE value = '" . $title . "' " . ($language ? "AND code = '" . $language . "'" : "") . " AND name LIKE 'title_%'");

        if ($full_text)
            $titleid = substr($full_text, 6, strlen($full_text) - 6);

    }

    return $titleid;
}

function func_get_title($titleid, $code = false)
{
    global $sql_tbl, $shop_language;

    if (empty($titleid))
        return false;

    $title = func_get_languages_alt('title_' . $titleid, $code);

    if (empty($title)) {
        $title = func_query_first_cell("SELECT title FROM " . $sql_tbl['titles'] . " WHERE titleid = '" . $titleid . "'");
    }

    return $title;
}

/**
 * Detect price
 */
function func_is_price($price, $cur_symbol = '$', $cur_symbol_left = true)
{
    if (is_numeric($price))
        return true;

    $price = trim($price);

    $cur_symbol = preg_quote($cur_symbol, '/');

    if ($cur_symbol_left) {
        $price = preg_replace("/^" . $cur_symbol . '/S', '', $price);
    } else {
        $price = preg_replace('/' . $cur_symbol . "$/S", '', $price);
    }

    return func_is_numeric($price);
}

/**
 * Convert price
 */
function func_detect_price($price, $cur_symbol = '$', $cur_symbol_left = true)
{
    if (!is_numeric($price)) {

        $price = trim($price);

        $cur_symbol = preg_quote($cur_symbol, '/');

        if ($cur_symbol_left) {
            $price = preg_replace("/^" . $cur_symbol . '/S', '', $price);
        } else {
            $price = preg_replace('/' . $cur_symbol . "$/S", '', $price);
        }

        $price = func_convert_number($price);

    }

    return doubleval($price);
}

/**
 * Detect number
 */
function func_is_numeric($var, $from = NULL)
{
    global $config;

    if (!in_array(gettype($var), array('string', 'integer', 'double')))
        return false;

    if (!is_numeric($var)) {

        if (strlen($var) == 0)
            return false;

        if (empty($from))
            $from = $config['Appearance']['number_format'];

        if (empty($from))
            $from = "2.,";

        $var = str_replace(" ", '', str_replace(substr($from, 1, 1), '.', str_replace(substr($from, 2, 1), '', $var)));

        if (!is_numeric($var))
            return false;

    } else {

        return true;

    }

    return is_numeric($var);
}

/**
 * Convert local number format to inner number format
 */
function func_convert_number($var, $from = NULL)
{
    global $config;

    if (strlen(@$var) == 0)
        return $var;

    if (empty($from))
        $from = $config['Appearance']['number_format'];

    if (empty($from))
        $from = "2.,";

    return round(func_convert_numeric($var, $from), intval(substr($from, 0, 1)));
}

/**
 * Convert local number format (without precision) to inner number format
 */
function func_convert_numeric($var, $from = NULL)
{
    global $config;

    if (is_int($var) || is_double($var))
        return $var;

    if (!is_string($var) || strlen($var) == 0)
        return 0;

    $var = trim($var);

    if (preg_match("/^\d+$/S", $var))
        return intval($var);

    if (empty($from))
        $from = $config['Appearance']['number_format'];

    if (empty($from))
        $from = "2.,";

    return doubleval(str_replace(" ", '', str_replace(substr($from, 1, 1), '.', str_replace(substr($from, 2, 1), '', $var))));
}

/**
 * Format price according to 'Input and display format for floating comma numbers' option
 */
function func_format_number($price, $thousand_delim = NULL, $decimal_delim = NULL, $precision = NULL)
{
    global $config;

    if (strlen(@$price) == 0)
        return $price;

    $format = $config['Appearance']['number_format'];

    if (empty($format)) $format = "2.,";

    if (is_null($thousand_delim) || $thousand_delim === false)
        $thousand_delim = substr($format, 2, 1);

    if (is_null($decimal_delim) || $decimal_delim === false)
        $decimal_delim = substr($format, 1, 1);

    if (is_null($precision) || $precision === false)
        $precision = intval(substr($format, 0, 1));

    return number_format(round((double)$price + 0.00000000001, $precision), $precision, $decimal_delim, $thousand_delim);
}

/**
 * Convert string to use in custom javascript code
 */
function func_js_escape($string)
{
    return strtr(
        $string,
        array(
            '\\' => '\\\\',
            "'"  => "\\'",
            '"'  => '\\"',
            "\r" => '\\r',
            "\n" => '\\n',
            '</' => '<\/',
        )
    );
}

/**
 * Generate product flags (stored in statis service array - xcart_quick_flags table)
 * work for all/selected products
 */
function func_build_quick_flags($id = false, $tick = 0)
{
    global $sql_tbl, $active_modules;

    x_load_module('Product_Options'); // For XCVariants*
    XCVariantsChange::repairIntegrity($id);

    $where = '';

    if ($id !== false && !is_array($id)) {

        $where = " WHERE " . $sql_tbl['products'] . ".productid = '" . $id . "'";

        db_query("DELETE FROM " . $sql_tbl['quick_flags'] . " WHERE productid = '" . $id . "'");

        x_load('product');
        XCProductSalesStats::repairIntegrity($id);

    } elseif (is_array($id) && !empty($id)) {

        $idStr = "productid IN ('" . implode("','", $id) . "')";

        $where = " WHERE " . $sql_tbl['products'] . "." . $idStr;

        db_query("DELETE FROM " . $sql_tbl['quick_flags'] . " WHERE " . $idStr);

    } else {

        db_query("DELETE FROM " . $sql_tbl['quick_flags']);
        func_set_time_limit(SECONDS_PER_DAY);

    }

    if ($tick > 0)
        func_display_service_header('lbl_rebuild_quick_flags');

    $image_fields = $sql_tbl['images_T'] . ".image_path AS image_path_T";

    if (empty($active_modules['Product_Options'])) {

        $sd = db_query("SELECT $sql_tbl[products].productid, '' AS is_variants, '' AS is_product_options, IF($sql_tbl[product_taxes].productid IS NULL, '', 'Y') AS is_taxes, $image_fields  FROM $sql_tbl[products] LEFT JOIN $sql_tbl[product_taxes] ON $sql_tbl[product_taxes].productid = $sql_tbl[products].productid LEFT JOIN $sql_tbl[images_T] ON $sql_tbl[images_T].id = $sql_tbl[products].productid $where GROUP BY $sql_tbl[products].productid ORDER BY NULL");

    } else {

        $sd = db_query("SELECT $sql_tbl[products].productid, " . XCVariantsSQL::isHaveVariants('is_variants') . ", IF($sql_tbl[classes].productid IS NULL, '', 'Y') AS is_product_options, IF($sql_tbl[product_taxes].productid IS NULL, '', 'Y') AS is_taxes, $image_fields FROM $sql_tbl[products] ".XCVariantsSQL::getJoinQueryAllRows()." LEFT JOIN $sql_tbl[classes] ON $sql_tbl[classes].productid = $sql_tbl[products].productid LEFT JOIN $sql_tbl[product_taxes] ON $sql_tbl[product_taxes].productid = $sql_tbl[products].productid LEFT JOIN $sql_tbl[images_T] ON $sql_tbl[images_T].id = $sql_tbl[products].productid $where GROUP BY $sql_tbl[products].productid ORDER BY NULL");

    }

    $updated = 0;

    if ($sd) {

        while ($row = db_fetch_array($sd)) {

            func_array2insert('quick_flags', func_addslashes($row), true);

            $updated ++;

            if ($tick > 0 && $updated % $tick == 0) {

                echo ". ";

                if (($updated/$tick) % 100 == 0)
                    echo "\n";

                func_flush();

            }

        }

        db_free_result($sd);
    }

    return $updated;
}

/**
 * Generate matrix: MIN(product price) x membershipid (stored in statis service array - xcart_quick_prices table)
 * (with variantid)
 * work for all/selected products
 */
function func_build_quick_prices($id = false, $tick = 0)
{
    global $sql_tbl, $config, $active_modules;

    x_load_module('Product_Options'); // For XCVariants*
    XCVariantsChange::repairIntegrity($id);

    // Define product condition
    $where_products = $where_pricing = '';

    if ($id !== false && !is_array($id)) {

        $where_products = " AND $sql_tbl[products].productid = '$id'";

        db_query("DELETE FROM $sql_tbl[quick_prices] WHERE productid = '$id'");

        x_load('product');
        XCProductSalesStats::repairIntegrity($id);

    } elseif (is_array($id) && !empty($id)) {

        $where_products = " AND $sql_tbl[products].productid IN ('".implode("','", $id)."')";

        db_query("DELETE FROM $sql_tbl[quick_prices] WHERE productid IN ('".implode("','", $id)."')");

    } else {

        db_query("DELETE FROM $sql_tbl[quick_prices]");
        func_set_time_limit(SECONDS_PER_DAY);

    }
    $where_pricing = str_replace($sql_tbl['products'], $sql_tbl['pricing'], $where_products);

    if ($tick > 0)
        func_display_service_header('lbl_rebuild_quick_prices');

    // Get common data
    $res = db_query("SELECT $sql_tbl[pricing].productid, MIN(CONCAT($sql_tbl[pricing].price,'/',$sql_tbl[pricing].membershipid, '/', $sql_tbl[pricing].priceid)) as priceid, $sql_tbl[pricing].membershipid, '0' AS variantid FROM $sql_tbl[pricing] WHERE ".XCVariantsSQL::isProductPrice()." AND $sql_tbl[pricing].quantity = '1' $where_pricing GROUP BY $sql_tbl[pricing].productid, $sql_tbl[pricing].membershipid ORDER BY NULL");

    if ($res) {

        $i = 0;

        while ($arr = db_fetch_array($res)) {

            $i++;

            list($tmp1, $arr['membershipid'], $arr['priceid']) = explode("/", $arr['priceid'], 3);

            $arr['variantid'] = intval($arr['variantid']);
            func_array2insert('quick_prices', func_addslashes($arr), true);

            if ($tick > 0 && $i % $tick == 0) {

                echo ". ";

                if (($i/$tick) % 100 == 0)
                    echo "\n";

                func_flush();

            }

        }

        db_free_result($res);
    }

    if (empty($active_modules['Product_Options']))
        return $i;

    // Get Variants' prices
    $res = db_query("SELECT DISTINCT $sql_tbl[products].productid FROM $sql_tbl[products] ".XCVariantsSQL::getJoinQueryVariants()." WHERE 1 $where_products ");

    if (!$res)
        return $i;

    while ($arr = db_fetch_array($res)) {

        $productid = $arr['productid'];
        $variantid = func_get_default_variantid($productid, true);

        if (empty($variantid))
            continue;

        $prices = func_query_hash("SELECT membershipid, priceid FROM $sql_tbl[pricing] WHERE variantid = '$variantid' AND quantity = '1' ORDER BY price", "membershipid", false, true);

        if (empty($prices))
            continue;

        foreach ($prices as $mid => $priceid) {

            $i++;

            $query_data = array(
                'productid'     => $productid,
                'priceid'       => $priceid,
                'membershipid'  => $mid,
                'variantid'     => $variantid,
            );

            func_array2insert('quick_prices', $query_data, true);

            if ($tick > 0 && $i % $tick == 0) {

                func_flush(". ");

            }

        }

    }

    db_free_result($res);

    return $i;
}

/**
 * Get data cache content and regenerate cache file on demand
 */
function func_data_cache_get($name, $params = array(), $force_rebuild = false) { // {{{
    global $data_caches, $var_dirs, $xcart_dir;

    require_once "$xcart_dir/include/data_cache.php";

    if (
        !isset($data_caches[$name])
        || empty($data_caches[$name]['func'])
        || !function_exists($data_caches[$name]['func'])
        || !empty($data_caches[$name]['use_func_cache_logic'])
    ) {
        return FALSE;
    }

    global $memcache;

    if ($memcache) {
        if (TRUE === $force_rebuild) {
            func_flush_mcache();
        }

        return func_get_mcache($name, $params);
    }

    $no_save = defined('BLOCK_DATA_CACHE_' . strtoupper($name));
    if (
        !defined('USE_DATA_CACHE')
        || !constant('USE_DATA_CACHE')
        || $no_save
    ) {
        return call_user_func_array($data_caches[$name]['func'], $params);
    }

    $cache_key = '';

    if (
        !empty($params)
        || isset($data_caches[$name]['include_global_keys'])
        || isset($data_caches[$name]['include_constants'])
    ) {

        $keys = $params;

        if (
                isset($data_caches[$name]['exclude_keys'])
                && is_array($data_caches[$name]['exclude_keys'])
           ) {
            foreach ($data_caches[$name]['exclude_keys'] as $k) {
                func_unset($keys, $k);
            }
        }

        if (
                isset($data_caches[$name]['include_global_keys'])
                && is_array($data_caches[$name]['include_global_keys'])
           ) {
            foreach ($data_caches[$name]['include_global_keys'] as $k) {
                if (isset($GLOBALS[$k]))
                    $keys[$k] = $GLOBALS[$k];

            }
        }

        if (
                isset($data_caches[$name]['include_constants'])
                && is_array($data_caches[$name]['include_constants'])
           ) {
            foreach ($data_caches[$name]['include_constants'] as $k) {
                if (defined($k))
                    $keys[$k] = constant($k);

            }
        }

        foreach($keys as $_key) {
            if (is_scalar($_key) && $_key !== '')
                $cache_key .= '.' . $_key;
            elseif(is_array($_key))    
                $cache_key .= '.' . md5(serialize($_key));
        }
    }

    $data = FALSE;

    if (empty($data_caches[$name]['has_private_data'])) {
        require_once "$xcart_dir/include/classes/class.xc_cache_lite.php";
        $cache_lite = XC_Cache_Lite::get_instance();
        $cache_lite->setLifeTime( empty($data_caches[$name]['ttl']) ? 0  : $data_caches[$name]['ttl'] );
        $cache_lite->setCacheDir( empty($data_caches[$name]['dir']) ? '' : $data_caches[$name]['dir'] );
        $cache_lite->setFileNameProtection(!empty($data_caches[$name]['fileNameProtection']));
        $cache_lite->setHashedDirectoryLevel(
            empty($data_caches[$name]['hashedDirectoryLevel']) ? 0 : $data_caches[$name]['hashedDirectoryLevel']
        );

        if (!$force_rebuild) {
            $data = $cache_lite->get($cache_key, $name);
            if ($data) {
                // Cache is valid
                return $data['data'];
            }
        }

        if (!$data) {
            $data = call_user_func_array($data_caches[$name]['func'], $params);
            if (func_validate_cache_func($name, $data)) {
                $cache_lite->save($data, $cache_key, $name);
            }
        }

        return $data;
    } else {
        $path = $var_dirs['cache'] . '/' . $name . $cache_key . '.php';
        if (
            file_exists($path)
            && !$force_rebuild
            && (
                !isset($data_caches[$name]['ttl'])
                || $data_caches[$name]['ttl'] <= 0
                || (
                    filemtime($path) + $data_caches[$name]['ttl'] > XC_TIME
                )
            )
        ) {

            if (!include($path))
                return FALSE;

            return $$name;

        } else {

            $data = call_user_func_array($data_caches[$name]['func'], $params);
            if (TRUE
                && is_writable($var_dirs['cache'])
                && is_dir($var_dirs['cache'])
                && func_validate_cache_func($name, $data)
            ) {
                $fp = fopen($path, 'w');
                $is_unlink = FALSE;
                if ($fp) {
                    if (!fwrite($fp, "<?php\nif (!defined('XCART_START')) { header('Location: ../../'); die('Access denied'); }\n"))
                        $is_unlink = TRUE;
                    if (!$is_unlink && !func_data_cache_write($fp, '$' . $name, $data))
                        $is_unlink = TRUE;
                    fclose($fp);
                    func_chmod_file($path);
                }

                if ($is_unlink) {
                    unlink($path);
                }
            }

            return $data;
        }
    }

    return '';
} // }}}

/**
 * Clear data cache
 */
function func_data_cache_clear($name = FALSE) { // {{{
    global $data_caches, $xcart_dir;

    if ($name !== FALSE) {
        $func_exists = isset($data_caches[$name]['class'])
            ? method_exists($data_caches[$name]['class'], $name)
            : function_exists($data_caches[$name]['func']);

        if (
            !isset($data_caches[$name])
            || empty($data_caches[$name]['func'])
            || !$func_exists
        ) {
            return FALSE;
        }
    }

    if (empty($name) || empty($data_caches[$name]['has_private_data'])) {
        require_once "$xcart_dir/include/classes/class.xc_cache_lite.php";
        $cache_lite = XC_Cache_Lite::get_instance();

        $cache_lite->setLifeTime( empty($data_caches[$name]['ttl']) ? 0  : $data_caches[$name]['ttl'] );
        $cache_lite->setCacheDir( empty($data_caches[$name]['dir']) ? '' : $data_caches[$name]['dir'] );
        $cache_lite->setFileNameProtection(!empty($data_caches[$name]['fileNameProtection']));
        $cache_lite->setHashedDirectoryLevel( 
            empty($data_caches[$name]['hashedDirectoryLevel']) ? 0 : $data_caches[$name]['hashedDirectoryLevel']
        );

        $cache_lite->clean($name);
    }   

    if (empty($name) || !empty($data_caches[$name]['has_private_data'])) {
        global $var_dirs;
        func_internal_data_cache_clear($name, $var_dirs);
    }
} // }}}

function func_internal_data_cache_clear($name, &$var_dirs) { // {{{
    $path = $var_dirs['cache'];
    $dir = opendir($path);
    if (!$dir)
        return false;

    while ($file = readdir($dir)) {
        if (
            $file != '.'
            && $file != '..'
            && (
                (
                    $name === false
                    && preg_match("/\.(php|tpl|js|css)$/S", $file)
                ) || (
                    $name !== false
                    && strpos($file, $name . '.') === 0
                )
            )
        ) {
            @unlink($path . XC_DS . $file);
        }
    }

    closedir($dir);

    return true;
} // }}}

/**
 * Write array to data cache file
 */
function func_data_cache_write($fp, $prefix, $data) { // {{{
    if (!is_array($data)) {
        if (!fwrite($fp, $prefix.' = ')) {
            return FALSE;
        }

        if (is_bool($data)) {
            if (!fwrite($fp, ($data ? 'TRUE' : 'FALSE') . ";\n"))
                return FALSE;
        } elseif (is_int($data) || is_float($data)) {
            if (!fwrite($fp, $data . ";\n"))
                return FALSE;
        } elseif (is_null($data)) {
            assert('!is_null($data) /* '.__FUNCTION__.': NULL value cached */');
            if (!fwrite($fp, "NULL;\n"))
                return FALSE;
        } else {
            if (!fwrite($fp, "'" . str_replace("'", "\'", str_replace("\\", "\\\\", $data)) . "';\n"))
                return FALSE;
        }
    } else {
        foreach ($data as $key => $value) {
            if (!func_data_cache_write($fp, $prefix . "['" . str_replace("'", "\'", $key) . "']", $value))
                return FALSE;
        }
    }

    return TRUE;
} // }}}

/**
 * Erase service array (Group editing of products functionality)
 */
function func_ge_erase($geid = FALSE) { // {{{
    global $sql_tbl, $XCARTSESSID;

    $where = empty($geid) ? "sessid = '$XCARTSESSID'" : "geid = '$geid'";

    return db_query("DELETE FROM $sql_tbl[ge_products] WHERE $where");
} // }}}

/**
 * Store temporary data in database for some reason
 */
function func_db_tmpwrite($data, $ttl = 600)
{
    $id = md5(microtime());

    $hash = array (
        'id'         => addslashes($id),
        'data'         => addslashes(serialize($data)),
        'expire'     => XC_TIME + $ttl,
    );

    func_array2insert('temporary_data', $hash, true);

    return $id;
}

/**
 * Read previously stored temporary data
 */
function func_db_tmpread($id, $destroy = false)
{
    global $sql_tbl;

    $tmp = func_query_first_cell("SELECT data FROM " . $sql_tbl['temporary_data'] . " WHERE id='" . addslashes($id) . "'");

    if ($tmp === false)
        return false;

    if ($destroy) {
        db_query("DELETE FROM " . $sql_tbl['temporary_data'] . " WHERE id='" . addslashes($id) . "'");
    }

    return unserialize($tmp);
}

/**
 * Display service page header
 */
function func_display_service_header($title = '', $as_text = false)
{
    global $smarty;

    if (!defined('BENCH_BLOCK'))
        define('BENCH_BLOCK', true);

    if (!defined('SERVICE_HEADER')) {

        define('SERVICE_HEADER', true);

        func_set_time_limit(86400);

        func_flush(func_display('main/service_header.tpl', $smarty, false), HTML_MODE);

        if (!defined('NO_RSFUNCTION'))
            register_shutdown_function('func_display_service_footer');
    }

    if (!empty($title)) {

        if (!$as_text) {

            $title = func_get_langvar_by_name($title, null, false, true);

            if (empty($title))
                return;
        }

        func_flush($title.": ", HTML_MODE);

    }

}

/**
 * Display service page footer
 */
function func_display_service_footer()
{
    global $smarty;

    if (defined('SERVICE_HEADER')) {

        func_display('main/service_footer.tpl', $smarty);

        func_flush();

    }

}

/**
 * Close current window through JS-code
 */
function func_close_window()
{
?>
<script type="text/javascript">
//<![CDATA[
/* CMD: window_close */
if (window.popupControl)
    window.popupControl.close();
else
    window.close();
//]]>
</script>
<?php
    exit;
}

/**
 * Reloads parent window using JS and close current (optionaly)
 *
 * @param mixed $url           Redirect URL
 * @param mixed $close_current Close current window flag
 *
 * @return void
 * @see    ____func_see____
 */
function func_reload_parent_window($url = false, $close_current = true)
{
    $reloader_code = <<<JS
<script type="text/javascript">
//<![CDATA[
/* CMD: opener_reload */
if (window.opener) {
    window.opener.location.reload();
}
else if (window.parent) {
    window.parent.location.reload();
}
//]]>
</script>
JS;

    $redirect_code = <<<JS
<script type="text/javascript">
//<![CDATA[
/* CMD: opener_relocate */
if (window.opener) {
    window.opener.location = '$url';
}
else if (window.parent) {
    window.parent.location = '$url';
}    
//]]>
</script>
JS;

    echo empty($url) ? $reloader_code : $redirect_code;

    if ($close_current) {
        func_close_window();
    }

}

/**
 * Get value from array with presence check and default value
 */
function get_value($array, $index, $default = false)
{
    if (isset($array[$index]))
        return $array[$index];

    return $default;
}

/**
 * Get default image URL
 */
function func_get_default_image($type, $link='relative')
{
    global $config, $xcart_dir, $xcart_web_dir, $current_location;

    if (
        !isset($config['available_images'][$type])
        || empty($config['setup_images'][$type]['default_image'])
    ) {
        return false;
    }

    $default_image = $config['setup_images'][$type]['default_image'];

    if (is_url($default_image)) {
        return $default_image;
    }

    $default_image = func_realpath($default_image);

    if (
        !strncmp($xcart_dir, $default_image, strlen($xcart_dir))
        && file_exists($default_image)
    ) {

        if ($link == 'relative')
            $default_image = str_replace($xcart_dir, $xcart_web_dir, $default_image);
        else
            $default_image = str_replace($xcart_dir, $current_location, $default_image);

        if (X_DEF_OS_WINDOWS)
            $default_image = str_replace("\\", '/', $default_image);

        return $default_image;

    }

    return '';
}

/**
 * Convert EOL symbols to BR tags
 * if content hasn't any tags
 */
function func_eol2br($content)
{
    return ($content == strip_tags($content))
        ? str_replace("\n", "<br />", $content)
        : $content;
}

/**
 * Insert the trademark to string (used for shipping methods name)
 */
function func_insert_trademark($string, $use_alt = false)
{
    global $config;

    if (strpos($string, '##') === false)
        return $string;

    $reg = $sm = $tm = '';

    if (
        $config['Shipping']['realtime_shipping'] == 'Y' 
        && $config['Shipping']['use_intershipper'] != 'Y'
    ) {
        $is_enabled_trademark = true;
    } else {
        $is_enabled_trademark = false;
    } 

    if ($is_enabled_trademark) {

        $reg = "&#174;";

        if (empty($use_alt)) {

            $sm = "<sup>SM</sup>";
            $tm = "<sup>TM</sup>";

        } else {

            $reg = " (R)";
            $sm = " (SM)";
            $tm = " (TM)";

        }

    }

    $result = str_replace("##R##", $reg, trim($string));
    $result = str_replace("##SM##", $sm, $result);
    $result = str_replace("##TM##", $tm, $result);

    return $result;
}

/**
 * Convert html trademark to X-Cart internal format (used for shipping methods name)
 */
function func_convert_trademark($string)
{
    $pattern = array(
        '/&lt;sup&gt;&amp;reg;&lt;\/sup&gt;/',      # <sup>R</sup>
        '/&lt;sup&gt;&#174;&lt;\/sup&gt;/',         # <sup>R</sup>
        '/&lt;sup&gt;&amp;trade;&lt;\/sup&gt;/',    # <sup>TM</sup>
        '/&lt;sup&gt;&#8482;&lt;\/sup&gt;/',        # <sup>TM</sup>
        '/&lt;sup&gt;&amp;#8480;&lt;\/sup&gt;/',    # <sup>SM</sup>
        '/&lt;sup&gt;&#8480;&lt;\/sup&gt;/',        # <sup>SM</sup>
    );

    $replace = array(
        '##R##',
        '##R##',
        '##TM##',
        '##TM##',
        '##SM##',
        '##SM##'
    );

    $result = preg_replace($pattern, $replace, $string);

    return $result;
}

function func_use_arb_account($params = false)
{
    global $sql_tbl, $config;

    if (
        empty($config['Shipping']['ARB_id'])
        || empty($config['Shipping']['ARB_password'])
        || empty($config['Shipping']['ARB_account'])
    ) {
        return false;
    }

    if (!is_array($params))
        $params = func_query_first("SELECT param07 FROM " . $sql_tbl['shipping_options'] . " WHERE carrier='ARB'");

    if (isset($params['param07'])) {

        $tmp = explode(',', $params["param07"]);

        return (isset($tmp[1]) && $tmp[1] == 'Y');

    }

    return false;
}

function func_get_address_by_ip($ip)
{// {{{
    global $geoip_request_delay;
    static $data = null;

    if (!isset($data)) {
        $data = func_get_cache_func('', 'get_address_by_ip');
        $data = $data ? $data['data'] : $data;
    }

    if (isset($data[$ip])) {
        return $data[$ip];
    }

    x_session_register('geoip_request_delay', 0);

    $result = array();
    if ($geoip_request_delay - XC_TIME > 0) {
        return $result;
    }

    x_load('http');
    
    // Force libcurl in order to use low timeout in requests
    if (function_exists('curl_init')) {

        list($header, $response) = func_http_request_libcurl(
            'GET',
            func_https_fix_url('http://freegeoip.net/json/' . $ip, FALSE),
            '',
            '',
            array(),
            5
        );

    } else {

        list($header, $response) = func_http_get_request('freegeoip.net/json/' . $ip, FALSE, FALSE);
    }

    $is_200_ok = false;

    // headers are returned as an array in case libcurl is not available
    // ==> func_fsockopen_request is used in func_http_get_request
    $header = is_array($header) ? $header['ERROR'] : $header;

    if (empty($header)) {
        // It seems service is dead, wait 2 hours
        $delay_in_min = 120;
    } else {
        $delay_in_min = 5;
        // Check header response code
        $is_200_ok = preg_match("/HTTP\/.*\s*200\s*OK/i", $header);
    }

    if (!$is_200_ok) {
        // 1000 queries per hour limit in effect wait delay_in_min min
        $geoip_request_delay = XC_TIME + SECONDS_PER_MIN*$delay_in_min;
        $response = false;
    } else {
        $geoip_request_delay = 0;
    }

    if (!empty($response)) {
        
        $responseData = func_json_decode($response);
        if (is_object($responseData)) {
            $result = (array)$responseData;
        } else {
            $result = array();
        }
        
    }

    assert('!empty($result["country_code"]) /* '.__FUNCTION__.': Request to freegeoip.net returns empty or 1000limit in effect*/');

    // It seems this is local IP address
    if ($result['country_name'] == 'Reserved')
        $result = array();

    ksort($result);

    if ($is_200_ok) {
        $data[$ip] = $result;
        func_save_cache_func($data, '', 'get_address_by_ip');
    }

    return $result;
} // }}}

/**
 * Check IP
 */
function func_is_valid_ip($ip, $is_mask = false)
{
    $ip = trim($ip);
    
    $valid_ip_v4 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
    $valid_ip_v6 = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
    
    $ip_v4_mask = "/^\d{1,3}|\*\.\d{1,3}|\*\.\d{1,3}|\*\.\d{1,3}|\*$/";
    
    return $is_mask
        ? ((bool)preg_match($ip_v4_mask, $ip) || $valid_ip_v6)
        : ($valid_ip_v4 || $valid_ip_v6) ? true : false;
}

/**
 * Compare IP and mask
 */
function func_compare_ip($ip, $mask)
{
    $ip = trim($ip);

    if (!func_is_valid_ip($ip))
        return false;

    if (is_array($mask)) {
        $mask = func_array_map('trim', $mask);
    } else {
        $mask = array(trim($mask));
    }

    $mask = preg_grep("/^\d+|\*\.\d+|\*\.\d+|\*\.\d+|\*$/", $mask);

    if (count($mask) == 0)
        return false;

    $octets = explode('.', $ip);

    foreach ($mask as $m) {

        $moctets = explode('.', $m);
        $found = true;

        for ($i = 0; $i < 4 && $found; $i++) {
            if ($octets[$i] != $moctets[$i] && $moctets[$i] != '*')
                $found = false;
        }

        if ($found)
            return $m;
    }

    return false;
}

function func_secure_urldecode($param)
{
    return addslashes(urldecode($param));
}

/**
 * Wrapper for setcookie()
 */
function func_setcookie_raw()
{
    global $PHP_SELF, $active_modules;

    $arr = func_get_args();

    if (count($arr) < 1)
        return false;

    if (count($arr) > 7)
        $arr = array_slice($arr, 0, 7);

    if (empty($arr[1]))
        $arr[1] = '';

    if (empty($arr[2]))
        $arr[2] = 0;

    if (empty($arr[3]))
        $arr[3] = empty($PHP_SELF) ? null : preg_replace("/\/[^\/]+$/S", '', $PHP_SELF);

    if (empty($arr[4]) || strpos($arr[4], '.') === false) {

        $arr[4] = '';

    } else {

        if (preg_match("/([^:]*):[\d]+/", $arr[4], $_match))
            $arr[4] = $_match[1];

    }

    $arr[5] = isset($arr[5]) ? (bool)$arr[5] : false;

    // Never remove the version_compare(phpversion(), '5.2.0') below to work X-Cart installer correctly on old PHP versions
    if (version_compare(phpversion(), '5.2.0') >= 0) {
        // Determines whether the cookie will be made accessible only through the HTTP protocol or not.
        $arr[6] = isset($arr[6]) ? (bool)$arr[6] : true;

    } elseif (isset($arr[6])) {
        // To work installer correctly on PHP 4.x version
        unset($arr[6]);

    }

    if (
        !empty($active_modules['EU_Cookie_Law']) 
        && func_is_eucl_is_ready()
    ) {

        $arr[2] = func_eucl_get_expiration_time($arr);

    } else {

        func_eucl_delay_cookie($arr);

    }

    return call_user_func_array('setcookie', $arr);
}

function func_eucl_delay_cookie($cookie)
{
    global $eucl_cookies_storage;

    if (is_array($cookie)) {
        $eucl_cookies_storage[] = $cookie;
    }
}

function func_setcookie($name, $value = '', $ttl = 0, $httponly = true)
{
    global $xcart_http_host, $xcart_https_host, $HTTPS, $xcart_web_dir, $XCART_SESSION_NAME, $php_url;

    // To speed up ajax requests and auxiliary scripts
    if (
        $name == $XCART_SESSION_NAME
        && (
            defined('QUICK_START')
            || (
                func_is_ajax_request()
                && strpos($php_url['url'], 'get_block.php') !== false
            )
        )
    ) {
        return false;
    }

    $webdir = (empty($xcart_web_dir) ? '/' : $xcart_web_dir);

    func_setcookie_raw($name, $value, $ttl, $webdir, $xcart_http_host, false, $httponly);

    if ($xcart_http_host != $xcart_https_host)
        func_setcookie_raw($name, $value, $ttl, $webdir, $xcart_https_host, $HTTPS, $httponly);

    return true;
}

/**
 * Function splits string to array by EOL characters
 *
 * @param   string   $string                 String to split by any combination of eol characters
 * @param   int      $min_count optional.    If provided, then result array will be filled with
 *                                           empty string values if it's size less than $min_count
 *
 * @access  public
 * @return  array
 * @since   4.5.3
 */

function func_split_by_eol($string, $min_count = 0) {

    assert('!is_null($string) && is_numeric($min_count) /* '.__FUNCTION__.': wrong params */');

    $result = preg_split("/[\r\n]+/", $string);

    if (empty($result) || !is_array($result)) {
        return array_fill(0, $min_count, '');
    }

    $result_count = count($result);

    if ($result_count < $min_count) {
        $result = array_merge($result, array_fill($result_count, $min_count - $result_count, ''));
    }

    return $result;

}

/**
 * Stores current URL as a last working URL for specified users area, so an user of this type can get back to it after successful login.
 *
 * @param   mixed    $current_area    Code of X-Cart users area ('A' - admin, 'P' - provider, etc).
 * @param   mixed    $url optional.    If provided, then URL of this page should be stored unconditionally as the last working url.
 *                    In other case we analyze URL of currently opened page.
 * @access  public
 * @return  boolean    True if URL was successfully stored and false in other case.
 * @since   4.1.10
 */
function func_url_set_last_working_url($current_area, $url = NULL)
{
    global $REQUEST_METHOD, $php_url, $http_location, $https_location, $last_working_url, $active_modules;

    if (is_null($url)) {

        if (
            $REQUEST_METHOD !== 'GET'
            || func_is_ajax_request()
        ) {

            return false;

        } else {

            $url = $php_url['url'] . (!zerolen($php_url['query_string']) ? '?' . $php_url['query_string'] : '');

        }

    }

    x_session_register('last_working_url');

    if (
        (
            strncasecmp($url, $http_location, strlen($http_location)) === 0
            || strncasecmp($url, $https_location, strlen($https_location)) === 0
        )
        && strpos($url, '.php') !== false
        && strpos($url, 'login.php') === false
        && strpos($url, 'help.php?section=Password_Recovery') === false
        && strpos($url, 'secure_login.php') === false
    ) {
        $last_working_url[$current_area] = $url;

        if (!empty($active_modules['Simple_Mode'])) {
            if ($current_area == 'A') {
                $last_working_url['P'] = $url;
            }
            if ($current_area == 'P') {
                $last_working_url['A'] = $url;
            }
        }

        return true;
    }

    return false;
}

/**
 * Unsets last working URL for specified X-Cart users area.
 *
 * @param   mixed    $current_area    Code of X-Cart users area ('A' - admin, 'P' - provider, etc).
 * @access  public
 * @return  boolean    Always returns true.
 * @since   4.1.10
 */
function func_url_unset_last_working_url($current_area)
{
    global $last_working_url, $active_modules;

    x_session_register('last_working_url');

    func_unset($last_working_url, $current_area);

    if (!empty($active_modules['Simple_Mode'])) {

        if ($current_area == 'A') {
            func_unset($last_working_url, 'P');
        }

        if ($current_area == 'P') {
            func_unset($last_working_url, 'A');
        }

    }

    return true;
}

/**
 * Returns last working URL for specified X-Cart users area.
 *
 * @param   mixed    $current_area    Code of X-Cart users area ('A' - admin, 'P' - provider, etc).
 * @access  public
 * @return  string    URL.
 * @since   4.1.10
 */
function func_url_get_last_working_url($current_area)
{
    global $last_working_url, $HTTPS, $http_location, $https_location, $config;

    x_session_register('last_working_url');

    $url = '';

    if (isset($last_working_url[$current_area])) {

        $url = $last_working_url[$current_area];

        if (
            $HTTPS
            && preg_match("/^http:\/\//", trim($url))
            && $config['Security']['leave_https'] != 'Y'
        ) {
            $url = preg_replace("/^" . preg_quote($http_location, '/') . '/', $https_location, trim($url));
        }
    }

    return $url;
}

/**
 * Returns PHP engine execution mode.
 * Possible values:
 *  - privileged if a PHP process has the same permissions as owner of current file;
 *  - nonprivileged if a PHP process does not have privileged permissions.
 *
 * @access  public
 * @return  string    Execution mode (privileged or nonprivileged).
 * @since   4.1.10
 */
function func_get_php_execution_mode()
{
    static $exec_mode;

    if (isset($exec_mode)) {

        return $exec_mode;
    }

    if (
        !function_exists('get_current_user')
        || !function_exists('posix_geteuid')
        || !function_exists('posix_getpwuid')
    ) {
        $exec_mode = 'nonprivileged';

        return 'nonprivileged';
    }

    $file_username = @get_current_user();

    $process_user = @posix_getpwuid(posix_geteuid());

    if (
        empty($file_username)
        || empty($process_user)
        || empty($process_user['name'])
    ) {

        $exec_mode = 'nonprivileged';

        return 'nonprivileged';

    }

    $exec_mode = $file_username == $process_user['name']
        ? 'privileged'
        : 'nonprivileged';

    return $exec_mode;
}

/**
 * Returns regular expression for proper email validation
 */
function func_email_validation_regexp($format = '') { //{{{
    // Regexp for address part according to RFC 822
/*
    $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
    $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
    $atom =  '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
    $quoted_pair = '\\x5c[\\x00-\\x7f]';
    $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
    $word = "($atom|$quoted_string)";
    $local_part = "$word(\\x2e$word)*";

    $sub_domain = "([a-zA-Z0-9]{1}|[a-zA-Z0-9]([a-zA-Z0-9-]){0,61}[a-zA-Z0-9])";
    $domain = "$sub_domain(\\x2e$sub_domain)*";

    $addr_spec = "^$local_part\\x40$domain$";

    return $addr_spec;
*/

    // RFC 2822
    # return "^(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$";

    // RFC 2822 - based reg.exp. without double quotes and square brackets
    if (empty($format)) {
        // Synchronize with xpc_strip_extended_email_data function on change
        return "^[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z](?:[a-z0-9-]*[a-z0-9])$";
    } elseif ($format == XCEmailFormat::EXTENDED) {
        // Your Company Name <sales@yourstore.com> format
        return "^([a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z](?:[a-z0-9-]*[a-z0-9]))|([A-z 0-9]{3,}[ ]{1}(<){1}[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z](?:[a-z0-9-]*[a-z0-9])(>){1})$";
    }
} //}}}

/**
 * Display error page using message.html file
 */
function func_show_error_page($title, $message = '', $extra_info = "")
{
    global $xcart_dir;

    $xcart_home = func_get_xcart_home();

    if (is_readable($xcart_dir . '/message.html')) {
        $output = file_get_contents($xcart_dir . '/message.html');
    }

    $output = (is_string($output) && strstr($output,"%MESSAGE%"))
        ? str_replace(
            array(
                "%XCART_HOME%",
                "%TITLE%",
                "%MESSAGE%",
                "%EXTRA_INFO%",
            ),
            array(
                $xcart_home,
                $title,
                $message,
                $extra_info,
            ),
            $output
        )
        : "<h1>" . $title . "</h1>\n<p>" . $message . "</p>\n<p>" . $extra_info . "</p>";

    die($output);
}

/**
 * Display 404 page using skin/common_files/404/404_<language prefix>.html file depending on current store language
 */
function func_page_not_found()
{
    global $xcart_dir, $xcart_web_dir, $xcart_http_host, $xcart_https_host, $smarty_skin_dir, $HTTPS, $alt_skin_dir;

    if (
        isset($_COOKIE['alt_skin'])
        && !empty($_COOKIE['alt_skin'])
        && defined('DEMO_MODE')
    ) {
        $smarty_skin_dir = '/' . $_COOKIE['alt_skin'];
    }

    $dir_404 = XC_DS . '404' . XC_DS;

    $store_language = isset($_COOKIE['store_language']) ? $_COOKIE['store_language'] : 'en';

    $is_found = false;

    $filename404 = $dir_404 . '404_' . $store_language . '.html';

    if (
        !empty($alt_skin_dir)
        && is_dir($alt_skin_dir . $dir_404)
    ) {
        $dir_404 = $alt_skin_dir . $dir_404;
        $filename404 = $alt_skin_dir . $filename404;
    } else {
        $dir_404 = $xcart_dir . $smarty_skin_dir . $dir_404;
        $filename404 = $xcart_dir. $smarty_skin_dir . $filename404;
    }

    $is_found = true;

    if (!is_file($filename404)) {

        $skin_dir404 = @opendir($dir_404);

        $is_found = false;

        while (
            $skin_dir404
            && (false !== ($file = readdir($skin_dir404)))
            && !$is_found
        ) {

            if (
                is_file($dir_404 . $file)
                && preg_match('/404_/', $file)
            ) {

                $is_found = true;

                $filename404 = $dir_404 . $file;

            }

        }

    }

    if ($HTTPS)
        $base_replacement = '<base href="https://' . $xcart_https_host . $xcart_web_dir . '/"';
    else
        $base_replacement = '<base href="http://' . $xcart_http_host . $xcart_web_dir . '/"';

    @header("HTTP/1.0 404 Not Found");

    if ($is_found) {
        echo preg_replace('/<base\s+href=".*"/USs', $base_replacement , implode("", file($filename404)));
    }

    exit;
}

/**
 * Display Access denied page (403 error)
 *
 * @param mixed $id                 Error ID
 * @param mixed $params params for labels substitute
 *
 * @return void
 * @see    ____func_see____
 */
function func_403($id = 0, $lang_replace_to = array()) { // {{{
    global $smarty, $container_classes;
    global $current_area, $xcart_dir, $location;

    $area = 'C';

    if (isset($current_area)) {

        $area = $current_area;

    } elseif (defined('AREA_TYPE')) {

        $area = AREA_TYPE;

    }

    $dirCase = array(
        'A'    => 'admin',
        'P'    => 'provider',
        'B'    => 'partner',
        'C'    => 'customer',
    );

    $dir = isset($dirCase[$area]) ? $dirCase[$area] : 'customer';

    $container_classes[] = 'error-page';

    if (!is_object($smarty)) {
        //include $xcart_dir . '/smarty.php';
        // To avoid error like Fatal error:  Call to a member function assign() on a non-object when an smarty non-defined 
        // For example the calls like func_force_data_cache_update->func_array2insert->func_check_tbl_fields->func_403(78)
        class fakeSmarty {
            public $webmaster_mode = false;
            public function assign() {}
        }
        $smarty = new fakeSmarty();
        $fake_smarty_is_used = true;
    }

    $smarty->assign('main', $area == 'C' ? '403' : 'access_denied');

    // Prepare a message
    if (is_numeric($id) && $id > 0) {

        $message = func_get_langvar_by_name('txt_err_msg_code_' . $id, $lang_replace_to, false, true);

        if (empty($message)) {
            $message = func_get_langvar_by_name(
                'txt_err_msg_code_X',
                array(
                    'code' => $id,
                ),
                false,
                true
            );
        }

        // Add to log
        x_log_add('INTERNAL', $message . (empty($id) ? '' : " Id:$id"));

        $smarty->assign('id', $id);
        $smarty->assign('message', $message);
    }

    // Assign the location line
    $location[] = array(
        func_get_langvar_by_name('lbl_warning'),
        '',
    );

    $smarty->assign('location', $location);

    if (!empty($fake_smarty_is_used)) {
        echo "<p>Id: {$id}</p>";
        echo "<p>Message: {$message}</p>";
    } else {
        if ($area == 'A' || $area == 'P') {
            global $x_error_reporting, $user_account, $active_modules;
            include $xcart_dir . '/modules/gold_display.php';
        }

        func_display($dir . '/home.tpl', $smarty);
    }

    exit;
} // }}}

function func_truncate($str, $length = 500, $etc = '...')
{
    if (strlen($str) > $length) {

        $length -= min($length, strlen($etc));

        $str = substr(preg_replace('/\s+?(\S+)?$/', '', substr($str, 0, $length + 1)), 0, $length) . $etc;

    }

    return $str;
}

/**
 * Function that determines whether the script being pointed to by the link
 * pertains to the shop from which the function is run.
 */
function func_is_current_shop($href)
{
    global $http_location, $https_location;

    $href = trim($href);

    if (preg_match("/^http:\/\//S", $href)) {
        return (substr($href, 0, strlen($http_location)) == $http_location);
    }

    if (preg_match("/^https:\/\//S", $href)) {
        return (substr($href, 0, strlen($https_location)) == $https_location);
    }

    if (preg_match("/^\//S", $href)) {

        $http_parsed = @parse_url($http_location);
        $https_parsed = @parse_url($http_location);

        return (
            substr($href, 0, strlen($http_parsed['path'])) == $http_parsed['path']
            || substr($href, 0, strlen($https_parsed['path'])) == $https_parsed['path']
        );
    }

    return true;
}

function func_xss_free($html, $strip = false, $collect_errors = false)
{

    if (empty($html) || (is_string($html) && strip_tags($html) == $html))
        return $html;

    $new_html = $html;

    if (is_array($html)) {

        foreach($html as $k => $v) {
            $html[$k] = func_xss_free($html[$k], $strip, $collect_errors);
        }

        $new_html = $html;

    } else {

        $new_html = func_clear_from_xss($html, $strip, $collect_errors);

        $new_html = $new_html['html'];

    }

    return $new_html;
}

function func_xss_changed($html, $strip = false, $collect_errors = false)
{
    $new_html = func_clear_from_xss($html, $strip, $collect_errors);

    return $new_html['changed'];
}

/**
 * Filters an HTML code to be XSS-free.
 */
function func_clear_from_xss($html, $strip = false, $collect_errors = false)
{
    global $xcart_dir, $login;

    if (empty($html) || strip_tags($html) == $html) {

        return array(
            'html'    => $html,
            'changed' => false,
        );

    }

    $new_html = func_purify_html($html, $strip, $collect_errors);

    return array(
       'html'    => $new_html,
       'changed' => $new_html == $html,
    );

}

function func_unhtmlentities($str)
{
    static $tr_tbl1 = 0;
    static $tr_tbl2 = 0;

    global $default_charset;

    if (!is_string($str) && !is_numeric($str)) {
        return '';
    }

    if ($tr_tbl1 === 0) {

        $tr_tbl1 = array_flip(get_html_translation_table(HTML_ENTITIES));
        $tr_tbl2 = array_flip(get_html_translation_table(HTML_SPECIALCHARS, ENT_QUOTES));

        $tr_tbl2['&apos;'] = "'";
    }

    if (strtolower($default_charset) == "utf-8") {

        foreach ($tr_tbl1 as $k => $v) {

            if ($v != utf8_encode(html_entity_decode($k))) {

                $tr_tbl1[$k] = utf8_encode(html_entity_decode($v));

            }

        }

    }

    $str = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $str);

    $str = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $str);

    return strtr(strtr($str, $tr_tbl1), $tr_tbl2);
}

function func_unhtml($var)
{
    return is_array($var)
        ? func_array_map_hash('func_unhtml', $var)
        : func_unhtmlentities($var);
}

/**
 * Convert & to &amp;
 */
function func_convert_amp($str)
{
    // Do not convert html entities like &thetasym; &Omicron; &euro; &#8364; &#8218;
    return preg_replace('/&(?![a-zA-Z0-9#]{1,8};)/Ss', '&amp;', $str);
}

/**
 * Check if provider seller address feature can be applied
 */
function func_is_seller_address_applicable($user, $not_allow_change_seller_address = false)
{
    global $single_mode, $config, $sql_tbl;

    $not_allow_change_seller_address = ($not_allow_change_seller_address)
        ? $not_allow_change_seller_address
        : (
            $config['Shipping']['allow_change_seller_address'] == 'Y'
                ? 'N'
                : 'Y'
        );

    if (
        empty($user)
        || $single_mode
        || $not_allow_change_seller_address == 'Y'
        || $config['Shipping']['realtime_shipping'] != 'Y'
    ) {
        return false;
    }

    $info = func_query_first("SELECT id FROM $sql_tbl[customers] WHERE id='$user' AND usertype='P' AND status='Y'");

    if (empty($info)) {
        return false;
    }

    return true;
}

/**
 * Check if provider seller address is empty (used to display warning top message)
 */
function func_is_seller_address_empty($user)
{
    global $sql_tbl;

    $fields = array(
        'city',
        'state',
        'country',
        'zipcode',
    );

    $seller_address = func_query_first("SELECT userid FROM $sql_tbl[seller_addresses] WHERE userid='$user' AND " . implode("<>'' AND ", $fields) . "<>''");

    return empty($seller_address);
}

/**
 * Check URL format
 */
function func_check_url($url)
{
    if (!is_string($url) || empty($url))
        return false;

    $real_url = '';

    $_url = parse_url($url);

    if (empty($_url['scheme']))
        $_url['scheme'] = 'http';

    if (!in_array($_url['scheme'], array('https', 'http')))
        return false;

    $real_url = $_url['scheme'] . '://';

    if (!empty($_url['host'])) $real_url .= $_url['host'];
    if (!empty($_url['path'])) $real_url .= $_url['path'];
    if (!empty($_url['query'])) $real_url .= '?' . $_url['query'];
    if (!empty($_url['fragment'])) $real_url .= '#' . $_url['fragment'];

    $re = "/^(https?):(?:\/\/(?:((?:[a-z0-9-._~!$&'()*+,;=:]|%[0-9A-F]{2})*)@)?((?:[a-z0-9-._~!$&'()*+,;=]|%[0-9A-F]{2})*)(?::(\d*))?(\/(?:[a-z0-9-._~!$&'()*+,;=:@\/]|%[0-9A-F]{2})*)?|(\/?(?:[a-z0-9-._~!$&'()*+,;=:@]|%[0-9A-F]{2})+(?:[a-z0-9-._~!$&'()*+,;=:@\/]|%[0-9A-F]{2})*)?)(?:\?((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9A-F]{2})*))?(?:#((?:[a-z0-9-._~!$&'()*+,;=:\/?@]|%[0-9A-F]{2})*))?$/Sis";

    $result = (bool)preg_match($re, $real_url);

    if ($result)
        return $real_url;

    return false;
}

/**
 * Return realtime shipping carriers list
 */
function func_get_carriers()
{
    global $config, $carrier;

    $carriers = array();

    if ($config['Shipping']['use_intershipper'] == 'Y') {

        $carriers[] = array('Intershipper', 'InterShipper');

        $carrier = 'Intershipper';

    } else {

        $carriers[] = array('CPC',  'Canada Post');
        $carriers[] = array('FDX',  'FedEx');
        $carriers[] = array('USPS', 'U.S.P.S');
        $carriers[] = array('ARB',  'Airborne / DHL');
        $carriers[] = array('APOST','Australia Post');
        $carriers[] = array('1800C', '1-800Courier');

    }

    return $carriers;
}

/**
 * Package size corrections from inches and pounds to the shop measurement system
 */
function func_correct_dimensions($pack, $weight_calculation = true)
{
    global $config;

    if (round($config['General']['dimensions_symbol_cm'], 2) != XCPhysics::CM_PER_1INCH) {

        foreach (array('length', 'width', 'height', 'girth') as $field) {

            if (!empty($pack[$field]))
                $pack[$field] = round($pack[$field] * XCPhysics::CM_PER_1INCH / $config['General']['dimensions_symbol_cm'], 2);

        }

    }

    // Convert weight limit to the weight unit specified in the store's General settings is different
    if (round($config['General']['weight_symbol_grams'], 1) != round(XCPhysics::GRAMS_PER_1LB, 1) && $weight_calculation) {

        $pack['weight'] = round($pack['weight'] * XCPhysics::GRAMS_PER_1LB / $config['General']['weight_symbol_grams'], 2);

    }

    return $pack;
}

/**
 * Return weight in lbs
 */
function func_weight_in_lbs($weight, $p = 2)
{
    return func_units_convert(func_weight_in_grams($weight), 'g', 'lbs', $p);
}

/**
 * Translate characters with diacritics to translit
 */
function func_translit($str, $charset = false, $subst_symbol = '_')
{
    global $config;

    static $tr = 0;

    if ($tr === 0) {
        $transl = array (
          '!' => '161',
          '"' => '1066,1098,8220,8221,8222',
          "'" => '1068,1100,8217,8218',
          '\'\'' => '147,148',
          '(R)' => '174',
          '(TM)' => '153,8482',
          '(c)' => '169',
          '+-' => '177',
          $subst_symbol => '32,47,92,172,173,8211', # Replace spaces/slashes by the $subst_symbol('_' by default)
          '.' => '183',
          '...' => '8230',
          '0/00' => '8240',
          '<' => '8249',
          '<<' => '171',
          '>' => '8250',
          '>>' => '187',
          '?' => '191',
          'A' => '192,193,194,195,196,197,256,258,260,1040,7840,7842,7844,7846,7848,7850,7852,7854,7856,7858,7860,7862',
          'AE' => '198',
          'B' => '1041,1042',
          'C' => '199,262,264,266,268,1062',
          'CH' => '1063',
          'Cx' => '264',
          'D' => '208,270,272,1044',
          'D%' => '1026',
          'DS' => '1029',
          'DZ' => '1039',
          'E' => '200,201,202,203,274,276,278,280,282,1045,7864,7866,7868,7870,7872,7874,7876,7878',
          'EUR' => '128,8364',
          'F' => '1060',
          'G' => '284,286,288,290,1043',
          'G%' => '1027',
          'G3' => '1168',
          'Gx' => '284',
          'H' => '292,294,1061',
          'Hx' => '292',
          'I' => '204,205,206,207,296,298,300,302,304,1048,7880,7882',
          'IE' => '1028',
          'II' => '1030',
          'IO' => '1025',
          'J' => '308,1049',
          'J%' => '1032',
          'Jx' => '308',
          'K' => '310,1050',
          'KJ' => '1036',
          'L' => '163,313,315,317,319,321,1051',
          'LJ' => '1033',
          'M' => '1052',
          'N' => '209,323,325,327,330,1053',
          'NJ' => '1034',
          'No.' => '8470',
          'O' => '164,210,211,212,213,214,216,332,334,336,416,467,1054,7884,7886,7888,7890,7892,7894,7896,7898,7900,7902,7904,7906',
          'OE' => '140,338',
          'P' => '222,1055',
          'R' => ',340,342,344,1056',
          'S' => '138,346,348,350,352,1057',
          'SCH' => '1065',
          'SH' => '1064',
          'Sx' => '348',
          'T' => '354,356,358,1058',
          'Ts' => '1035',
          'U' => '217,218,219,220,360,362,364,366,368,370,431,1059,7908,7910,7912,7914,7916,7918,7920',
          'Ux' => '364',
          'V' => '1042',
          'V%' => '1038',
          'W' => '372',
          'Y' => '159,221,374,376,1067,7922,7924,7926,7928',
          'YA' => '1071',
          'YI' => '1031',
          'YU' => '1070',
          'Z' => '142,377,379,381,1047',
          'ZH' => '1046',
          '`' => '8216',
          '`E' => '1069',
          '`e' => '1101',
          'a' => '224,225,226,227,228,229,257,259,261,1072,7841,7843,7845,7847,7849,7851,7853,7855,7857,7859,7861,7863',
          'ae' => '230',
          'b' => '1073,1074',
          'c' => '162,231,263,265,267,269,1094',
          'ch' => '1095',
          'cx' => '265',
          'd' => '271,273,1076',
          'd%' => '1106',
          'ds' => '1109',
          'dz' => '1119',
          'e' => '232,233,234,235,275,277,279,281,283,1077,7865,7867,7869,7871,7873,7875,7877,7879',
          'f' => '131,402,1092',
          'g' => '285,287,289,291,1075',
          'g%' => '1107',
          'g3' => '1169',
          'gx' => '285',
          'h' => '293,295,1093',
          'hx' => '293',
          'i' => '236,237,238,239,297,299,301,303,305,1080,7881,7883',
          'ie' => '1108',
          'ii' => '1110',
          'io' => '1105',
          'j' => '309,1081',
          'j%' => '1112',
          'jx' => '309',
          'k' => '311,312,1082',
          'kj' => '1116',
          'l' => '314,316,318,320,322,1083',
          'lj' => '1113',
          'm' => '1084',
          'mu' => '181',
          'n' => '241,324,326,328,329,331,1085',
          'nj' => '1114',
          'o' => '186,176,242,243,244,245,246,248,333,335,337,417,449,1086,7885,7887,7889,7891,7893,7895,7897,7899,7901,7903,7905,7907',
          'oe' => '156,339',
          'p' => '167,182,254,1087',
          'r' => '341,343,345,1088',
          's' => '154,347,349,351,353,1089',
          'sch' => '1097',
          'sh' => '1096',
          'ss' => '223',
          'sx' => '349',
          't' => '355,357,359,1090',
          'ts' => '1115',
          'u' => '249,250,251,252,361,363,365,367,369,371,432,1091,7909,7911,7913,7915,7917,7919,7921',
          'ux' => '365',
          'v' => '1074',
          'v%' => '1118',
          'w' => '373',
          'y' => '253,255,375,1099,7923,7925,7927,7929',
          'ya' => '1103',
          'yen' => '165',
          'yi' => '1111',
          'yu' => '1102',
          'z' => '158,378,380,382,1079',
          'zh' => '1078',
          '|' => '166',
          '~' => '8212',
        );

        $tr = array();

        foreach ($transl as $letter => $set) {

            $letters = explode(",", $set);

            foreach ($letters as $v) {

                if ($v < 256) $tr[chr($v)] = $letter;

                $tr["&#" . $v . ";"] = $letter;

            }

        }

        // Add ASCII symbols not mentioned above
        for ($i = 0; $i < 256; $i++) {
            if (empty($tr["&#" . $i . ";"]))
                $tr["&#" . $i . ";"] = chr($i);
        }
    }

    if ($charset === false) {
        $charset = isset($config['db_charset'])
            ? $config['db_charset']
            : "ISO-8859-1";
    }

    if (
        strtolower($charset) != "iso-8859-1"
        && function_exists('mb_encode_numericentity')
    ) {
        $str = @mb_encode_numericentity($str, array (0x0, 0xffff, 0, 0xffff), $charset);

    } elseif (
        strtolower($charset) != "iso-8859-1"
        && function_exists('iconv')
    ) {
        $str = @iconv($charset, "ISO-8859-1//TRANSLIT", $str);
    }

    // Cannot be translited
    if (empty($str))
        return $str;

    return strtr($str, $tr);
}

/**
 * Calculate the root web-directory of X-Cart
 */
function func_get_xcart_home()
{
    global $PHP_SELF;

    $xcart_home = substr($PHP_SELF, 0, strrpos($PHP_SELF,'/'));

    $removeCase = array(
        'C'    => DIR_CUSTOMER,
        'A'    => DIR_ADMIN,
        'P'    => DIR_PROVIDER,
        'B'    => DIR_PARTNER,
    );

    if (defined('AREA_TYPE') && isset($removeCase[constant('AREA_TYPE')]))
        $remove = $removeCase[constant('AREA_TYPE')];
    else    
        $remove = '';

    if (!empty($remove)) {
        $xcart_home = preg_replace("/\/*" . preg_quote($remove, '/') . "\/*$/", '', $xcart_home);
    }

    return $xcart_home;
}

/**
 * Get hostname for proxied X-Carts
 */
function func_get_hostname()
{
    if( isset( $_SERVER["HTTP_X_FORWARDED_HOST"]))
        return $_SERVER["HTTP_X_FORWARDED_HOST"];
    else
        return $_SERVER["HTTP_HOST"];
}

/**
 * Direct logging to var/log/x-errors-<label>.php file. Used for critical issues
 */
function func_direct_log($label, $text)
{
    global $xcart_dir;

    $log_filename = sprintf($xcart_dir . "/var/log/x-errors_%s-%s.php", strtolower($label), date('ymd'));

    if (!file_exists($log_filename)) {

        $log_signature = '<' . '?php die(); ?' . ">\n";

        $fp = @fopen($log_filename, "a+");

        if ($fp === false) return false;

        @fwrite($fp, $log_signature);

        @fclose($fp);

    }

    $stack = func_get_backtrace(1);
    $backtrace = "Backtrace:\n".implode("\n", $stack)."\n\n";

    $data = sprintf(
        "[%s] %s %s:\n%s\n%s\n",
        date('d-M-Y H:i:s'),
        $label,
        'direct_log',
        $text,
        $backtrace
    );

    $fp = @fopen($log_filename, "a+");

    if ($fp === false) return false;

    @fwrite($fp, $data);

    @fclose($fp);

}

/**
 * Get first county entry
 */
function func_default_county($state, $country = 'US')
{
    global $sql_tbl;

    return func_query_first_cell("SELECT $sql_tbl[counties].countyid FROM $sql_tbl[counties], $sql_tbl[states] WHERE $sql_tbl[counties].stateid = $sql_tbl[states].stateid AND $sql_tbl[states].code = '".addslashes($state)."' AND $sql_tbl[states].country_code = '".addslashes($country)."' ORDER BY $sql_tbl[states].state, $sql_tbl[counties].county");
}

/**
 * Convert charset-non-standart symbols in UTF-8 / HTML entites symbols
 */
function func_ajax_convert($output, &$templater)
{
    static $entities = array(
        8364 => '&euro;',
        8218 => '&sbquo;',
        402 => '&fnof;',
        8222 => '&bdquo;',
        8230 => '&hellip;',
        8224 => '&dagger;',
        8225 => '&Dagger;',
        710 => '&circ;',
        8240 => '&permil;',
        352 => '&Scaron;',
        8249 => '&lsaquo;',
        338 => '&OElig;',
        8216 => '&lsquo;',
        8217 => '&rsquo;',
        8220 => '&ldquo;',
        8221 => '&rdquo;',
        8226 => '&bull;',
        8211 => '&ndash;',
        8212 => '&mdash;',
        732 => '&tilde;',
        8482 => '&trade;',
        353 => '&scaron;',
        8250 => '&rsaquo;',
        339 => '&oelig;',
        376 => '&Yuml;'
    );

    global $default_charset;

    $char_set = trim(strtolower((isset($default_charset) && ($default_charset != ''))
        ? $default_charset
        : "ISO-8859-1"));

    $tran = array();

    if (
        in_array(
            $char_set,
            array(
                'iso-8859-1',
                'iso-8859-2',
                'iso-8859-3',
                'iso-8859-4',
                'iso-8859-5',
                'iso-8859-6',
                'iso-8859-7',
                'iso-8859-8',
                'iso-8859-9',
                'iso-8859-10',
                'iso-8859-11',
                'iso-8859-12',
                'iso-8859-13',
                'iso-8859-14',
                'iso-8859-15',
            )
        )
    ) {
        $tran = array(
            128 => 8364,
            130 => 8218,
            131 => 402,
            132 => 8222,
            133 => 8230,
            134 => 8224,
            135 => 8225,
            136 => 710,
            137 => 8240,
            138 => 352,
            139 => 8249,
            140 => 338,
            142 => 381,
            145 => 8216,
            146 => 8217,
            147 => 8220,
            148 => 8221,
            149 => 8226,
            150 => 8211,
            151 => 8212,
            152 => 732,
            153 => 8482,
            154 => 353,
            155 => 8250,
            156 => 339,
            158 => 382,
            159 => 376
        );
    }

    if (count($tran) > 0) {

        $table = array();

        foreach ($tran as $k => $v) {

            if (isset($entities[$v])) {

                $table[chr($k)] = $entities[$v];

                continue;

            }

            $s = '';

            while ($v > 0) {

                $s .= chr($v > 256 ? floor($v / 256) : $v);

                $v = $v > 256 ? $v % 256 : 0;

            }

            $table[chr($k)] = $s;
        }

        $output = strtr($output, $table);
    }

    return $output;
}

/*
* Synchronize with get_secure_random_key from install.php
*/
function func_get_secure_random_key($count, $mode = '') { //{{{
    global $xcart_dir;

    $count = $count < 1 ? 1 : $count;

    require_once $xcart_dir . '/include/classes/class.XCPasswordHash.php';
    $t_hasher = new XCPasswordHash();

    $output = $t_hasher->get_random_bytes($count);

    $ascii_str = substr(base64_encode($output), 0, $count);

    if (empty($mode)) {
        $base64url_safe = strtr($ascii_str, '+/=', '-_,');
    } elseif($mode == 'user_password') {
        $rand_num1 = mt_rand(0, 9);
        $rand_num2 = mt_rand(0, 9);
        $base64url_safe = strtr($ascii_str, '+/=', $rand_num1 . '_' . $rand_num2);
    }

    return $base64url_safe;
} // }}}

function func_check_secure_random_key($key, $length) { //{{{
    // Key is generated by func_get_secure_random_key
    assert('is_numeric($length) /* '.__FUNCTION__.': wrong params */');
    return preg_match('/^[a-z0-9_,-]{' . $length . '}$/i', $key);
} // }}}

function func_get_uid()
{
    static $cache = false;

    if (!$cache)
        $cache = md5(uniqid(mt_rand(), true));

    return $cache;
}

/**
 * Gift_Certificates: get unique id for xcart_giftcerts
 * May by called from modules/Gift_Certificates/config.php so should be defined in kernel func.php
 */
function func_giftcert_get_gcid($is_uniq=CHECK_UNIQ)
{   
    global $sql_tbl;

    $stop_counter = 0;
    while (true) {
        $gcid = substr(strtoupper(md5(uniqid(mt_rand()))), 0, 16);
        $stop_counter++;

        // Exit conditions from infinite while loop
        if (
            $is_uniq == SKIP_CHECK_UNIQ
            || $stop_counter > 10
            || func_query_first_cell("SELECT COUNT(gcid) FROM $sql_tbl[giftcerts] WHERE gcid='$gcid'") == 0
        ) {
            break;
        }
    }

    return $gcid;
}

/**
 * Checks if memory limit exceeds
 */
function func_check_mem_raw()
{
    global $x_mem_threshold;

    if (!function_exists('memory_get_usage'))
        return true;

    $memory_limit = func_convert_to_byte(ini_get('memory_limit'));

    if ($memory_limit == 0)
        return true;

    $x_mem_threshold = max(intval($x_mem_threshold), 4194304);

    $memory_usage = memory_get_usage();

    return ($memory_usage + $x_mem_threshold) < $memory_limit;
}

/**
 * Checks if time limit exceeds
 */
function func_check_time_raw()
{
    global $x_time_threshold;

    $x_time_threshold = max(intval($x_time_threshold), 3);

    $t = func_get_script_time();

    return (func_microtime() + $x_time_threshold) < $t['limit'];
}

/**
 * Checks if system resources (time and memory) limits exceed
 */
function func_check_sysres()
{
    return func_check_mem_raw() && func_check_time_raw();
}

/**
 * Store pack of variables to session for 'remember user' facility
 */
function func_store_remember()
{
    func_remember_user(true);
}

/**
 * Update product rnd keys in the product_rnd_keys table
 *
 * @return void
 * @see    ____func_see____
 */
function func_refresh_product_rnd_keys($productid = null, $period = 43200)
{
    global $config, $sql_tbl;
    static $max_rnd_number = '';

    $_time = XC_TIME - $period;
    
    settype($config['last_rnd_keys_refresh_time'], 'int');
    if (intval($config['last_rnd_keys_refresh_time']) > $_time && empty($productid)) {
        return false;
    }

    if (empty($max_rnd_number))
        $max_rnd_number = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[products]");

    $rnd = mt_rand();
    if ($productid > 0) {
        func_array2insert(
            'product_rnd_keys',
            array(
                'productid' => intval($productid),
                'rnd_key' => "FLOOR(1 + (RAND(NOW() + $rnd) * $max_rnd_number))"
            ),
            true,
            false,
            array('rnd_key' => '')
        );
        
    } else {    

        db_query("DELETE FROM $sql_tbl[product_rnd_keys]");
        db_query("REPLACE INTO $sql_tbl[product_rnd_keys] (productid, rnd_key) SELECT productid, FLOOR(1 + (RAND(NOW() + $rnd) * $max_rnd_number)) AS rnd_key FROM $sql_tbl[products]");

        func_array2insert(
            'config',
            array(
                'name' => 'last_rnd_keys_refresh_time',
                'value' => XC_TIME
            ),
            true
        );
    }


    return true;
}

/**
 * Restore pack of variables from session for 'remember user' facility
 */
function func_restore_remember()
{
    func_remember_user(false);
}

/**
 * Main dispatcher function for 'remember user' facility
 */
function func_remember_user($store = true)
{
    x_session_register('_remember_vars');

    global $_remember_varnames, $_remember_vars;

    if (empty($_remember_varnames))
        return;

    if ($store)
        $_remember_vars = array();

    foreach ($_remember_varnames as $var) {

        if ($store) {

            if (isset($_POST[$var]))
                $_remember_vars[$var] = $_POST[$var];

        } else {

            $GLOBALS[$var] = $_remember_vars[$var];

        }

    }

    if (!$store) {

        $_remember_varnames = $_remember_vars = array();

    }

}

/**
 * Restore $var_dirs with .htaccess
 */
function func_restore_var_dir($dir_key, $dir_path) { //{{{
    global $display_critical_errors, $var_dirs_rules;

    if (!is_dir($dir_path)) {
        @unlink($dir_path);
        func_mkdir($dir_path);
        if (!is_dir($dir_path)) {
            $dir_info = $display_critical_errors ? $dir_path : '';
            func_show_error_page("Cannot write data to the temporary directory $dir_info", "Please check if it exists, and has writable permissions.");
        }
    }

    if (!is_writable($dir_path)) {
        $dir_info = $display_critical_errors ? $dir_path : '';
        func_show_error_page("Cannot write data to the temporary directory $dir_info", "Please check if it exists, and has writable permissions.");
    }

    if (
        !empty($var_dirs_rules[$dir_key])
        && is_array($var_dirs_rules[$dir_key])
    ) {
        foreach ($var_dirs_rules[$dir_key] as $f => $c) {
            if (file_exists($dir_path . '/' . $f))
                continue;

            if ($__fp = @fopen($dir_path . '/' . $f, 'w')) {
                @fwrite($__fp, $c);
                @fclose($__fp);
                func_chmod_file($dir_path . '/' . $f, 0644);
            }
        }
    }

} //}}}

/**
 * This function creates HMAC_SHA1 signature for $data by using $key
 */
function hmac_sha1($data, $key)
{
    $blocksize = 64;

    if (strlen($key) > $blocksize) {

        $key = pack('H*', sha1($key));

    }

    $key = str_pad($key, $blocksize, chr(0x00));
    $ipad = str_repeat(chr(0x36), $blocksize);
    $opad = str_repeat(chr(0x5c), $blocksize);
    $hmac = pack('H*', sha1(($key^$opad).pack('H*', sha1(($key^$ipad).$data))));

    return $hmac;
}

/**
 * Get CRC32 as HEX representation of integer
 */
function func_crc32($str)
{
    $crc32 = crc32($str);

    if (crc32('test') != -662733300 && $crc32 > 2147483647)
        $crc32 -= 4294967296;

    $hex = dechex(abs($crc32));

    return str_repeat('0', 8-strlen($hex)).$hex;
}

function func_strftime($format, $timestamp = null)
{
    if (is_null($timestamp))
        $timestamp = func_microtime();

    if (XC_DS == '\\') {

        $_win_from = array('%D','%h', '%n', '%r', '%R', '%t', '%T');

        $_win_to = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');

        if (strpos($format, '%e') !== false) {

            $_win_from[] = '%e';

            $_win_to[] = sprintf('%\' 2d', date('j', $timestamp));

        }

        if (strpos($format, '%l') !== false) {

            $_win_from[] = '%l';

            $_win_to[] = sprintf('%\' 2d', date('h', $timestamp));

        }

        $format = str_replace($_win_from, $_win_to, $format);

    }

    return strftime($format, $timestamp);
}

function func_check_languages_flags($languages = null)
{
    global $all_languages;

    $languages = $languages ? $languages : $all_languages;

    assert('/*'.__FUNCTION__.' @params*/ 
    is_array($languages) && !empty($languages)');

    $found = true;
    
    if (is_array($languages)) {
        foreach ($languages as $v) {

            if (!isset($v['tmbn_url'])) {
                $found = false;
                break;
            }    

        }
    }

    return $found;
}

/**
 * This function returns canonical name of page.
 */
function func_get_canonical ($smarty)
{
    $main = $smarty->getTemplateVars('main');
    settype($main, 'string');

    switch ($main) {

        case 'catalog':
        case 'manufacturer_products':

            $page = ($smarty->getTemplateVars('navigation_page') !== null && $smarty->getTemplateVars('navigation_page') != 0) ? $smarty->getTemplateVars('navigation_page') : 1;

            $total_super_pages = $smarty->getTemplateVars('total_super_pages') !== null ? $smarty->getTemplateVars('total_super_pages') : 1;

            $cat = ($smarty->getTemplateVars('cat') !== null && $smarty->getTemplateVars('cat') != 0) ? $smarty->getTemplateVars('cat') : "";

            $_page = $total_super_pages > 1 ? "&amp;page=" . $page : '';

            if ($main == 'catalog') {

                if ($cat) {

                    return "home.php?cat=" . $cat . $_page;

                } else {

                    return "home.php";

                }

            } else {

                $manufacturerid = $smarty->getTemplateVars('manufacturerid') !== null ? $smarty->getTemplateVars('manufacturerid') : "";

                return "manufacturers.php?manufacturerid=" . $manufacturerid . $_page;

            }

        case 'product' :
            $_product_in_template = $smarty->getTemplateVars('product');
            return 'product.php' . (isset($_product_in_template['productid']) ? "?productid=" . $_product_in_template['productid'] : "");

        case 'pages' :
            $_page_in_template = $smarty->getTemplateVars('page_data');
            return 'pages.php' . (isset($_page_in_template['pageid']) ? "?pageid=" . $_page_in_template['pageid'] : "");

    }

    return '';
}

/**
 * Set max_execution_time safely
 */
function func_set_time_limit($time)
{
    if (!is_int($time) || $time < 0) {

        return false;

    }

    $t = func_get_script_time();

       if (($time > 0 && $t['max'] >= $time) || ($time == 0 && $t['max'] >= SECONDS_PER_WEEK)) {

           return true;

    }

    set_time_limit($time);

    if (ini_get('max_execution_time') != $time) {
        return false;
    }

    if ($time == 0) {
        $time = SECONDS_PER_WEEK;
    }

    $t = func_get_script_time();

    if ($t['max'] < $time) {
        func_get_script_time($time);
    }

    return true;
}

/**
 * Get script time
 */
function func_get_script_time($tnew = FALSE) { // {{{
    static $t = FALSE;
    static $st = FALSE;
    static $time_diff = FALSE;

    global $max_execution_time;

    if ($st === FALSE) {
        list($_usec, $_sec) = explode(" ", constant('XCART_START_TIME'), 2);
        $st = doubleval($_usec) + doubleval($_sec);
    }

    if ($tnew) {
        $t = $tnew;
        $time_diff = func_microtime() - $st;
        return;
    } elseif ($t === FALSE) {
        $t = max(doubleval($max_execution_time), 0);
    }

    return array(
        'current'     => func_microtime() - $st,
        'max'         => $t,
        'real_max'    => $t + $time_diff,
        'limit'       => $st + $t + $time_diff,
    );
} // }}}

function func_get_usertypes($include_disabled_usertypes = true)
{
    global $active_modules;

    $usertypes = array(
        'A' => func_get_langvar_by_name('lbl_administrator'),
        'P' => func_get_langvar_by_name('lbl_provider'),
        'C' => func_get_langvar_by_name('lbl_customer'),
    );

    if (!empty($active_modules['XAffiliate']) || $include_disabled_usertypes) {
        $usertypes['B'] = func_get_langvar_by_name('lbl_partner');
    }

    if (!empty($active_modules['Simple_Mode'])) {

        $usertypes['P'] = $usertypes['A'];

        unset($usertypes['A']);

    }

    return $usertypes;
}

function func_get_active_usertypes()
{

    return func_get_usertypes(false);

}

function func_get_active_modules($force_rebuild = false) { // {{{
    global $active_modules;
    static $res;

    if (
        isset($res)
        && empty($force_rebuild)
    ) {
        return $res;
    }

    $_active_modules = func_data_cache_get('modules', array(), $force_rebuild);

    if (!empty($active_modules['Simple_Mode']))
        $_active_modules['Simple_Mode'] = $active_modules['Simple_Mode'];

    $res = $_active_modules;
    return $_active_modules;
} // }}}

/**
 * Returns regular expression for proper login validation
 */
function func_login_validation_regexp($reverse = false)
{
    return '[' . ($reverse ? '^' : '') . "a-zA-Z0-9.@!#$%&'*+\/=?^_`{|}~-]+";
}

/**
 * Sets time of event into xcart_config table
 */
function func_set_event($event)
{
    func_array2insert(
        'config',
        array(
            'name'         => addslashes(is_string($event) ? $event : ''),
            'category'     => 'XCART_INNER_EVENTS',
            'value'     => XC_TIME
        ),
        true
    );
}

/**
 * Gets time of event from xcart_config table
 */
function func_get_event($event)
{
    global $sql_tbl;

    if (!is_string($event)) $event = '';

    return func_query_first_cell("SELECT value FROM " . $sql_tbl['config'] . " WHERE category='XCART_INNER_EVENTS' AND name='" . addslashes($event) . "'");
}

/*
 Check if script in allowed array. The same code as is_https_link.
*/
function func_is_always_allowed_link($link, $add_condition=true)
{

    /*
     Corect possible bugs in is_https_link also
    */
    if (empty($add_condition))
        return false;

    $allowed_scripts = array();

    $allowed_scripts = array(
        array(
            'pages.php',
            "alias=",
            "is_ajax_request=Y",
            "open_in_layer=Y"
        ),
        array(
            'get_block.php',
            "block=latest_xcart_news",
        ),
        array(
            'get_block.php',
            "block=xcart_paid_modules",
        ),
    );

    $link = preg_replace('!^/+!S', '', $link);

    foreach ($allowed_scripts as $allowed_script) {

        if (!is_array($allowed_script))
            $allowed_script = array($allowed_script);

        $tmp = true;

        foreach ($allowed_script as $v) {

            $p = strpos($link, $v);

            if ($p === false) {
                $tmp = false;
                break;
            }

            if ($v[strlen($v)-1] === '=') continue;

            if ($p + strlen($v) < strlen($link)) {

                $last = $link[$p+strlen($v)];

                if ($last === '?') continue;

                if ($last !== '&') {

                    $tmp = false;

                    break;
                }

            }

        }

        if ($tmp) return true;
    }

    return false;
}

function func_is_allowed_operate_as_user_mode($identifiers)
{
    global $active_modules, $sql_tbl;

    $is_fstaff = $userid = 0;
    
    if (!empty($identifiers['A']))
        $userid = $identifiers['A']['userid'];
    elseif (!empty($identifiers['P']) && !empty($active_modules['Simple_Mode']))
        $userid = $identifiers['P']['userid'];

    if (!empty($userid))
        $is_fstaff = func_query_first_cell("SELECT COUNT(id) FROM $sql_tbl[customers] INNER JOIN $sql_tbl[memberships] USING (membershipid) WHERE id='$userid' AND $sql_tbl[memberships].flag='FS'");

    return !empty($userid) && empty($is_fstaff);
}

/**
 * Check - is background request or not
 */
function func_is_ajax_request()
{
    return (
            isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'
        );
}

/**
 * Check - is background JSON-based request or not
 */
function func_is_ajax_json_request()
{
    return
        func_is_ajax_request()
        && isset($_SERVER['HTTP_ACCEPT'])
        && preg_match('/application\/json/Ss', $_SERVER['HTTP_ACCEPT']);
}

/**
 * Register AJAX message
 */
function func_register_ajax_message($msg, $params = array())
{
    global $ajax_messages;

    if (!func_is_ajax_request())
        return false;

    if (!is_array($ajax_messages))
        $ajax_messages = array();

    $ajax_messages[] = array(
        'name'         => $msg,
        'params'     => is_array($params) ? $params : array(),
    );

    return true;
}

/**
 * Prepare AJAX messages for displaying
 */
function func_prepare_ajax_messages()
{
    global $ajax_messages, $default_charset;

    $strings = array();

    if (is_array($ajax_messages) && count($ajax_messages) > 0) {

        foreach ($ajax_messages as $m) {

            $strings[] = '<div class="ajax-internal-message" style="display: none;">' . $m['name'] . ':' . func_json_encode(func_convert_encoding($m['params'], $default_charset, 'UTF-8')) . '</div>';

        }

    }

    return implode("\n", $strings);
}

/**
 * AJAX request finalization
 */
function func_ajax_finalize()
{
    if (!func_is_ajax_request()) {
        return false;
    }

    $messages = func_prepare_ajax_messages();

    if ($messages) {

        func_flush($messages);

    }

    exit;
}

/**
 * Finalize XPC iframe 
 */
function func_xpc_iframe_finalize($location) 
{
    if (!defined('IS_XPC_IFRAME'))
        return;

    global $current_location, $smarty_skin_dir, $top_message, $bill_output, $config;

    $xpc_error = (isset($top_message['content'])  ? $top_message['content']  : '');
    $xpc_type  = (isset($top_message['xpc_type']) ? $top_message['xpc_type'] : func_constant('XPC_REDIRECT_DO_NOTHING'));

    if (
        strpos($location, 'error_message.php') !== false
        && func_constant('XPC_REDIRECT_ALERT_ERROR') !== $xpc_type
    ) {
        // Redirect to error message page
        
        if (isset($bill_output['billmes'])) {
            $xpc_error = $bill_output['billmes'];
        } else {
            $xpc_error = 'error';
        }
        
        $xpc_type = func_constant('XPC_REDIRECT_DISPLAY_ERROR');

    } 

    if ($xpc_type == func_constant('XPC_REDIRECT_DISPLAY_ERROR')) {

        echo htmlspecialchars($xpc_error);    

    } elseif ($xpc_type == func_constant('XPC_REDIRECT_ALERT_ERROR')) {

        $txt_ajax_error_note = addslashes(func_get_langvar_by_name('txt_ajax_error_note', null, false, true));
        $lbl_error = addslashes(func_get_langvar_by_name('lbl_error', null, false, true));

        echo <<<EOL
<script type="text/javascript">
    window.parent.xReload('$txt_ajax_error_note', '$lbl_error', 'E');
</script>
EOL;
    }

    // Notify parent window that iframe is loaded
    if (
        !empty($config['UA']['version'])
        && $config['UA']['version'] == '8'
        && $config['UA']['browser'] == 'MSIE'
    ) {
        echo '<script src="' . $current_location . $smarty_skin_dir . '/lib/jquery-min.1x.js"></script>';
    } else {
        echo '<script src="' . $current_location . $smarty_skin_dir . '/lib/jquery-min.js"></script>';
    }

    echo <<<EOL
<script type="text/javascript">
  $(function () {
    window.JSON && window.postMessage && window.parent.postMessage(
      JSON.stringify({message: "ready", params: {"height": $(document).height()}}), "*"
    );
  });
</script>
EOL;

    // Remove error message from session, 
    // so that it's not displayed in the main window
    $top_message = false;
    exit;
}

/**
 * AJAX request finalization in templater
 */
function func_ajax_templater_finalize($tpl_output, &$templater)
{
    if (func_is_ajax_request()) {

        $messages = func_prepare_ajax_messages();

        if ($messages) {

            $tpl_output = str_replace('</body>', $messages . '</body>', $tpl_output);

        }

    }

    return $tpl_output;
}

/**
 * Set AJAX error & error code
 */
function func_ajax_set_error($status, $code = 0)
{
    static $flag = false;

    if ($flag)
        return false;

    $flag = true;
    $code = max(0, intval($code));
    $status = (string)$status;

    header_remove('Pragma');
    header_remove('P3P');

    // The most ajax error can be cached
    if ($code === 0) {
        header('Cache-Control: max-age=3600');
        header('Expires: ' . gmdate('D, d M Y H:i:s', XC_TIME+3600) . ' GMT');
    }

    // Do not send headers in production to speed up ajax errors
    if (defined('DEVELOPMENT_MODE')) {
        header('X-Request-Status: ' . $status);
        header('X-Request-Error-Code: ' . $code);
    }

    func_flush('AJAX error :: ' . $status . ' :: ' . $code);

    return true;
}

/**
 * JSON decode wrapper
 */
function func_json_decode($str)
{
    global $xcart_dir;

    if (function_exists('json_decode'))
        return json_decode($str);

    require_once ($xcart_dir . '/include/lib/Services/Services_JSON/JSON.php');

    $json = new Services_JSON();

    $res = $json->decode($str);

    return $res;
}

/**
 * JSON encode
 */
function func_json_encode($data)
{
    if (function_exists('json_encode'))
        return json_encode($data);

    global $xcart_dir;

    require_once ($xcart_dir . '/include/lib/Services/Services_JSON/JSON.php');

    $json = new Services_JSON();

    $res = $json->encode($data);
    #bt:0106083 Overwrite "Content-type: application/json" sending in Services/Services_JSON/JSON.php
    header('Content-Type: text/html');

    return $res;
}

/**
 * Display data as JSON
 */
function func_ajax_display_json($data)
{
    func_flush(func_json_encode($data));

    exit;
}

function func_get_aliases_list()
{
    global $http_location, $https_location;

    $aliases = array(
        // aliases for domain:port format
        preg_replace("/([^:]*):[\d]+(\/.)*/", "$1$2", $http_location),
        preg_replace("/([^:]*):[\d]+(\/.)*/", "$1$2", $https_location)
    );

    // aliases for domain and path ending with slash
    $aliases[] = rtrim($http_location, XC_WEB_DS) . XC_WEB_DS;
    $aliases[] = rtrim($https_location, XC_WEB_DS) . XC_WEB_DS;

    if (defined('MY_LICENSE_URL'))
        $aliases[] = constant('MY_LICENSE_URL');

    $aliases = preg_replace('%^http[s]{0,1}://(www\.)*%i', '', $aliases);

    return $aliases;
}

// Function checks if the current store is registered or evaluation copy
// It returns:
/**
 * 'false'  if the store is registered
 * 'WRONG_DOMAIN' if the store is registered on another domain
 * 'EVALUATION' if the store is evaluation copy
 */
// You should use MY_LICENSE_URL constant if you want to use multidomain

function func_is_evaluation()
{
    global $is_install_preview, $config;

    if (isset($is_install_preview) && $is_install_preview == 'Y')
        return false;

    $_license_url = rtrim($config['license_url'], XC_WEB_DS) . XC_WEB_DS;

    $_license_url = preg_replace('%^http[s]{0,1}://(www\.)*%i', '', $_license_url);

    if (empty($_license_url))
        return 'EVALUATION';

    if     (
            (
                defined('AREA_TYPE')
                && constant('AREA_TYPE') == 'C'
            )
            || in_array(strtolower($_license_url), func_array_map('strtolower', func_get_aliases_list()))
    ) {
        return false;
    }

    return 'WRONG_DOMAIN';
}

/**
 * Check if evaluation is expired from installation date
 */
function func_is_evaluation_expired()
{
    global $config;

    $db_install_date = $config['db_install_date'];

    $evaluation_period = SECONDS_PER_DAY * 30; // 30 days

    if (
        empty($db_install_date)
        || XC_TIME - $db_install_date > $evaluation_period
        || XC_TIME - $db_install_date < 0
    ) {
        return TRUE;
    } else {
        return FALSE;
    }
}

/**
 * Check all active payment methods
 */
function func_check_active_payments()
{
    global $sql_tbl, $active_modules;

    $active_payment_methods = func_query_first_cell("SELECT * FROM " . $sql_tbl['payment_methods'] . " WHERE active='Y'");

    $ac_configured = (!empty($active_modules['Amazon_Checkout']) && func_is_acheckout_enabled());
    
    if (
        !empty($active_payment_methods)
        || $ac_configured
    ) {
        return true;
    } 

    if (
        empty($active_modules['Amazon_Checkout'])
    ) {
        // Amazon_Checkout is enabled but is setup incorrectly
        $lbl = 'lbl_no_active_payment_methods';
    } else {
        $lbl = 'lbl_no_active_payment_methods_and_ac';
    }

    return func_get_langvar_by_name($lbl);
}

/**
 * Store greeting text for Greet Visitor module
 */
function func_store_greeting($user_account = array())
{
    $fullname = $user_account['title'] . ' '
        . $user_account['firstname'] . ' '
        . $user_account['lastname'];

    $fullname = trim($fullname);

    if (empty($fullname)) {
        $fullname = $user_account['login'];
    }

    func_setcookie('GreetingCookie', $fullname, XC_TIME + 15552000);
}

/**
 * Use safe mode as function
 */
function func_safe_mode ()
{
    global $xcart_dir, $current_location;

    require $xcart_dir . '/include/safe_mode.php';
}

function func_storefront_update ()
{
    global $config, $smarty, $QUERY_STRING, $php_url, $login;

    if (
        !empty($_GET['storefront'])
        && in_array($_GET['storefront'], array('open', 'close'))
    ) {

        // Does not allow to open/close store in demo mode bt:84296
        func_safe_mode();

        $new_value = $_GET['storefront'] == 'close' ? 'Y' : '';

        x_log_flag(
            'log_activity', 
            'ACTIVITY', 
            "'" . 
            $login 
                . "' user has changed 'General::shop_closed' option value from '" 
                . $config["General"]["shop_closed"] 
                . "' to '" 
                . $new_value 
                . "'"
        );

        func_array2update(
            'config',
            array(
                'value' => $new_value,
            ),
            "name='shop_closed'"
        );

        $config['General']['shop_closed'] = $new_value;

        $smarty->assign('config', $config);

    }

    $QUERY_STRING = func_qs_remove($QUERY_STRING, 'storefront');

    $storefront_link = ($QUERY_STRING == '' ? '' : $QUERY_STRING . "&amp;" ) . "storefront=" . ($config['General']['shop_closed'] == 'Y' ? 'open' : 'close');

    $smarty->assign('storefront_link', $php_url['url'] . "?" . $storefront_link);
}

/**
 * Return Google map address and description information
 *
 * @param array   $address      address array
 * @param boolean $get_shipping flag to use as shipping address
 *
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_get_gmap($address, $get_shipping = true)
{
    $pref = $get_shipping ? 's_' : 'b_';

    if (!isset($address[$pref . 'address_2'])) {

        list(
            $address[$pref . 'address'], 
            $address[$pref . 'address_2']
        ) = func_split_by_eol($address[$pref . 'address'], 2);

    }

    if ($address[$pref . 'country'] == 'GB') 
        $address[$pref . 'state'] = '';

    return array(
        'address' =>
              @$address[$pref . 'address'] . ','
            . @$address[$pref . 'address_2'] . ','
            . @$address[$pref . 'zipcode'] . ','
            . @$address[$pref . 'city'] . ','
            . @$address[$pref . 'state'] . ','
            . @$address[$pref . 'country'],
        'description' => array(
            'name'    => @$address[$pref . 'firstname'] . ' ' . @$address[$pref . 'lastname'],
            'type'    => $get_shipping ? 'shipping' : 'billing',
            'phone'   => @$address['phone'],
            'address' => @$address[$pref . 'address'] . ',<br />'
                . @$address[$pref . 'address_2'] . ',<br />'
                . @$address[$pref . 'zipcode'] . ',<br />'
                . @$address[$pref . 'city'] . ',<br />'
                . @$address[$pref . 'state'] . ',<br />'
                . @$address[$pref . 'country'],
        ),
    );
}

/**
 * Return RewriteBase value for .htaccess
 *
 * @return string
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_get_rewrite_base()
{
    global $http_location;

    $http_location_info = @parse_url($http_location);

    return (
        empty($http_location_info['path'])
            ? '/'
            : rtrim($http_location_info['path'], '/')
        . '/'
    );
}

/**
 * Return Apache 401 issue string for .htaccess
 *
 * @return string
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_get_apache_401_issue()
{
    return
        substr(php_sapi_name(), 0, 6) == 'apache'
        ? 'RewriteCond %{ENV:REDIRECT_STATUS} !^401$'
        : '';
}

/**
 * Check if displaying states is needed for specified coutry
 *
 * @param string $country ____param_comment____
 *
 * @return bool
 * @see    ____func_see____
 */
function func_is_display_states($country = '')
{
    global $sql_tbl;

    if (empty($country)) {
        return false;
    }

    $display_states = func_query_first_cell("SELECT display_states FROM " . $sql_tbl['countries'] . " WHERE code = '" . $country. "'");

    return 'Y' == $display_states;
}

/**
 * Check if there is any active country
 * 
 * @return boolean
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_is_display_countries()
{
    global $sql_tbl;

    $result = func_query_first_cell("SELECT active FROM " . $sql_tbl['countries'] . " WHERE active='Y'");

    return false !== $result;
}

/**
 * Purify html code using htmlpurifier library
 *
 * @param string $html           HTML content
 * @param bool   $collect_errors Collect errors flag
 * @param bool   $strip          Input html is escaped with slashes
 *
 *
 * @return array
 * @see    ____func_see____
 */
function func_purify_html($html = '', $strip = false, $collect_errors = false)
{
    global $xcart_dir, $default_charset, $login, $var_dirs;

    static $purifier = null;

    // Create purifier object

    if (is_null($purifier)) {

        require_once $xcart_dir . '/include/lib/htmlpurifier4/library/HTMLPurifier.auto.php';

        $purifier_config = HTMLPurifier_Config::createDefault();

        if (!empty($default_charset) && strtolower($default_charset) != 'utf-8') {

            $purifier_config->set('Core.Encoding', $default_charset);

            // Look at http://htmlpurifier.org/docs/enduser-utf8.html#whyutf8-htmlpurifier
            $purifier_config->set('Core.EscapeNonASCIICharacters', true);

        } else {

            $purifier_config->set('Core.Encoding', 'UTF-8');

        }

        if (!empty($collect_errors))
            $purifier_config->set('Core.CollectErrors', true);

        $purifier_config->set('Cache.SerializerPath', $var_dirs['cache']); 

        $purifier = new HTMLPurifier($purifier_config);

    }

    if ($strip) {
        $html = stripslashes($html);
    }

    $html = $purifier->purify($html);

    /**
     * Log message for admin
     */
    if ($collect_errors) {

        $e = $purifier->context->get('ErrorCollector');

        $raw = $e->getraw();

        // use own method to define changed
        $changed = !empty($raw) ? true: false;

        if ($changed && is_array($raw)) {

            $output = array();

            foreach($raw as $r) {

                if (!in_array($r[2], $output))
                    $output[] = $r[2];

                if (count($output) > 10)
                    break;

            }
            x_log_flag(
                'log_xss_attempts',
                'XSS',
                'The user ' . $login . ' tried to use malicious code. This code has been removed:' . "\n" . print_r($output, true)
            );
        }
    }

    if ($strip) {

        $html = addslashes($html);

    }

    // Make htmlspecialchars in ENT_QUOTES mode like prepare.php bt:88707
    $html = str_replace("'", '&#039;', $html);

    return $html;
}

/**
 * Prepare javascript alert
 *
 * @param mixed $message Message to display
 * @param mixed $url     URL redirect to
 * @param mixed $display Forced displaying
 *
 * @return void
 * @see    ____func_see____
 */
function func_js_alert($message, $url, $display = false)
{

    $message = func_js_escape(strip_tags($message));

    $txt_continue = strip_tags(func_get_langvar_by_name('lbl_continue', false, false, true));

    $url_amp = func_convert_amp($url);

    $js_message = <<<JS
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<script type="text/javascript">
//<![CDATA[
    alert('$message');
    self.location = '$url';
//]]>
</script>
</head>
<body>
$message
<br /><br />
<a href="$url_amp">$txt_continue</a>
</body>
</html>
JS;

    if ($display) {
        echo $js_message;
        return;
    }

    return $js_message;
}

/**
 * Returns location of the certain area by area type
 *
 * @param string $area  Area (User type)
 * @param mixed  $https HTTPS side flag
 *
 * @return void
 * @see    ____func_see____
 */
function func_get_area_catalog($area = 'C', $https = null)
{
    global $xcart_catalogs, $xcart_catalogs_insecure, $xcart_catalogs_secure, $active_modules;

    $catalogs = array (
        'A' => 'admin',
        'P' => 'provider',
        'C' => 'customer',
        'B' => 'partner',
    );

    if (!empty($active_modules['Simple_Mode']) && $area == 'P') {
        $area = 'A';
    }

    if (!in_array($area, array('A','P','C','B'))) {
        $area = 'C';
    }

    $result = $xcart_catalogs[$catalogs[$area]];

    if (true === $https) {

        $result = $xcart_catalogs_secure[$catalogs[$area]];

    } elseif (false === $https) {

        $result = $xcart_catalogs_insecure[$catalogs[$area]];

    }

    return $result;
}

/**
 * Returns current location
 *
 * @return string
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_get_current_area()
{
    global $HTTPS;

    static $current_area = null;

    if (is_null($current_area)) {

        $current_area = func_get_area_catalog(
            defined('AREA_TYPE') ? constant('AREA_TYPE') : 'C',
            $HTTPS
        );

    }

    return $current_area;
}

/**
 * Returns the date pattern compatible with jQuery dateFormat function
 *
 * @return string
 * @see    ____func_see____
 */
function func_get_ui_date_format()
{
    global $config;

    $format = $config['Appearance']['date_format'];

    $patterns = array(
        '/%d/' => 'dd',
        '/%e/' => 'd',
        '/%m/' => 'mm',
        '/%n/' => 'm',
        '/%B/' => 'MM',
        '/%b/' => 'M',
        '/%y/' => 'y',
        '/%Y/' => 'yy',
        '/%A, /' => '' // not supported
    );

    $out = preg_replace(
        array_keys($patterns),
        array_values($patterns),
        $format
    );

    return $out;
}

/**
 * Prepares timestamp for the start/end of the certain day
 *
 * @param mixed $ts      UNIX timestamp/formatted date string
 * @param mixed $day_end End date flag
 *
 * @return void
 * @see    ____func_see____
 */
function func_prepare_search_date($ts = 0, $day_end = false)
{
    global $config;

    if (0 === $ts) {

        $ts = XC_TIME;

    } elseif (false !== func_strtotime($ts)) {
        
        // $ts contained formatted string, prepare UNIX timestamp
        $ts = func_strtotime($ts);

    } else {
   
        // Validate timestamp
        if (!is_numeric($ts)) {
            $ts = intval($ts);
        }

        if (!checkdate(date('m', $ts), date('d', $ts), date('Y', $ts))) {
            // Invalid timestamp
            $ts = XC_TIME;
        }
    }

    $date = mktime(0, 0, 0, date('m', $ts), date('d', $ts), date('Y', $ts));

    if ($day_end) {
        $date += 86399;
    }

    return $date;
}

/**
 * Sum up main associative array with some another array
 * by specified keys
 *
 * @param array $main Result array
 * @param array $with Array to sum up with main
 * @param array $keys Keys array
 *
 * @return array
 * @see    ____func_see____
 */
function func_array_sum_assoc($main = array(), $with = array(), $keys = array())
{
    if (
        empty($keys)
        || empty($with)
    ) {
        return $main;
    }

    foreach($keys as $k) {

        if (!isset($main[$k])) {
            $main[$k] = 0;
        }

        if (!isset($with[$k])) {
            $with[$k] = 0;
        }

        if (!is_numeric($with[$k])) {
            continue;
        }

        $main[$k] += $with[$k];
    }

    return $main;
}

/**
 * Check if the word is in the .htaccess file body
 *
 * @param string $word word string
 *
 * @return boolean
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_test_htaccess($word)
{
    global $xcart_dir;
    static $xcart_htaccess = NULL;

    if (is_null($xcart_htaccess)) {
        if (!is_readable($xcart_dir . XC_DS . '.htaccess'))
            return false;

        $xcart_htaccess = @file_get_contents($xcart_dir . XC_DS . '.htaccess');
        $xcart_htaccess = (string)$xcart_htaccess;
    } 

    $word = (string)$word;
    if (strpos($xcart_htaccess, $word) === false)
        return false;

    return true;
}

/**
 * Redefines $config['alt_skin'] using validated data from $_COOKIES or $_GET
 */

function func_redefine_alt_skin_from_get_or_cookies() {
    global $config, $xcart_web_dir;

    if (!empty($_COOKIE['alt_skin'])) {
        $_redefined_alt_skin = $_COOKIE['alt_skin'];
    } elseif (!empty($_GET['alt_skin'])) {
        $_redefined_alt_skin = $_GET['alt_skin'];
    }

    if (!empty($_redefined_alt_skin)) {
        $altSkinsInfo = func_data_cache_get('get_schemes', array($xcart_web_dir));

        if (in_array($_redefined_alt_skin, array_keys($altSkinsInfo))) {
            $config['alt_skin'] = $_redefined_alt_skin;
        }
    }

}

function func_get_alt_skin() {
    global $config, $xcart_web_dir;

    $altSkinsInfo = array();
    $alt_skin_info = array();
    $alt_skin_dir = '';

    if (defined('NO_ALT_SKIN') && 'Y' === constant('NO_ALT_SKIN')) {
        return array($altSkinsInfo, $alt_skin_info, $alt_skin_dir);
    }

    $altSkinsInfo = func_data_cache_get('get_schemes', array($xcart_web_dir)); //func_get_schemes

    if (!in_array($config['alt_skin'], array_keys($altSkinsInfo))) {
        $alt_skin_info = current($altSkinsInfo);
    } else {
        $alt_skin_info = &$altSkinsInfo[$config['alt_skin']];
    }

    $alt_skin_dir = &$alt_skin_info['path'];

    return array($altSkinsInfo, $alt_skin_info, $alt_skin_dir);
}

/**
 * Get alternative skin description array
 *
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_get_schemes($xcart_web_dir) { // {{{
    global $xcart_dir, $smarty_skin_dir;

    $schemesDir = $xcart_dir . XC_DS . 'skin';

    $schemes = @opendir($schemesDir);
    if ($schemes === FALSE) {
        x_log_add('file_errors', 'Cannot open dir ' . $schemesDir, TRUE);
        return FALSE;
    }

    $altSkinInfo = array();

    while (($schemesSkinName = @readdir($schemes)) !== false) {

        if ($schemesSkinName == '..') 
            continue;

        $schemesSkin = $schemesDir . XC_DS . $schemesSkinName;

        $altskinINI = $schemesSkin . XC_DS . 'altskin.ini';

        if (
            is_dir($schemesSkin)
            && is_readable($altskinINI)
        ) {

            $skinInfo = parse_ini_file($altskinINI);

            if (!empty($skinInfo)) {

                $id = @$skinInfo['order'] . '_' . $schemesSkinName;

                $altSkinInfo[$id] = $skinInfo;

                $altSkinInfo[$id]['path'] = $schemesSkin;

                $altSkinInfo[$id]['alt_skin_dir'] = '/skin/' . $schemesSkinName;

                $altSkinInfo[$id]['alt_schemes_skin_name'] = $schemesSkinName;

                $altSkinInfo[$id]['web_path'] = $xcart_web_dir . $altSkinInfo[$id]['alt_skin_dir'];

                if (
                    !empty($altSkinInfo[$id]['screenshot'])
                    && is_readable($schemesSkin . XC_DS . $altSkinInfo[$id]['screenshot'])
                ) {

                    $altSkinInfo[$id]['screenshot'] = $altSkinInfo[$id]['web_path'] . '/' . $altSkinInfo[$id]['screenshot'];

                } else {

                    $altSkinInfo[$id]['screenshot'] = $xcart_web_dir . $smarty_skin_dir . '/images/no_screenshot.png';

                }
            }

        }

    }

    ksort($altSkinInfo);

    return $altSkinInfo;
} // }}}

/**
 * Generates checksum of the certain fields of an array
 *
 * @param array $data   Associative array
 * @param array $fields Fields to go into hash
 *
 * @return void
 * @see    ____func_see____
 */
function func_generate_checksum($data = array(), $fields = array())
{

    if (
        empty($data)
        || empty($fields)
        || !is_array($data)
        || !is_array($fields)
    ) {
        return '';
    }

    $c = '';

    foreach ($fields as $f) {

        $c .= $f;

        if (!isset($data[$f])) {
            continue;
        }

        $c .= is_array($data[$f])
            ? serialize($data[$f])
            : $data[$f];
    }

    return md5($c);
}

/**
 * Return implode string by a key of an associative array
 * 
 * @param array  $arr   Data array
 * @param string $key   Key to implode
 * @param string $delim Delimiter
 *  
 * @return string
 * @see    ____func_see____
 */
function func_implode_assoc($arr = array(), $key = '', $delim = '/')
{
    $_arr = array();

    foreach ($arr as $v) {
        $_arr[] = $v[$key];
    }

    return implode($delim, $_arr);
}

/**
 * Get data from function and store it into memcache
 * 
 * @param string $key     inner key
 * @param string $func    function name to store in cache
 * @param array  $params  function parameters
 * @param int    $timeout timeout of function data
 *  
 * @return mixed
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_get_data_from_func($key, $func, $params = array(), $timeout = 0)
{
    global $memcache;

    $get_data = true;

    if ($memcache) {
        if (!empty($params)) 
            $key .= md5(serialize($params));

        $data = func_get_mcache_data($key);

        $get_data = false === $data || empty($data);
    }

    if ($get_data) {
        global $xcart_dir;
        require_once "$xcart_dir/include/data_cache.php";

        $data = call_user_func_array($func, $params);

        if ($memcache) {
            func_store_mcache_data($key, $data, $timeout);
        }
    }

    return $data;
}

/**
 * Store split checkout structure into DB. It is linked to orderids
 * 
 * @param array $split_query split checkout structure
 *  
 * @return integer 
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_store_split_checkout_data($split_query)
{
    $orderids = '|' . implode('|', $split_query['orderid']) . '|';

    return func_array2insert(
        'split_checkout',
        array(
            'orderids' => $orderids,
            'data'     => addslashes(serialize($split_query)),
        ),
        true
    );
}

/**
 * Get split checkout structure for order that is defined by orderid
 * 
 * @param integer $orderid Order identifier
 *  
 * @return mixed
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_get_split_checkout_data_by_orderid($orderid)
{
    global $sql_tbl;

    $data = func_query_first_cell('SELECT data FROM ' . $sql_tbl['split_checkout'] . ' WHERE orderids LIKE \'%|' . $orderid . '|%\'');
;
    return !empty($data)
        ? unserialize($data)
        : false;
}

/**
 * Get order split checkout structure optimized for order data that is defined by orderid
 * 
 * @param integer $orderid Order identifier
 *  
 * @return mixed
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_get_split_checkout_order_data_by_orderid($orderid)
{
    $data = func_get_split_checkout_data_by_orderid($orderid);

    $result = false;

    $payment_method = array();

    $return = false;

    if (false !== $data) {

        foreach ($data['transaction_query'] as $paymentid => $payment_query) {

            foreach ($payment_query as $id => $transaction_info) {

                $result[$paymentid . '_' . $id] = $transaction_info;

                $payment_method[] = $transaction_info['payment'];

            }

        }

        $return = array(
            'payment_method' => implode(', ', $payment_method),
            'data'           => $result,
        );

    }

    return $return;
}

/**
 * Remove orderid connection with split checkout data (partially paid checkout)
 * 
 * @param array   $split_data split checkout data
 * @param integer $orderid    order ID
 *  
 * @return void
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_remove_orderid_from_split_data(&$split_data, $orderid)
{
    $orderids = array_flip($split_data['orderid']);

    unset($orderids[$orderid]);

    $split_data['orderid'] = array_flip($orderids);
}

/**
 * Complete split checkout queries
 * 
 * @param array   $split_query       Split checkout transaction query
 * @param integer $current_paymentid Payment ID of current payment processor (will be skipped if POSSIBLE_TRANSACTION_QUERY defined)
 *  
 * @return boolean
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_process_split_checkout($split_query, $current_paymentid)
{
    if (defined('POSSIBLE_TRANSACTION_QUERY')) {

        unset($split_query[$current_paymentid]);

    }

    $full_return = true;

    foreach ($split_query as $payment) {

        $first_transaction = current($payment);

        $module_params = $first_transaction['module_params'];

        $payment_name = preg_replace("/\.php/Ss", '', $module_params['processor']);

        func_pm_load($payment_name);

        if (
            !empty($first_transaction['functions']['complete'])
            && function_exists($first_transaction['functions']['complete'])
        ) { 

            $return = call_user_func_array($first_transaction['functions']['complete'], $first_transaction['functions']['params_complete']);

            $full_return = $full_return && $return;

        }

    }

    return $full_return;
}

/**
 * Decode input data to required charset from init charset
 * 
 * @param mixed $data Request data
 * @param mixed $from_encoding Encoding of the $data
 * @param mixed $from_encoding Output encoding of the $data
 *  
 * @return string
 * @see    ____func_see____
 */
function func_convert_encoding($data, $from_encoding = '', $to_encoding = '') {

    static $func_convert_function = false;
    static $params = false;

    if (empty($from_encoding)) {
        $from_encoding = 'ISO-8859-1';
    }

    if (empty($to_encoding)) {
        $to_encoding = 'ISO-8859-1';
    }

    if (
        strcasecmp($from_encoding, $to_encoding) == 0
        || empty($data)
        || $func_convert_function == 'can_not_be_encoded'
    ) {
        return $data;
    }

    // Define convert function and params
    if (empty($func_convert_function)) {

        if (function_exists('mb_convert_encoding')) {
            $func_convert_function = 'mb_convert_encoding';
            $params = array('data', 'to_encoding', 'from_encoding');

        } elseif (function_exists('iconv')) {
            $func_convert_function = 'iconv';
            $params = array('from_encoding', 'to_encoding', 'data');

        } else {
            $func_convert_function = 'can_not_be_encoded';
            return $data;
        }
    }


    if (is_array($data)) {
        foreach($data as $k => $v)
            $new_data[$k] = func_convert_encoding($v, $from_encoding, $to_encoding);
    } else {
        // Call iconv or mb_convert_encoding with params
        $new_data = $func_convert_function($$params[0], $$params[1], $$params[2]);
    }

    return empty($new_data) ? $data : $new_data;
}

function func_html_entity_decode($string) { // {{{
    static $trans_tbl = FALSE;

    // replace numeric entities
    $string = preg_replace('~&#x0*([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
    $string = preg_replace('~&#0*([0-9]+);~e', 'chr(\\1)', $string);

    // replace literal entities
    if ($trans_tbl === FALSE) {
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
    }
    return strtr($string, $trans_tbl);
} // }}}

/**
 * Wrapper for htmlentities
 * 
 * @param mixed $data
 *  
 * @return mixed
 * @see    ____func_see____
 */
function func_htmlentities($data) { // {{{
    global $default_charset;

    assert( '!empty($default_charset) /* '.__FUNCTION__.': default_charset is not set */');

    if (empty($default_charset)) {
        $default_charset = 'UTF-8';
    } 

    if (is_array($data)) {
        return func_array_map('func_htmlentities', $data);
    }

    return htmlentities($data, ENT_QUOTES, $default_charset);
} // }}}

/**
 * Wrapper for htmlspecialchars
 * 
 * @param mixed $data
 *  
 * @return mixed
 * @see    ____func_see____
 */
function func_htmlspecialchars($data) { // {{{
    global $default_charset;

    assert( '!empty($default_charset) /* '.__FUNCTION__.': default_charset is not set */');

    if (empty($default_charset)) {
        $default_charset = 'UTF-8';
    } 

    if (is_array($data)) {
        return func_array_map('func_htmlspecialchars', $data);
    }

    return htmlspecialchars($data, ENT_QUOTES, $default_charset);
} // }}}

/*
 * Try to include module config.php if possible to define module sql_tbls
 * Do not use this function before common cycle by $active_modules in init.php due to new schema module initialization (include_func/include_init)
*/
function func_is_defined_module_sql_tbl($module_name, $table)
{
    global $sql_tbl, $xcart_dir, $smarty, $mail_smarty;

    if (empty($module_name))
        return FALSE;

    if (isset($sql_tbl[$table]))
        return TRUE;

    $is_module_exists = (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[modules] WHERE module_name='$module_name'") > 0);        

    if ($is_module_exists && !isset($sql_tbl[$table])) {
        $cannot_included_modules = array(
            'XAuth' => array('xauth_user_ids'),
        );

        if (isset($cannot_included_modules[$module_name])) {
            foreach ($cannot_included_modules[$module_name] as $_table) {
                $sql_tbl[$_table] = XC_TBL_PREFIX . $_table;
            }

        } elseif (is_readable($xcart_dir . "/modules/$module_name/config.php")) {
            include_once $xcart_dir . "/modules/$module_name/config.php";
        } else {
            $is_module_exists = false;
        }
    }

    return $is_module_exists && isset($sql_tbl[$table]);

}

/**
 * Call event
 *
 * @param string $event Event name
 *
 * @return mixed
 * @see    ____func_see____
 */
function func_call_event($event) {
    $events = func_events_storage();

    $result = null;
    if (isset($events[$event])) {
        $args = func_get_args();
        array_shift($args);
        $result = TRUE;
        foreach ($events[$event] as $callback) {
            if ($callback) {
                $r = call_user_func_array($callback, $args);
                assert('$r !== FALSE /* '.__FUNCTION__.': Some handler returns FALSE on the event*/');
                $result = $r;
            }
        }
    }

    return $result;
}

/**
 * Add event listener
 *
 * @param string   $event    Event name
 * @param callback $callback Event listener
 *
 * @return boolean|integer
 * @see    ____func_see____
 */
function func_add_event_listener($event, $callback) {
    $events = func_events_storage();

    if (!isset($events[$event])) {
        $events[$event] = array();
    }

    if (is_callable($callback)) {
        $idx = count($events[$event]);
        $events[$event][] = $callback;
        func_events_storage($events);

    } else {
        $idx = false;
    }

    return $idx;
}

/**
 * Remvoe evenet listener
 *
 * @param string  $event Event name
 * @param integer $idx   Listener index
 *
 * @return string
 * @see    ____func_see____
 */
function func_remove_event_listener($event, $idx) {
    $events = func_events_storage();

    if (isset($events[$event]) && isset($events[$event][$idx])) {
        $events[$event][$idx] = null;
        func_events_storage($events);
        $result = true;

    } else {
        $result = false;
    }

    return $result;
}

/**
 * Store events listeners
 *
 * @param array|void $data Events listeners list
 *
 * @return array
 * @see    ____func_see____
 */
function func_events_storage($data = null)
{
    static $store = array();

    if (is_array($data)) {
        $store = $data;
    }

    return $store;
}

/**
 * Check array elements emptiness
 */
function func_array_empty($data)
{
    if (empty($data))
        return true;

    if (!is_array($data)) 
        return empty($data);

    foreach ($data as $v) { 
        if (is_array($v)) {
            if (!func_array_empty($v))
                return false;

        } elseif (!empty($v)) {
            return false;
        }
    }

    return true;
}

/*
 * Wrapper for php empty function for binary strings filled by zero. For mysql types like `sessid` binary(32)
 */
function func_binary_empty($value)
{//{{{
    return
        empty($value) ||
        (is_scalar($value) && trim(str_replace(chr(0), '', $value)) == '');
}//}}}

/*
 * Wrapper for php constant function
 */
function func_constant($constant)
{
    if (defined($constant))
        return constant($constant);
    else       
        return false;
}

/*
 * Wrapper for php define function
 */
function func_define($name, $value, $case_insensitive = false) {//{{{
    if (!defined($name)) {
        return define($name, $value, $case_insensitive);
    } else {
        return false;
    }
}//}}}

/**
 * Stores postponed events in session
 *
 * @param array $data Postponed events list
 *
 * @return array
 * @see    ____func_see____
 */
function func_postponed_events_storage($data = null)
{
    x_session_register('postponed_events_storage');
    global $postponed_events_storage;

    if (!is_null($data))
        $postponed_events_storage = $data;

    if (!is_array($postponed_events_storage))
        $postponed_events_storage = array();

    return $postponed_events_storage;
}

/**
 * Postpone event to be triggered on next page load
 *
 * @param string $event Event name
 *
 * @return void
 * @see    ____func_see____
 */
function func_postpone_event($event) {
    $args = func_get_args();
    array_shift($args);

    $events = func_postponed_events_storage();
    $events[] = array('event' => $event, 'args' => $args);

    if (!empty($events)) {
        func_postponed_events_storage($events);
    }
}

/**
 * Trigger all previously postponed events
 *
 * @return void
 * @see    ____func_see____
 */
function func_trigger_postponed_events()
{
    $events = func_postponed_events_storage();

    foreach ($events as $e) {
        call_user_func_array('func_call_event', array_merge(array($e['event']), $e['args']));
    }

    func_postponed_events_storage(array());
}

/*
 * Assign smarty vars from global
 */
function func_assign_smarty_vars($check_vars)
{
    global $smarty;

    if (!is_array($check_vars))
        return false;

    $template_vars = $smarty->getTemplateVars();    
    foreach ($check_vars as $global_var) {
        global $$global_var;

        if (
            isset($$global_var)
            && !isset($template_vars[$global_var])
        ) {
            $smarty->assignByRef($global_var, $$global_var);
        }
    }

    return true;
}

/**
 * Detect whether a string was ISO-8859-1 compatible or not
 */
function func_is_latin1($str)
{
    return (preg_match("/^[\\x00-\\xFF]*$/u", $str) === 1);
}

/**
 * Get HTTP_RAW_POST_DATA independently on always_populate_raw_post_data setting
 */
function func_get_raw_post_data()
{
    global $HTTP_RAW_POST_DATA;

    if (!isset($HTTP_RAW_POST_DATA)) {
        $HTTP_RAW_POST_DATA = file_get_contents("php://input");
        if (empty($HTTP_RAW_POST_DATA))
            unset($HTTP_RAW_POST_DATA);
    }

    return @$HTTP_RAW_POST_DATA;
}

/**
 * Wrapper for strtotime
 */
function func_strtotime($time, $now = 0)
{
    // Unix Timestamp is not allowed, whereas YYYYMMDD is correct format
    // Dates before 1973-03-03 12:46:40 is not supported by the func_strtotime function
    if (preg_match('/^[0-9]{9,}$/s', $time)) 
        return false;

    if (!empty($now))
        $res = strtotime($time, $now);
    else        
        $res = strtotime($time);


    if (
        $res === false
        || $res < 0
    ) {
        return false;
    } else {
        return $res;
    }       
}

/**
 * Wrapper for strlen to support UTF8 strings
 */
function func_strlen($str, $encoding = '')
{
    global $default_charset;

    if (function_exists('mb_strlen')) {
        if (empty($encoding)) {
            $encoding = $default_charset;
        }

        if (empty($encoding)) {
            assert( 'false /* '.__FUNCTION__.': default_charset is not set */');
            $encoding = 'UTF-8';
        }

        return mb_strlen($str, $encoding);
    } else {
        return strlen($str);
    }

}

/**
 * Wrapper for substr to support UTF8 strings
 */
function func_substr()
{
    global $default_charset;

    $args = func_get_args();

    if (function_exists('mb_substr')) {
        if (
            !isset($args[3])
            && isset($args[2])
        ) {
            $args[3] = $default_charset;
        }

        return call_user_func_array('mb_substr', $args);
    } else {
        return call_user_func_array('substr', $args);
    }

}

function func_set_lng_current($lng_table, $lng)
{
    global $sql_tbl;

    if (empty($lng))
        return false;

    $sql_tbl[$lng_table . 'current'] = $sql_tbl[$lng_table . $lng];
    return true;
}

function func_validate_language_code($lng)
{
    global $all_languages, $shop_language;

    if (!empty($all_languages)) {
        if (isset($all_languages[$lng])) 
            return $lng;

        $codes = array_keys($all_languages);
        if (empty($shop_language))
            return $codes[0];
        else            
            return $shop_language;
    }            

    return $lng;
}

if (!function_exists('func_microtime')) {
    function func_microtime() { // {{{
        list( $usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    } // }}}
}

function func_get_internal_banners($type)
{
    global $sql_tbl;

    if (empty($type)) {
        return;
    }

    $return = func_query("SELECT * FROM $sql_tbl[internal_banners] WHERE type='$type'");
    return $return;
}

function func_get_securable_current_location() { //{{{

    global $current_location, $https_location, $config;

    if ($config['Security']['use_https_login'] == 'Y') {
        return $https_location;
    } else {
        return $current_location;
    }

} //}}}

class XCRobots {
    public static function getRobotsSignatures() { // {{{
        $_ua = array (
            'X-Cart info'    => array(
                'X-Cart info',
                'X-Cart Catalog Generator',
            ),
            'Google'        => array(
                'Googlebot',
                'Mediapartners-Google',
                'Googlebot-Mobile',
                'Googlebot-Image',
                'Adsbot-Google',
            ),
            'Yahoo'            => array(
                'Slurp',
                'YahooSeeker',
            ),
            'Microsoft'        => array(
                'MSNBot',
                'MSNBot-Media',
                'MSNBot-NewsBlogs',
                'MSNBot-Products',
                'MSNBot-Academic',
                'bingbot',
                'adidxbot',
            ),
            'Ask'            => array(
                'Teoma',
            ),
            'Baidu'          => array(
                'baiduspider',
            ),
            'dotnetdotcom.org' => array(
                'dotbot',
            ),
            'Qihoo' => array(
                'Qihoo',
            ),
            'Tencent' => array(
                'Sosospider',
            ),
            'Entireweb.com' => array(
                'Speedy Spider',
            ),
            'WuKong' => array(
                'WukongSpider',
            ),
            'NHN Corporation' => array(
                'Yeti',
            ),
            'NetEase Inc' => array(
                'YodaoBot',
            ),
        );
        return $_ua;
    } // }}}

    public static function isDefinedRobotVars() { // {{{
        global $HTTP_USER_AGENT, $is_robot;

        x_session_register('is_robot');
        return (
            empty($HTTP_USER_AGENT)
            || defined('IS_ROBOT')
            || !empty($is_robot)
        );

    } // }}}

    public static function getRobotName() { // {{{
        global $HTTP_USER_AGENT;
        static $res;

        if (isset($res)) {
            return $res;
        }

        $l_user_agent = $HTTP_USER_AGENT;
        $ua = self::getRobotsSignatures();

        foreach ($ua as $k => $v) {

            foreach ($v as $u) {

                if (stristr($l_user_agent, $u) !== FALSE) {
                    $res = $k;
                    return $k;
                }

            }

        }

        $res = '';
        return $res;
    } // }}}

    public static function isExternalRobot() { // {{{
        if (!defined('IS_ROBOT'))
            return FALSE;

        $robot = defined('ROBOT') ? constant('ROBOT') : '';

        $is_external_robot = strpos($robot, 'X-Cart') === FALSE;

        return $is_external_robot;
    } // }}}
}

?>
