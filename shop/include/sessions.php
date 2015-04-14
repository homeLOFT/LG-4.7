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
 * Sessions-related functionality
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    a71790f75ac47004093334e62598c7d47a9248f1, v186 (xcart_4_7_0), 2015-02-24 13:50:26, sessions.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

/**
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 * DO NOT CHANGE ANYTHING BELOW THIS LINE UNLESS
 * YOU REALLY KNOW WHAT YOU ARE DOING
 * !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
 */

if (defined('XCART_SESSION_START'))
    return;

define('XCART_SESSION_START', 1);

/**
 * Session unknown id limit
 */
define('X_SESSION_UNKNOWN_LIMIT', 3);

/**
 * Session duration (in seconds) for pos operator
 */
define('XC_POS_OPERATOR_SESSION_LENGTH', 39600);

/**
 * PHP build-in sessions tuning (for type '1' & '2')
 */

// PHP 4.3.0 and higher allow to turn off trans-sid using this command:
ini_set('url_rewriter.tags','');
// Let's garbage collection will occurs more frequently
ini_set('session.gc_probability',90);
ini_set('session.gc_divisor',100); // for PHP >= 4.3.0
ini_set('session.use_cookies', false);

/**
 * Anti cache block
 */

if (defined('SET_EXPIRE')) {
    header("Expires: ".gmdate("D, d M Y H:i:s", constant('SET_EXPIRE'))." GMT");
} else {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
}

header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

if (defined('SET_EXPIRE')) {
    header("Cache-Control: public");
}
elseif ($HTTPS) {
    header("Cache-Control: private, must-revalidate");
}
else {
    header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0");
    header("Pragma: no-cache");
}

/**
 * P3P header
 */
if (!empty($config['Security']['p3p_compact_policy']))
    header("P3P: " . (!empty($config['Security']['p3p_policyref']) ? "policyref=\"{$config['Security']['p3p_policyref']}\", " : "") . "CP=\"{$config['Security']['p3p_compact_policy']}\"");

if (defined('DO_NOT_START_SESSION') && !defined('DEMO_MODE')) {

    return;

}

/**
 * Get session id
 */
if (isset($_POST[$XCART_SESSION_NAME])) {

    $session_source = 'P';

    $XCARTSESSID = $_POST[$XCART_SESSION_NAME];

} elseif (isset($_GET[$XCART_SESSION_NAME])) {

    $session_source = 'G';

    $XCARTSESSID = $_GET[$XCART_SESSION_NAME];

} elseif (isset($_COOKIE[$XCART_SESSION_NAME])) {

    $session_source = 'C';

    $XCARTSESSID = $_COOKIE[$XCART_SESSION_NAME];

} else {

    $session_source = false;

    $XCARTSESSID = false;

}

$md5_REMOTE_ADDR = md5($REMOTE_ADDR);

if (func_session_use_history()) {//{{{

    $session_host = empty($HTTP_HOST) ? "" : $HTTP_HOST;

    $is_session_exists = !empty($XCARTSESSID) && func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[sessions_data] WHERE sessid = '$XCARTSESSID'") > 0;

    // Remembers xid
    if (!empty($XCARTSESSID) && !$is_session_exists) {

        if ($session_source == 'C') {

            // Remembers xid if a user goes to old host
            $remember_xid = func_query_first_cell("SELECT dest_xid FROM $sql_tbl[session_history] WHERE ip = '$md5_REMOTE_ADDR' AND host = '$session_host' AND xid = '$XCARTSESSID'");

            if (
                $remember_xid
                && !func_binary_empty($remember_xid)
            ) {
                $XCARTSESSID = $remember_xid;
                $session_source = 'R';
            }

        } elseif (
            isset($_COOKIE[$XCART_SESSION_NAME])
            && in_array($session_source, array('G', 'P'))
            && func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[sessions_data] WHERE sessid = '" . $_COOKIE[$XCART_SESSION_NAME] . "'")
        ) {
            // Remembers xid if a user goes to cached page with old xid (form / link)
            $XCARTSESSID = $_COOKIE[$XCART_SESSION_NAME];
            $session_source = 'R';
        }
    }
}//}}}

$init_xid = $XCARTSESSID;
x_session_start($XCARTSESSID);

