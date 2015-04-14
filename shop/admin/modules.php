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
 * Script for module management
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v99 (xcart_4_7_0), 2015-02-17 13:29:01, modules.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('ADMIN_MODULES_CONTROLLER', true);

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

$location[] = array(func_get_langvar_by_name('lbl_modules'), '');

function _func_toggle_module($module_name, $module_new_state_is_active) { //{{{

    global $sql_tbl, $active_modules, $login, $shop_type, $identifiers, $top_message;

    $state_changed = (!empty($active_modules[$module_name]) != $module_new_state_is_active);

    if (!$state_changed) {
        return '';
    }

    $redirect = '';

    $reload_self = 'modules.php';

    // Below are old-style events replaced with "module.ajax.toggle" now

    if ($module_name == 'Simple_Mode') {

        x_session_register('identifiers', array());
        if (empty($active_modules['Simple_Mode']) && $module_new_state_is_active) {
            $identifiers['P'] = $identifiers['A'];
        } else {
            func_unset($identifiers, 'P');
        }

        $redirect = $reload_self;

    } elseif ($module_name == 'Flyout_Menus' && $module_new_state_is_active) {

        $redirect = 'modules.php?mode=fc_rebuild';

    } elseif ($module_name == 'Product_Options') {

        $redirect = 'modules.php?mode=cache_rebuild';

    }

    if (empty($active_modules[$module_name])) {

        // Set error top message in case of module fail
        $save_message = $top_message;
        $top_message = array(
            'type' => 'E',
            'content' => func_get_langvar_by_name('err_module_enable_error')
        );
        x_session_save();

        $active_modules[$module_name] = true;

        // Declare some global vars to be used in included modules
        global $xcart_dir, $config, $xcart_catalogs;
        global $xcart_http_host, $xcart_https_host, $xcart_web_dir, $current_location;
        global $data_caches, $var_dirs, $memcache;
        global $smarty, $mail_smarty, $smarty_skin_dir;
        global $REQUEST_METHOD, $HTTPS, $HTTP_USER_AGENT;
        global $xc_security_key_session, $xc_security_key_config;

        $include_func = $include_init = true;

        $_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . $module_name;
        $_config_file = $_module_dir . XC_DS . 'config.php';
        if (is_readable($_config_file)) {
            include_once $_config_file;
        }

        $top_message = $save_message;

    }

    // Enable module in db after attempt to load it to avoid store crash with buggy modules
    db_query("UPDATE $sql_tbl[modules] SET active = '" . (($module_new_state_is_active) ? 'Y' : 'N') . "' WHERE module_name='$module_name'");

    $_returned_redirect = func_call_event('module.ajax.toggle', $module_name, $module_new_state_is_active);
    if (is_string($_returned_redirect) && strlen($_returned_redirect) > 0) {
        $redirect = $_returned_redirect;
    };

    if ($module_new_state_is_active) {
        x_log_flag('log_activity', 'ACTIVITY', "'$login' user has turned ON '$module_name' module");
        func_postpone_event('module.enable', $module_name);
    } else if (!($module_name == 'Simple_Mode' && stristr($shop_type, 'GOLD'))) {
        x_log_flag('log_activity', 'ACTIVITY', "'$login' user has turned OFF '$module_name' module");
        func_postpone_event('module.disable', $module_name);
    }

    return $redirect;

} //}}}

function _func_parse_module_tags($raw_module_tags, &$tags_list) { //{{{

    $allowed_filter_tags = array(
        'checkout',
        'marketing',
        'misc',
        'orders',
        'products',
        'security',
        'shipping',
        'stats',
        'taxes',
        'tools',
        'userexp',
    );

    $return = array();

    foreach ($raw_module_tags as $tag) {
        $tag = strtolower(trim($tag));
        if (!in_array($tag, $allowed_filter_tags)) {
            $tag = 'none';
        }
        if (!array_key_exists($tag, $tags_list)) {
            $tags_list[$tag] = array('count' => 0);
        }
        $tags_list[$tag]['count']++;

        if (!in_array($tag, $return)) {
             $return[] = $tag;
        }
    }

    return $return;

} //}}}

