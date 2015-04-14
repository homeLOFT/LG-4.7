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
 * X-Monitoring page
 *
 * @category   X-Cart
 * @package    Modules
 * @subpackage Admin interface
 * @author     Michael Bugrov <mixon@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v12 (xcart_4_7_0), 2015-02-17 13:29:01, xmonitoring.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir . '/include/security.php';

if (empty($active_modules['XMonitoring'])) {
    func_403();
}

require $xcart_dir . '/include/safe_mode.php';

$xm_action = isset($xm_action) ? $xm_action : 'undefined';

if (
    $REQUEST_METHOD == 'POST'
    && $xm_action != 'undefined'
) {
    
    x_load('backoffice', 'perms', 'security');
    
    func_check_perms_redirect(XCActions::MANAGE_XMONITORING_FILES);
    
    $xmonitoring_rules = func_xmonitoring_get_rules();
    $xmonitoring_xc_version = func_xmonitoring_get_xcart_version();
    
    if (
        $xmonitoring_rules != false
        && $xmonitoring_xc_version != false
        && func_xmonitoring_create_key()
    ) {
        
        func_xmonitoring_load_API();
        
        x_session_register('top_message');
        
        switch($xm_action) {
            case 'generate':
                $generate_result = func_xmonitoring_API_generate_snapshot($xmonitoring_rules, $xmonitoring_xc_version, true);
                if ($generate_result) {
                    $top_message = array(
                        'type'      => 'I',
                        'content'   => func_get_langvar_by_name('txt_xmonitoring_snapshot_created', false, false, true),
                    );
                }
                break;

            case 'accept':
                $accept_result = func_xmonitoring_API_accept_file($xmonitoring_rules, $xmonitoring_xc_version, $xm_params);
                if ($accept_result) {
                    $top_message = array(
                        'type'      => 'I',
                        'content'   => func_get_langvar_by_name('txt_xmonitoring_file_accepted', array('filename' => $xm_params), false, true),
                    );
                }
                break;

            case 'accept_selected':
                $xm_accepted = array();
                $xm_file2accept = array_keys($xm_files);                
                foreach($xm_file2accept as $xm_file) {
                    $accept_selected_result = func_xmonitoring_API_accept_file($xmonitoring_rules, $xmonitoring_xc_version, $xm_file);
                    if ($accept_selected_result) {
                        $xm_accepted[] = $xm_file;
                    }
                }                
                $top_message = array(
                    'type'      => 'I',
                    'content'   => func_get_langvar_by_name('txt_xmonitoring_files_accepted', array('filenames' => implode(', ', $xm_accepted)), false, true),
                );
                break;

            case 'restore':
                $restore_result = func_xmonitoring_API_restore_file($xmonitoring_rules, $xmonitoring_xc_version, $xm_params);
                if ($restore_result) {
                    $top_message = array(
                        'type'      => 'I',
                        'content'   => func_get_langvar_by_name('txt_xmonitoring_file_restored', array('filename' => $xm_params), false, true),
                    );
                } else {
                    $top_message = array(
                        'type'      => 'E',
                        'content'   => func_get_langvar_by_name('err_xmonitoring_record_corrupted', array('filename' => $xm_params), false, true),
                    );
                }
                break;

            case 'restore_selected':
                $xm_restored = array();
                $xm_file2restore = array_keys($xm_files);                
                foreach($xm_file2restore as $xm_file) {
                    $restore_selected_result = func_xmonitoring_API_restore_file($xmonitoring_rules, $xmonitoring_xc_version, $xm_file);
                    if ($restore_selected_result) {
                        $xm_restored[] = $xm_file;
                    }
                }                
                $top_message = array(
                    'type'      => 'I',
                    'content'   => func_get_langvar_by_name('txt_xmonitoring_files_restored', array('filenames' => implode(', ', $xm_restored)), false, true),
                );
                break;

            case 'remove':
                $remove_result = func_xmonitoring_API_remove_file($xmonitoring_rules, $xmonitoring_xc_version, $xm_params);
                if ($remove_result) {
                    $top_message = array(
                        'type'      => 'I',
                        'content'   => func_get_langvar_by_name('txt_xmonitoring_file_removed', array('filename' => $xm_params), false, true),
                    );
                }
                break;

            case 'report_selected_files':
                $xm_reported = array();
                $xm_reported_results = array();
                $xm_file2report = array_keys($xm_files);
                foreach($xm_file2report as $xm_file) {
                    $report_selected_result = func_xmonitoring_report_file($xmonitoring_rules, $xmonitoring_xc_version, $xm_file);
                    if ($report_selected_result) {
                        $xm_reported[] = $xm_file;
                        $xm_reported_results[] = $report_selected_result;
                    }
                }

                func_xmonitoring_report($xmonitoring_rules, $xmonitoring_xc_version, $xm_reported_results);

                $top_message = array(
                    'type'      => 'I',
                    'content'   => func_get_langvar_by_name('txt_xmonitoring_files_reported', array('filenames' => implode(', ', $xm_reported)), false, true),
                );
                break;

            case 'page_accept':
                $accept_result = func_xmonitoring_API_accept_page($xmonitoring_rules, $xmonitoring_xc_version, $xm_params);
                if ($accept_result) {
                    $top_message = array(
                        'type'      => 'I',
                        'content'   => func_get_langvar_by_name('txt_xmonitoring_page_accepted', array('pagename' => $xm_params), false, true),
                    );
                }
                break;

            case 'page_accept_selected':
                $xm_accepted = array();
                $xm_page2accept = array_keys($xm_pages);
                foreach($xm_page2accept as $xm_page) {
                    $accept_selected_result = func_xmonitoring_API_accept_page($xmonitoring_rules, $xmonitoring_xc_version, $xm_page);
                    if ($accept_selected_result) {
                        $xm_accepted[] = $xm_page;
                    }
                }
                $top_message = array(
                    'type'      => 'I',
                    'content'   => func_get_langvar_by_name('txt_xmonitoring_pages_accepted', array('pagenames' => implode(', ', $xm_accepted)), false, true),
                );
                break;

            case 'report_selected_pages':
                $xm_reported = array();
                $xm_reported_results = array();
                $xm_page2report = array_keys($xm_pages);
                foreach($xm_page2report as $xm_page) {
                    $report_selected_result = func_xmonitoring_report_page($xmonitoring_rules, $xmonitoring_xc_version, $xm_page);
                    if ($report_selected_result) {
                        $xm_reported[] = $xm_page;
                        $xm_reported_results[] = $report_selected_result;
                    }
                }

                func_xmonitoring_report($xmonitoring_rules, $xmonitoring_xc_version, $xm_reported_results);

                $top_message = array(
                    'type'      => 'I',
                    'content'   => func_get_langvar_by_name('txt_xmonitoring_pages_reported', array('pagenames' => implode(', ', $xm_reported)), false, true),
                );
                break;
        }
        
        if (isset($top_message['content'])) {
            x_log_flag(
                'log_activity',
                'ACTIVITY',
                "User '$login' ('$login_type' user type) Remote IP '$REMOTE_ADDR' performed an operation in X-Monitoring and got the following result: $top_message[content]"
            );
        }
        
    } else {
    
        $top_message = array(
            'type'      => 'E',
            'content'   => func_get_langvar_by_name('err_xmonitoring_get_xmdata_failed', false, false, true),
        );    
    }
    
    if (
        in_array($xm_action, array('page_accept', 'page_report', 'page_accept_selected', 'report_selected_pages'))
    ) {
        func_header_location('xmonitoring.php#xmonitoring-tabs-pages');
    } else {
        func_header_location('xmonitoring.php#xmonitoring-tabs-files');
    }
    
} elseif (
    $REQUEST_METHOD == 'GET'
    && ($xm_action == 'diff' || $xm_action == 'page_diff')
    && !empty($xm_params)
) {
    $xmonitoring_rules = func_xmonitoring_get_rules();
    $xmonitoring_xc_version = func_xmonitoring_get_xcart_version();
    
    if (
        $xmonitoring_rules != false
        && $xmonitoring_xc_version != false
        && func_xmonitoring_create_key()
    ) {
        func_xmonitoring_load_API();

        if ($xm_action == 'page_diff') {
            $xmonitoring_diff = func_xmonitoring_API_diff_page($xmonitoring_rules, $xmonitoring_xc_version, $xm_params);
        } else {
            $xmonitoring_diff = func_xmonitoring_API_diff_file($xmonitoring_rules, $xmonitoring_xc_version, $xm_params);
        }

        $smarty->assignByRef('xmonitoring_diff', $xmonitoring_diff);
        echo func_display('modules/XMonitoring/diff.tpl', $smarty, false);
        exit;
    }
}