register_shutdown_function('x_session_finish');

// Save current xid for current host
x_session_register('is_robot');

if (empty($is_robot)) {
    $_robot = XCRobots::getRobotName();
    $_is_robot = !empty($_robot);
} else {
    $_is_robot = ($is_robot == 'Y');
}

if (1
    && !empty($HTTP_HOST)
    && !$_is_robot
    && func_session_use_history()
    && func_is_valid_ip($REMOTE_ADDR)
) {//{{{
    $old_xid = func_query_first("SELECT xid, dest_xid FROM $sql_tbl[session_history] WHERE ip = '$md5_REMOTE_ADDR' AND host = '$session_host'");

    if (empty($old_xid)) {

        db_query("REPLACE INTO $sql_tbl[session_history] (`ip`, `host`, `xid`, `dest_xid`) VALUES ('$md5_REMOTE_ADDR', '$session_host', '$XCARTSESSID', '')");

    } elseif(
        $old_xid['xid'] != $XCARTSESSID
        || !func_binary_empty($old_xid['dest_xid'])
    ) {

        func_array2update(
            'session_history',
            array(
                'xid' => $XCARTSESSID,
                'dest_xid' => '',
            ),
            "ip = '$md5_REMOTE_ADDR' AND host = '$session_host'"
        );
    }

    // Update destination xid for other hosts
    if (
        !empty($init_xid)
        && $init_xid != $XCARTSESSID
    ) {
        db_query("UPDATE $sql_tbl[session_history] SET dest_xid = '$XCARTSESSID' WHERE ip = '$md5_REMOTE_ADDR' AND host != '$session_host' AND (xid = '$init_xid' OR dest_xid = '$init_xid')");
    }
}//}}}

/**
 * Remove xid from URL
 */
if (TRUE
    && !empty($REQUEST_METHOD)
    && $REQUEST_METHOD == 'GET'
    && !empty($QUERY_STRING)
    && preg_match("/(?:^|\&)" . preg_quote($XCART_SESSION_NAME, '/') . "=[\d\w]+/", $QUERY_STRING)
) {//{{{
    x_session_register('xid_remove_try', true);

    if (
        $xid_remove_try
        || !empty($_COOKIE)
    ) {

        $xid_remove_try = false;

        $qs = func_qs_remove($QUERY_STRING, $XCART_SESSION_NAME);

        func_header_location($php_url['url'] . (empty($qs) ? "" : ("?" . $qs)), true, 302, true);

    }

    $xid_remove_try = true;
}//}}}

if (defined('NEW_SESSION')) {

    x_session_register('is_new_session', true);

} elseif (x_session_is_registered('is_new_session')) {

    x_session_unregister('is_new_session');

}

#################################################################
//   FUNCTIONS
#################################################################

/**
 * Start session
 */
