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
 * Monitoring service API implementation
 *
 * @category   X-Cart
 * @package    Modules
 * @subpackage X-Monitoring
 * @author     Michael Bugrov <mixon@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v8 (xcart_4_7_0), 2015-02-17 23:56:28, monitoring_api.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

function func_xmonitoring_API_check_files($files, $extended = false)
{
    $errors = array();
    
    foreach($files as $filename => $file_info) {
        
        $file_snapshot = func_xmonitoring_get_file_snapshot($filename);
        
        if ($file_snapshot !== false) {
            $diff_result = array_diff($file_snapshot, $file_info);
        } else {
            $diff_result = $file_info;
        }
        
        #
        # Unset unused data from diff array
        #
        unset($diff_result['signature']);
        unset($diff_result['signature_check_record']);
        unset($diff_result['signature_check_content']);
        
        if (
            !empty($diff_result)
        ) {
            if ($extended) {
                $errors[] = array(
                    'filename' => $filename,
                    'snapshot' => $file_snapshot,
                    'info' => $file_info,
                    'error' => $diff_result,
                );
            } else {
                $errors[] = array(
                    'filename' => $filename,
                    'error' => $diff_result,
                );
            }            
        }
    }
    
    return empty($errors) ? true : $errors;
}

function func_xmonitoring_API_has_files($files, $extended = false)
{
    $errors = array();
    
    foreach($files as $filename => $file_info) {
        
        if ($extended) {
            $errors[] = array(
                'filename' => $filename,
                'info' => $file_info,
                'error' => $file_info,
            );
        } else {
            $errors[] = array(
                'filename' => $filename,
                'error' => $file_info,
            );
        }        
    }
    
    return empty($errors) ? true : $errors;
}

function func_xmonitoring_API_get_system_files($xmonitoring_rules, $xcart_version)
{
    if (
        isset($xmonitoring_rules['systemFiles'][$xcart_version])
    ) {
        return func_xmonitoring_get_files_by_mask($xmonitoring_rules['systemFiles'][$xcart_version]);
    }
    
    return false;
}

function func_xmonitoring_API_get_secure_files($xmonitoring_rules, $xcart_version)
{
    if (
        isset($xmonitoring_rules['secureFiles'][$xcart_version])
    ) {
        return func_xmonitoring_get_files_by_mask($xmonitoring_rules['secureFiles'][$xcart_version]);
    }
    
    return false;
}

function func_xmonitoring_API_get_skin_files($xmonitoring_rules, $xcart_version)
{
    if (
        isset($xmonitoring_rules['skinFiles'][$xcart_version]['exclude'])
    ) {
        return func_xmonitoring_get_files_by_mask($xmonitoring_rules['skinFiles'][$xcart_version]['exclude']);
    }
    
    return false;
}

function func_xmonitoring_API_get_hack_files($xmonitoring_rules)
{
    if (
        isset($xmonitoring_rules['hackFiles'])
    ) {
        return func_xmonitoring_get_files_by_mask($xmonitoring_rules['hackFiles']);
    }
    
    return false;
}

function func_xmonitoring_API_accept_file($xmonitoring_rules, $xcart_version, $filename)
{  
    $system_files = func_xmonitoring_API_get_system_files($xmonitoring_rules, $xcart_version);
    $secure_files = func_xmonitoring_API_get_secure_files($xmonitoring_rules, $xcart_version);

    $fileinfo = func_xmonitoring_get_file_info($filename);
    
    if (
        in_array($fileinfo, $system_files, true)
        || in_array($fileinfo, $secure_files, true)
    ) {
        return func_xmonitoring_set_file_snapshot($filename);
    }
    
    return false;
}

