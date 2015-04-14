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
 * Backoffice functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v189 (xcart_4_7_0), 2015-02-17 23:56:28, func.backoffice.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

x_load('files');

require_once $xcart_dir . '/include/classes/class.XCSignature.php';

define('RESELLERS_MODE', true);
define('CHECK_RESELLERS_MODE', !RESELLERS_MODE);

/**
 * This function determines the files location for current user
 */
function func_get_files_location($userid = 0, $usertype = '')
{
    global $logged_userid, $single_mode, $files_dir_name, $files_dir_prefix;
    global $user_account;

    if ($userid == 0) {
        $userid = $logged_userid;
    }

    if ($usertype === '') {
        $usertype = $user_account['usertype'];
    }

    if ($single_mode || $usertype == 'A')
        return $files_dir_name;

    $files_dir = $files_dir_name . XC_DS . $files_dir_prefix . $userid;

    return $files_dir;
}

/**
 * This function updates/inserts the language variable into 'languages_alt'
 */
function func_languages_alt_insert($name, $value, $code = '') { // {{{
    global $sql_tbl, $all_languages;

    if (!is_array($all_languages))
        return FALSE;

    if (empty($code)) {

        // For empty code update/insert variables for all languages

        foreach($all_languages as $k => $v) {
            db_query("REPLACE INTO $sql_tbl[languages_alt] (code, name, value) VALUES ('$v[code]', '$name', '$value')");
        }
    }
    else {

        // For not empty $code...

        $result = FALSE;

        // Check if $code is valid

        foreach($all_languages as $k => $v) {
            if ($code == $v['code']) {
                $result = TRUE;
                break;
            }
        }

        if (!$result)
            return FALSE;

        // Update/insert variable for $code

        db_query("REPLACE INTO $sql_tbl[languages_alt] (code, name, value) VALUES ('$code', '$name', '$value')");
    }

    return TRUE;
} // }}}

/**
 * Callback function: determination of empty field
 */
function func_callback_empty($value)
{
    return strlen($value) > 0;
}

function func_disable_paypal_methods($paypal_solution, $enable=false)
{
    global $sql_tbl, $config, $active_modules;

    x_load('payment');

    $paypal_direct  = func_query_first("SELECT $sql_tbl[payment_methods].paymentid, $sql_tbl[payment_methods].active FROM $sql_tbl[payment_methods], $sql_tbl[ccprocessors] WHERE $sql_tbl[payment_methods].processor_file='ps_paypal_pro.php' AND $sql_tbl[payment_methods].processor_file=$sql_tbl[ccprocessors].processor AND $sql_tbl[payment_methods].paymentid<>$sql_tbl[ccprocessors].paymentid");
    $paypal_directid = ($paypal_direct['paymentid']) ? $paypal_direct['paymentid'] : 0;
    $paypal_expressid = func_query_first_cell("SELECT $sql_tbl[payment_methods].paymentid FROM $sql_tbl[payment_methods], $sql_tbl[ccprocessors] WHERE $sql_tbl[payment_methods].processor_file='ps_paypal_pro.php' AND $sql_tbl[payment_methods].processor_file=$sql_tbl[ccprocessors].processor AND $sql_tbl[payment_methods].paymentid=$sql_tbl[ccprocessors].paymentid");
    $paypal_bmlid = func_query_first_cell("SELECT $sql_tbl[payment_methods].paymentid FROM $sql_tbl[payment_methods], $sql_tbl[ccprocessors] WHERE $sql_tbl[payment_methods].processor_file='ps_paypal_bml.php' AND $sql_tbl[payment_methods].processor_file=$sql_tbl[ccprocessors].processor AND $sql_tbl[payment_methods].paymentid=$sql_tbl[ccprocessors].paymentid");
    $paypalid = func_query_first_cell("SELECT $sql_tbl[payment_methods].paymentid FROM $sql_tbl[payment_methods], $sql_tbl[ccprocessors] WHERE $sql_tbl[payment_methods].processor_file='ps_paypal.php' AND $sql_tbl[payment_methods].processor_file=$sql_tbl[ccprocessors].processor AND $sql_tbl[payment_methods].paymentid=$sql_tbl[ccprocessors].paymentid");
    $paypal_advancedid = func_query_first_cell("SELECT $sql_tbl[payment_methods].paymentid FROM $sql_tbl[payment_methods], $sql_tbl[ccprocessors] WHERE $sql_tbl[payment_methods].processor_file='ps_paypal_advanced.php' AND $sql_tbl[payment_methods].processor_file=$sql_tbl[ccprocessors].processor AND $sql_tbl[payment_methods].paymentid=$sql_tbl[ccprocessors].paymentid");
    $paypal_payflowlinkid = func_query_first_cell("SELECT $sql_tbl[payment_methods].paymentid FROM $sql_tbl[payment_methods], $sql_tbl[ccprocessors] WHERE $sql_tbl[payment_methods].processor_file='ps_paypal_payflowlink.php' AND $sql_tbl[payment_methods].processor_file=$sql_tbl[ccprocessors].processor AND $sql_tbl[payment_methods].paymentid=$sql_tbl[ccprocessors].paymentid");
    $paypal_pro_hostedid = func_query_first_cell("SELECT $sql_tbl[payment_methods].paymentid FROM $sql_tbl[payment_methods], $sql_tbl[ccprocessors] WHERE $sql_tbl[payment_methods].processor_file='ps_paypal_pro_hosted.php' AND $sql_tbl[payment_methods].processor_file=$sql_tbl[ccprocessors].processor AND $sql_tbl[payment_methods].paymentid=$sql_tbl[ccprocessors].paymentid");

    $disable_methods = array();
    $enable_methods = array();

    $show_paypal_methods = func_check_payment_country('ps_paypal.php', $config['paypal_country']);

    switch ($paypal_solution) {
        case 'ipn':
            $disable_methods = array($paypal_expressid, $paypal_directid, $paypal_advancedid, $paypal_payflowlinkid, $paypal_pro_hostedid);
            if ($show_paypal_methods['ipn'] == 'Y') {
                $enable_methods[] = $paypalid;
            } else {
                $disable_methods[] = $paypalid;
            }
            break;

        case 'pro':
        case 'uk':
            $disable_methods[] = $paypalid;
            $disable_methods[] = $paypal_advancedid;
            $disable_methods[] = $paypal_payflowlinkid;
            $disable_methods[] = $paypal_pro_hostedid;
            $enable_methods = array($paypal_expressid, $paypal_directid);
            if (!$enable && $paypal_direct['active'] != 'Y') {
                $disable_methods[] = $paypal_expressid;
                $disable_methods[] = $paypal_directid;
            }
            break;

        case 'pro_hosted':
            $disable_methods = array($paypal_directid, $paypalid, $paypal_payflowlinkid, $paypal_advancedid);
            $enable_methods = array($paypal_expressid, $paypal_pro_hostedid);
            if ($show_paypal_methods['pro_hosted'] != 'Y') {
                $disable_methods[] = $paypal_expressid;
                $disable_methods[] = $paypal_pro_hostedid;
            }
            break;

        case 'express':
            $disable_methods = array($paypalid, $paypal_directid, $paypal_advancedid, $paypal_payflowlinkid, $paypal_pro_hostedid);
            $enable_methods[] = $paypal_expressid;
            if ($show_paypal_methods['express'] == 'Y') {
                $enable_methods[] = $paypal_expressid;
            } else {
                $disable_methods[] = $paypal_expressid;
            }
            break;
        case 'advanced':
            $disable_methods = array($paypal_directid, $paypalid, $paypal_payflowlinkid, $paypal_pro_hostedid);
            if ($show_paypal_methods['advanced'] == 'Y') {
                $enable_methods[] = $paypal_advancedid;
                $enable_methods[] = $paypal_expressid;
            } else {
                $disable_methods[] = $paypal_advancedid;
                $disable_methods[] = $paypal_expressid;
            }
            break;
        case 'payflowlink':
            $disable_methods = array($paypal_directid, $paypalid, $paypal_advancedid, $paypal_pro_hostedid);
            if ($show_paypal_methods['payflowlink'] == 'Y') {
                $enable_methods[] = $paypal_payflowlinkid;
                $enable_methods[] = $paypal_expressid;
            } else {
                $disable_methods[] = $paypal_payflowlinkid;
                $disable_methods[] = $paypal_expressid;
            }
            if ($show_paypal_methods['express'] == 'Y') {
                $enable_methods[] = $paypal_expressid;
            } else {
                $disable_methods[] = $paypal_expressid;
            }
            break;
    }

    if (!func_array_empty($disable_methods)) {
        func_array2update('payment_methods', array('active' => 'N'), "paymentid IN ('".implode("','", $disable_methods)."')");
    }

    if ($enable && !func_array_empty($enable_methods)) {
        func_array2update('payment_methods', array('active' => 'Y'), "paymentid IN ('".implode("','", $enable_methods)."')");
    }

    if (in_array($paypal_solution, array('uk', 'pro', 'pro_hosted')) && !empty($paypal_expressid)) {
        $active = func_query_first_cell("SELECT active FROM $sql_tbl[payment_methods] WHERE paymentid = '". (($paypal_solution == 'pro_hosted') ? $paypal_pro_hostedid : $paypal_directid) . "'");
        func_array2update('payment_methods', array('active' => $active), "paymentid = '$paypal_expressid'");
    }
    if (in_array($paypal_solution, array('advanced', 'payflowlink')) && !empty($paypal_expressid)) {
        $active = func_query_first_cell("SELECT active FROM $sql_tbl[payment_methods] WHERE paymentid = " . ($paypal_solution == 'advanced' ? $paypal_advancedid : $paypal_payflowlinkid) );
        func_array2update('payment_methods', array('active' => $active), "paymentid = '$paypal_expressid'");
    }
    
    if (!empty($paypal_expressid) || $paypal_solution == 'ipn') {
        $active = func_query_first_cell("SELECT active FROM $sql_tbl[payment_methods] WHERE paymentid = '" . (($paypal_solution == 'ipn') ? $paypalid : $paypal_expressid) . "'");
        $active = ($active == 'Y' && !empty($active_modules['Bill_Me_Later'])) ? 'Y' : 'N';
        func_array2update('payment_methods', array('active' => $active), "paymentid = '$paypal_bmlid'");
    }

}

