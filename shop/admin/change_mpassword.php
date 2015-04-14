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
 * Merchant password change interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v44 (xcart_4_7_0), 2015-02-17 13:29:01, change_mpassword.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/**
 * Change Merchant password
 */

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

x_load('order','crypt');

if (!$merchant_password && !empty($config['mpassword'])) {
    func_403(14);
}

x_session_register('change_mpassword_referer');

if ((empty($change_mpassword_referer) || empty($err)) && $REQUEST_METHOD == 'GET' && func_is_internal_url($HTTP_REFERER)) {
    $change_mpassword_referer = $HTTP_REFERER;
}

if ($REQUEST_METHOD == 'POST') {

    require $xcart_dir . '/include/safe_mode.php';

    $top_message['type'] = 'E';
    $top_message['content'] = false;

    // Check old merchant password

    if ($merchant_password != $old_password && !empty($config['mpassword'])) {
        $top_message['content'] = func_get_langvar_by_name('txt_wrong_old_mpassword');
    }

    // Check differences (new and old passwords)

    if ($new_password != $confirm_password) {
        $top_message['content'] = func_get_langvar_by_name('txt_different_mpasswords');
    }

    // Check password length

    if (strlen($new_password) < 6) {
        $top_message['content'] = func_get_langvar_by_name('txt_small_mpassword');
    }

    if ($top_message['content'] !== false) {
        func_header_location("change_mpassword.php?err=Y".($from_config ? "&from_config=".$from_config : ''));
    }

    // Update merchant password

    $old_password = $merchant_password;
    func_array2insert('config', array('name' => 'mpassword', 'value' => text_crypt('Merchant password test phrase','C',$new_password)), true);
    $merchant_password = $new_password;
    $top_message['type'] = 'I';
    if (empty($config['mpassword'])) {
        $top_message['content'] = func_get_langvar_by_name('txt_added_mpassword')."<br /><br /><font color=\"red\">".func_get_langvar_by_name('lbl_add_mpassword_warn')."</font>";
        db_query("UPDATE $sql_tbl[config] SET value = 'Y' WHERE name = 'blowfish_enabled'");
        $config['Security']['blowfish_enabled'] = 'Y';
        func_data_recrypt();
    }
    else {
        $top_message['content'] = func_get_langvar_by_name('txt_changed_mpassword');
        func_change_mpassword_recrypt($old_password);
    }

    if ($from_config)
        func_html_location("configuration.php?option=".$from_config, 0);

    if (!empty($change_mpassword_referer)) {
        $url = $change_mpassword_referer;
        $change_mpassword_referer = '';
        x_session_unregister('change_mpassword_referer');
        func_html_location($url, 0);
    }
    func_html_location('change_mpassword.php', 0);
}

$title = func_get_langvar_by_name((empty($config['mpassword']) ? "lbl_add_mpassword" : "lbl_change_mpassword"));
$location[] = array($title, '');
$smarty->assign('section_title',$title);

if (!empty($from_config)) {
    $smarty->assign('from_config', $from_config);
}

$smarty->assign('main','change_mpassword');

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$dialog_tools_data = array('help' => true);
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);
?>