function _func_sort_module_tags($a, $b) { //{{{
    if ($a == 'none' || $b == 'none') {
        return ($a == 'none') ? 1 : -1;
    } else if ($a == 'all' || $b == 'all') {
        return ($a == 'all') ? -1 : 1;
    } else {
        return strcmp($a, $b);
    }
} //}}}

function _func_adjust_tags_list(&$tags_list, $all_count, $cookie_name) { //{{{

    if (count($tags_list) == 1) {
        $tags_list = array();
        return;
    }

    if (!empty($tags_list)) {

        if (
            !empty($_COOKIE[$cookie_name])
            && array_key_exists($_COOKIE[$cookie_name], $tags_list)
        ) {
            $selected_tag = $_COOKIE[$cookie_name];
        } else {
            $selected_tag = 'all';
        }

        $tags_list['all'] = array(
            'count' => $all_count
        );

        foreach ($tags_list as $k => $v) {
            $tags_list[$k]['label'] = func_get_langvar_by_name('lbl_modules_tag_' . strtolower($k));
            if ($k == $selected_tag) {
                $tags_list[$k]['checked'] = true;
            }
        }

        uksort($tags_list, '_func_sort_module_tags');
    }
} //}}}

if (func_is_ajax_request()) {

    if ($mode == 'toggle' && !empty($module_name) && isset($active)) {

        // Parse ajax request to enable module

        $active = (bool)$active;
        $state_changed = (!empty($active_modules[$module_name]) != $active);

        if (XCSecurity::$admin_safe_mode || !$state_changed) {
            func_register_ajax_message('moduleToggle', array('result' => 0, 'redirect' => 'modules.php'));
            func_header_location('modules.php');
        }

        $redirect = _func_toggle_module($module_name, $active);

        func_remove_xcart_caches(true, func_get_cache_dirs());

        func_register_ajax_message(
            'moduleToggle',
            array(
                'result' => 1,
                'redirect' => ((!empty($redirect)) ? $redirect : ''),
                'message' => ((!empty($top_message['content'])) ? $top_message : ''),
            )
        );

        if (empty($redirect)) {
            $top_message = '';
        }

    }

    func_header_location('modules.php');

}

if ($REQUEST_METHOD == 'POST') {

    require $xcart_dir . '/include/safe_mode.php';

    $redirect = '';

    foreach ($_POST as $module_name => $val) {
        if ($val == 'on') {
            $module_redirect = _func_toggle_module($module_name, true);
            if (!empty($module_redirect)) {
                $redirect = $module_redirect;
            }

        }
    }

    foreach ($active_modules as $module_name => $on) {
        if (!isset($_POST[$module_name])
            && !(
                $module_name == 'Simple_Mode'
                && stristr($shop_type, 'GOLD')
            )
        ) {
            $module_redirect =_func_toggle_module($module_name, false);
            if (!empty($module_redirect)) {
                $redirect = $module_redirect;
            }
        }
    }

    func_remove_xcart_caches(true, func_get_cache_dirs());

    if (!empty($redirect)) {
        func_header_location($redirect);
    }

    func_header_location('modules.php');

} elseif ($mode == 'fc_rebuild') {
    if (!empty($active_modules['Flyout_Menus']) && func_fc_use_cache()) {
        func_fc_build_categories(1);
    }

    func_header_location('modules.php');

} elseif ($mode == 'cache_rebuild') {
    func_build_quick_prices(false, 10);
    func_flush("<br />");
    func_build_quick_flags(false, 10);

    func_header_location('modules.php');
}
/**
 * Generate modules list
 */
$modules = func_query("SELECT * FROM $sql_tbl[modules] ORDER BY module_name");
$mod_options = func_query_column("SELECT DISTINCT $sql_tbl[modules].module_name FROM $sql_tbl[modules], $sql_tbl[config] WHERE $sql_tbl[modules].module_name=$sql_tbl[config].category", 'module_name');

$modules_filter_tags = array();