/**
 * This function inserts the zone elements
 * country (C), state (S), county (G), city (T), zip code (Z), address (A)
 */
function func_insert_zone_element($zoneid, $field_type, $zone_elements)
{
    global $sql_tbl;

    db_query("DELETE FROM $sql_tbl[zone_element] WHERE zoneid='$zoneid' AND field_type='$field_type'");
    if (!empty($zone_elements) && is_array($zone_elements)) {
        foreach ($zone_elements as $k=>$v) {
            $v = trim($v);
            if (empty($v)) continue;

            db_query("REPLACE INTO $sql_tbl[zone_element] (zoneid, field, field_type) VALUES ('$zoneid', '$v', '$field_type')");
        }
    }
}

function func_array_merge_ext()
{
    $vars = func_get_args();

    if (!is_array($vars) || empty($vars))
        return array();

    foreach($vars as $k => $v) {
        if (!is_array($v) || empty($v))
            unset($vars[$k]);
    }

    if (empty($vars))
        return array();

    $vars = array_values($vars);
    $orig = array_shift($vars);
    foreach ($vars as $var) {
        foreach ($var as $k => $v) {
            if (isset($orig[$k]) && is_array($orig[$k]) && is_array($v)) {
                $orig[$k] = func_array_merge_ext($orig[$k], $v);
            }
            else {
                $orig[$k] = $v;
            }
        }
    }

    return $orig;
}

function func_get_var_upgrade_dir() {
    global $xcart_dir;

    return $xcart_dir . '/var/upgrade';
}

/**
 * Get information about directory:
 *  - how many files does directory contain
 *  - what size does directory have
 */
function func_get_dir_status($directory, $hr = false, $rec_level = 0)
{

    $result = array(
        'files' => 0,
        'size'=>0,
        'is_large' => false
    );

    if ($rec_level++ > MAX_FUNC_NESTING_LEVEL) {
        $result['is_large'] = true;
        return $result;
    }

    $dir = @opendir($directory);
    if (!$dir) return $result;

    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') continue;

        $path = $directory.XC_DS.$file;

        if (is_file($path)) {
            $result['files']++;
            $result['size'] += filesize($path);
        } else {
            $temp = func_get_dir_status($path, false, $rec_level);
            $result['files'] += $temp['files'];
            $result['size'] += $temp['size'];
            if ($temp['is_large']) $result['is_large'] = true;
        }
    }

    closedir($dir);

    // human readable form
    if ($hr) {

        $powers = array('kb' => 1, 'Mb' => 2, 'Gb' => 3);

        $hr_size = '';

        foreach (array_reverse($powers) as $name => $power) {
            if (($size = $result['size']/pow(1024, $power)) > 0.9) {
                $hr_size = round($size)." ".$name;
                break;
            }
        }

        if (empty($hr_size)) {
            $hr_size = $result['size']." bytes";
        }

        $result['size'] = $hr_size;
        $result['dir'] = $directory;

    }

    return $result;
}

function func_get_configuration_styles() {

    global $active_modules, $used_option_styles;
    x_session_register('used_option_styles', array());

    if (
        empty($_GET['option'])
        || !isset($active_modules[$_GET['option']])
        || isset($used_option_styles[$_GET['option']])
    ) {
        return '';
    }

    $used_option_styles[$_GET['option']] = 1;

    // Cannot be removed as called from func_display where x_session_finish is disabled
    x_session_save('used_option_styles');

    return $_GET['option'];
}


/**
 * This function updates the field 'display_states' of xcart_countries table
 * depending on existing states information
 */
function func_update_country_states ($country, $all_countries=false)
{
    global $sql_tbl;

    $countries = array();

    if (empty($country) && !$all_countries)
        return;

    if (!$all_countries) {

        if (is_array($country))
            $countries = $country;
        elseif (!empty($country))
            $countries[] = $country;

    }

    $countries_with_states = func_query_column("SELECT DISTINCT(country_code) FROM $sql_tbl[states] WHERE 1 " . (!empty($countries) ? " AND country_code IN ('".implode("','", $countries)."')" : ""));

    db_query("UPDATE $sql_tbl[countries] SET display_states='N' WHERE 1" . (!empty($countries) ? " AND code IN ('".implode("','", $countries)."')" : ""));

    if (!empty($countries_with_states))
        db_query("UPDATE $sql_tbl[countries] SET display_states='Y' WHERE code IN ('" . implode("','", $countries_with_states) . "')");

}

/**
 * Display time period
 */
function func_display_time_period($t)
{

    if (empty($t))
        return "0:0:0";

    $ms = $t - floor($t);
    $ms = $ms > 0 ? round($ms*1000, 0) : 0;

    $t = floor($t);
    $s = $t % 60;

    $t = floor($t / 60);
    $m = $t > 0 ? $t % 60 : 0;

    if ($t > 0)
        $t = floor($t / 60);

    $h = $t > 0 ? $t % 24 : 0;

    return $h.":".$m.":".$s;

}

/**
 * Detect max data size for inserting to DB
 */
function func_get_max_upload_size()
{
    global $sql_max_allowed_packet;

    $upload_max_filesize = func_upload_max_filesize();

    if ($sql_max_allowed_packet && $sql_max_allowed_packet < $upload_max_filesize) {
        $upload_max_filesize = $sql_max_allowed_packet-1024;
    }

    $upload_max_filesize = func_convert_to_megabyte($upload_max_filesize);

    return $upload_max_filesize;
}

/**
 * This function updates the cache of zone elements
 * Format: C2-S3-G4-T1-Z5-A2
 */
function func_zone_cache_update ($zoneid)
{
    global $sql_tbl;

    $result = '';

    $data = func_query("SELECT field_type, count(*) as count FROM $sql_tbl[zone_element] WHERE zoneid='$zoneid' GROUP BY field_type ORDER BY NULL");

    if (!empty($data)) {
        $result_array = array();
        for ($i = 0; $i < count($data); $i++) {
            $result_array[] = $data[$i]['field_type'].$data[$i]['count'];
        }
        $result = implode("-", $result_array);
    }

    db_query("UPDATE $sql_tbl[zones] SET zone_cache='$result' WHERE zoneid='$zoneid'");
}

/**
 * Change shop secure keys in memory
 */