function x_session_start($sessid = '') { // {{{
    global $XCART_SESSION_VARS, $XCART_SESSION_NAME, $XCARTSESSID, $XCART_SESSION_EXPIRY;
    global $sql_tbl, $config;
    global $top_message;

    // $sessid should contain only '0'..'9' or 'a'..'z' or 'A'..'Z'
    if (
        !is_string($sessid) 
        || strlen($sessid) != 32 //must be md5 value
        || preg_match('/[^0-9a-z]/Ssi', $sessid)
    ) {
        $sessid = '';
    }

    // Always generate unique id for new sessions
    $XCART_SESSION_VARS = array();
    $l = isset($_SERVER['REMOTE_PORT']) ? $_SERVER['REMOTE_PORT'] : 0;
    list($usec, $sec) = explode(' ', microtime());
    srand((float) $sec + ((float) $usec * 1000000) + (float)$l);

    // Delete expired sessions
    if (!mt_rand(0, 60)) { // {{{
        // For performance purposes use mt_rand
        $delete_ids = func_query_column("SELECT sessid FROM $sql_tbl[sessions_data] WHERE expiry < '" . XC_TIME . "'");
        if (!empty($delete_ids)) {
            db_query("DELETE FROM $sql_tbl[sessions_data] WHERE expiry < '" . XC_TIME . "'");
            func_delete_session_related_data($delete_ids);
        }
    } //}}}

    if (empty($sessid)) {
        $sessid = x_session_generateid();
        $sess_data = array();
    } else {
        $sess_data = func_query_first("SELECT * FROM $sql_tbl[sessions_data] WHERE sessid='$sessid'");

        if (empty($sess_data)) {
            // Change $sessid to new for security reason
            $sessid = x_session_generateid();
        }
    }

    if ($sess_data) {
        $XCART_SESSION_VARS = x_session_unserialize($sess_data['data']);
        // Enable only for admin and provider areas because does not work correctly in the IE 8 browser in the customer area.
        if (
            defined('AREA_TYPE')
            && in_array(AREA_TYPE, array('A', 'P'))
            && !func_skip_fingerprint_check()
            && $sess_data['signature'] != x_session_get_fg($sessid, $sess_data['start'], $sess_data['expiry'])
        ) {
            assert('FALSE /* '.__FUNCTION__.': Session is dropped, check if you use dynamic IPs */');
            // Check session by fingerprint

            // Some users type 'FALSE' as value instead of FALSE
            if (
                XC_TIME - $sess_data['start'] < SECONDS_PER_MIN*40
                && XCSecurity::PROTECT_XID_BY_IP
                && XCSecurity::PROTECT_XID_BY_IP !== 'FALSE'
            ) {
                // Previous session has timelife < 40 min
                $show_warning_about_ip = TRUE;
            }

            $sess_data = FALSE;
            db_query("DELETE FROM $sql_tbl[sessions_data] WHERE sessid='$sessid'");
            $sessid = x_session_generateid();
        } elseif (
            isset($XCART_SESSION_VARS['_session_force_regenerate'])
            || func_check_regen_sess_condition()
        ) {
            // Change session
            unset($XCART_SESSION_VARS['_session_force_regenerate']);
            $new_sessid = x_session_generateid();
            func_session_change($sessid, $new_sessid);

            $new_signature = x_session_get_fg($new_sessid, $sess_data['start'], $sess_data['expiry']);
            db_query("UPDATE $sql_tbl[sessions_data] SET sessid='$new_sessid', signature='$new_signature' WHERE sessid='$sessid'");
            $sessid = $new_sessid;
        }
    }

    if (!empty($sess_data['data'])) {
        $xcart_session_length_add_to_pos_operator = func_get_pos_operator_session_duration($sess_data['data']);
    } else {
        $xcart_session_length_add_to_pos_operator = 0;
    }

    assert('defined("XCART_SESSION_LENGTH") /* XCART_SESSION_LENGTH is not defined */');
    $expiry_time = XC_TIME + XCART_SESSION_LENGTH + $xcart_session_length_add_to_pos_operator;
    if (empty($sess_data)) {
        if (!defined('NEW_SESSION')) {
            define('NEW_SESSION', TRUE);
        }

        $XCART_SESSION_VARS = array();

        func_array2insert(
            'sessions_data',
            array(
                'sessid' => $sessid,
                'start'  => XC_TIME,
                'expiry' => $expiry_time,
                'data'   => addslashes(serialize($XCART_SESSION_VARS)),
                'signature' => x_session_get_fg($sessid, XC_TIME, $expiry_time),
            ),
            TRUE
        );

        if (!empty($show_warning_about_ip)) {

            x_session_register('top_message');
            $top_message = array(
                'content' => func_get_langvar_by_name('txt_session_was_dropped'),
                'type' => "E"
            );
        }

    } elseif ($sess_data['expiry'] + 200 < $expiry_time) { // Performance: update expiry every 200 sec
        $new_signature = x_session_get_fg($sessid, $sess_data['start'], $expiry_time);
        db_query("UPDATE $sql_tbl[sessions_data] SET expiry='$expiry_time', signature='$new_signature' WHERE sessid='$sessid'");
    }

    $XCART_SESSION_EXPIRY = $expiry_time;
    $XCARTSESSID = $sessid;
    func_setcookie($XCART_SESSION_NAME, $XCARTSESSID);
} // }}}

/**
 * Change current session to session with specified ID
 */