if (is_array($modules)) {
    $force_rebuild = false;
    foreach ($modules as $k => $v) {
        if ((!empty($active_modules[$v['module_name']]) && $v['active'] != 'Y') || (empty($active_modules[$v['module_name']]) && $v['active'] == 'Y')) {
            if ($v['active'] == 'Y') {
                $active_modules[$v['module_name']] = true;
            } else {
                $modules[$k]['active'] = 'Y';
            }
            $force_rebuild = true;
        }

        if (in_array($v['module_name'], $mod_options)) {
            if ($v['module_name'] == 'UPS_OnLine_Tools')
                $modules[$k]['options_url'] = 'ups.php';
            else
                $modules[$k]['options_url'] = "configuration.php?option=".addslashes($v['module_name']);
        }

        // Check module requirements
        $modules[$k]['requirements_passed'] = func_check_module_requirements($v['module_name']);

        if (empty($modules[$k]['requirements_passed']))
            $predefined_lng_variables[] = 'module_requirements_'.$v['module_name'];

        $predefined_lng_variables[] = 'module_descr_'.$v['module_name'];
        $predefined_lng_variables[] = 'module_name_'.$v['module_name'];
        $tmp = func_get_langvar_by_name('module_name_'.$v['module_name'], NULL, false, true);
        $modules[$k]['true_name'] = (empty($tmp) ? $v["module_name"] : $tmp);

        // Fill modules tags array
        $v['tags'] = (!empty($v['tags'])) ? explode(',', $v['tags']) : array('');
        $modules[$k]['tags'] = _func_parse_module_tags($v['tags'], $modules_filter_tags);

    }

    function func_sort_modules($a, $b) {
        return strcmp($a['true_name'], $b['true_name']);
    }

    usort($modules, 'func_sort_modules');

    if ($force_rebuild) {
        func_data_cache_get('modules', array(), true);
    }

    _func_adjust_tags_list($modules_filter_tags, count($modules), 'xcart_selected_tag_modules');

}

$smarty->assign('modules', $modules);
$smarty->assign('modules_filter_tags', $modules_filter_tags);
$smarty->assign('main', 'modules');

$modules_tabs = array();

$modules_tabs[] = array(
    'title' => func_get_langvar_by_name('lbl_modules_installed'),
    'tpl' => 'admin/main/modules_installed.tpl',
    'anchor' => 'installed',
);

$modules_tabs[] = array(
    'title' => func_get_langvar_by_name('lbl_modules_official'),
    'tpl' => 'admin/main/modules_official.tpl',
    'anchor' => 'official',
);

$modules_tabs[] = array(
    'title' => func_get_langvar_by_name('lbl_modules_thirdparty'),
    'tpl' => 'admin/main/modules_thirdparty.tpl',
    'anchor' => 'thirdparty',
);

$smarty->assign('admin_safe_mode', XCSecurity::$admin_safe_mode);
$smarty->assign('modules_tabs', $modules_tabs);

$thirdparty_banners = array();
$thirdparty_banners[] = array('zoneid' => '13', 'n' => 'ae2fa3f2');
$thirdparty_banners[] = array('zoneid' => '14', 'n' => 'adfa435c');
$thirdparty_banners[] = array('zoneid' => '15', 'n' => 'a9e81039');
$thirdparty_banners[] = array('zoneid' => '16', 'n' => 'a603eeed');
$thirdparty_banners[] = array('zoneid' => '17', 'n' => 'a87cfe9e');
$thirdparty_banners[] = array('zoneid' => '18', 'n' => 'a5c1aa23');

shuffle($thirdparty_banners);
$smarty->assign('thirdparty_banners', $thirdparty_banners);

$paid_modules = func_get_xcart_paid_modules();
$paid_modules_filter_tags = array();
if (!empty($paid_modules)) {
    foreach ($paid_modules as $k => $v) {
        if (empty($v['tags'])) {
            $v['tags'] = array('');
        }
        $paid_modules[$k]['tags'] = _func_parse_module_tags($v['tags'], $paid_modules_filter_tags);
    }
}
_func_adjust_tags_list($paid_modules_filter_tags, count($paid_modules), 'xcart_selected_tag_extensions');

$smarty->assign('paid_modules', $paid_modules);
$smarty->assign('paid_modules_filter_tags', $paid_modules_filter_tags);

if (empty($_COOKIE['hide_dialog_xcart_paid_modules'])) {
    $banner_tools_data[] = array (
        'template' => 'admin/main/modules_banner.tpl',
    );
    $smarty->assign('banner_tools_data', $banner_tools_data);
}

// Assign the current location line
$smarty->assign('location', $location);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);
?>