function func_change_shop_secure_keys($keys) { // {{{
    global $xc_security_key_session, $xc_security_key_config, $xc_security_key_general;

    $xc_security_key_session = $keys['xc_security_key_session'];
    $xc_security_key_config = $keys['xc_security_key_config'];
    $xc_security_key_general = $keys['xc_security_key_general'];

    return true;
} // }}}

/**
 * Check - allow or not remote IP address for admin area
 */
function func_check_allow_admin_ip($ip = false)
{
    global $config, $REMOTE_ADDR;

    if (!isset($config['allowed_ips']) || empty($config['allowed_ips']))
        return false;

    if (!is_array($config['allowed_ips']))
        $config['allowed_ips'] = func_array_map("trim", explode(",", $config['allowed_ips']));

    if (empty($config['allowed_ips']))
        return false;

    return func_compare_ip(
        empty($ip) ? $REMOTE_ADDR : $ip,
        $config['allowed_ips']
    );

}

function func_delete_expired_ip_register_codes() { // {{{
    global $config, $sql_tbl;

    if (
        isset($config['ip_register_codes'])
        && !empty($config['ip_register_codes'])
    ) {

        if (!is_array($config['ip_register_codes']))
            $config['ip_register_codes'] = unserialize($config['ip_register_codes']);

        if (is_array($config['ip_register_codes'])) {

            $changed = false;

            foreach($config['ip_register_codes'] as $k => $v) {

                if ($v['expiry'] < XC_TIME) {

                    $changed = true;

                    func_unset($config['ip_register_codes'], $k);

                }

            }

            if ($changed) {
                $old_configs = func_query("SELECT " . XCConfigSignature::getSignedFields() . " FROM $sql_tbl[config] WHERE name IN ('ip_register_codes')");
                $res_changed = true;

                func_array2insert(
                    'config',
                    array(
                        'name'  => 'ip_register_codes',
                        'value' => addslashes(serialize($config['ip_register_codes'])),
                    ),
                    true
                );

                func_secure_update_config_signatures($old_configs);

            }

        }
    }

    return isset($res_changed);
} // }}}

/**
 * Send to shop_administrator/administrator message with IP address registration link
 */
function func_send_admin_ip_reg($mode = 'C', $local_login = false, $user_data = array()) { // {{{
    global $login, $config, $xcart_catalogs, $REMOTE_ADDR, $mail_smarty, $logged_userid, $sql_tbl;

    if (empty($local_login))
        $local_login = $login;

    if (!isset($config['ip_register_codes']) || empty($config['ip_register_codes']))
        $config['ip_register_codes'] = array();
    elseif (!is_array($config['ip_register_codes']))
        $config['ip_register_codes'] = unserialize($config['ip_register_codes']);

    if (!is_array($config['ip_register_codes']))
        $config['ip_register_codes'] = array();

    $md5 = func_get_secure_random_key(32);
    while (isset($config['ip_register_codes'][$md5]))
        $md5 = func_get_secure_random_key(32);

    $found = false;
    foreach ($config['ip_register_codes'] as $k => $v) {
        if ($v['ip'] == $REMOTE_ADDR) {
            $found = $k;
            break;
        }
    }

    if ($found && isset($config['ip_register_codes'][$found])) {
        $md5 = $found;
        $config['ip_register_codes'][$found]['expiry'] = XC_TIME+86400*3;

    } else {
        $config['ip_register_codes'][$md5] = array(
            'ip' => $REMOTE_ADDR,
            'expiry' => XC_TIME+86400*3
        );
    }

    $old_configs = func_query("SELECT " . XCConfigSignature::getSignedFields() . " FROM $sql_tbl[config] WHERE name IN ('ip_register_codes')");
    func_array2insert('config',    array('name' => 'ip_register_codes', 'value' => addslashes(serialize($config['ip_register_codes']))), true);

    x_load('security', 'mail', 'crypt');

    func_secure_update_config_signatures($old_configs);

    $mail_smarty->assign('mode', $mode);
    $mail_smarty->assign('ip', $REMOTE_ADDR);
    $mail_smarty->assign('local_login', $local_login);
    $mail_smarty->assign('date', date("m/d/Y H:i:s T"));

    $mail_smarty->assign('url', $xcart_catalogs['admin']."/ip_register.php?key=".$md5);

    if (
        empty($user_data)
        && !empty($logged_userid)
    ) {
        $user_data = func_query_first("SELECT * FROM $sql_tbl[customers] WHERE id='$logged_userid'");
    }

    if (
        !empty($user_data)
        && $user_data['status'] == 'Y'
        && in_array($user_data['usertype'], array('A','P'))
        && !empty($user_data['email'])
        && text_decrypt($user_data['password'])
    ) {
        $email = $user_data['email'];
    } else {
        $email = $config['Company']['site_administrator'];
    }

    func_send_mail($email, "mail/security_ip_note_subj.tpl", "mail/security_ip_note.tpl", $config['Company']['site_administrator'], true);

    return array('sent_to_email' => $email, 'unknown_IP' => $REMOTE_ADDR);
} // }}}

/**
 * Register IP address for Admin area
 */
function func_register_admin_ip($ip)
{
    global $config, $sql_tbl;

    if (!isset($config['allowed_ips']) || empty($config['allowed_ips']))
        $config['allowed_ips'] = array();
    elseif (!is_array($config['allowed_ips']))
        $config['allowed_ips'] = func_array_map("trim", explode(",", $config['allowed_ips']));

    if (in_array($ip, $config['allowed_ips']))
        return true;

    $config['allowed_ips'][] = $ip;

    $old_configs = func_query("SELECT " . XCConfigSignature::getSignedFields() . " FROM $sql_tbl[config] WHERE name IN ('allowed_ips')");

    func_array2insert('config', array('name' => 'allowed_ips', 'value' => addslashes(implode(",", $config['allowed_ips']))), true);

    x_load('security');

    func_secure_update_config_signatures($old_configs);

    func_remove_ip_request($ip, true);

    return true;
}

/**
 * Delete IP address from registration requests list by IP address(es) or by request id
 */
function func_remove_ip_request($ids, $by_ip = false)
{
    global $config, $sql_tbl;

    if (!isset($config['ip_register_codes']) || empty($config['ip_register_codes']) || empty($ids))
        return false;

    if (!is_array($config['ip_register_codes']))
        $config['ip_register_codes'] = unserialize($config['ip_register_codes']);

    if (!is_array($config['ip_register_codes']))
        return false;

    if (!is_array($ids))
        $ids = array($ids);

    $changed = false;
    foreach ($config['ip_register_codes'] as $k => $v) {
        if ((!$by_ip && in_array($k, $ids)) || ($by_ip && in_array($v['ip'], $ids))) {
            $changed = true;
            func_unset($config['ip_register_codes'], $k);
        }
    }

    if ($changed) {
        $old_configs = func_query("SELECT " . XCConfigSignature::getSignedFields() . " FROM $sql_tbl[config] WHERE name IN ('ip_register_codes')");

        func_array2insert('config', array('name' => 'ip_register_codes', 'value' => addslashes(serialize($config['ip_register_codes']))), true);
        func_secure_update_config_signatures($old_configs);
    }

    return true;
}

function func_is_user_access_control_enabled() { // {{{
    $is_enabled =   XCSecurity::BLOCK_UNKNOWN_ADMIN_IP
                    || XCSecurity::PROTECT_DB_AND_PATCHES == 'ip'
                    || XCSecurity::PROTECT_ESD_AND_TEMPLATES == 'ip';
    return $is_enabled;
}//}}}

/**
 * Remove all X-Cart caches including smarty cache and templates
 */
