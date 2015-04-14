<?php
// vim: set ts=4 sw=4 sts=4 et:
/*****************************************************************************\
+-----------------------------------------------------------------------------+
| X-Cart                                                                      |
| Copyright (c) 2001-2015 Ruslan R. Fazlyev <rrf@x-cart.com>                  |
| All rights reserved.                                                        |
+-----------------------------------------------------------------------------+
| PLEASE READ  THE FULL TEXT OF SOFTWARE LICENSE AGREEMENT IN THE "COPYRIGHT" |
| FILE PROVIDED WITH THIS DISTRIBUTION. THE AGREEMENT TEXT IS ALSO AVAILABLE  |
| AT THE FOLLOWING URL: http://www.x-cart.com/license.php                     |
|                                                                             |
| THIS  AGREEMENT  EXPRESSES  THE  TERMS  AND CONDITIONS ON WHICH YOU MAY USE |
| THIS SOFTWARE   PROGRAM   AND  ASSOCIATED  DOCUMENTATION   THAT  RUSLAN  R. |
| FAZLYEV (hereinafter  referred to as "THE AUTHOR") IS FURNISHING  OR MAKING |
| AVAILABLE TO YOU WITH  THIS  AGREEMENT  (COLLECTIVELY,  THE  "SOFTWARE").   |
| PLEASE   REVIEW   THE  TERMS  AND   CONDITIONS  OF  THIS  LICENSE AGREEMENT |
| CAREFULLY   BEFORE   INSTALLING   OR  USING  THE  SOFTWARE.  BY INSTALLING, |
| COPYING   OR   OTHERWISE   USING   THE   SOFTWARE,  YOU  AND  YOUR  COMPANY |
| (COLLECTIVELY,  "YOU")  ARE  ACCEPTING  AND AGREEING  TO  THE TERMS OF THIS |
| LICENSE   AGREEMENT.   IF  YOU    ARE  NOT  WILLING   TO  BE  BOUND BY THIS |
| AGREEMENT, DO  NOT INSTALL OR USE THE SOFTWARE.  VARIOUS   COPYRIGHTS   AND |
| OTHER   INTELLECTUAL   PROPERTY   RIGHTS    PROTECT   THE   SOFTWARE.  THIS |
| AGREEMENT IS A LICENSE AGREEMENT THAT GIVES  YOU  LIMITED  RIGHTS   TO  USE |
| THE  SOFTWARE   AND  NOT  AN  AGREEMENT  FOR SALE OR FOR  TRANSFER OF TITLE.|
| THE AUTHOR RETAINS ALL RIGHTS NOT EXPRESSLY GRANTED BY THIS AGREEMENT.      |
|                                                                             |
| The Initial Developer of the Original Code is Ruslan R. Fazlyev             |
| Portions created by Ruslan R. Fazlyev are Copyright (C) 2001-2015           |
| Ruslan R. Fazlyev. All Rights Reserved.                                     |
+-----------------------------------------------------------------------------+
*****************************************************************************/

/**
 * Common module installator
 * 
 * @category   X-Cart
 * @package    Core
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com> 
 * @copyright  Copyright (c) 2001-2015 Ruslan R. Fazlyev <rrf@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    686a4c944b1bab093a0d60e12aff76ce8db05664, v3 (xcart_4_7_0), 2015-02-18 13:16:20, install.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) { header('Location: ../'); die('Access denied'); }

// Start
require_once ($xcart_dir . '/include/patch.php');

global $modules, $module_definition;

$modules[2]['name'] = 'custom_modinstall';

if (0 <= ext_install_version_compare(ext_install_get_version(), '4.4.0')) {

    // 4.4.0 and higher
    if (isset($module_definition['skin_files'])) {
        if (3 == $_POST['params']['install_type'] || 3 == $HTTP_POST_VARS['params']['install_type']) {
            $module_definition['skin_files'] = true;

        } else {
            unset($module_definition['skin_files']);
        }
    }
}

/**
 * Functions
 */

