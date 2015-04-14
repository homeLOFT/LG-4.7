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
 * This script is meant to generate the current status of the store files
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v58 (xcart_4_7_0), 2015-02-17 13:29:01, snapshots.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/**
 * Status values:
 *   A - no file (absent) presented in the original distribution
 *   N - new file
 *   M - changed file
 *   R - file exists but not readable
 * File type values:
 *   T - template
 *   P - script
 */

define('NO_ALT_SKIN', 'Y');

require __DIR__.'/auth.php';

require $xcart_dir . '/include/security.php';

x_load('snapshots');

func_set_time_limit(86400);

$memory_allowed = func_set_memory_limit('96M');

if (!$memory_allowed) {
    $smarty->assign('top_message', array(
        'content' => func_get_langvar_by_name('lbl_fingerprints_not_enough_memory'),
        'type' => 'W'
    ));
}

$location[] = array(func_get_langvar_by_name('lbl_system_snapshots'), 'snapshots.php');

x_session_register('file_log_name');
x_session_register('search_data');

/**
 * This function sort out the file log by file path/name
 */
function f_sort_filelog($a, $b)
{
    return ($a['display_filename'] > $b['display_filename']);
}

/**
 * This function opens MD5 file and returns its content
 */
function f_get_md5_file_content($md5file)
{
    global $top_message;

    if (file_exists($md5file)) {

        if ($fd = fopen($md5file, 'r')) {

            $file_content = fread($fd, func_filesize($md5file));

            fclose($fd);

            return $file_content;

        } else {

            $top_message['content'] = func_get_langvar_by_name('txt_md5_file_cannot_open');

        }

    } else {

        $top_message['content'] = func_get_langvar_by_name('txt_md5_file_not_found');

    }

    $top_message['type'] = 'E';

    return false;
}

/**
 * Always fresh the snapshot when comparing with the current status
 */
$force_refresh = 'Y';

/**
 * Prepare the snapshots from config
 */
$config['snapshots'] = unserialize($config['snapshots']);

if (!is_array($config['snapshots']))
    $config['snapshots'] = array();

if (
    $REQUEST_METHOD == 'POST'
    && $mode == 'delete'
) {

    include $xcart_dir.'/include/safe_mode.php';
    func_check_perms_redirect(XCActions::MANAGE_SYSTEM_FINGERPRINTS);

    // Delete the snapshots passed in $to_delete array

    if (is_array($to_delete)) {
        $flag = false;

        foreach ($to_delete as $id=>$i) {

            $_tmp = array();

            foreach ($config['snapshots'] as $k=>$v) {

                if ($v['time'] == $id) {
                    $flag = true;
                    @unlink(f_get_md5file_name($id));
                    continue;
                }

                $_tmp[] = $v;
            }

            if ($flag)
                $config['snapshots'] = $_tmp;
        }

        if ($flag) {

            $top_message['content'] = func_get_langvar_by_name('msg_snpst_deleted');

            f_update_snapshots($config['snapshots']);

        }

    }

    func_header_location("snapshots.php?mode=generate");
}

if ($REQUEST_METHOD == 'POST' && $mode == 'generate') {

    // Generate the snapshot

    include $xcart_dir.'/include/safe_mode.php';
    func_check_perms_redirect(XCActions::MANAGE_SYSTEM_FINGERPRINTS);

    $current_time = XC_TIME;
    $md5file = f_get_md5file_name($current_time);

    echo func_get_langvar_by_name('txt_begin_generating_snapshot',false,false,true);
    func_flush();

    $result = func_generate_snapshot($md5file);

    if ($result['error']) {
        $top_message['content'] = func_get_langvar_by_name('err_'.$result['errordescr'], '', false, true);
        $top_message['type'] = 'E';
    }
    else {
        $top_message['content'] = func_get_langvar_by_name('msg_snapshot_generated', '', false, true);
        $str = '';
        if (!empty($result['unprocessed_files'])) {
            $str = "<br />".func_get_langvar_by_name('txt_N_unprocessed_files_in_snapshot', array('unproc'=>$result['unprocessed_files'], 'total'=>$result['total_files']), false, true)."<br />";
            func_snapshot_add_to_log($result['unprocessed_files_list']);
        }

        $config['snapshots'][] = array('time'=>$current_time, 'descr'=>$new_descr);
        f_update_snapshots($config['snapshots']);
        echo "<br />".func_get_langvar_by_name('msg_snapshot_generated',false,false,true).$str;
    }

    func_html_location("snapshots.php?mode=generate", 5);
}

/**
 * Check post_max_size exceeding
 */
func_check_uploaded_files_sizes('new_file', 506);

