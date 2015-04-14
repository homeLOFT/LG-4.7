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
 * Upgrade interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    9f5c9028d1fc32fd925d3c226f8754781bc5300b, v63 (xcart_4_7_0), 2015-02-24 17:07:52, upgrade.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }


define('ADMIN_UPGRADE.PHP', TRUE);

require $xcart_dir.'/include/safe_mode.php';
func_check_perms_redirect(XCActions::UPGRADE);

$patch_result    = array();
$patch_log       = array();
$patch_errorcode = $prepatch_errorcode = $postpatch_errorcode = $sql_errorcode = 1;
$patch_completed = 0;

list($xcart_current_version, $xcart_target_version) =
    explode("-", strtr($target_version, '_'," "));

func_auto_scroll(func_get_langvar_by_name('txt_applying_patch_wait', NULL, false, true)."<hr />\n");

/**
 * Prepare patch stage
 */
if (is_readable($upgrade_repository . XC_DS . $patch_filename . XC_DS . 'patch_pre.php')) {
    $prepatch_errorcode = 0;

    echo func_get_langvar_by_name('lbl_applying_pre_patch',false,false,true);
    flush();

    include $upgrade_repository . XC_DS . $patch_filename . XC_DS . 'patch_pre.php';

    if ($prepatch_errorcode == 1) {
        $patch_result[] = func_get_langvar_by_name('lbl_pre_patch_was_applied_successfully');
        func_flush(func_get_langvar_by_name('lbl_ok',false,false,true)."<br />\n");
    } else {
        $patch_result[] = "<font color=\"red\">".func_get_langvar_by_name('lbl_pre_patch_was_not_applied',false,false,true)."</font>";
        if (!empty($patch_pre_err)) 
            $patch_result[] = $patch_pre_err;
        
        func_flush("<font color=\"red\">".func_get_langvar_by_name('lbl_error',false,false,true)."</font><br />");
    }
}

/**
 * Begin upgrade only if prepatch was successful
 */