function func_remove_xcart_caches($rebuild_cache = FALSE, $dirs = array())
{// {{{
    global $smarty, $var_dirs;

    $is_large = false;

    $var_dir = dirname($var_dirs['cache']);

    if (empty($dirs)) {
        $dirs = array('cache', 'templates_c');
        assert('empty($smarty->compile_id_based_on_filters) /*'.__FUNCTION__.' In most cases templates_c should not cleared if $smarty->compile_id_based_on_filters is true .*/');
    }

    foreach ($dirs as $subdir) {
        if (!file_exists($var_dirs[$subdir])) {
            continue;
        }

        $tmpdir = func_tempdir($var_dir, 'trash_' . $subdir);
        if (
            is_dir($tmpdir)
            && rename($var_dirs[$subdir], $tmpdir)
        ) {
            $dir_to_delete = $tmpdir;

            # Create subdirs for cache/templates_c directories
            foreach ($var_dirs as $k => $v) {
                if (strpos($v, $var_dirs[$subdir]) !== FALSE) {
                    func_restore_var_dir($k, $v);
                }
            }
        } else {
            $dir_to_delete = $var_dirs[$subdir];

            // rename is fail, remove the tmpdir
            if (is_dir($tmpdir)) {
                func_rm_dir($tmpdir);
            }
        }

        $result = func_rm_dir($dir_to_delete);
            $is_large = $is_large || !empty($result['is_large']);

        if ($dir_to_delete !== $tmpdir) {
            # Create subdirs for cache/templates_c directories when rename is failed
            foreach ($var_dirs as $k => $v) {
                if (strpos($v, $var_dirs[$subdir]) !== FALSE) {
                    func_restore_var_dir($k, $v);
                }
            }
        }
    }

    if ($rebuild_cache) {
        func_data_cache_get('modules', array(), TRUE);
    }

    settype($result, 'array');
    return array_merge($result, array('is_large' => $is_large));

} // }}}

function func_get_cache_dirs()
{
    global $smarty;
    return $smarty->compile_id_based_on_filters ? array('cache'): array('cache', 'templates_c');
}


/**
 * Create a temporary directory.
 */
function func_tempdir($dir, $prefix='') { // {{{
    global $xcart_fs_default_permissions;

    $mode = func_fs_get_dir_permissions($dir);

    if (empty($mode))
        $mode = 0777;


    if (substr($dir, -1) != '/') $dir .= '/';

    do {
      $path = $dir.$prefix.mt_rand(0, 9999999);
    } while (!mkdir($path, $mode));

    return $path;
} // }}}

/**
 * Check tax service name
 */
function func_check_tax_service_name($tax_name)
{
    return (bool)preg_match("/^[a-zA-Z][\w\d]*$/", $tax_name) && strlen($tax_name) <= 10;
}


/**
 * Generate anonymous customers from orders
 */
function func_generate_anonymous_from_orders($params) { // {{{
    global $sql_tbl, $config;

    $rows_per_step = intval($params['rows_per_step']);
    $total_count = intval($params['total_count']);

    if ($total_count > 0) {
        $orders = func_query("SELECT * FROM $sql_tbl[orders] WHERE all_userid=0 AND email!='' LIMIT $rows_per_step");
    }

    if (empty($orders)) {
        return 'all_generated';
    }

    x_load('order'); // For XCPlaceOrder
    $language = empty($config['default_customer_language']) ? $config['default_admin_language'] : $config['default_customer_language'];
    $cnt = 0;
    func_flush("$total_count orders remaining<br />\n");
    foreach ($orders as $order) {
        $order = func_addslashes($order);
        $_all_userid = XCPlaceOrder::addAnonymousCustomer($order, $language, $order['date']);
        if (empty($_all_userid)) {
            $_all_userid = -1;// Fake value to avoid circling
        }

        db_query("UPDATE $sql_tbl[orders] SET all_userid='$_all_userid' WHERE orderid='$order[orderid]'");

        if (++$cnt % 200 == 0) {
            func_flush("<br />\n");
        }

        if ($cnt % 3 == 0) {
            func_flush('. ');
        }

    }

    return count($orders) >= $rows_per_step ? 'partially_generated' : 'all_generated';
} // }}}

/**
 * Generate form id
 */
function func_generate_formid($force = false)
{
    global $sql_tbl, $XCARTSESSID;

    static $stored_md5 = false;

    if (!empty($stored_md5) && !$force)
        return $stored_md5;

    $check_string = "SELECT COUNT(*) FROM " . $sql_tbl['form_ids'] . " WHERE sessid = '" . $XCARTSESSID . "' AND formid = '";

    do {
        $stored_md5 = md5(XC_TIME . mt_rand(0, time()) . uniqid(mt_rand(), true));
    } while (func_query_first_cell($check_string . $stored_md5 . "'"));

    func_array2insert(
        'form_ids',
        array(
            'sessid' => $XCARTSESSID,
            'formid' => $stored_md5,
            'expire' => XC_TIME,
        )
    );

    return $stored_md5;
}

/**
 * Templater output filter for formid substitute
 */
function func_substitute_formid($tpl_output, &$templater)
{
    static $current_formid = false;

    if (preg_match("/<form[^>]+method\s*=\s*['\"]?post['\"]?[^>]*>/SUis", $tpl_output)) {

        if (empty($current_formid))
            $current_formid = func_generate_formid();

        if (!empty($current_formid))
            $tpl_output = preg_replace("/(<form[^>]+method\s*=\s*['\"]?post['\"]?[^>]*>)/SUis", "\\1\n<input type=\"hidden\" name=\"_formid\" value=\"$current_formid\" />", $tpl_output);

    }

    return $tpl_output;
}

function func_check_module_requirements($module_name)
{
    global $xcart_dir;

    $result = true;

    $_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . $module_name;
    $_func_file   = $_module_dir . XC_DS . 'admin_func.php';

    if (is_readable($_func_file)) {
        require_once $_func_file;
    }

    $_f = $module_name . '_check_module_requirements';
    if (function_exists($_f)) 
        $result = $_f();
    else 
        $result = true;

    return $result;
}

/**
 * Check formid
 */
function func_check_formid($formid = null)
{
    global $XCARTSESSID, $sql_tbl;

    if (is_null($formid))
        $formid = isset($_POST['_formid']) ? $_POST['_formid'] : false;

    return !(
        empty($formid) ||
        !is_string($formid) ||
        !preg_match("/^[\da-f]{32}$/i", $formid) ||
        !func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[form_ids] WHERE sessid = '$XCARTSESSID' AND formid = '$formid'")
    );
}

// Check limit for post_max_size and upload_max_filesize
// INPUT: filename, form_size - average post size without filesize
function func_check_uploaded_files_sizes($filename = 'userfile', $form_size = 500, $max_filesize = '')
{
    global $REQUEST_METHOD, $HTTP_REFERER, $top_message, $upload_warning_message;

    #to avoid double checking
    static $is_checked = false;

    if ($is_checked)
        return true;

    $is_checked = true;

    if ($REQUEST_METHOD != 'POST' || !stristr($_SERVER['CONTENT_TYPE'],'multipart/form-data'))
        return true;

    $post_max_size = func_convert_to_byte(ini_get('post_max_size'));

    if (empty($max_filesize))
        $max_filesize = func_upload_max_filesize();
    else
        $max_filesize = min(func_upload_max_filesize(), $max_filesize);

    // Check post_max_size exceeding

    $error = empty($_POST) && empty($_GET) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > $post_max_size;

    // Check upload_max_filesize exceeding

    if ($filename == 'none')
        $filename = '';

    if (
        !empty($filename)
        && isset($_FILES[$filename])
        && !$error
        && !is_uploaded_file($filename)
        && $_FILES[$filename]['error'] == 1
    ) {
        $error = true;
    }

    if ($error) {
        $top_message['type'] = 'E';
        $upload_warning_message = func_get_langvar_by_name('txt_max_file_size_warning3', array('size1' =>func_convert_to_megabyte($max_filesize), 'size2' => func_convert_to_megabyte($_SERVER['CONTENT_LENGTH'] - $form_size)), false, true);
        $top_message['content'] = $upload_warning_message;
        func_header_location(func_is_internal_url($HTTP_REFERER) ? $HTTP_REFERER : 'home.php');
    }

    return true;
}

/**
 * Text variables to use in license text
 */
function func_set_resellers($mode = RESELLERS_MODE)
{
    global $smarty;
    static $is_resellers;

    if ($mode === RESELLERS_MODE) {
        $smarty->assign('txt_reg_wrong_domain', '');
        $smarty->assign('txt_reg_not_registered', '');
        $smarty->assign('txt_reg_ups_not_registered', '');

        $is_resellers = $mode;
    }

    return $is_resellers;
}

function func_is_resellers() { // {{{
    return func_set_resellers(CHECK_RESELLERS_MODE) === RESELLERS_MODE;
} // }}}