/**
 * Custom module_modinstall step function
 * 
 * @param array $params Step parameters
 *  
 * @return boolean
 * @see    ____func_see____
 * @since  1.0.0
 */
function module_custom_modinstall($params)
{
    global $error;

    $displayed = false;
    $res = false;
    $errorMessage = false;

    $need = ext_install_need_changes();

    if (!$need) {

        $res = true;

    } elseif (!isset($params['patched'])) {
        list($displayed, $errorMessage) = ext_install_patch_step();
        $res = !$errorMessage;

    } elseif ('patch' == $params['patched']) {

        $errorMessage = ext_install_patch();
        $res = !$errorMessage;

    } elseif ('copy' == $params['patched']) {

        $errorMessage = ext_install_copy();
        $res = !$errorMessage;

    } elseif ('cancel' == $params['patched']) {

        if (headers_sent()) {
            echo '<meta http-equiv="Refresh" content="0;URL=' . $_SERVER['REQUEST_URI'] . '" />';

        } else {
            header('Location: ' . $_SERVER['REQUEST_URI']);
        }
    }

    if (isset($params['patched']) || (!$displayed && $res)) {
        module_modinstall($params);
        $res = !$error;
    }

    if ($errorMessage) {
        critical_error($errorMessage);

    } elseif ($displayed) {
        $res = false;
    }

    $error = !$res;
}

/**
 * Check - chnages need or not
 * 
 * @return boolean
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_need_changes()
{
    global $xcart_dir, $module_definition;

    $copyDir = $xcart_dir . DIRECTORY_SEPARATOR . $module_definition['prefix'] . '.copy';

    $changes = ext_install_compare_files($copyDir);

    return 0 < count($changes);
}

/**
 * Patch processing step
 * 
 * @return array Displayed status & error message
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_patch_step()
{
    global $xcart_dir, $module_definition;

    $patchDir = $xcart_dir . DIRECTORY_SEPARATOR . $module_definition['prefix'] . '.patches';
    $copyDir = $xcart_dir . DIRECTORY_SEPARATOR . $module_definition['prefix'] . '.copy';

    $errorMessage = false;
    $displayed = false;

    if (file_exists($patchDir) && is_dir($patchDir) &&!is_readable($patchDir)) {
        $errorMessage = 'Please make sure the account, under which the web server runs has read permissions for the directory <strong>' . $patchDir . '</strong>';
    }

    if (file_exists($copyDir) && is_dir($copyDir) && !is_readable($copyDir)) {
        $errorMessage = 'Please make sure the account, under which the web server runs has read permissions for the directory <strong>' . $copyDir . '</strong>';
    }

    if (!$errorMessage) {
        if (
            file_exists($patchDir)
            && is_dir($patchDir)
            && file_exists($copyDir)
            && is_dir($copyDir)
        ) {

            if (ext_install_check_patch()) {
                $errorMessage = ext_install_patch();
                
            } else {
                ext_install_show_choice();
                $displayed = true;
            }

        } elseif (file_exists($patchDir) && is_dir($patchDir)) {
            $errorMessage = ext_install_patch();

        } elseif (file_exists($copyDir) && is_dir($copyDir)) {
            $errorMessage = ext_install_copy();
        }

    }

    return array($displayed, $errorMessage);
}

/**
 * Apply patches
 * 
 * @return boolean
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_patch()
{
    ext_install_create_backup();

    $log = array();
    ext_install_show_css();
    echo '<p><strong>Apply patches ...</strong></p>';
    foreach (ext_install_get_patches() as $patch) {
        $rejects = false;
        func_patch_apply($patch['orig'], $patch['patch'], false, false, $log, $rejects);
    }

    foreach ($log as $str) {
        echo str_replace("\n", "<br />\n", $str) . '<br />' . "\n";
    }

    return false;
}

/**
 * Copy new files
 * 
 * @return void
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_copy()
{
    global $xcart_dir, $module_definition;

    ext_install_create_backup();

    ext_install_show_css();
    echo '<p><strong>Copy files ...</strong></p>';

    $copyDir = $xcart_dir . DIRECTORY_SEPARATOR . $module_definition['prefix'] . '.copy';

    return ext_install_copy_recursive($copyDir);
}

/**
 * Check patches and display checking results
 * 
 * @return boolean
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_check_patch()
{
    $res = true;

    $log = array();
    ext_install_show_css();
    echo '<p><strong>Check patches ...</strong></p>';
    foreach (ext_install_get_patches() as $patch) {
        $rejects = false;
        $res = func_patch_apply($patch['orig'], $patch['patch'], false, false, $log, $rejects, true);
        if (!$res) {
            break;
        }
    }

    foreach ($log as $str) {
        echo str_replace("\n", "<br />\n", $str) . '<br />' . "\n";
    }

    return $res;
}

/**
 * Compat functions
 */

