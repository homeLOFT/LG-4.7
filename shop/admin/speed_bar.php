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
 * Speed bar management interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v45 (xcart_4_7_0), 2015-02-17 13:29:01, speed_bar.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('IS_MULTILANGUAGE', 1);

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

x_load('backoffice');

if (isset($config['speed_bar']))
    $speed_bar = unserialize($config['speed_bar']);

$location[] = array(func_get_langvar_by_name('lbl_speed_bar_management'), '');

$speed_bar = empty($speed_bar) ? array() : func_stripslashes($speed_bar);

if ($REQUEST_METHOD == 'POST') {
    require $xcart_dir.'/include/safe_mode.php';

    if ($mode == 'delete' && !empty($to_delete)) {//{{{

        // Delete link from Speed Bar

        $to_delete = array_keys($to_delete);
        foreach ($speed_bar as $k=>$v) {
            if (in_array($v['id'], $to_delete)) {
                unset($speed_bar[$k]);
            }
        }
    }//}}}
    elseif ($mode == 'update') {//{{{

        // Update Speed Bar

        if (is_array($posted_data) && !empty($posted_data)) {
            foreach ($posted_data as $k=>$v) {
                $v['orderby'] = abs(intval($v['orderby']));
                $v['active'] = ($v['active'] == 'Y' ? 'Y' : 'N');
                $v['link'] = (empty($v['link']) ? "#" : $v['link']);
                func_languages_alt_insert('speed_bar_'.$v['id'], $v['title'], $shop_language);
                if ($shop_language != $config['default_admin_language']) {
                    foreach ($speed_bar as $v2) {
                        if ($v2['id'] == $v['id']) {
                            $v['title'] = $v2['title'];
                            break;
                        }
                    }
                }

                $posted_data[$k] = $v;
            }

            $speed_bar = $posted_data;
        }
    } //}}}
    elseif ($mode == 'add' && !empty($new_title)) {//{{{

        // Generate unique id for new link

        $idx = 1;
        foreach ($speed_bar as $k=>$v) {
            if ($v['id'] > $idx)
                $idx = $v['id'];
        }
        $idx++;

        func_languages_alt_insert('speed_bar_'.$idx, $new_title, $shop_language);
        $speed_bar[] = array(
            'id'      => $idx,
            'orderby' => abs(intval($new_orderby)),
            'title'   => stripslashes($new_title),
            'link'    => (empty($new_link) ? "#" : $new_link),
            'active'  => ($new_active == 'Y' ? 'Y' : 'N'));
    }//}}}

    if (is_array($speed_bar)) {
        function mysortfunc($a,$b) {
            return ($a['orderby'] >= $b['orderby']);
        }

        usort ($speed_bar, 'mysortfunc');
    }

    $speed_bar = func_speed_bar_add_url_to_compare($speed_bar);
    db_query("REPLACE INTO $sql_tbl[config] (name,value,defvalue,variants) VALUES ('speed_bar','".addslashes(serialize($speed_bar))."','','')");

    func_header_location('speed_bar.php');
}// if ($REQUEST_METHOD == 'POST')

foreach ($speed_bar as $k => $v) {
    $tmp = func_get_languages_alt('speed_bar_'.$v['id']);
    if (!empty($tmp))
        $speed_bar[$k]['title'] = $tmp;
}

$smarty->assign('speed_bar', $speed_bar);

$smarty->assign('main','speed_bar');

// Assign the current location line
$smarty->assign('location', $location);

// Assign the section navigation data
$dialog_tools_data = array('help' => true);
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);

/**
 * Add 'url_to_compare' element to find current tab in the customer area
 */
function func_speed_bar_add_url_to_compare($l_speed_bar)
{//{{{
    global $xcart_https_host, $xcart_http_host, $xcart_web_dir;

    if (!is_array($l_speed_bar)) {
        return $l_speed_bar;
    }

    $home_page_urls = array('', '/', 'home.php', 'index.html', 'index.php');
    foreach ($l_speed_bar as $k => $v) {
        $parsed_url =  parse_url($v['link']);
        if (empty($parsed_url)) {
            continue;
        }

        $parsed_url['path'] = empty($parsed_url['path']) ? '' : $parsed_url['path'];
        if (
            empty($parsed_url['host'])
            && !empty($parsed_url['path'])
        ) {
            $parsed_url['path'] = str_ireplace($xcart_https_host, '', $parsed_url['path']);
            $parsed_url['path'] = str_ireplace($xcart_http_host, '', $parsed_url['path']);
            $parsed_url['path'] = preg_replace('%^//%', '', $parsed_url['path'], 1);
        }

        $parsed_url['path'] = preg_replace('/^' . preg_quote($xcart_web_dir, '/') . '/i', '', $parsed_url['path'], 1);
        $parsed_url['path'] = trim($parsed_url['path'], '/');

        $l_speed_bar[$k]['url_to_compare'] = array(strtolower(
            $parsed_url['path']
             . ((empty($parsed_url['query'])) ? '' : '?' . $parsed_url['query'])
        ));

        if (
            empty($l_speed_bar[$k]['url_to_compare'][0])
            || in_array($l_speed_bar[$k]['url_to_compare'][0], $home_page_urls)
        ) {
            $l_speed_bar[$k]['url_to_compare'] = $home_page_urls;
        }

    }

    return $l_speed_bar;
}// func_speed_bar_add_url_to_compare}}}

?>