if ($REQUEST_METHOD == 'POST' && $mode == 'upload') {

    include $xcart_dir.'/include/safe_mode.php';
    func_check_perms_redirect(XCActions::MANAGE_SYSTEM_FINGERPRINTS);

    // Uploading the snapshot

    $current_time = XC_TIME;
    $md5file = f_get_md5file_name($current_time);

    if (is_uploaded_file($new_file)) {
        if (!@move_uploaded_file($new_file, $md5file)) {
            $top_message['content'] = func_get_langvar_by_name('msg_err_file_operation');
            $top_message['type'] = 'E';
        }
        else {
            $config['snapshots'][] = array('time'=>$current_time, 'descr'=>$new_descr);
            f_update_snapshots($config['snapshots']);
            $top_message['content'] = func_get_langvar_by_name('msg_file_uploaded');
        }
    }

    func_header_location("snapshots.php?mode=generate");
}

if ($REQUEST_METHOD == 'POST' && ($mode == 'process')) {

    include $xcart_dir.'/include/safe_mode.php';
    func_check_perms_redirect(XCActions::MANAGE_SYSTEM_FINGERPRINTS);

    // Compare current status of files with snapshot

    // Define if update of the file log is needed...
    $need_to_update = (empty($file_log) || $force_refresh == 'Y');
    if (!$need_to_update) {
        $need_to_update = ($search_data['file_log']['mode'] != $posted_data['mode']);
        if (!$need_to_update) {
            $need_to_update = ($posted_data["mode"] == "1" && $search_data["file_log"]['snapshot'] != $posted_data['snapshot'] || ($posted_data['mode'] == '2' && ($search_data['file_log']['snapshot1'] != $posted_data['snapshot1'] || $search_data['file_log']['snapshot2'] != $posted_data['snapshot2'])));
        }
    }

    if ($need_to_update) {

        if ($posted_data['mode'] == '1') {
            if (empty($posted_data['snapshot'])) {
                $top_message['content'] = func_get_langvar_by_name('msg_snapshot_not_selected');
                $top_message['type'] = 'E';
                func_header_location('snapshots.php');
            }

            // First MD5 file
            $md5file = f_get_md5file_name($posted_data['snapshot']);
        }
        elseif ($posted_data['mode'] == '2') {
            if (empty($posted_data['snapshot1']) || empty($posted_data['snapshot2'])) {
                $top_message['content'] = func_get_langvar_by_name('msg_snapshot_not_selected');
                $top_message['type'] = 'E';
                func_header_location('snapshots.php');
            }
            elseif ($posted_data['snapshot1'] == $posted_data['snapshot2']) {
                $top_message['content'] = func_get_langvar_by_name('msg_snapshots_not_different');
                $top_message['type'] = 'E';
                func_header_location('snapshots.php');
            }

            // First MD5 file
            $md5file = f_get_md5file_name($posted_data['snapshot1']);

            // Second MD5 file
            $md5file_2 = f_get_md5file_name($posted_data['snapshot2']);
        }

        $file_log = array();
        $files_list = array();

        // Read the MD5 checksums from file

        if (!($file_content = f_get_md5_file_content($md5file)) || ($posted_data['mode'] == '2' && !($file_content_2 = f_get_md5_file_content($md5file_2)))) {
            func_header_location('snapshots.php');
        }

        $re_templates_dir = '/' . preg_quote($xcart_dir . $smarty_skin_root_dir, XC_DS) . '/S';

        if (!empty($file_content)) {

            // Compare the files_list with $file_content...

            if ($posted_data['mode'] == '2') {

                // Generate the files list from $file_content_2

                $files_md5_list = array();
                $_file_log_2 = explode("\n", $file_content_2);
                unset($file_content_2);
                if (is_array($_file_log_2)) {
                    foreach ($_file_log_2 as $line) {
                        if (empty($line)) continue;

                        list($key, $value) = explode(":", $line);
                        if (empty($value)) continue;

                        $key = $xcart_dir.base64_decode($key);
                        $files_list[] = $key;
                        // generate the hash: <file => MD5>
                        $files_md5_list[$key] = $value;
                    }
                }

                unset($_file_log_2);
            }
            else {

                // Generate the full files list

                $files_list = f_get_filelist($xcart_dir);
                sort($files_list);
            }

            $skin_dir = preg_replace('/'.preg_quote($xcart_dir, XC_DS)."\/(.*)$/", "\\1", implode('|', $smarty->getTemplateDir()));

            // Parse file with original MD5 checksums and generate the filelog

            $_file_log = explode("\n", $file_content);
            unset($file_content);

            foreach ($_file_log as $line) {
                if (empty($line)) continue;

                list($key, $value) = explode(":", $line);
                if (empty($value)) continue;

                $key = $xcart_dir.base64_decode($key);

                $_tmp = array();

                if (is_numeric($_key = array_search($key, $files_list)) || $value == 'R') {
                    unset($files_list[$_key]);

                    if (preg_match($re_templates_dir, $key)) {
                        // File type is template (file within skin1 directory)
                        $_tmp['type'] = 'T';
                        $_tmp['dir'] = preg_replace("/^.*" . $re_templates_dir . '/', '', dirname($key));
                        $_tmp['file'] = $_tmp['dir'].'/'.basename($key);
                    }
                    elseif (preg_match("/\.php$/iS", $key)) {
                        // File type is PHP script
                        $_tmp['type'] = 'P';
                    }

                    if ($value == 'R') {
                        $_tmp['status'] = 'U';
                    }
                    elseif ($md5_current = func_md5_file($key, $posted_data['mode'])) {
                        // Status - modified or no
                        $_tmp['status'] = ($md5_current == $value ? '' : 'C');
                    }
                    else {
                        // Status - not readable
                        $_tmp['status'] = 'R';
                    }
                }
                else {
                    // Status - file not found
                    $_tmp['status'] = 'A';
                }

                $_tmp['display_filename'] = str_replace($xcart_dir, '', $key);
                $file_log[$key] = $_tmp;
            }

            unset($_tmp);
        }
        else {
            $top_message['type'] = 'E';
            func_header_location('snapshots.php');
        }

        foreach ($files_list as $k=>$v) {

            // Scanning the files list for new files

            if (preg_match($re_templates_dir, $v)) {
                // File type is template (file within skin1 directory)
                $file_log[$v]['type'] = 'T';
                $file_log[$v]['dir'] = preg_replace("/^.*". $re_templates_dir .'/', '', dirname($v));
                $file_log[$v]['file'] = $file_log[$v]['dir'].'/'.basename($v);
            }
            elseif (preg_match("/\.php$/iS", $v)) {
                // File type is PHP script
                $file_log[$v]['type'] = 'P';
            }

            // Status - new file
            $file_log[$v]['status'] = 'N';
            $file_log[$v]['display_filename'] = str_replace($xcart_dir, '', $v);
        }
    }

    usort($file_log, 'f_sort_filelog');

    $file_log_name = func_temp_store(serialize($file_log));

    // Save the form data for further displaying in the current session

    if (!empty($posted_data)) {
        $search_data['file_log'] = $posted_data;
    }
    else {
        $search_data['file_log'] = array();
    }

    func_header_location("snapshots.php?mode=process");
}