function x_session_id($sessid = '', $re_register = 're_register_vars') { //{{{
    global $sql_tbl, $XCART_SESSION_VARS, $XCARTSESSID, $XCART_SESSION_UNPACKED_VARS;

    $XCART_SESSION_VARS = array();

    if ($sessid) {

        $sess_data = func_query_first("SELECT * FROM $sql_tbl[sessions_data] WHERE sessid='$sessid'");

        $XCARTSESSID = $sessid;

        if ($sess_data) {

            $XCART_SESSION_VARS = x_session_unserialize($sess_data['data']);

            if (!empty($XCART_SESSION_UNPACKED_VARS)) {

                foreach ($XCART_SESSION_UNPACKED_VARS as $var => $v) {

                    if (isset($GLOBALS[$var]))
                        unset($GLOBALS[$var]);

                    unset($XCART_SESSION_UNPACKED_VARS[$var]);

                }

            }

            $sess_registered_vars = x_session_register_calls();

            if (
                $re_register == 're_register_vars'
                && !empty($sess_registered_vars)
            ) {
                foreach ($sess_registered_vars as $var) {
                    if (
                        isset($GLOBALS[$var])
                        || !isset($XCART_SESSION_VARS[$var])
                    ) {
                        continue;
                    }
                    $GLOBALS[$var] = $XCART_SESSION_VARS[$var];
                }
            }

        } else {

            x_session_start($sessid);

        }

    } else {

        $sessid = $XCARTSESSID;

    }

    return $sessid;
}//}}}

/**
 * Cut off variable if it came from _GET, _POST or _COOKIES
 */
function check_session_var($varname) { // {{{
    return !isset($_GET[$varname]) && !isset($_POST[$varname]) && !isset($_COOKIE[$varname]);
} //}}}

/**
 * Register or return all x_session_register calls in current php static scope
 */
function x_session_register_calls($varname = '') { // {{{
    static $sess_registered_vars = array();
    
    if (empty($varname))
        return array_keys($sess_registered_vars);

    // Register registred variables 
    $sess_registered_vars[$varname] = 1;

    return true;
} //}}}

/**
 * Get session value without changing global
 */
function x_session_get_var($varname, $default = '') { // {{{
    global $XCART_SESSION_VARS;

    if (!isset($XCART_SESSION_VARS[$varname])) {
        $session_value = isset($GLOBALS[$varname]) && check_session_var($varname)
            ? $GLOBALS[$varname]
            : $default;

    } elseif (isset($GLOBALS[$varname]) && check_session_var($varname)) {
        $session_value = $GLOBALS[$varname];
    } else {
        $session_value = $XCART_SESSION_VARS[$varname];
    }

    return $session_value;
} //}}}

/**
 * Register variable XCART_SESSION_VARS array from the database
 */
function x_session_register($varname, $default = '') { // {{{
    global $XCART_SESSION_VARS, $XCART_SESSION_UNPACKED_VARS;

    if (empty($varname))
        return false;

    $XCART_SESSION_VARS[$varname] = x_session_get_var($varname, $default);

    // Unpack variable $varname from $XCART_SESSION_VARS array
    $XCART_SESSION_UNPACKED_VARS[$varname] = $XCART_SESSION_VARS[$varname];

    $GLOBALS[$varname] = $XCART_SESSION_VARS[$varname];

    x_session_register_calls($varname);
} //}}}

/**
 * Save the XCART_SESSION_VARS array in the database
 */