function func_is_enabled_evaluation_popup() { // {{{

    global $is_enabled_evaluation_popup;

    x_session_register('is_enabled_evaluation_popup');

    return $is_enabled_evaluation_popup;

} // }}}


function func_disable_evaluation_popup() { // {{{
    global $REQUEST_METHOD, $is_enabled_evaluation_popup, $config;

    if (
        $config['Adaptives']['is_first_start'] == 'Y'
        || $REQUEST_METHOD != 'GET'
    ) {
        return false; 
    }

    x_session_register('is_enabled_evaluation_popup');
    $is_enabled_evaluation_popup = false;

} // }}}


function func_enable_evaluation_popup() { // {{{
    global $is_enabled_evaluation_popup;
    x_session_register('is_enabled_evaluation_popup');
    $is_enabled_evaluation_popup = true;
} // }}}


/*
 * Check if ssl shared cert is used. Return false, or web_dirs for http/https
*/
function func_is_used_ssl_shared_cert($http_location, $https_location) {

    if (
        $http_location === $https_location
        || strpos($http_location, 'http://') === false
        || strpos($https_location, 'https://') === false
    )
        return false;

    $http_location = rtrim($http_location, '/') . '/';
    $https_location = rtrim($https_location, '/') . '/';

    $http_location = str_replace('http://', '://', $http_location);
    $https_location = str_replace('https://', '://', $https_location);

    $http_location_rest = preg_replace('%://.*?/%', '/', $http_location);
    $https_location_rest = preg_replace('%://.*?/%', '/', $https_location);

    $https_location_rest = rtrim($https_location_rest, '/');
    $http_location_rest = rtrim($http_location_rest, '/');

    if ($http_location_rest !== $https_location_rest) {
        return array(
            'http' => $http_location_rest,
            'https' => $https_location_rest
        );
    }    

    return false;
}

/*
* Check if %$module% apache module is enabled. Skip check if apache_get_modules function is not avalaible
*/
function func_apache_check_module($module)
{
    $res = true;
    
    if (function_exists('apache_get_modules')) {
        $modules = apache_get_modules();

        if (!empty($modules)) {
            $res = false;
            foreach ($modules as $k => $m) {
                if (strpos($m, $module) !== false) {
                    $res = true;
                    break;
                }                
            }
        }
    }

    return $res;
}

/*
 * Used on admin/configuration.php POST. Check if admin has changed some option
 */
function func_option_is_changed($category, $name)
{
    global $config, $sql_tbl;

    if (empty($name))
        return false;

    $new_value = func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name='$name'");
    
    if ($name == 'alt_skin')
        $old_value = $config['alt_skin'];
    else
        $old_value = $config[$category][$name];

    return $new_value != $old_value;
}

/*
 * Get all offline payment methods Used as source for force_offline_paymentid config var (sql/xcart_data.sql)
 */
function func_get_offline_payment_methods($condition='')
{
    global $sql_tbl;

    $methods = func_query_hash("SELECT paymentid, payment_method FROM $sql_tbl[payment_methods] WHERE processor_file='' $condition AND is_cod!='Y'", 'paymentid', false, true);

    if (!empty($methods)) {
        $methods[0] = '';
        unset($methods[14]);// Do not use Gift Certificate pm
        ksort($methods);
    }

    return empty($methods) ? array() : $methods;
}

/**        
 * Clear force_offline_paymentid if it is C.O.D.
 */        
function func_check_force_offline_paymentid_for_cod()
{ 
    global $config, $sql_tbl;

    $force_offline_paymentid = intval($config['Egoods']['force_offline_paymentid']);
    $is_cod_force_offline_payment = func_query_first_cell("SELECT paymentid FROM $sql_tbl[payment_methods] WHERE paymentid='$force_offline_paymentid' AND is_cod='Y'");

    if (!empty($is_cod_force_offline_payment)) {
       func_array2update('config', array('value' => '0'), "name='force_offline_paymentid'");
    }

    return true;
}

/**
 * Check incompatible options on the the admin/configuration.php and return error message on error
 */
function func_configuration_has_incompatible_options($option) { //{{{
    global $sql_tbl, $config;

    $error_message = '';
    if (in_array($option, array('Shipping', 'General', 'Appearance', 'Taxes'))) {
        $new_values = func_query_hash("SELECT name,value FROM $sql_tbl[config] WHERE name IN ('enable_shipping','realtime_shipping','enable_all_shippings','apply_default_country','use_simple_product_sort','products_order','provider_register','provider_register_moderated', 'tax_operation_scheme', 'allow_user_modify_tax_number')", 'name', false, true);
    }

    if (empty($new_values)) {
        return $error_message;
    }

    $old_values_string = $config['Shipping']['enable_shipping'] . $config['Shipping']['realtime_shipping'] . $config['Shipping']['enable_all_shippings'] . $config['General']['apply_default_country'];

    if (1
        && $new_values['enable_shipping'] == 'Y'
        && $new_values['realtime_shipping'] == 'Y'
        && $new_values['enable_all_shippings'] == 'Y'
        && $new_values['apply_default_country'] == 'N'
        && $old_values_string != 'YYYN'
    ) {
        $error_message .= func_get_langvar_by_name('msg_adm_enable_all_shippings_apply_default_country_incompatible', null, false, true) . '<hr />';
    }

    $old_values_string = $config['Appearance']['products_order'] . $config['General']['use_simple_product_sort'];
    $new_values_string = $new_values['products_order'] . $new_values['use_simple_product_sort'];
    if (1
        && $new_values['products_order'] != 'orderby'
        && $new_values['use_simple_product_sort'] == 'Y'
        && $old_values_string != $new_values_string
    ) {
        $error_message .= func_get_langvar_by_name('msg_adm_products_order_n_use_simple_product_sort_incompatible', null, false, true) . '<hr />';
    }

    if (!empty($config['General']['provider_register'])) {
        $old_values_string = $config['General']['provider_register'] . $config['General']['provider_register_moderated'];
        $new_values_string = $new_values['provider_register'] . $new_values['provider_register_moderated'];
        if (1
            && $new_values['provider_register'] == 'Y'
            && $new_values['provider_register_moderated'] != 'Y'
            && $old_values_string != $new_values_string
        ) {
            $error_message .= func_get_langvar_by_name('msg_adm_provider_register_non_moderated_insecure', null, false, true) . '<hr />';
        }
    }

    $old_values_string = $config['Taxes']['tax_operation_scheme'] . $config['Taxes']['allow_user_modify_tax_number'];
    $new_values_string = $new_values['tax_operation_scheme'] . $new_values['allow_user_modify_tax_number'];

    if (
        $new_values['allow_user_modify_tax_number'] == 'N'
        && $old_values_string != $new_values_string
    ) {
        x_load('taxes');

        if ($new_values['tax_operation_scheme'] != XCTaxesDefs::TAX_SCHEME_GENERAL) {
            $error_message .= func_get_langvar_by_name('msg_adm_tax_scheme_allow_modify_tax_number_incomatible', null, false, true) . '<hr />';
        }
    }

    return $error_message;
} //}}}

/**
 * Prepare data and redirect to image cache regeneration page from admin/configuration.php
 */
function func_configuration_redirect_to_generate_image_cache($image_type, $cache_types, $redirect_section) {

    global $image_cache_tasks;
    x_session_register('image_cache_tasks');

    $image_cache_tasks = array();
    foreach ($cache_types as $cache_name) {
        $image_cache_tasks[] = array(
            $image_type,
            false,
            $cache_name,
        );
    }
    func_header_location('tools.php?regenerate_dpicons=Y&return_url=' . urlencode('configuration.php?option=' . $redirect_section));

}

/**
 * Obtain and parse paid modules list
 */