/**
 * Process GET request
 */

/**
 * Check the current snapshots list
 */
$total_snapshots = 0;
foreach ($config['snapshots'] as $k=>$v) {
    if (!file_exists(f_get_md5file_name($v['time']))) {
        $config['snapshots'][$k]['no_file'] = 'Y';
        $config['snapshots'][$k]['filename'] = preg_replace("/^".preg_quote($xcart_dir, XC_DS).'/', '', f_get_md5file_name($v['time']));
    }
    else {
        $total_snapshots++;
    }
}

if ($total_snapshots == 0 && $mode != 'generate') {
    $top_message['content'] = func_get_langvar_by_name('txt_no_snapshots');

    func_header_location("snapshots.php?mode=generate");
}

if (!empty($file_log_name)) {
    if (file_exists($file_log_name)) {
        $file_log = unserialize(func_temp_read($file_log_name, 'delete_src_file'));
    }
    else {
        if (empty($top_message)) {
            $top_message['content'] = func_get_langvar_by_name('msg_snapshots_cmp_expired');
            $top_message['type'] = 'W';
            $smarty->assign('top_message', $top_message);
            unset($top_message);
        }

        $file_log_name = '';
        $mode = '';
    }
}

if (!empty($file_log) && !empty($search_data['file_log'])) {

    // Filtering the filelog

    foreach ($file_log as $k=>$v) {
        if ((!empty($search_data['file_log']['filter']) && $v['type'] != $search_data['file_log']['filter']) || (is_array($search_data['file_log']['status']) && !in_array($v['status'], $search_data['file_log']['status']))) {
            unset($file_log[$k]);
        }
    }
}


/**
 * Assign the Smarty variables
 */
$smarty->assign('file_log', @$file_log);
$smarty->assign('total_snapshots', $total_snapshots);

$smarty->assign('mode', $mode);
$smarty->assign('search_prefilled', @$search_data['file_log']);

$smarty->assign('snapshots', $config['snapshots']);

$smarty->assign('main','snapshots');

// Assign the current location line
$smarty->assign('location', $location);

$dialog_tools_data['left'][] = array('link' => "snapshots.php?mode=generate", 'title' => func_get_langvar_by_name('lbl_snapshots'));
$dialog_tools_data['left'][] = array('link' => 'snapshots.php', 'title' => func_get_langvar_by_name('lbl_compare_snapshots'));

$dialog_tools_data['right'][] = array('link' => 'general.php', 'title' => func_get_langvar_by_name('lbl_summary'));
$dialog_tools_data['right'][] = array('link' => $xcart_web_dir.DIR_ADMIN.'/tools.php', 'title' => func_get_langvar_by_name('lbl_tools'));
$dialog_tools_data['right'][] = array('link' => $xcart_web_dir.DIR_ADMIN.'/logs.php', 'title' => func_get_langvar_by_name('lbl_shop_logs'));
$dialog_tools_data['right'][] = array('link' => $xcart_web_dir.DIR_ADMIN.'/user_access_control.php', 'title' => func_get_langvar_by_name('lbl_user_access_control'));

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);
?>
