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
 * 2b39e63712da5477e1aaf5cfa80d1370f583bce9, v47 (xcart_4_7_0), 2015-02-17 23:56:28, version.php, Yuriy
 * This script is required by X-Cart support team
 */

if (is_readable('../top.inc.php')) {
    include_once '../top.inc.php';
}
if (!defined('XCART_START')) die("ERROR: Can not initiate application! Please check configuration.");

require $xcart_dir . '/init.php';

$xcart_db_version = '';

$res = $sql_obj->query("SELECT value FROM $sql_tbl[config] WHERE name='version'");

if ($sql_obj->num_rows($res) < 1) {

    $xcart_db_version = "<= 2.4.1";

} else {
    for ($i = 0; $i < $sql_obj->num_rows($res);  $i++) {
        list ($version) = $sql_obj->fetch_row($res);
        if ($i != 0) $xcart_db_version .= ", ";

        $xcart_db_version .= $version;
    }
}

$sql_obj->free_result($res);

$_shop_type = $shop_type;

if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[modules] WHERE module_name = 'Simple_Mode'")) {

    if ($_shop_type !== 'PRO' && $_shop_type !== 'PLATINUM') {
        $_shop_type = 'PRO/PLATINUM';
    }

} else if ($_shop_type !== 'GOLD' && $_shop_type !== 'GOLDPLUS') {

    $_shop_type = 'GOLD/GOLDPLUS';

}

$xcart_db_version .= ' ' . $_shop_type;

# Header & styles
echo <<<EOT
<?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title>{$config['Company']['company_name']}</title>
    <style type="text/css">

    .builtin, .third_party {
        display:table-row;
    }
     
    .builtin_enabled, .third_party_enabled {
        width:300px;
        display:table-cell;
    }
     
    .builtin_disabled, .third_party_disabled {
        display:table-cell;
    }

    </style>

</head>
<body>
EOT;



# Version & skins
echo "X-Cart DB Version: $xcart_db_version<br />\n";

echo "<br />Checkout Module: ". $config['General']['checkout_module'];

if (!empty($alt_skin_info)) {
    echo "<br />Current Skin: ".htmlspecialchars($alt_skin_info['name'])." (".$alt_skin_info['web_path'].")";
} else {
    echo "<br />Current Skin: unknown";
}

$language_codes = func_query_column("SELECT DISTINCT code FROM $sql_tbl[languages]");
if (!empty($language_codes))
    echo "<br />Available Languages: ".implode(",", $language_codes);

if (!empty($active_modules['Product_Options'])) {
    $variants_in_use = func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[classes] WHERE avail='Y' AND is_modifier=''");
    if ($variants_in_use)
        echo "<br />Variants (in use)";
}    

# Modules
$modules = func_query_column("SELECT module_name FROM $sql_tbl[modules] WHERE module_name != 'Demo' AND module_name != 'Dev_Mode' AND module_name != 'Demo_Mode' ORDER BY module_name");
$builtin_modules = func_get_builtin_modules();

$third_party_modules = $third_party_enabled = $third_party_disabled = $builtin_enabled = $builtin_disabled = array();
$third_party_modules = array_diff($modules, $builtin_modules);

if (!empty($third_party_modules)) {
    foreach ($third_party_modules as $v) {
        if (!empty($active_modules[$v]))
            $third_party_enabled[] = $v;
        else    
            $third_party_disabled[] = $v;
    }
}

if (!empty($builtin_modules)) {
    foreach ($builtin_modules as $v) {
        if (!empty($active_modules[$v]))
            $builtin_enabled[] = $v;
        else    
            $builtin_disabled[] = $v;
    }
}

$third_party_enabled = func_iv_format_modules_output($third_party_enabled, '<b>Enabled</b>');
$third_party_disabled = func_iv_format_modules_output($third_party_disabled, '<b>Disabled</b>');
$builtin_enabled = func_iv_format_modules_output($builtin_enabled, '<b>Enabled</b>');
$builtin_disabled = func_iv_format_modules_output($builtin_disabled, '<b>Disabled</b>');

echo <<<EOT
<h2>3rd party modules</h2>

<div class='third_party'>
    <div class='third_party_enabled'>$third_party_enabled</div>
    <div class='third_party_disabled'>$third_party_disabled</div>
</div>

<h2>built-in modules</h2>
<div class='builtin'>
    <div class='builtin_enabled'>$builtin_enabled</div>
    <div class='builtin_disabled'>$builtin_disabled</div>
</div>
EOT;

echo <<<EOT
</body>
</html>
EOT;
/*==================================================================================================*/
/*                              FUNCTIONS                                                           */
/*==================================================================================================*/

function func_iv_load_modules_names($modules) { // {{{
    global $sql_tbl;

    $result = array();

    if (is_array($modules)) {
        array_walk($modules,
            create_function('&$module', '$module = \'module_name_\' . $module;')
        );
        $translations = func_query("SELECT name, value FROM $sql_tbl[languages] WHERE code='en' AND name IN ('" . implode("', '", $modules) . "')");
        if (is_array($translations)) {
            foreach ($translations as $translation) {
                $module_code = str_replace('module_name_', '', $translation['name']);
                if (!empty($module_code)) {
                    $result[$module_code] = $translation['value'];
                }
            }
        }
    }

    return $result;
} // }}}

function func_iv_format_modules_output($modules, $header = '') { // {{{
    if (empty($modules)) {
        return '';
    }

    $modules_names = func_iv_load_modules_names($modules);

    if (!empty($header)) {
        array_unshift($modules, $header);
    }

    $str = '';

    foreach ($modules as $v) {
        $str .= (!empty($modules_names[$v]) ? $modules_names[$v] : $v) . '<br />';
    }        
    
    return $str;
} // }}}

?>