function func_xmonitoring_API_diff_file($xmonitoring_rules, $xcart_version, $filename)
{
    global $smarty;

    $system_files = func_xmonitoring_API_get_system_files($xmonitoring_rules, $xcart_version);
    $secure_files = func_xmonitoring_API_get_secure_files($xmonitoring_rules, $xcart_version);

    $fileinfo_to = func_xmonitoring_get_file_info($filename);
    
    if (
        in_array($fileinfo_to, $system_files, true)
        || in_array($fileinfo_to, $secure_files, true)
    ) {
        x_load('files');
        
        $fileinfo_from = func_xmonitoring_get_file_snapshot($filename, true);
        
        $smarty->assign('xmonitoring_snapshot_error', 'Y');
        $file_diff = func_get_langvar_by_name('err_xmonitoring_no_file_snapshot', array('filename' => $filename), false, true);
        
        if ($fileinfo_from) {
        
            if (
                isset($fileinfo_from['signature_check_content'])
                && $fileinfo_from['signature_check_content']
            ) {

                $filepath_from = func_temp_store($fileinfo_from['fcontent']);
                $filepath_to = func_xmonitoring_resolve_relative_filename($filename);

                unset($fileinfo_from['fcontent']);
                
                $file_diff = func_xmonitoring_compare_files($filepath_from, $filepath_to);
                
                $smarty->assign('xmonitoring_snapshot_error', 'N');

                unlink($filepath_from);

            } else {
                $file_diff = func_get_langvar_by_name('err_xmonitoring_invalid_snapshot', array('filename' => $filename), false, true);
            }
        }
        
        $result = array(
            'fileinfo_from' => $fileinfo_from,
            'fileinfo_to' => $fileinfo_to,
            'file_diff' => $file_diff,
        );

        return $result;
    }
    
    return false;
}

function func_xmonitoring_API_restore_file($xmonitoring_rules, $xcart_version, $filename)
{    
    $system_files = func_xmonitoring_API_get_system_files($xmonitoring_rules, $xcart_version);
    $secure_files = func_xmonitoring_API_get_secure_files($xmonitoring_rules, $xcart_version);

    $fileinfo = func_xmonitoring_get_file_info($filename);
    
    $file_snapshot = func_xmonitoring_get_file_snapshot($filename, true);
    
    if (
        (
            in_array($fileinfo, $system_files, true)
            || in_array($fileinfo, $secure_files, true)
        )
        && $file_snapshot
    ) {        
        #
        # Check file snapshot signature
        #
        if (
            func_xmonitoring_check_content_signature($file_snapshot)
        ) {
            $file_path = func_xmonitoring_resolve_relative_filename($filename);
            
            if (file_put_contents($file_path, $file_snapshot['fcontent'], LOCK_EX) !== false) {
                return func_xmonitoring_set_file_snapshot($filename);
            }
        }
    }
    
    return false;
}

function func_xmonitoring_API_remove_file($xmonitoring_rules, $xcart_version, $filename)
{    
    $skin_files = func_xmonitoring_API_get_skin_files($xmonitoring_rules, $xcart_version);
    $hack_files = func_xmonitoring_API_get_hack_files($xmonitoring_rules);
    
    $fileinfo = func_xmonitoring_get_file_info($filename);
    
    if (
        in_array($fileinfo, $skin_files, true)
        || in_array($fileinfo, $hack_files, true)
    ) {
        return unlink(func_xmonitoring_resolve_relative_filename($filename));
    }
    
    return false;
}

function func_xmonitoring_API_generate_snapshot($xmonitoring_rules, $xcart_version)
{
    // get files to check
    $system_files = func_xmonitoring_API_get_system_files($xmonitoring_rules, $xcart_version);
    $secure_files = func_xmonitoring_API_get_secure_files($xmonitoring_rules, $xcart_version);
    
    $system_snapshots_result = func_xmonitoring_create_snapshots($system_files);
    $secure_snapshots_result = func_xmonitoring_create_snapshots($secure_files);
    
    if (
        is_array($system_snapshots_result)
        || is_array($secure_snapshots_result)
    ) {
        return array(
            'system_check' => $system_snapshots_result,
            'secure_check' => $secure_snapshots_result,
        );
    }
    
    return false;
}