// Define data for the navigation within section

$dialog_tools_data = array();

$dialog_tools_data['right'][] = array(
    'link'  => 'configuration.php?option=XMonitoring&right', 
    'title' => func_get_langvar_by_name('lbl_xmonitoring_configure')
);

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);

$smarty->assign('main', 'xmonitoring');


$location[] = array(func_get_langvar_by_name('lbl_xmonitoring_title'), 'xmonitoring.php');

// Assign the current location line
$smarty->assign('location', $location);

// Get monitoring data
$xmonitoring_data = func_xmonitoring_get_events();

// Define time data format
define('UTC_TF', 'Y-m-d H:i:s');

$xmonitoring_expired = 'Y';

if ($xmonitoring_data) {
    
    if ($xmonitoring_data->actionResult == 'OK') {
        
        $xmonitoring_values = array('0' => 'OK', '1' => 'Problem', '2' => 'Unknown');
    
        $xmonitoring_triggers = array();
        
        foreach($xmonitoring_data->actionParams['triggers'] as $xmonitoring_trigger) {
            $xmonitoring_triggers[$xmonitoring_trigger['triggerid']] = $xmonitoring_trigger['description'];
        }
        
        $xmonitoring_items = array();
        
        foreach($xmonitoring_data->actionParams['items'] as $xmonitoring_item) {
            $xmonitoring_items[$xmonitoring_item['itemid']] = $xmonitoring_item['name'];
        }
        
        $xmonitoring_alerts = array();
        
        foreach($xmonitoring_data->actionParams['alerts'] as $xmonitoring_alert) {
            $xmonitoring_alerts[$xmonitoring_alert['eventid']] = $xmonitoring_alert['reason'];
        }
        
        $xmonitoring_events = array();
        $xmonitoring_events_groups = array();
        $xmonitoring_reasons_groups = array();
        
        $xmonitoring_xcart_time = date(UTC_TF);
        
        // Change default timezone to UTC
        @date_default_timezone_set('UTC');
        
        $xmonitoring_server_time = $xmonitoring_data->actionParams['serverTime'];

        $xmonitoring_time_offset = (
            strtotime($xmonitoring_xcart_time)
            - strtotime($xmonitoring_server_time)
            + $config['Appearance']['timezone_offset']
        );

        // Prepare events data
        for($i = 0; $i < count($xmonitoring_data->actionParams['events']); $i++) {
            
            $xm_curr = current($xmonitoring_data->actionParams['events']);
            $xm_next = next($xmonitoring_data->actionParams['events']);

            if (
                isset($xmonitoring_triggers[$xm_curr->objectid])
                && isset($xmonitoring_values[$xm_curr->value])
            ) {
                
                $xm_curr_time = strtotime(date(UTC_TF, $xm_curr->clock)) + $xmonitoring_time_offset;
                
                if (
                    $xm_next === false
                    || $xm_curr->objectid != $xm_next->objectid
                ) {
                    $xm_next_time = strtotime($xmonitoring_server_time) + $xmonitoring_time_offset;
                }
                else {
                    $xm_next_time = strtotime(date(UTC_TF, $xm_next->clock)) + $xmonitoring_time_offset;
                }
                
                $xm_duration_ms = abs($xm_next_time - $xm_curr_time);
                $xm_duration_hr = func_xmonitoring_date_diff($xm_next_time, $xm_curr_time);
                
                $xmonitoring_events[$xm_curr->eventid] = array(
                    'eventid' => $xm_curr->eventid,
                    'type' => $xmonitoring_triggers[$xm_curr->objectid],
                    'time' => $xm_curr_time,
                    'value' => $xmonitoring_values[$xm_curr->value],
                    'reason' => isset($xmonitoring_alerts[$xm_curr->eventid]) ? $xmonitoring_alerts[$xm_curr->eventid] : '',
                    'duration_ms' => $xm_duration_ms,
                    'duration_hr' => $xm_duration_hr,
                    'trigger' => $xm_curr->value_changed,
                );

                if ($xm_curr->value > 0) {
                    if (
                        isset($xmonitoring_events_groups[$xm_curr->objectid])
                    ) {
                        $xmonitoring_events_groups[$xm_curr->objectid]['duration_ms'] += $xm_duration_ms;
                    }
                    else {
                        $xmonitoring_events_groups[$xm_curr->objectid] = array(
                            'objectid' => $xm_curr->objectid,
                            'type' => $xmonitoring_triggers[$xm_curr->objectid],
                            'value' => $xmonitoring_values[$xm_curr->value],
                            'duration_ms' => $xm_duration_ms,
                        );
                    }
                    if (
                        stristr($xmonitoring_triggers[$xm_curr->objectid], 'X-Cart') !== false
                        && isset($xmonitoring_alerts[$xm_curr->eventid])
                    ) {
                        if (
                            isset($xmonitoring_reasons_groups[$xmonitoring_alerts[$xm_curr->eventid]])
                        ) {
                            $xmonitoring_reasons_groups[$xmonitoring_alerts[$xm_curr->eventid]]['duration_ms'] += $xm_duration_ms;
                        }
                        else {
                            $xmonitoring_reasons_groups[$xmonitoring_alerts[$xm_curr->eventid]] = array(
                                'reason' => preg_replace('/\((\d+)\)/i', '', $xmonitoring_alerts[$xm_curr->eventid]),
                                'duration_ms' => $xm_duration_ms,
                            );
                        }
                    }
                }
            }
        }
        
        $xmonitoring_begin_date = strtotime($xmonitoring_data->actionParams['beginDate']);
        $xmonitoring_end_date = strtotime($xmonitoring_data->actionParams['endDate']);

        $xmonitoring_duration_ms = abs($xmonitoring_end_date - $xmonitoring_begin_date);
        $xmonitoring_duration_hr = func_xmonitoring_date_diff($xmonitoring_end_date, $xmonitoring_begin_date);
        
        // Assign the retrieved data
        $smarty->assign('xmonitoring_events', $xmonitoring_events);
        $smarty->assign('xmonitoring_events_groups', $xmonitoring_events_groups);
        $smarty->assign('xmonitoring_reasons_groups', $xmonitoring_reasons_groups);

        $smarty->assign('xmonitoring_begin_date', $xmonitoring_begin_date);
        $smarty->assign('xmonitoring_end_date', $xmonitoring_end_date);
        
        $smarty->assign('xmonitoring_duration_ms', $xmonitoring_duration_ms);
        $smarty->assign('xmonitoring_duration_hr', $xmonitoring_duration_hr);

        $smarty->assign('xmonitoring_has_items', !empty($xmonitoring_items) ? 'Y' : 'N');

        $xmonitoring_expired = 'N';
        
        x_session_register('xmonitoring_authorized');

        $xmonitoring_authorized = 'Y';
    }
}

$smarty->assign('xmonitoring_expired', $xmonitoring_expired);
$smarty->assign('xmonitoring_has_snapshots', func_xmonitoring_has_monitoring_files());

$smarty->assign('xmonitoring_admin_dir', $xcart_web_dir . DIR_ADMIN);

if (is_readable($xcart_dir . '/modules/gold_display.php')) {
    include $xcart_dir . '/modules/gold_display.php';
}

func_display('admin/home.tpl', $smarty);

?>