if (!function_exists('critical_error')) {

/**
 * critical_error compat version
 * 
 * @param string $txt Error message
 *  
 * @return void
 * @see    ____func_see____
 * @since  1.0.0
 */
function critical_error($txt)
{
    ext_install_show_css();
    echo '<div id="dialog-message"><div class="box message-e" title="Error">' . $txt . '</div></div>';
}
}

/**
 * Service function
 */

/**
 * Show CSS
 * 
 * @return void
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_show_css()
{
    static $show = false;
    static $customShow = false;

    if (file_exists('skin/common_files/css/install.css')) {
        $show = true;
    }

    if (!$show) {
        $show = true;
        echo <<<CSS
<style type="text/css">
<!--
#dialog-message {
  position: relative;
  margin: 15px 0 20px;
  width: 100%;
  text-align: center;
}

#dialog-message .box {
  position: relative;
  width: 450px;
  margin: 0 auto;
  border: 1px solid #000;
  border-top: 3px solid #000;
  padding: 10px 25px 10px 59px;
  vertical-align: middle;
  text-align: left;
  min-height: 32px;
}

#dialog-message .box a.close-link:link,
#dialog-message .box a.close-link:visited,
#dialog-message .box a.close-link:hover,
#dialog-message .box a.close-link:active
{
  display: block;
  position: absolute;
  top: 5px;
  right: 5px;
  width: 13px;
  height: 13px;
  text-decoration: none;
}

#dialog-message .close-img {
  width: 13px;
  height: 13px;
  background: transparent url(../images/but_cross.gif) no-repeat left top;
}

#dialog-message .message-i {
  color: #112536;
  border-color: #7a97c1;
  background: #f4f5f7 url(../images/icon_info.gif) no-repeat 10px 10px;
}

#dialog-message .message-w {
  color: #3e3104;
  border-color: #c3902f;
  background: #f8f7f3 url(../images/icon_warning.gif) no-repeat 10px 10px;
}

#dialog-message .message-e {
  color: #590a0a;
  border-color: #d30000;
  background: #f7f3f3 url(../images/icon_error.gif) no-repeat 10px 10px;
}

#dialog-message .anchor {
  position: relative;
  margin-left: auto;
  margin-right: 0;
  height: 15px;
  text-align: right;
  vertical-align: middle;
}

#dialog-message .anchor img {
  width: 12px;
  height: 10px;
  vertical-align: middle;
  background: transparent url(../images/goto_arr.gif) no-repeat left top;
}
-->
</style>
CSS;
    }

    if (!$customShow) {
        $customShow = true;
        echo <<<CSS2
<style type="text/css">
<!--
ul.choice {
    padding: 0px;
    margin: 0px;
}

ul.choice li {
    padding: 0px;
    margin: 0px;
    list-style: none;
    vertical-align: middle;
}

ul.choice li * {
    vertical-align: middle;
}

.center {
    text-align: center;
}
-->
</style>
CSS2;
    }
}

/**
 * Show choice block
 * 
 * @return void
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_show_choice()
{
    global $xcart_dir, $module_definition;

    ext_install_show_css();

    $path = $xcart_dir . DIRECTORY_SEPARATOR . $module_definition['prefix'] . '.backup';

    echo <<<HTML
<p>Patches cannot be applied, because the files which are to be patched, have been modified before. Do you want to copy the pre-patched files over the old ones or to cancel the installation?</p>
<ul class="choice">
    <li><input type="radio" name="params[patched]" value="copy" id="patched_copy" checked="checked" /><label for="patched_copy">Copy</label></li>
    <li><input type="radio" name="params[patched]" value="cancel" id="patched_cancel" /><label for="patched_cancel">Cancel</label></li>
</ul>
<p><strong>Warning!</strong> Copying patched files over the old ones may break your store. If this happens please copy the contents of the $path directory to your X-Cart installation directory.</p>
<div class="center"><input type="submit" onclick="javascript: this.form.elements.namedItem('current').value = this.form.elements.namedItem('current').value - 1; return true;" /></div>
HTML;
}

/**
 * Get patches list
 * 
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_get_patches()
{
    global $xcart_dir, $module_definition;

    $patchDir = $xcart_dir . DIRECTORY_SEPARATOR . $module_definition['prefix'] . '.patches';

    return ext_install_scan_patches($patchDir);

}

/**
 * Scan pacthes recursive
 * 
 * @param string $path Path
 *  
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_scan_patches($path)
{
    global $xcart_dir;

    $result = array();

    $d = @opendir($path);
    if ($d) {
        while ($file = readdir($d)) {
            if ('.' == $file || '..' == $file) {
                continue;
            }

            $lpath = $path . DIRECTORY_SEPARATOR . $file;
    
            if (is_dir($lpath)) {
                $result = array_merge($result, ext_install_scan_patches($lpath));

            } elseif (preg_match('/\.patch$/Ss', $file)) {
                $result[$lpath] = array(
                    'patch' => $lpath,
                    'orig'  => $xcart_dir . DIRECTORY_SEPARATOR . preg_replace('/^.+\.patches./Ss', '', substr($lpath, 0, -6)),
                );
            }
        }
        closedir($d);
    }

    return $result;
}

/**
 * Copy new files recursive 
 * 
 * @param string $path Path
 *  
 * @return void
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_copy_recursive($path)
{
    global $xcart_dir;

    $d = @opendir($path);
    if ($d) {
        while ($file = readdir($d)) {
            if ('.' == $file || '..' == $file || 'CVS' == $file || '.svn' == $file || '.htaccess' == $file) {
                continue;
            }

            $lpath = $path . DIRECTORY_SEPARATOR . $file;
    
            if (is_dir($lpath)) {
                ext_install_copy_recursive($lpath);

            } else {
                $copy = $xcart_dir . DIRECTORY_SEPARATOR . preg_replace('/^.+\.copy./Ss', '', $lpath);
                echo 'Copy ' . $lpath . ' to ' . $copy . ' ...<br />' . "\n";
                copy($lpath, $copy);
            }
        }
        closedir($d);
    }
}

/**
 * Create bbackup 
 * 
 * @return void
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_create_backup()
{
    global $xcart_dir, $module_definition;

    ext_install_show_css();
    echo '<p><strong>Create backup ...</strong></p>';

    $backupPath = $xcart_dir . DIRECTORY_SEPARATOR . $module_definition['prefix'] . '.backup';
    if (file_exists($backupPath)) {
        func_rm_dir($backupPath);
    }
    mkdir($backupPath);

    $copyDir = $xcart_dir . DIRECTORY_SEPARATOR . $module_definition['prefix'] . '.copy';

    return ext_install_create_backup_recursive($copyDir, $backupPath, $xcart_dir);
}

/**
 * Create backup recursive 
 * 
 * @param string $copyPath   Original path
 * @param string $backupPath Backup path
 * @param string $localPath  Local path
 *  
 * @return void
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_create_backup_recursive($copyPath, $backupPath, $localPath)
{
    $d = @opendir($copyPath);
    if ($d) {
        while ($file = readdir($d)) {
            if ('.' == $file || '..' == $file || 'CVS' == $file || '.svn' == $file || '.htaccess' == $file) {
                continue;
            }

            $lpath = $copyPath . DIRECTORY_SEPARATOR . $file;
            $bpath = $backupPath . DIRECTORY_SEPARATOR . $file;
            $xpath = $localPath . DIRECTORY_SEPARATOR . $file;

            if (!file_exists($xpath)) {
                continue;
            }    

            if (is_dir($lpath)) {
                if (!file_exists($bpath)) {
                    mkdir($bpath);
                }

                ext_install_create_backup_recursive($lpath, $bpath, $xpath);

            } else {
                echo 'Backup ' . $xpath . ' ...<br />' . "\n";
                copy($xpath, $bpath);
            }
        }
        closedir($d);
    }
}

/**
 * Compare files recursive
 * 
 * @param string $path Path
 *  
 * @return array
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_compare_files($path)
{
    global $xcart_dir;

    $result = array();

    $d = @opendir($path);
    if ($d) {
        while ($file = readdir($d)) {
            if ('.' == $file || '..' == $file || 'CVS' == $file || '.svn' == $file || '.htaccess' == $file) {
                continue;
            }

            $lpath = $path . DIRECTORY_SEPARATOR . $file;
    
            if (is_dir($lpath)) {
                $result = array_merge($result, ext_install_compare_files($lpath));

            } else {
                $copy = $xcart_dir . DIRECTORY_SEPARATOR . preg_replace('/^.+\.copy./Ss', '', $lpath);
                if (!file_exists($copy) || md5_file($lpath) != md5_file($copy)) {
                    $result[] = $lpath;
                }
            }
        }
        closedir($d);
    }

    return $result;
}

/**
 * Get X-Cart version 
 * 
 * @return string
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_get_version()
{
    global $sql_tbl;

    $version = func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name='version'");

    return $version ? $version : '2.4.1';
}

/**
 * [COMPAT] version_compare() function
 * 
 * @param string $ver1 Version 1
 * @param string $ver2 Version 2
 *  
 * @return integer
 * @see    ____func_see____
 * @since  1.0.0
 */