function func_xmonitoring_API_check_installation($xmonitoring_rules, $xcart_version, $extended = false)
{
    if (
        func_xmonitoring_has_monitoring_files() == 'Y'
        && func_xmonitoring_create_key()
    ) {
        // get files to check
        $system_files = func_xmonitoring_API_get_system_files($xmonitoring_rules, $xcart_version);
        $secure_files = func_xmonitoring_API_get_secure_files($xmonitoring_rules, $xcart_version);

        $skin_files = func_xmonitoring_API_get_skin_files($xmonitoring_rules, $xcart_version);
        $hack_files = func_xmonitoring_API_get_hack_files($xmonitoring_rules);

        // we do have file snapshots, check changes
        $system_check_result = func_xmonitoring_API_check_files($system_files, $extended);
        $secure_check_result = func_xmonitoring_API_check_files($secure_files, $extended);

        $skin_check_result = func_xmonitoring_API_has_files($skin_files, $extended);
        $hack_check_result = func_xmonitoring_API_has_files($hack_files, $extended);

        if (
                is_array($system_check_result)
                || is_array($secure_check_result)
                || is_array($skin_check_result)
                || is_array($hack_check_result)
        ) {
            return array(
                'system_check' => $system_check_result,
                'secure_check' => $secure_check_result,
                'skin_check' => $skin_check_result,
                'hack_check' => $hack_check_result,
            );
        }
    }

    return true;
}

function func_xmonitoring_API_has_webpages($xmonitoring_rules, $xcart_version)
{
    if (
        isset($xmonitoring_rules['webPages'][$xcart_version])
    ) {
        return (count($xmonitoring_rules['webPages'][$xcart_version]) > 0 ? true : false);
    }

    return false;
}

function func_xmonitoring_API_check_webpages($xmonitoring_rules, $xcart_version, $extended = false)
{
    if (
        func_xmonitoring_API_has_webpages($xmonitoring_rules, $xcart_version)
    ) {
        $pages = func_xmonitoring_get_pages();

        if (
            $pages
        ) {
            return $pages;
        }
    }

    return false;
}

function func_xmonitoring_API_diff_page($xmonitoring_rules, $xcart_version, $filename, $skip_pre_tag = false)
{
    global $smarty;
    
    $page = func_xmonitoring_get_page($filename);

    if ($page) {

        x_load('files');

        $filepath_from = func_temp_store(base64_decode($page['page_content_old']));
        $filepath_to = func_temp_store(base64_decode($page['page_content_new']));

        $file_diff = func_xmonitoring_compare_files($filepath_from, $filepath_to, $skip_pre_tag);

        $smarty->assign('xmonitoring_snapshot_error', 'N');

        unlink($filepath_from);
        unlink($filepath_to);

        $result = array(
            'fileinfo_from' => array('filename' => $filename),
            'fileinfo_to' => array('filename' => $filename),
            'file_diff' => $file_diff,
        );

        return $result;
    }

    return false;
}

function func_xmonitoring_API_accept_page($xmonitoring_rules, $xcart_version, $pagename)
{
    return func_xmonitoring_set_page($pagename);
}

function func_xmonitoring_API_output($xm_data)
{
    //header('Content-type: application/php');

    echo serialize($xm_data);
}

if (
    !empty($action)
    && !empty($apikey)
) {
    if (
        func_xmonitoring_verify_apikey($apikey)
        && func_xmonitoring_verify_request_ip()
        && ($xmonitoring_rules = func_xmonitoring_get_rules()) != false
        && ($xmonitoring_xc_version = func_xmonitoring_get_xcart_version()) != false
    ) {
        $result = false;
        #
        # API action processor
        #
        switch ($action) {        
            case 'check_installation':
                $result = func_xmonitoring_API_check_installation($xmonitoring_rules, $xmonitoring_xc_version);
                break;
        }
        #
        # Return result
        #
        func_xmonitoring_API_output(
            new DTO($action,
                'OK',
                array(
                    'apikey' => $apikey,
                    'errors' => $result,
                )
            )
        );

    } else {
        echo 'Error: 102, command not supported.';
    }
    
    exit;
}

?>
