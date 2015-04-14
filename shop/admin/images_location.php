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
 * Image types locations (FS/DB) interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    187be6efc0a6c31dd489f08d9e4028b5df5305a6, v86 (xcart_4_7_0), 2015-02-18 00:12:08, images_location.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

x_load('backoffice','files','image');

func_set_time_limit(2700);

$location[] = array(func_get_langvar_by_name('lbl_images_location'), '');

$logfile = 'imglog.txt';
$logfile_name = $var_dirs['log'].XC_DS.$logfile;
// process N images per pass
$images_step = 50;

if ($mode == 'viewlog') {

    if (file_exists($logfile_name)) {
        $incfile = func_file_get($logfile_name, true);
        $smarty->assign('incfile', $incfile);
    }

    func_display('admin/main/images_location_log.tpl',$smarty);
    func_html_location('images_location.php', 60);
    exit;
}

/**
 * Main code
 */
if ($REQUEST_METHOD == 'POST') {
    require $xcart_dir.'/include/safe_mode.php';

    $display_service_header = false;

    $str_out = '';
    foreach ($data as $k => $v) {
        $k = stripslashes($k);
        if (!isset($config['available_images'][$k]) || defined("NO_CHANGE_LOCATION_".$k))
            continue;

        $config_data = $config['setup_images'][$k];

        // If checkboxes are unchecked
        if (empty($v['save_url'])) {
            $v['save_url'] = '';
        }

        if (empty($v['md5_check'])) {
            $v['md5_check'] = '';
        }

        if ($v['location'] != $config_data['location'] || $v['save_url'] != $config_data['save_url']) {
            if (!$display_service_header) {
                func_display_service_header('lbl_moving_images_');
                $display_service_header = true;
            }
            $str_out .= func_get_langvar_by_name('lbl_image_title_'.$k, NULL, false, true)."\n".func_get_langvar_by_name('lbl_img_location', NULL, false, true)." ".$v['location']."\n\n";

            $v['location'] = stripslashes($v['location']);
            if (!func_move_images($k, $v)) {
                // cannot move, reuse old params
                $v['location'] = $config_data['location'];
                $str_out .= func_get_langvar_by_name('lbl_failed', NULL, false, true) . "\n";

            } else {
                $str_out .= func_get_langvar_by_name('lbl_success', NULL, false, true)."\n";
            }

            $str_out .= "\n";
        }

        func_unset($config_data, 'image_x', 'image_y');

        $is_update = false;
        foreach ($config_data as $k2 => $v2) {
            if ($config_data[$k2] != $v[$k2]) {
                $is_update = true;
                break;
            }
        }

        if ($is_update) {
            $config_data = func_addslashes(func_array_merge($config_data, $v));
            $config_data['itype'] = $k;
            func_array2insert('setup_images', $config_data, true);
        }
    }

    if (
        isset($data['T']['location']) &&
        isset($config['setup_images']["T"]["location"]) &&
        $data['T']['location'] != $config['setup_images']["T"]["location"]
    ) {
        // necessary when moving thumbnails only
        func_build_quick_flags();
    }

    if (
        isset($data['G']['location']) &&
        isset($config['setup_images']["G"]["location"]) &&
        $data['G']['location'] != $config['setup_images']["G"]["location"]
    ) {
        func_data_cache_clear('languages');
    }

    func_data_cache_get('setup_images', array(), true);

    if ($str_out) {

        // Creating of log file

        $log_header = "    Remote IP: $REMOTE_ADDR\n    Logged as: $login\n    Request URI:$PHP_SELF\n\n";
        $str_out = "\n".str_repeat("*", 70)."\n\n".func_get_langvar_by_name('lbl_log_created', NULL, false, true)." ".date("Y-m-d H:i:s")."\n".$log_header.$str_out;

        $fd = func_fopen($logfile_name, "a+", true);
        if ($fd) {
            fwrite($fd, $str_out);
            fclose($fd);
            func_chmod_file($logfile_name);

            func_header_location("images_location.php?mode=viewlog");
        }
        else {
            echo func_get_langvar_by_name('txt_log_file_N_cant_be_created', array('logfile' => $logfile_name), false, true)."<p>\n";
            echo "<pre>".$str_out."</pre>";
            echo "<br /><br /><a href=\"images_location.php\">".func_get_langvar_by_name('lbl_continue', NULL, false, true)."</a>";
            exit();
        }
    }

    func_header_location('images_location.php');
}

if (file_exists($logfile_name)) {
    $smarty->assign('log_file', "images_location.php?mode=viewlog");
    $smarty->assign('log_file_date', filemtime($logfile_name));
}

foreach($config['available_images'] as $t => $u) {
    if (defined('NO_CHANGE_LOCATION_'.$t))
        unset($config['available_images'][$t]);
}

foreach ($config['available_images'] as $k => $v) {
    $config['setup_images'][$k]['label'] =
        func_get_langvar_by_name('lbl_image_title_'.$k,
            null, false, true);

    if (empty($config['setup_images'][$k]['label'])) {
        $config['setup_images'][$k]['label'] =
            func_get_langvar_by_name('lbl_image_title_',
                array('type' => $k), false, true);
    }

    $dialog_tools_data['left'][] = array(
        'link' => "#".$k,
        'title' => $config['setup_images'][$k]['label']
    );
}

$smarty->assignByRef('config', $config);
/**
 * Smarty display code goes here
 */
$smarty->assign('xcart_dir',$xcart_dir);
$smarty->assign('main','images_location');

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);

?>