function ext_install_version_compare($ver1, $ver2)
{
    if (function_exists('version_compare')) {
        return version_compare($ver1, $ver2);
    }

    $ver1 = str_replace('..', '.', preg_replace('/([^\d\.]+)/S', '.\\1.', str_replace(array('_', '-', '+'), array('.', '.', '.'), $ver1)));
    $ver2 = str_replace('..', '.', preg_replace('/([^\d\.]+)/S', '.\\1.', str_replace(array('_', '-', '+'), array('.', '.', '.'), $ver2)));

    $ver1 = (array)explode('.', $ver1);
    $ver2 = (array)explode('.', $ver2);

    $ratings = array(
        '/^dev$/Si'   => -100,
        '/^alpha$/Si' => -90,
        '/^a$/Si'     => -90,
        '/^beta$/Si'  => -80,
        '/^b$/Si'     => -80,
        '/^RC$/Si'    => -70,
        '/^pl$/Si'    => -60,
    );
    $keys = array_keys($ratings);
    $values = array_values($ratings);
    $result = 0;
    foreach ($ver1 as $k => $v) {
        if (!is_numeric($v)) {
            $v = preg_replace($keys, $values, $v);
        }

        if (!is_numeric($ver2[$k])) {
            $ver2[$k] = preg_replace($keys, $values, $ver2[$k]);
        }

        $r = strcmp($v, $ver2[$k]);
        if ($r != 0) {
            $result = $r;
            break;
        }
    }

    return $result;
}