if ($prepatch_errorcode == 1) {

require $xcart_dir . DIR_ADMIN . '/patch_files.php';

if ($dir = @opendir($upgrade_repository . XC_DS . $target_version)) {

    if ($patch_errorcode != 1) {
        $patch_result[] = "<font color=\"red\">DIFF PATCH FAILED</font>";
    }
    // Apply .sql patches


    // Check for DB version first

    $db_version = func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name='version'");

    if (empty($db_version)) {
        $patch_result[] = "<font color=\"red\">".func_get_langvar_by_name('lbl_wrong_db_version', NULL, false, true)."</font>";
        $sql_errorcode  = 0;
    }
    elseif ($db_version == $xcart_target_version) {
        $patch_result[] = "<font color=\"blue\">".func_get_langvar_by_name('lbl_db_patched_already', NULL, false ,true)."</font>";
    }
    else {

        // Patch database

        $patch_lines = array();
        $sql_errors   = array();
        $sql_patch_files = array();
        $_mods = func_query_column("SELECT module_name FROM $sql_tbl[modules]");

        // SQL-Patch names convention:

        // patch.sql - patch for X-Cart core
        // patch_*.sql - reserved for addons.
        // *.sql - other possbile patch
        //         (cannot contain prefix 'patch_')

        //                    \1 - patch for addon       \2 - wrong patch
        $re = '@^(?:patch(?:_('.implode('|',$_mods).'))|(patch_).*|.*).sql$@';
        while (($file = readdir($dir)) !== false) {
            if (preg_match($re, $file, $refs) && empty($refs[2]))
                $sql_patch_files[] = $file;
        }
        closedir($dir);

        sort($sql_patch_files);

        $sql_apply_error = false;
        $sql_empty_patch = true;
        foreach ($sql_patch_files as $file) {
            $patch_lines = file("$upgrade_repository/$target_version/$file");

            if (empty($patch_lines))
                continue;

            $sql_empty_patch = false;
            $sql_errors = ExecuteSqlQuery(implode('',$patch_lines), XC_CONTINUE_ON_SQLERROR);

            if (!empty($sql_errors)) {
                $patch_result[] = "<font color=red>SQL PATCH ``$file'' FAILED AT QUERIES:</font>";
                foreach ($sql_errors as $sk => $sql_error) {
                    $patch_result[] = "\t\t" . $sql_error;
                    $sql_errors[$sk] = substr(strip_tags($sql_error), 0, 255);
                }

                $_sql_file = array(
                    'status' => 10,
                    'orig_file' => $file
                );

                $_sql_file['status_lbl'] = "SQL PATCH `$file' FAILED AT QUERIES:";
                $_sql_file['status_txt'] = strip_tags( implode('<br>', $sql_errors), '<br>');

                $phase_result['failed_files'][] = $_sql_file;

                $sql_apply_error = true;
            }
            else {
                $patch_result[] = "SQL PATCH: ``$file'' applied successfully"; 
            }
        }

        if ($sql_apply_error) {
            $sql_errorcode = 0;
        }
        else {
            $sql_errorcode = 1;
            $patch_result[] = "<font color=\"green\">".func_get_langvar_by_name('txt_db_successfully_patched', NULL, false, true)."</font>";
        }

        if ($sql_empty_patch) {
            $patch_result[] = "<font color=\"blue\">".func_get_langvar_by_name('lbl_empty_sql_patch', NULL, false, true)."</font>";
        }
    }

}

/**
 * Run post-patch script
 */


if (is_readable($upgrade_repository . XC_DS . $patch_filename . XC_DS . 'patch_post.php')) {
    $postpatch_errorcode = 0;

    echo "<br />\n" . func_get_langvar_by_name('lbl_applying_after_patch',false,false,true);
    flush();

    include $upgrade_repository . XC_DS . $patch_filename . XC_DS . 'patch_post.php';

    if ($postpatch_errorcode == 1) {
        $patch_result[] = func_get_langvar_by_name('lbl_after_patch_was_applied_successfully',false,false,true);
        echo func_get_langvar_by_name('lbl_ok',false,false,true)."<br />\n";
    } else {
        $patch_result[] = "<font color=\"red\">".func_get_langvar_by_name('lbl_after_patch_was_no_applied',false,false,true)."</font>";
        echo "<font color=\"red\">".func_get_langvar_by_name('lbl_error',false,false,true)."</font><br />\n";
    }
    flush();
}

/**
 * Update version & upgrade history if files and sql DB are patched OK
 */
if ($patch_errorcode == 1 || $sql_errorcode == 1 || $postpatch_errorcode == 1) {
    $patch_result[] = "Updating DB version info.";
    db_query("UPDATE $sql_tbl[config] SET value='$config[upgrade_history]\n$xcart_current_version-$xcart_target_version' WHERE name='upgrade_history'");

    if ($xcart_target_version != '4.7.0') {
        db_query("UPDATE $sql_tbl[config] SET value='".$xcart_target_version."' WHERE name='version'");
    } else {
        db_query("UPDATE $sql_tbl[config] SET value='".$xcart_target_version." beta' WHERE name='version'");
    }

    if ($patch_errorcode == 1 && $sql_errorcode == 1 && $postpatch_errorcode == 1)
        $patch_completed = 1;

    x_session_unregister('patch_files');

    // Clear cache/templates_c anyway for any successful operation
    func_rm_dir($var_dirs['templates_c'], true);
    func_rm_dir($var_dirs['cache'], true);
    
    // Remove file key for the upgrade pack
    @unlink($upgrade_repository . XC_DS . $patch_filename . XC_DS . 'allow2upgrade_key');
} else {
    $patch_result[] = "<font color=\"red=\">".func_get_langvar_by_name('lbl_db_version_has_not_been_updated', NULL, false, true)."</font>";
}

} // if ($prepatch_errorcode == 1) {

/**
 * Storing phase results
 */

$phase_result['patched_files'] = $patched_files;
$phase_result['excluded_files'] = $excluded_files;
$phase_result['patch_log'] = $patch_log;
$phase_result['patch_phase'] = 'upgrade_final';
$phase_result['patch_result'] = $patch_result;
$phase_result['patch_completed']= $patch_completed;

?>