function func_get_xcart_paid_modules() { // {{{
    global $config, $HTTPS, $sql_tbl, $active_modules;

    if (
        !is_url($config['rss_xcart_paid_modules'])
        || !function_exists('xml_parser_create')
        || !empty($_COOKIE['skip_remote_feeds'])
    ) {
        return array();
    }

    static $res = FALSE;
    if ($res) {
        return $res;
    }

    $modules = func_query_column("SELECT module_name FROM $sql_tbl[modules] ORDER BY module_name");

    // Add active modules for special checks

    foreach ($active_modules as $mname => $mvalue) {
        $modules[] = '_active_' . $mname;
    }

    // CreSecure Hosted Payment Page integration
    $has_cc_cresecure_hpp = func_query_first_cell("SELECT processor FROM $sql_tbl[ccprocessors] WHERE processor='cc_cresecure_hpp.php'");
    if (!empty($has_cc_cresecure_hpp))
        $modules[] = 'cc_cresecure_hpp.php';

    // BrainTree payment integration
    $has_cc_braintree = func_query_first_cell("SELECT processor FROM $sql_tbl[ccprocessors] WHERE processor='cc_braintree.php'");
    if (!empty($has_cc_braintree))
        $modules[] = '_braintree';

    // X-Payments
    if (!empty($active_modules['XPayments_Connector'])) {
        func_xpay_func_load();
        if (xpc_is_payment_methods_exists())
            $modules[] = '_xp';
    }

    if (
        in_array('New_Arrivals', $modules)
        && in_array('On_Sale', $modules)
        && in_array('Quick_Reorder', $modules)
    ) {
        $modules[] = '_Hot_Products';
    }

    if (isset($config['Decorate']['xmas_decor'])) {
        $modules[] = '_xmas_decor';
    }

    $modules[] = '_Ideal_Responsive';

    $cache_key = md5(serialize($modules)). $HTTPS;

    global $xcart_dir;
    require_once "$xcart_dir/include/data_cache.php";

    if ($data = func_get_cache_func($cache_key, 'get_xcart_paid_modules')) {
        $res = $data['data'];
        return $res;
    }

    $rss_url = parse_url($config['rss_xcart_paid_modules']);

    x_load('http','xml');
    list($header, $result) = func_http_get_request($rss_url['host'], $rss_url['path'], @$rss_url['query']);

    if (empty($header)) {
        func_temporarely_disable_remote_feeds();
    }

    if (
        $HTTPS
        && !empty($result)
    ) {
        $result = str_replace('http://', 'https://', $result);
    }    

    $parse_error = false;
    $options = array(
        'XML_OPTION_CASE_FOLDING' => 1,
        'XML_OPTION_TARGET_ENCODING' => 'UTF-8'
    );
    $parsed = func_xml_parse($result, $parse_error, $options);

    $paid_modules = array();
    if (!empty($parsed)) {
        $items = func_array_path($parsed, 'MODULES/#/ITEM', TRUE);

        if (is_array($items)) {
            foreach ($items as $item) {

                $service_name = func_array_path($item, '#/SERVICE_NAME/0/#', TRUE);
                if (!empty($service_name) && in_array($service_name, $modules)) {
                    continue;
                }

                $tags = func_array_path($item, '#/TAGS/0/#/TAG', TRUE);
                if (!empty($tags)) {
                    foreach ($tags as $k => $v) {
                        $tags[$k] = func_array_path($v, '#', TRUE);
                    }
                } else {
                    $tags = array();
                }

                $paid_modules[] = array(
                    'name' => func_array_path($item, '#/TITLE/0/#', TRUE),
                    'desc' => func_array_path($item, '#/DESCRIPTION/0/#', TRUE),
                    'image' => func_array_path($item, '#/IMAGE/0/#', TRUE),
                    'icon' => func_array_path($item, '#/ICON/0/#', TRUE),
                    'price' => func_array_path($item, '#/PRICE/0/#', TRUE),
                    'price_suffix' => func_array_path($item, '#/PRICE_SUFFIX/0/#', TRUE),
                    'page' => func_array_path($item, '#/LINK/0/#', TRUE),
                    'tags' => $tags,
                );
            }
            func_save_cache_func($paid_modules, $cache_key, 'get_xcart_paid_modules');
        }
    }
    
    $res = $paid_modules;

    return $res;
} // }}}

/*
 Return possible processors in X-Payments, in sql or array format
*/
function func_get_xp_processors($format = 'in_sql')
{ //{{{
    global $config, $sql_tbl, $active_modules;

    if (
        !is_url($config['rss_xcart_paid_modules'])
        || !function_exists('xml_parser_create')
        || !empty($_COOKIE['skip_remote_feeds'])
    ) {
        // Show these methods with 'via X-Payments' note if remote feed not available
        $methods = array (
            'ANZ eGate',
            'American Express Web-Services API Integration',
            'Authorize.Net AIM',
            'Authorize.Net CIM',
            'Bean Stream/FirstData Canada',
            'BluePay',
            'Caledon',
            'Chase Paymentech',
            'CyberSource - SOAP toolkit API',
            'DIBS',
            'DirectOne - Direct Interface',
            'eProcessing Network - Transparent Database Engine',
            'SecurePay Australia',
            'eSelect DirectPost',
            'ECHO NVP',
            'Elavon Payment Gateway',
            'ePDQ MPI XML (Phased out)',
            'eWay Relatime Payments XML',
            'First Data Global Gateway e4(SM) Web Service API',
            'Global Iris',
            'GoEmerchant - XML Gateway API',
            'Innovative Gateway',
            'Intuit',
            'iTransact XML',
            'NetRegistry',
            'Netbilling - Direct Mode 3.1',
            'Ogone/ePDQ e-Commerce',
            'PayGate Korea',
            'Payflow Pro',
            'PayPal Payments Pro (PayPal API)',
            'PayPal Payments Pro (Payflow API)',
            'PSiGate XML API',
            'QuantumGateway - Transparent QGWdatabase Engine',
            'Worldpay Corporate Gateway - Direct Model',
            'Realex',
            'Sage Pay Go - Direct Interface',
            'SecurePay',
            'SkipJack',
            'USA ePay - Transaction Gateway API',
            'Virtual Merchant - Merchant Provided Form',
            'WebXpress',
            'Worldpay US',
        );
    } else {

        global $xcart_dir;
        require_once "$xcart_dir/include/data_cache.php";
        if ($data = func_get_cache_func('', 'get_xp_processors')) {
            $methods = $data['data'];
        } else {
            $rss_url = parse_url($config['rss_xcart_xpc_processors']);

            x_load('http','xml');
            list($header, $result) = func_http_get_request($rss_url['host'], $rss_url['path'], @$rss_url['query']);

            if (empty($header)) {
                func_temporarely_disable_remote_feeds();
            }

            $parse_error = false;
            $options = array(
                'XML_OPTION_CASE_FOLDING' => 1,
                'XML_OPTION_TARGET_ENCODING' => 'UTF-8'
            );
            $parsed = func_xml_parse($result, $parse_error, $options);

            $methods = array();

            if (!empty($parsed)) {
                $items = func_array_path($parsed, 'MODULES/#/MODULE', TRUE);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $name = func_array_path($item, '#/NAME/0/#', TRUE);
                        $methods[] = $name;
                    }
                    func_save_cache_func($methods, '', 'get_xp_processors');
                }
            }
        }
    }

    if ($format != 'in_sql')
        return $methods;

    $storage = func_data_cache_get('sql_tables_fields');
    $fields = $storage[strtolower($sql_tbl['ccprocessors'])];

    if (empty($fields))
        return array();

    $pattern = "'" . implode("','",$fields) . "'";

    $ret = '';
    foreach ($methods as $method) {
        $method_str = str_replace("'module_name'", "'$method' AS module_name", $pattern);
        $method_str = str_replace("'type'", "'Z_via_xp' AS type", $method_str);
        $method_str = str_replace("'disable_ccinfo'", "'N' AS disable_ccinfo", $method_str);
        $method_str = str_replace("'background'", "'Y' AS background", $method_str);
        $method_str = str_replace("'processor'", "'via_xp' AS processor", $method_str);

        $ret .= " UNION (SELECT " . $method_str . ")";
    }    

    return $ret;
} //}}}

/*
 Example: Return all active_modules/unset phrases from modules/config.php modules/init.php
 functest.func_get_phrases_from_files
*/
function func_get_phrases_from_files($directory, $files, $phrases, $recursive = 'recursive', $output='all')
{
    global $xcart_dir;

    $dir = @opendir($xcart_dir . XC_DS . $directory);
    if (!$dir) return '';

    $result = array();
    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') continue;

        $path = $directory.XC_DS.$file;

        if (
            is_file($xcart_dir . XC_DS .$path)
            && in_array($file, $files)
        ) {
            $content = file_get_contents($xcart_dir . XC_DS .$path);
            preg_match_all("/.*(?:$phrases).*/mi", $content, $arr);
            if ($output == 'all')
                $result[$path] = $arr;
            else    
                $result[$path] = !func_array_empty($arr);
        } elseif(
            is_dir($xcart_dir . XC_DS .$path)
            && $recursive == 'recursive'
        ) {
            $tmp = func_get_phrases_from_files($path, $files, $phrases, $recursive, $output);
            if (!func_array_empty($tmp))
                $result[$path] = $tmp;
        }
    }
    closedir($dir);

    return $result;
}