function x_session_save() { // {{{
    global $XCARTSESSID, $XCART_SESSION_VARS, $XCART_SESSION_UNPACKED_VARS, $sql_tbl;

    if (empty($XCARTSESSID))
        return false;
    
    $old_data = func_query_first("SELECT data, start FROM $sql_tbl[sessions_data] WHERE sessid = '$XCARTSESSID'");
    $start = $old_data['start'];
    $old_data = $old_data['data'];
    $md5_old_data = md5($old_data);

    if (!empty($old_data))
        $old_data = x_session_unserialize($old_data);

    if (!is_array($old_data)) {
        $old_data = array();
        $md5_old_data = '40cd750bba9870f18aada2478b24840a'; // md5(serialize(array()))
    }

    $varnames = func_get_args();

    if (empty($varnames))
        $varnames = is_array($XCART_SESSION_UNPACKED_VARS) ? array_keys($XCART_SESSION_UNPACKED_VARS) : array();

    $data = array();

    foreach (array_keys($XCART_SESSION_VARS) as $varname) {

        $data[$varname] = in_array($varname, $varnames)
            ? $GLOBALS[$varname]
            : @$old_data[$varname];

    }

    // Save session variables in the database
    $data = serialize($data);

    if (
        defined('BENCH')
        && constant('BENCH')
    ) {

        global $bench_max_session;

        $bench_max_session = max($bench_max_session, strlen($data));

    }

    $_is_robot = XCRobots::isExternalRobot();

    $md5_new_data = md5($data);
    if (
        !$_is_robot
        && $md5_new_data != $md5_old_data
    ) {
        $expiry_time = XC_TIME + XCART_SESSION_LENGTH + func_get_pos_operator_session_duration($data);
        $new_signature = x_session_get_fg($XCARTSESSID, $start, $expiry_time);
        db_query("UPDATE $sql_tbl[sessions_data] SET expiry='$expiry_time', signature='$new_signature', data = '".addslashes($data)."' WHERE sessid = '$XCARTSESSID'");        
    }
} //}}}

/**
 * Unregister variable $varname from $XCART_SESSION_VARS array
 */
function x_session_unregister($varname, $unset_global = false) { // {{{
    global $XCART_SESSION_VARS, $XCART_SESSION_UNPACKED_VARS;

    if (empty($varname))
        return false;

    func_unset($XCART_SESSION_VARS, $varname);

    func_unset($XCART_SESSION_UNPACKED_VARS, $varname);

    if ($unset_global) {

        func_unset($GLOBALS, $varname);

    }

} //}}}

/**
 * Find out whether a global variable $varname is registered in
 * $XCART_SESSION_VARS array
 */
function x_session_is_registered($varname) { // {{{
    global $XCART_SESSION_VARS;

    return !empty($varname) && isset($XCART_SESSION_VARS[$varname]);
} //}}}

/**
 * Generate unique session id
 */
