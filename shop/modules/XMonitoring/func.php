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
 * Module functional script
 *
 * @category   X-Cart
 * @package    Modules
 * @subpackage X-Monitoring
 * @author     Michael Bugrov <mixon@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v14 (xcart_4_7_0), 2015-02-17 23:56:28, func.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

#
# Simple DTO object
#
class DTO {

    public $actionName;
    
    public $actionResult;
    
    public $actionParams;
    
    function __construct($actionName, $actionResult, $actionParams) {
    
       $this->actionName = $actionName;
       $this->actionResult = $actionResult;
       $this->actionParams = $actionParams;
    
    }

}

#
# Monitoring module core functions
#
function func_xmonitoring_install()
{
    global $sql_tbl, $config;
    
    $apikey = func_xmonitoring_get_registered();

    if ($apikey) {
        x_load('crypt');        
        db_query("
            UPDATE $sql_tbl[config]
            SET value = '" . addslashes(text_crypt($apikey)) . "'
            WHERE name = 'xmonitoring_api_key'
        ");
    }

    if (defined('XCART_INSTALL')) {
        db_query("
            UPDATE $sql_tbl[config]
            SET value = '" . addslashes($config['Company']['site_administrator']) . "'
            WHERE name = 'xmonitoring_notification_email'
        ");
    }

    if (!$apikey) {
        global $top_message;
        x_session_register('top_message');

        db_query("UPDATE $sql_tbl[modules] SET active = 'N' WHERE module_name = 'XMonitoring'");
        func_data_cache_get('modules', array(), true);

        $top_message = array(
            'type'      => 'E',
            'content'   => func_get_langvar_by_name('err_xmonitoring_install'),
        );

        if (!defined('XCART_INSTALL')) {
            func_header_location('modules.php');
        }
    }

    return $apikey;
}

function func_xmonitoring_on_module_enable($module_name)
{
    global $top_message;
    
    if ($module_name == 'XMonitoring') {
        func_xmonitoring_install();
        
        $top_message = array(
            'type'      => 'I',
            'content'   => func_get_langvar_by_name('txt_xmonitoring_module_enabled', false, false, true),
        );
    }
}

function func_xmonitoring_on_module_disable($module_name)
{
    global $config, $top_message;

    if ($module_name == 'XMonitoring') {        
        func_xmonitoring_get_disabled();
        
        $top_message = array(
            'type'      => 'W',
            'content'   => func_get_langvar_by_name('txt_xmonitoring_module_disabled', false, false, true),
        );
        
    }
}

function func_xmonitoring_on_module_toggle($module_name, $module_new_state)
{
    $redirect = false;

    switch ($module_new_state) {
        case true:
                func_xmonitoring_on_module_enable($module_name);
            break;
        case false:
                func_xmonitoring_on_module_disable($module_name);
                $redirect = "modules.php";
            break;
    }

    return $redirect;
}

function func_xmonitoring_hide_section($xm_module_info, $section)
{
    return str_replace($section . ' style="display: none;"', '', $xm_module_info);
}

function func_xmonitoring_show_section($xm_module_info, $section)
{
    return str_replace($section . ' style="display: none;"', '', $xm_module_info);
}

function func_xmonitoring_date_diff($time1, $time2, $precision = 3)
{
    // If not numeric then convert texts to unix timestamps
    if (!is_int($time1)) {
        $time1 = strtotime($time1);
    }
    if (!is_int($time2)) {
        $time2 = strtotime($time2);
    }

    // If time1 is bigger than time2
    // Then swap time1 and time2
    if ($time1 > $time2) {
        $ttime = $time1;
        $time1 = $time2;
        $time2 = $ttime;
    }

    // Set up intervals and diffs arrays
    $intervals = array('year', 'month', 'day', 'hour', 'minute', 'second');
    $diffs = array();

    // Loop thru all intervals
    foreach ($intervals as $interval) {
        // Set default diff to 0
        $diffs[$interval] = 0;
        // Create temp time from time1 and interval
        $ttime = strtotime('+1 ' . $interval, $time1);
        // Loop until temp time is smaller than time2
        while ($time2 >= $ttime) {
            $time1 = $ttime;
            $diffs[$interval]++;
            // Create new temp time from time1 and interval
            $ttime = strtotime('+1 ' . $interval, $time1);
        }
    }

    $count = 0;
    $times = array();
    // Loop thru all diffs
    foreach ($diffs as $interval => $value) {
        // Break if we have needed precission
        if ($count >= $precision) {
          break;
        }
        // Add value and interval 
        // if value is bigger than 0
        if ($value > 0) {
            // Add value and interval to times array
            $times[] = $value . $interval[0];
            $count++;
        }
    }
    
    // Return string with times
    return implode(' ', $times);
}

function func_xmonitoring_load_API()
{
    if (!function_exists('func_xmonitoring_API_check_installation')) {
        #
        # Set default API time limit
        #
        set_time_limit(XMONITORING_TIMEOUT);
        
        require_once XMONITORING_DIR . '/monitoring_api.php';
    }
}

function func_xmonitoring_get_apikey()
{
    static $APIKEY_CACHE = null;

    if ($APIKEY_CACHE == null) {

        global $xcart_dir, $blowfish, $encryption_types, $config;

        if ($blowfish == null) {
            # (due to QUICK_START) [[[
            # Initialize encryption subsystem
            #
            require_once($xcart_dir . '/include/blowfish.php');
            #
            # Start Blowfish class (due to QUICK_START)
            #
            $blowfish = new ctBlowfish();
            # (due to QUICK_START) ]]]
        }
    
        x_load('crypt');

        $APIKEY_CACHE = text_decrypt($config['xmonitoring_api_key']);
    }

    return $APIKEY_CACHE;
}

function func_xmonitoring_verify_apikey($apikey)
{
    return $apikey == func_xmonitoring_get_apikey();
}

function func_xmonitoring_API_request($action, $params = array())
{
    x_load('http');
    
    $monitoring_apikey = func_xmonitoring_get_apikey();
    
    $post_request = array();
    $post_request[] = "action=$action";

    if (!empty($monitoring_apikey)) {
        $post_request[] = "apikey=$monitoring_apikey";
    }

    $post_request = array_merge($post_request, $params);

    list($headers, $result) = func_https_request(
        'POST',
        'https://' . XMONITORING_API_DOMAIN . XMONITORING_API_PATH,
        $post_request
    );

    if ($result) {
        $xm_data = @unserialize($result);
        if (
            is_object($xm_data)
            && $xm_data->actionResult == 'OK'
        ) {
            if (
                !empty($monitoring_apikey)
                && func_xmonitoring_verify_apikey($xm_data->actionParams['key'])
            ) {
                return $xm_data;
            }
            return $xm_data;
        }
    }
    
    x_log_add('xmonitoring', array('request' => $post_request, 'headers' => $headers, 'result' => $result));
    
    return false;
}

function func_xmonitoring_get_registered()
{
    global $xcart_http_host, $xcart_web_dir, $config;
    
    $params = array(
        'monitoringUrl=' . urlencode('http://' . $xcart_http_host . $xcart_web_dir),
        'notificationEmail=' . urlencode($config['Company']['site_administrator'])
    );
    
    $result = func_xmonitoring_API_request('register', $params);
    
    return isset($result->actionParams['key']) ? $result->actionParams['key'] : false;
}

function func_xmonitoring_update_email($notification_email)
{
    $params = array(
        'dataType=notificationEmail',
        'newValue=' . urlencode($notification_email)
    );
    
    return func_xmonitoring_API_request('update', $params);
}

function func_xmonitoring_get_events()
{
    return func_xmonitoring_API_request('events');
}

function func_xmonitoring_get_rules()
{
    $result = func_xmonitoring_API_request('getRules');
    
    return isset($result->actionParams['rules']) ? $result->actionParams['rules'] : false;
}

function func_xmonitoring_get_status()
{
    return func_xmonitoring_API_request('status');
}

function func_xmonitoring_get_enkey()
{
    return func_xmonitoring_API_request('getEnkey');
}

function func_xmonitoring_get_disabled()
{
    return func_xmonitoring_API_request('disable');
}

function func_xmonitoring_get_page($pagename)
{
    $params = array(
        'page_name=' . urlencode($pagename)
    );

    $result = func_xmonitoring_API_request('getPage', $params);

    return isset($result->actionParams['page']) ? $result->actionParams['page'] : false;
}

function func_xmonitoring_set_page($pagename)
{
    $params = array(
        'page_name=' . urlencode($pagename)
    );

    $result = func_xmonitoring_API_request('setPage', $params);

    return isset($result->actionParams['page']) ? $result->actionParams['page'] : false;
}

function func_xmonitoring_get_pages()
{
    $result = func_xmonitoring_API_request('getPages');

    return isset($result->actionParams['pages']) ? $result->actionParams['pages'] : false;
}

function func_xmonitoring_report_file($xm_rules, $xc_version, $xm_file)
{
    $xm_diff = func_xmonitoring_API_diff_file($xm_rules, $xc_version, $xm_file, true);

    if (
        $xm_diff
    ) {
        return "file: $xm_file\n"
             . 'fperms: ' . $xm_diff['fileinfo_from']['fperms'] . '->' . $xm_diff['fileinfo_to']['fperms'] . "\n"
             . 'fmtime: ' . $xm_diff['fileinfo_from']['fmtime'] . '->' . $xm_diff['fileinfo_to']['fmtime'] . "\n"
             . 'signature_check_record: ' . ($xm_diff['fileinfo_from']['signature_check_record'] ? 'OK' : 'Error') . "\n"
             . 'signature_check_content: ' . ($xm_diff['fileinfo_from']['signature_check_content'] ? 'OK' : 'Error') . "\n"
             . 'fmodified: ' . ($xm_diff['file_diff'] ? 'true' : 'false'). "\n\n";
    }

    return false;
}

function func_xmonitoring_report_page($xm_rules, $xc_version, $xm_page)
{
    $xm_diff =  func_xmonitoring_API_diff_page($xm_rules, $xc_version, $xm_page, true);

    if (
        $xm_diff
        && $xm_diff['file_diff']
    ) { 
        return "page: $xm_page\n---\n" . html_entity_decode($xm_diff['file_diff']) . "\n---\n";
    }

    return false;
}

function func_xmonitoring_report($xm_rules, $xc_version, $xm_diff)
{
    global $config, $current_location, $HTTP_REFERER;

    $xm_diff_string = implode("\n", $xm_diff);

    $xmonitoring_report = <<<XM_REPORT
X-Monitoring changes report
----------------------------------------------------------------
URL: $current_location
Version: $xc_version
----------------------------------------------------------------
$xm_diff_string
----------------------------------------------------------------
XM_REPORT;

    $post_request = array();
    $post_request[] = 'product_type=XC';
    $post_request[] = 'user_email=' . $config['Company']['site_administrator'];
    $post_request[] = "redirect_url=$HTTP_REFERER";
    $post_request[] = "report=" . $xmonitoring_report;

    x_load('http');

    list($headers, $result) = func_https_request(
        'POST',
        XMONITORING_REPORT_URL,
        $post_request
    );

    return true;
}

function func_xmonitoring_get_xcart_version()
{
    global $sql_tbl;
    
    $version = func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name = 'version'");
    
    if ($version) {
        return preg_replace('/(\d+)\.(\d+)\.(\d+)/i', '${1}.${2}.x', $version);
    }
    
    return false;
}

function func_ajax_block_xmonitoring_files()
{
    global $xmonitoring_authorized, $active_modules, $xcart_dir, $current_area, $config, $smarty;
    
    x_session_register('xmonitoring_authorized');
    
    if (
        !empty($active_modules['XMonitoring'])
        && $xmonitoring_authorized == 'Y'
    ) {
        #
        # load X-Monitoring API's
        #
        func_xmonitoring_load_API();
        
        $xmonitoring_rules = func_xmonitoring_get_rules(func_xmonitoring_get_apikey());
        $xmonitoring_xc_version = func_xmonitoring_get_xcart_version();
        
        x_load('backoffice', 'perms', 'security');

        $current_area = 'A';
        
        func_check_admin_security_redirect();
        
        func_check_perms_redirect(XCActions::MANAGE_XMONITORING_FILES);

        if (
            $xmonitoring_rules != false
            && $xmonitoring_xc_version != false
        ) {
            $check_result = func_xmonitoring_API_check_installation($xmonitoring_rules, $xmonitoring_xc_version, true);

            # should define directly due to QUICK_START option
            $config['Appearance']['datetime_format'] = $config['Appearance']['date_format'] . " " . $config['Appearance']['time_format'];

            $smarty->assign('xmonitoring_has_snapshots', func_xmonitoring_has_monitoring_files());

            $smarty->assignByRef('xmonitoring_files', $check_result);
            $smarty->assignByRef('config', $config);

            $result = func_display('modules/XMonitoring/filesystem.tpl', $smarty, false);

            return $result;
        }

        return func_get_langvar_by_name('err_xmonitoring_feature_no_available', false, false, true);
    }
}

function func_ajax_block_xmonitoring_pages()
{
    global $xmonitoring_authorized, $active_modules, $xcart_dir, $current_area, $config, $smarty;
    
    x_session_register('xmonitoring_authorized');
    
    if (
        !empty($active_modules['XMonitoring'])
        && $xmonitoring_authorized == 'Y'
    ) {
        #
        # load X-Monitoring API's
        #
        func_xmonitoring_load_API();
        
        $xmonitoring_rules = func_xmonitoring_get_rules(func_xmonitoring_get_apikey());
        $xmonitoring_xc_version = func_xmonitoring_get_xcart_version();
        
        x_load('backoffice', 'perms', 'security');

        $current_area = 'A';
 
        func_check_admin_security_redirect();
        
        func_check_perms_redirect(XCActions::MANAGE_XMONITORING_FILES);

        if (
            $xmonitoring_rules != false
            && $xmonitoring_xc_version != false
        ) {
            $check_result = func_xmonitoring_API_check_webpages($xmonitoring_rules, $xmonitoring_xc_version, true);

            # should define directly due to QUICK_START option
            $config['Appearance']['datetime_format'] = $config['Appearance']['date_format'] . " " . $config['Appearance']['time_format'];

            $smarty->assign('xmonitoring_has_webpages', func_xmonitoring_API_has_webpages($xmonitoring_rules, $xmonitoring_xc_version));

            $smarty->assignByRef('xmonitoring_webpages', $check_result);
            $smarty->assignByRef('config', $config);

            $result = func_display('modules/XMonitoring/webpages.tpl', $smarty, false);

            return $result;
        }

        return func_get_langvar_by_name('err_xmonitoring_feature_no_available', false, false, true);
    }
}


function func_xmonitoring_detect_php_mode()
{    
    return func_get_php_execution_mode();
}

function func_xmonitoring_make_relative_filename($filename)
{
    global $xcart_dir;
    
    return str_replace($xcart_dir . XC_DS, '', $filename);
}

function func_xmonitoring_resolve_relative_filename($filename)
{
    global $xcart_dir;
    
    x_load('files');
    
    return $xcart_dir . XC_DS . func_normalize_path($filename);
}

function func_xmonitoring_get_record_signature($record)
{
    $xm_hfunk = XMONITORING_HASH_FUNC;
    
    unset($record['fcontent']);
    
    return $xm_hfunk(XMONITORING_KEY . implode("|", $record));
}

function func_xmonitoring_get_content_signature($content)
{
    $xm_hfunk = XMONITORING_HASH_FUNC;
    
    return $xm_hfunk(XMONITORING_KEY . $content);
}

function func_xmonitoring_encrypt($content)
{    
    x_load('crypt');
    
    return text_crypt(base64_encode($content), 'B', XMONITORING_KEY);
}

function func_xmonitoring_decrypt($content)
{    
    x_load('crypt');
    
    return base64_decode(text_decrypt($content, XMONITORING_KEY));
}

function func_xmonitoring_is_allowed_path($file_path)
{
    global $xcart_dir;
    
    x_load('files');
    
    return func_allowed_path($xcart_dir, $file_path);
}

function func_xmonitoring_check_record_signature(&$record)
{
    
    if (isset($record['signature'])) {
        
        $signature = $record['signature'];
        unset($record['signature']);
        
        return (func_xmonitoring_get_record_signature($record) == $signature);
    }
    
    return false;
}

function func_xmonitoring_check_content_signature(&$record)
{    
    if (
        isset($record['fsign'])
        && isset($record['fcontent'])
    ) {
        return (func_xmonitoring_get_content_signature($record['fcontent']) == $record['fsign']);
    }

    return false;
}

function func_xmonitoring_create_key()
{
    # get second key from x-monitoring service
    if (!defined('XMONITORING_KEY')) {
        $result = func_xmonitoring_get_enkey();
        if ($result->actionParams['enkey']) {
            define('XMONITORING_KEY', XMONITORING_BASE_KEY . $result->actionParams['enkey']);
            return XMONITORING_KEY;
        }
        return false;
    }
    return XMONITORING_KEY;
}

function func_xmonitoring_rglob($pattern = '*', $flags = 0, $path = false, $skip_path_check = false)
{
    if (
        func_xmonitoring_is_allowed_path($path)
        ||
        $skip_path_check
    ) {
        clearstatcache();

        if (!$path) {
            $path = dirname($pattern) . DIRECTORY_SEPARATOR;
        }

        $pattern = basename($pattern);

        $paths = func_xmonitoring_glob($path . '*', GLOB_MARK | GLOB_ONLYDIR | GLOB_NOSORT);
        $files = func_xmonitoring_glob($path . $pattern, $flags);

        foreach ($paths as $path) {
            $files = array_merge($files, func_xmonitoring_rglob($pattern, $flags, $path, $skip_path_check));
        }
        
        return $files;
    }
    
    return false;
}

function func_xmonitoring_glob($pattern, $flags = 0)
{
    static $FILES_CACHE = array();

    if (!isset($FILES_CACHE[$pattern][$flags])) {
        $FILES_CACHE[$pattern][$flags] = glob($pattern, $flags);
    }
    
    return $FILES_CACHE[$pattern][$flags];
}

function func_xmonitoring_get_file_info($filename, $with_content = false, $skip_path_check = false)
{
    $file_path = func_xmonitoring_resolve_relative_filename($filename);
    
    if (
        func_xmonitoring_is_allowed_path($file_path)
        ||
        $skip_path_check
    ) {
        clearstatcache();
        
        $content = file_get_contents($file_path);
        
        if ($with_content) {            
            return array(
                'filename' => $filename,
                'fowner' => fileowner($file_path),
                'fperms' => decoct(fileperms($file_path)),
                'fmtime' => filemtime($file_path),
                'fcontent' => $content,
                'fsign' => func_xmonitoring_get_content_signature($content),
            );
        }
        else return array(
            'filename' => $filename,
            'fowner' => fileowner($file_path),
            'fperms' => decoct(fileperms($file_path)),
            'fmtime' => filemtime($file_path),
            'fsign' => func_xmonitoring_get_content_signature($content),
        );
    }
    
    return false;
}

function func_xmonitoring_is_recursive_search($file_mask)
{
    
    $result = str_replace(' -R', '', $file_mask);
    
    return $result == $file_mask ? false : $result;
}

function func_xmonitoring_list_files_by_mask($file_mask)
{
    static $FILES_CACHE = array();

    if (!isset($FILES_CACHE[$file_mask])) {

        $files_list = array();
    
        $files_path = func_xmonitoring_resolve_relative_filename($file_mask);
    
        #
        # Check whether the file is in allowed path or not
        #
        if (func_xmonitoring_is_allowed_path($files_path)) {
        
            #
            # Check if file exists or not
            #
            if (file_exists($files_path)) {
                #
                # Add file to the list
                #
                $files_list[$files_path] = $files_path;
            
            } else {
                #
                # Should we use recursive search
                #
                if (($files_path_recursive = func_xmonitoring_is_recursive_search($files_path)) != false) {
                    #
                    # Run through the list of files by mask recursive (-R)
                    #
                    $xm_files_list = func_xmonitoring_rglob($files_path_recursive, 0, false, true);
                } else {
                    #
                    # Run through the list of files by mask
                    #
                    $xm_files_list = func_xmonitoring_glob($files_path);
                }

                if (
                    $xm_files_list
                    && is_array($xm_files_list)
                ) {
                    # Loop through the list and create keys
                    foreach ($xm_files_list as $filename) {
                        $files_list[$filename] = $filename;
                    }
                }
            }
        }
    
        $files_info = array();
    
        foreach($files_list as $filename) {
            #
            # Get file key
            #
            $file_key = func_xmonitoring_make_relative_filename($filename);
            #
            # Get file info an add it to the list
            #
            $files_info[$file_key] = func_xmonitoring_get_file_info($file_key, false, true);
        }

        $FILES_CACHE[$file_mask] = $files_info;

    }

    return $FILES_CACHE[$file_mask];
}

function func_xmonitoring_get_files_by_mask($file_masks)
{
    if (is_array($file_masks)) {
        
        $files_list = array();
        
        foreach ($file_masks as $file_mask) {
            $files_list = array_merge($files_list, func_xmonitoring_list_files_by_mask($file_mask));
        }
        
        return $files_list;
    
    } elseif (is_string($file_masks)) {
        
        return func_xmonitoring_list_files_by_mask($file_masks);
        
    }
    
    return false;
}

function func_xmonitoring_has_monitoring_files()
{
    global $sql_tbl;
    
    return (intval(func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[monitoring_fsystem]")) > 0 ? 'Y' : 'N');
}

function func_xmonitoring_get_file_snapshot($filename, $with_content = false)
{
    global $sql_tbl;
    
    if ($with_content) {
        $record = func_query_first("
            SELECT
                filename, fowner, fperms, fmtime, fsign, fcontent, signature
            FROM
                $sql_tbl[monitoring_fsystem]
            WHERE
                filename = '" . addslashes($filename) . "'"
        );
    } else {
        $record = func_query_first("
            SELECT
                filename, fowner, fperms, fmtime, fsign, signature
            FROM
                $sql_tbl[monitoring_fsystem]
            WHERE
                filename = '" . addslashes($filename) . "'"
        );
    }
    
    $prepared = array_map('stripslashes', $record);
    
    if (!empty($prepared)) {

        $prepared['signature_check_record'] = func_xmonitoring_check_record_signature($prepared);
        
        if ($with_content) {
            $prepared['fcontent'] = func_xmonitoring_decrypt($prepared['fcontent']);
            
            $prepared['signature_check_content'] = func_xmonitoring_check_content_signature($prepared);
            
            if ($prepared['signature_check_content'] == false) {
                #
                # Content signature check failed, remove untrusted content
                #
                unset($prepared['fcontent']);
            }
        }
        
        return $prepared;
    }
    
    return false;
}

function func_xmonitoring_set_file_snapshot($filename)
{
    global $sql_tbl;
    
    $record = func_xmonitoring_get_file_info($filename, true);
    
    $record['fcontent'] = func_xmonitoring_encrypt($record['fcontent']);
    $record['signature'] = func_xmonitoring_get_record_signature($record);
    
    $prepared = array_map('addslashes', $record);
    
    return db_query("
        REPLACE INTO
            $sql_tbl[monitoring_fsystem] (filename, fowner, fperms, fmtime, fcontent, fsign, signature)
        VALUES ("
        . "'" . $prepared['filename'] . "', "
        . "'" . $prepared['fowner'] . "', "
        . "'" . $prepared['fperms'] . "', "
        . "'" . $prepared['fmtime'] . "', "
        . "'" . $prepared['fcontent'] . "', "
        . "'" . $prepared['fsign'] . "', "
        . "'" . $prepared['signature'] . "')"
    );
}

function func_xmonitoring_create_snapshots($files)
{
    if (
        $files
        && is_array($files)
    ) {
        if (
            func_xmonitoring_create_key()
        ) {

            $results = array();
        
            foreach($files as $file_info) {
                $results[$file_info['filename']] = func_xmonitoring_set_file_snapshot($file_info['filename']);
            }

            return $results;
        }
    }
    
    return false;
}

function func_xmonitoring_verify_request_ip()
{
    global $REMOTE_ADDR;
    
    return gethostbyname(XMONITORING_API_DOMAIN) == $REMOTE_ADDR;
}

function func_xmonitoring_is_file_diffable($filename)
{
    return preg_match('/((.*)\.(php|tpl|js|css|htaccess)|(xctmp(.*)))/i', $filename);
}

function func_xmonitoring_compare_files($from_filename, $to_filename, $skip_pre_tag = false)
{
    global $x_error_reporting;
    
    x_load('files');
    
    $from_filename = func_normalize_path($from_filename);
    $to_filename = func_normalize_path($to_filename);

    if (func_xmonitoring_is_file_diffable($to_filename)) {
        # disable error reporting
        error_reporting(0);
        
        require_once 'Text/Diff.php';
        require_once 'Text/Diff/Renderer/unified.php';

        $lines1 = file($from_filename);
        $lines2 = file($to_filename);
        
        if (
            count($lines1) == 0
            ||
            (1 - count($lines1) / count($lines2)) > 0.5
        ) {
            # restore error reporting
            error_reporting($x_error_reporting);
            
            $file_name = func_xmonitoring_make_relative_filename($to_filename);
            
            return func_get_langvar_by_name('err_xmonitoring_too_much_changes', array('filename' => $file_name), false, true);
            
        } else {
            
            $diff     = new Text_Diff('auto', array($lines1, $lines2));
            $renderer = new Text_Diff_Renderer_unified();

            $result = $renderer->render($diff);
            # restore error reporting
            error_reporting($x_error_reporting);

            if ($result) {
                if ($skip_pre_tag) {
                    return htmlentities($result);
                }
                return '<pre class="prettyprint lang-diff">' . htmlentities($result) . '</pre>';
            }
        }
    }
    
    return false;
}

?>