/**
 * Get all available options for menu on the Main page :: General settings page
 */
function func_get_configuration_options() { // {{{
    global $sql_tbl, $active_modules;

    $options = func_query_column("SELECT category FROM $sql_tbl[config] WHERE category NOT IN ('UPS_OnLine_Tools', 'Taxes', 'XCART_INNER_EVENTS', 'Lexity_hidden') AND category != '' GROUP BY category ORDER BY NULL");

    $all_modules = func_query_column("SELECT module_name FROM $sql_tbl[modules]");

    if (!empty($all_modules)) {
        foreach ($all_modules as $mn) {
            if (
                    in_array($mn, $options)
                    && !in_array($mn, array_keys($active_modules))
               ) {
                func_unset($options, array_search($mn, $options));
            }
        }
    }

    if (
        in_array('Mailchimp_Subscription', $options)
        && !in_array('Mailchimp_Subscription', $all_modules)
    ) {
        func_unset($options, array_search('Mailchimp_Subscription', $options));
    }

    return $options;
} // }}}

/**
 * Get all providers from store. Used in templates for PRO X-Cart version. 
 */
function func_get_providers()
{
    global $single_mode, $sql_tbl;

    if (!$single_mode) {
        $providers = func_query("SELECT id, login, title, firstname, lastname FROM $sql_tbl[customers] WHERE usertype='P' ORDER BY login, lastname, firstname");
    }

    settype($providers, 'array');

    return $providers;
}

/**
 * Start MySQL optimization procedure for all/selected tables
 */
function func_optimize_table($tbl = false, $tick = 0)
{
    global $sql_tbl;

    if (is_array($tbl)) {

        foreach ($tbl as $k => $v) {
            if (!empty($sql_tbl[$v]))
                $tbl[$k] = $sql_tbl[$v];
        }

        $tbls = func_query_column("SHOW TABLES");

        if (
            defined('X_MYSQL_LOWER_CASE_TABLE_NAMES') 
            && constant('X_MYSQL_LOWER_CASE_TABLE_NAMES') > 0
        ) {
            $tbls = array_map('strtolower', $tbls);
            $tbl = array_map('strtolower', $tbl);
        }

        foreach ($tbls as $k => $v) {
            if (!in_array($v, $tbl))
                unset($tbls[$k]);
        }

    } elseif (!empty($tbl)) {
        if (!empty($sql_tbl[$tbl]))
            $tbl = $sql_tbl[$tbl];

        $tbls = func_query_column("SHOW TABLES LIKE '".$tbl."'");

    } else {

        $tbls = func_query_column("SHOW TABLES");

    }

    if (empty($tbls))
        return false;

    $i = 0;

    foreach ($tbls as $v) {

        $i++;

        db_query("REPAIR TABLE " . $v);
        db_query("OPTIMIZE TABLE " . $v);
        db_query("ANALYZE TABLE " . $v);

        if (
            $tick > 0
            && $i % $tick == 0
        ) {
            func_flush(". ");
        }

    }
}

function func_drop_lng_tables($lng_table, $code='') {
    global $all_languages, $sql_tbl;

    if (empty($code)) {
        // Drop all tables
        $_languages = array_keys($all_languages);
        foreach ($_languages as $_code) {
            db_query("DROP TABLE IF EXISTS {$sql_tbl[$lng_table . $_code]}");
        }
    } else {
        db_query("DROP TABLE IF EXISTS {$sql_tbl[$lng_table . $code]}");
    }

    func_data_cache_clear('sql_tables_fields');

    return true;
}


function func_add_lng_table($lng_table, $code)
{
    global $config, $sql_tbl;

    $src_table = $sql_tbl[$lng_table . $config['default_admin_language']];
    $dest_table = XC_TBL_PREFIX . $lng_table . $code;
    if ($dest_table == $src_table)
        return true;

    db_query("CREATE TABLE IF NOT EXISTS `$dest_table` LIKE `$src_table`");

    func_data_cache_clear('sql_tables_fields');
    return true;
}

function func_delete_entity_from_lng_tables($lng_table, $key='', $key_name='')
{
    global $all_languages, $sql_tbl;

    if (!empty($key)) {
        $key = is_array($key) ? $key: array($key);
    } elseif($key !== '') {
        assert('FALSE /* '.__FUNCTION__.': Attention all rows will be removed from lng table for non-default empty $key */');
    }

    $_languages = array_keys($all_languages);
    foreach ($_languages as $_code) {
        if (empty($key))
            db_query("DELETE FROM {$sql_tbl[$lng_table . $_code]}");
        else            
            db_query("DELETE FROM {$sql_tbl[$lng_table . $_code]} WHERE $key_name IN ('".implode("','", $key)."')");
    }

    return true;
}

function func_repair_lng_integrity($lng_table, $main_table, $key_name, $def_fields)
{
    global $all_languages, $config, $sql_tbl;

    $defcode = $config['default_admin_language'];
    $_languages = array_keys($all_languages);

    // Delete non-related data from products_lng_.. tables
    foreach ($_languages as $_code) {
        $dest_table = $sql_tbl[$lng_table . $_code];
        db_query("DELETE FROM $dest_table WHERE $key_name NOT IN ( SELECT DISTINCT $key_name FROM $main_table) ");
    }

    $dest_table = $sql_tbl[$lng_table . $defcode];
    $src_table = $main_table;
    // Repair default lng table from xcart_products
    db_query("INSERT INTO $dest_table ( SELECT $key_name, $def_fields FROM $src_table WHERE $key_name NOT IN ( SELECT DISTINCT $key_name FROM $dest_table) )");

    foreach ($_languages as $_code) {
        if ($defcode == $_code)
            continue;

        $dest_table = $sql_tbl[$lng_table . $_code];
        $src_table = $sql_tbl[$lng_table . $defcode];
        
        db_query("INSERT INTO $dest_table ( SELECT * FROM $src_table WHERE $key_name NOT IN ( SELECT DISTINCT $key_name FROM $dest_table) )");
    }

    return true;
}

function func_is_news_applicable($news_versions)
{
    global $config;

    if (empty($news_versions)) {
        return true;
    } elseif (!preg_match('%\d\.\d\.\d.%', $news_versions)) {
        // Wrong format for xcartVersion field
        return false;
    }

    settype($config['applied_patches'], 'string');

    $arr_news_versions = explode(',', $news_versions);
    assert('is_array($arr_news_versions) /*'.__FUNCTION__.' Format error for rss feed*/');

    $is_applicable = false;
    foreach ($arr_news_versions as $version_rule) {
        $rules = explode('|', $version_rule);

        if (empty($rules[0]))
            continue;
        else
            $version = $rules[0];

        $operator   = isset($rules[1]) ? $rules[1] : '<';
        $patch      = isset($rules[2]) ? $rules[2] : '';

        if (version_compare($config['version'], $version, $operator)) {
            if (
                empty($patch)
                || strpos($config['applied_patches'], $patch) === false
            ) {
                $is_applicable = true;
                break;
            }
        }
    }

    return $is_applicable;

}

function func_tpl_get_admin_top_news()
{
    global $config;

    $all_news = func_tpl_get_xcart_news(0, $config['rss_xcart_admin_news_url']); 
    if (empty($all_news))
        return '';
    
    $applicable_news = array('title' => '', 'link' => '');
    foreach ($all_news as $news) {
        if ($news['xcartLocation'] == 'top' && func_is_news_applicable($news['xcartVersion'])) {
            $applicable_news = $news;

            break;
        }
    }

    return $applicable_news;
}