function x_session_generateid() { // {{{
    global $sql_tbl;

    do {

        $sessid = md5(uniqid(mt_rand(), true));

    } while (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[sessions_data] WHERE sessid = '$sessid'") > 0);

    return $sessid;
} //}}}

/**
 * Get session fingerprint
 */
function x_session_get_fg($sessid, $start, $expiry_time) { // {{{
    global $HTTP_USER_AGENT, $REMOTE_ADDR, $xc_security_key_session;
    static $result = array();

    // Some users type 'FALSE' as value instead of FALSE
    if (
        XCSecurity::PROTECT_XID_BY_IP
        && XCSecurity::PROTECT_XID_BY_IP !== 'FALSE'
    ) {
        $ip = $REMOTE_ADDR;

        if (XCSecurity::PROTECT_XID_BY_IP == 'secure_mask') {
            // Remove only last octet
            $ip = preg_replace('/[\.:][^\.:]*$/s', '', $ip); 
        } else if (XCSecurity::PROTECT_XID_BY_IP == 'mask') {
            // Remove two last octets
            $ip = preg_replace('/[^\.:]*[\.:][^\.:]*$/s', '', $ip); 
        }

    } else {
        $ip = '';
    }
    $str = $HTTP_USER_AGENT . $ip . $sessid . $start . $expiry_time . $xc_security_key_session;

    if (isset($result[$str]))
        return $result[$str];

    $result[$str] = sha1($str);

    return $result[$str];
} //}}}

/**
 * Check - regenerate session or not
 */
function func_check_regen_sess_condition() { // {{{
    global $PHP_SELF, $REQUEST_METHOD;

    return $REQUEST_METHOD == 'POST' && preg_match("/login\.php/", $PHP_SELF);
} //}}}

/**
 * Check - skip or not session fingerprint checking procedure
 */
function func_skip_fingerprint_check() { // {{{
    return defined('SKIP_CHECK_SESSGION_FG');
} //}}}

/**
 * Delete session related data
 */
function func_delete_session_related_data($delete_ids) { // {{{
    global $sql_tbl;

    if (empty($delete_ids) || !is_array($delete_ids))
        return FALSE;

    $deleteSet = implode("','", $delete_ids);

    if (func_session_use_history()) {
        db_query("DELETE FROM $sql_tbl[session_history] WHERE dest_xid IN ('$deleteSet')");
        db_query("DELETE FROM $sql_tbl[session_history] WHERE xid IN ('$deleteSet')");
    }

    $tables = array(
        'packages_cache',
        'shipping_cache',
        'users_online',
    );

    if (defined('AREA_TYPE')) {
        if (in_array(constant('AREA_TYPE'), array('A', 'P'))) {
            // Erase old service array (Group editing of products functionality)
            $tables[] = 'ge_products';
            // Erase form ids
            $tables[] = 'form_ids';
            // Erase iterations
            $tables[] = 'iterations';
        } elseif (constant('AREA_TYPE') == 'C') {
            // Erase old service array (3D secure service data)
            $tables[] = 'secure3d_data';
        }
    }

    // Clear cache for TaxCloud module
    if (!empty($sql_tbl['taxcloud_cache']))
        $tables[] = 'taxcloud_cache';

    // Clear cache for Amazon_Checkout module
    if (!empty($sql_tbl['amazon_data']))
        $tables[] = 'amazon_data';

    foreach ($tables as $table) {
        assert('!empty($sql_tbl[$table]) /* '.__FUNCTION__.': table not defined */');
        $xcart_table = $sql_tbl[$table];
        db_query("DELETE FROM $xcart_table WHERE sessid IN ('$deleteSet')");
    }

    // Clear Session_unknown_sid every year
    if (!mt_rand(0, 1000)) {
        func_session_delete_expired_unknown_sid();
    }

    if (!mt_rand(0, 500)) {
        func_session_delete_expired_session_history();
    }

    return TRUE;
} // }}}

/**
 * Change session related data if session id was changed
 */
function func_session_change($old_sessid, $new_sessid) { // {{{
    global $sql_tbl;

    db_query("UPDATE $sql_tbl[form_ids] SET sessid = '$new_sessid' WHERE sessid = '$old_sessid'");
    db_query("UPDATE $sql_tbl[ge_products] SET sessid = '$new_sessid' WHERE sessid = '$old_sessid'");
    db_query("UPDATE $sql_tbl[secure3d_data] SET sessid = '$new_sessid' WHERE sessid = '$old_sessid'");
    db_query("UPDATE $sql_tbl[users_online] SET sessid = '$new_sessid' WHERE sessid = '$old_sessid'");
    db_query("UPDATE $sql_tbl[shipping_cache] SET sessid = '$new_sessid' WHERE sessid = '$old_sessid'");
    db_query("UPDATE $sql_tbl[packages_cache] SET sessid = '$new_sessid' WHERE sessid = '$old_sessid'");
    db_query("UPDATE $sql_tbl[iterations] SET sessid = '$new_sessid' WHERE sessid = '$old_sessid'");
    db_query("UPDATE $sql_tbl[cc_pp3_data] SET sessid = '$new_sessid' WHERE sessid = '$old_sessid'");

    // TaxCloud module
    if (!empty($sql_tbl['taxcloud_cache']))
        db_query("UPDATE $sql_tbl[taxcloud_cache] SET sessid = '$new_sessid' WHERE sessid = '$old_sessid'");

    // Amazon_Checkout module
    if (!empty($sql_tbl['amazon_data']))
        db_query("UPDATE $sql_tbl[amazon_data] SET sessid = '$new_sessid' WHERE sessid = '$old_sessid'");

    return true;
} //}}}

function x_session_reset() { // {{{
    global $XCART_SESSION_UNPACKED_VARS;

    $XCART_SESSION_UNPACKED_VARS = array();

    return true;
} //}}}

function x_session_finish() { // {{{
    if (!defined('XC_DISABLE_SESSION_SAVE')) {
        x_session_save();
    }
} //}}}

function func_session_process_unknown_xid($sessid) { // {{{
    global $sql_tbl, $robot;

    if (
        !isset($_SERVER['REMOTE_ADDR'])
        || !defined('X_SESSION_UNKNOWN_LIMIT')
        || defined('QUICK_START')
        || defined('ANTIBOT_SKIP_INIT')
        || intval(constant('X_SESSION_UNKNOWN_LIMIT')) <= 0
    ) {
        return false;
    }

    // Patch to HTML catalog work properly bt:84751
    if ($robot == 'X-Cart Catalog Generator')
        return false;

    $ip = $_SERVER['REMOTE_ADDR'];

    if (!func_is_valid_ip($ip))
        return false;

    $md5_ip = md5($ip);
    $cnt = intval(func_query_first_cell("SELECT cnt FROM $sql_tbl[session_unknown_sid] WHERE ip = '$md5_ip' AND sessid = '$sessid'")) + 1;
    db_query("REPLACE INTO $sql_tbl[session_unknown_sid] (`ip`, `sessid`, `cnt`, `expiry_date`) VALUES ('$md5_ip', '$sessid', '$cnt', '" . (XC_TIME+SECONDS_PER_DAY*360) . "')");
    if ($cnt > X_SESSION_UNKNOWN_LIMIT)
        define('X_ERR_UNKNOWN_SESSION_ID', true);

    return true;
} //}}}

function func_session_truncate_unknown_xid() { // {{{
    global $sql_tbl;

    if (!isset($_SERVER['REMOTE_ADDR']))
        return false;

    $ip = $_SERVER['REMOTE_ADDR'];

    if (!func_is_valid_ip($ip))
        return false;

    db_query("DELETE FROM $sql_tbl[session_unknown_sid] WHERE ip = '" . md5($ip) . "'");

    return true;
} //}}}

function x_session_unserialize(&$data) { // {{{
    $result = unserialize($data);
    assert('FALSE !== $result /* '.__FUNCTION__.': could not unserialize session data */');
    if (FALSE === $result) {
        $result = array();
    }

    return $result;
} // }}}

function func_get_pos_operator_session_duration($sess_data) { // {{{
    global $sql_tbl;

    $pos_session_duration = XC_POS_OPERATOR_SESSION_LENGTH; // add 11 hours to the "pos operator" session length

    if (
        defined('XC_IS_POS_SESSION')
        && !empty($sess_data)
    ) {
        return $pos_session_duration;
    }

    $duration = 0;

    if (
        defined('USE_SIMPLE_SESSION_INTERFACE')
        || defined('USE_SIMPLE_DB_INTERFACE')
    ) {
        // Change this query to check some cookie like !empty($COOKIE['isPOS_SystemEnabled']) on USE_SIMPLE_SESSION_INTERFACE/USE_SIMPLE_DB_INTERFACE constant usage changes
        $is_module_enabled = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[modules] WHERE module_name = 'POS_System' AND active = 'Y'") > 0;
    } else {
        $all_active_modules = func_get_active_modules();
        $is_module_enabled = !empty($all_active_modules['POS_System']);
    }

    if (
        !empty($sess_data)
        && $is_module_enabled
    ) {
        $sess_data_unserialized = x_session_unserialize($sess_data);

        if (
            isset($sess_data_unserialized['identifiers']['C']['userid'])
            && $sess_data_unserialized['identifiers']['C']['userid'] > 0
        ) {
            $userid = func_query_first_cell("SELECT c.id FROM $sql_tbl[customers] c INNER JOIN $sql_tbl[memberships] m ON c.membershipid = m.membershipid WHERE c.id = '" . $sess_data_unserialized['identifiers']['C']['userid'] . "' AND m.active = 'Y' AND m.pos_operator = 1 LIMIT 1");

            if (!empty($userid)) {
                $duration = $pos_session_duration; // add 11 hours to the "pos operator" session length
                if (!defined('XC_IS_POS_SESSION')) {
                    define('XC_IS_POS_SESSION', true);
                } else {
                    assert('FALSE /* '.__FUNCTION__.': XC_IS_POS_SESSION is defined twice */');
                }
            }
        }
    }

    return $duration;
} // }}}

function func_session_use_history() { // {{{
    global $xcart_http_host, $xcart_https_host;

    if (
        !defined('USE_SESSION_HISTORY')
        || !constant('USE_SESSION_HISTORY')
    ) {
        return false;
    }

    // use history only if domains are different
    return !isset($xcart_http_host) || !isset($xcart_https_host) || $xcart_http_host != $xcart_https_host;
} // func_session_use_history}}}

?>