function func_tpl_get_admin_dashboard_news()
{
    global $config;

    $all_news = func_tpl_get_xcart_news(0, $config['rss_xcart_admin_news_url']); 
    if (empty($all_news))
        return array();
   
    $max_displayed_news = 50;
    $i = 0;
    $usual_news = $security_news = array();
    foreach ($all_news as $news) {
        if ($news['xcartLocation'] == 'dashboard' && func_is_news_applicable($news['xcartVersion'])) {
            $i++;
            if (strpos($news['xcartVersion'], 'sp_') !== false)
                $security_news[] = $news;
            else
                $usual_news[] = $news;
        }

        if ($i > $max_displayed_news)
            break;
    }

    return array('security_news' => $security_news, 'usual_news' => $usual_news);
}

/**
 * Obtain and parse 'X-Cart News' RSS feed 
 * Data source function for skin/common_files/main/xcart_news.tpl template
 */
function func_tpl_get_xcart_news($toplimit = 0, $rss_url = '') { // {{{
    global $config;

    $rss_url = empty($rss_url) ? $config['rss_xcart_news_url'] : $rss_url;

    if (
        !is_url($rss_url)
        || !function_exists('xml_parser_create')
        || !empty($_COOKIE['skip_remote_feeds'])
    ) {
        return array();
    }

    settype($toplimit, 'int');
    $cache_key = $toplimit . md5($rss_url);

    global $xcart_dir;
    require_once "$xcart_dir/include/data_cache.php";

    if ($data = func_get_cache_func($cache_key, 'tpl_get_xcart_news')) {
        return $data['data'];
    }

    $rss_url = parse_url($rss_url);

    x_load('http','xml');
    list($header, $result) = func_http_get_request($rss_url['host'], $rss_url['path'], @$rss_url['query']);

    if (empty($header)) {
        func_temporarely_disable_remote_feeds();
    }

    $parse_error = FALSE;
    $options = array(
        'XML_OPTION_CASE_FOLDING' => 1,
        'XML_OPTION_TARGET_ENCODING' => 'UTF-8'
    );
    $parsed = func_xml_parse($result, $parse_error, $options);

    $news = array();
    if (!empty($parsed)) {
        $items = func_array_path($parsed, 'RSS/CHANNEL/ITEM');

        if (is_array($items)) {
            $i = 0;
            foreach ($items as $item) {
                $news[] = array(
                    'title' => func_array_path($item, '#/TITLE/0/#'),
                    'link' => func_array_path($item, '#/LINK/0/#'),
                    'description' => func_array_path($item, '#/DESCRIPTION/0/#'),
                    'pubDate' => func_array_path($item, '#/PUBDATE/0/#'),
                    'xcartLocation' => func_array_path($item, '#/XCARTLOCATION/0/#'),
                    'xcartVersion' => func_array_path($item, '#/XCARTVERSION/0/#'),
                );
                $i++;

                if (
                    !empty($toplimit)
                    && $i == $toplimit
                ) {
                    break;
                }    
            }
            func_save_cache_func($news, $cache_key, 'tpl_get_xcart_news');
        }
    }


    return $news;
} // }}}

/**
 * Returns array with "groups" of config variables that are not secure 
 */
function func_is_not_secure_config_values() { //{{{

    $check_variables = array(
        'xc_security_key_session' => '7e79e4d2f66b9b05393e088c1e6c4031ace008507618e113517229d46e61c2a9547b324be625dd0b26b20cb87a6e85ecdbfa98aca56acdb80059d07b3e1c86d75cb5ee7cca7b0ca7c87dde07724fd02e626b7711cb5259012de21b4a79c08e6eafa1d94cff50724639679cb9e29b0f5be91a448185d5c873774828cdb60e1212b98e1034c680830873be57b32dbb5ada46ce0ac9650b103629fee25010519eadf49bbcc8bdfca95a299514d26fc9e44f70fc8b76c3df656d770308797c2383e6a833e0357e86c30954356bdee1a0b671d8cf157114e3f01beed4ae16e0e7cdae45c17a2355d65c65d473a4ab8a422e82915d328141cfeac08e70cc4c042f38bb',
        'xc_security_key_config' => 'cdd012dd7eea4f78b1969dc0a2b7c5fa6b3d4fe974cc634008b99f6e1517305a058bbc57c64e085c6cd4afbee26fb58dd723b48029a9a992aab23ddbf5ede8f8328c6cdc94ed01f835ae1405c647b132ff8e2f1003859457048f848310e1d7e75facb9c1b021439e80357a9b02aa4a4ef4086175c2a834f1afe9a14b27c5c156759a9004eebbd40a9df37bc5686985d0e10974a5fdb4b512da32b5b2c6983188d759b6d6fc9f6ae506bf5218ad3b35639c01a5fa91e5753e1e99a74fdbaabd47df010c8aecee3a8efd0491716bc93e8e6817ddce1d879a38b0b4019b19c11c06b1d020b3040a996943d831f7db6b42ab43d8e009c33db29e23d2223d47d668d7',
        'xc_security_key_general' => '8d9b00d918a7618bc9a0bedabfc7b3f1886bf4d72763a18265a26e4f6cc02396987f4124d76c7be09dc0872417bea8ecbb43fb4df0d877217de309278dd7f6156c3207892b39690ae0bf92132a02e72fc19830070be6e5b70d485d348f86905254c75fb1724fc0aac91b9b9728a61edf23550917e31d711b78eb080dc380d2c7b773d6e515ea7b6be5129719ebef7f29a45112f0de03f52124d50d7fb2d5808eb4bb3ea50d6c44b22d4ad32fa4d3ad463e7a12ffe81ef1ba973e03d9306302ef7d08ddb13d5dde1653c95c5bcdd525a6a00dd2e32977bdbbebe355c98ce54458b2c6fabce7b8a12c199566bc05215b141c5815c82de6b4bc1c579016037f5b9e',
        'blowfish_key' => '8d5db63ada15e11643a0b1c3477c2c5c',
        'installation_auth_code' => 'BG6GJH39',
    );

    $return = array();
    $matched_count = 0;

    foreach ($check_variables as $var => $value) {

        global $$var;

        if ($$var == $value) {
            $add = ($var == 'installation_auth_code') ? 'auth_code' : 'security_keys';
            $matched_count++;

            $return[$add] = true;
        }

    }
    assert('$matched_count == 0 || $matched_count == count($check_variables) /*'.__FUNCTION__.' Default security_keys/auth_code have been changed. Update check functtion.*/');

    return $return;

} //}}}

/**
 * This function checks the blowfish key generation date
 */
function func_check_bf_generation_date() { //{{{

    global $config;

    return empty($config['bf_generation_date'])
        || XC_TIME > mktime(
            0,
            0,
            0,
            date(
                'm',
                $config['bf_generation_date']
            ),
            date(
                'd',
                $config['bf_generation_date']
            ),
            date(
                'Y',
                $config['bf_generation_date']
            ) + 1);

} //}}}

/**
 * This function updates the blowfish key generation date
 */
function func_update_bf_generation_date() { //{{{

    global $config;

    $config['bf_generation_date'] = XC_TIME;

    func_array2insert(
        'config',
        array(
            'value'     => $config['bf_generation_date'],
            'name'      => 'bf_generation_date',
            'defvalue'  => '',
            'variants'  => '',
        ),
        true
    );

} //}}}

/**
 * This function checks the database backup generation date
 */
function func_check_db_backup_generation_date() { //{{{

    global $config;

    return $config['Security']['db_backup_notification_days'] > 0
        && (
            empty($config['db_backup_date'])
            || XC_TIME > ($config['db_backup_date'] + $config["Security"]["db_backup_notification_days"] * 86400)
        );

} //}}}

/**
 * This function updates the database backup generation date
 */
function func_update_db_backup_generation_date() { //{{{

    global $config;

    $config['db_backup_date'] = XC_TIME;

    func_array2insert(
        'config',
        array(
            'value'     => $config['db_backup_date'],
            'name'      => 'db_backup_date',
            'defvalue'  => '',
            'variants'  => ''
        ),
        true
    );

} //}}}

/**
 * This function sets cookie to disable remote http requests for internal X-Cart features
 */
function func_temporarely_disable_remote_feeds() { //{{{
    $_COOKIE['skip_remote_feeds'] = '1';
    func_setcookie('skip_remote_feeds', $_COOKIE['skip_remote_feeds'], XC_TIME + SECONDS_PER_DAY);
} //}}}

?>
