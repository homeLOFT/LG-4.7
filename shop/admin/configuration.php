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
 * Settings/modules configuration interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    f71c35a9a2e55bfde551535fca51fee723f5faff, v265 (xcart_4_7_2), 2015-04-23 16:42:48, configuration.php, mixon
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('ADMIN_CONFIGURATION_CONTROLLER', true);

define('USE_TRUSTED_POST_VARIABLES',1);

$trusted_post_variables = array(
    'gpg_key',
    'pgp_key',
    'xpc_private_key_password',
    'xpc_private_key',
    'xpc_public_key',
    'breadcrumbs_separator',
    'company_mail_from',
    'newsletter_email',
    'orders_department',
    'site_administrator',
    'smtp_mail_from',
    'support_department',
    'users_department',
);

define('XC_SKIP_BOUNCER_TEST',1);

class XCConfigVars {
    public static function unsetDisabledVariants($config_name, $variants) { // {{{
        global $config, $active_modules, $REQUEST_METHOD;

        switch ($config_name) {
            case 'products_order':
                // Disable productcode from products sort order option
                if ($config['Appearance']['display_productcode_in_list'] != 'Y') {
                    unset($variants['productcode']);
                }

                $_sort_fields = array();
                if (
                    empty($active_modules['Advanced_Customer_Reviews'])
                    || !func_acr_search_define_options($_sort_fields)
                ) {
                    unset($variants['review_rating']);
                    if (
                        $config['Appearance'][$config_name] == 'review_rating'
                        && $REQUEST_METHOD == 'GET'
                    ) {
                        self::update($config_name, 'orderby');
                    }
                }

                break;


        }

        return $variants;
    } // }}}

    protected static function update($config_name, $new_value) { // {{{
        return func_array2update(
            'config',
            array(
                'value' => $new_value,
            ),
            "name='$config_name'"
        );
    } // }}}
}

require __DIR__.'/auth.php';

require $xcart_dir.'/include/security.php';

x_load('mail','order');

$options = func_get_configuration_options();

if (empty($option)) {
    $option = 'General';
}

if (!in_array($option, $options)) {
    $top_message['content'] = func_get_langvar_by_name('msg_err_module_not_installed',array('module' => $option));
    func_header_location($xcart_catalogs['admin'] . "/configuration.php");
}

require $xcart_dir . '/include/countries.php';

require $xcart_dir . '/include/states.php';

// Update configuration variables
// these variables are for internal use in PHP scripts

$location[] = array(
    func_get_langvar_by_name('lbl_general_settings'),
    'configuration.php'
);

if ($REQUEST_METHOD == 'POST') {
    require $xcart_dir . '/include/safe_mode.php';

    if ($option == 'Security') {
        $location[count($location)-1][1] = 'configuration.php?option=Security';
        func_check_perms_redirect(XCActions::CHANGE_SECURITY_OPTIONS);
    }
}

if ($option == 'XPayments_Connector') {
    if ($REQUEST_METHOD == 'GET') {
        if ($_msg = $smarty->getTemplateVars('top_message')) {
            $top_message = $_msg;
        }
        func_header_location($xcart_catalogs['admin'] . '/xpc_admin.php');
    } elseif ($REQUEST_METHOD == 'POST') {
       require_once $xcart_dir . '/modules/XPayments_Connector/xpc_admin.php'; 
    }
}

$module_configuration_file = $xcart_dir . '/modules/' . $option . '/configuration.php';

if ($option == 'User_Profiles') {
    include $xcart_dir . DIR_ADMIN . '/user_profiles.php';
} elseif ($option == 'Contact_Us') {
    include $xcart_dir . DIR_ADMIN . '/contact_us_profiles.php';
} elseif ($option == 'Search_products') {
    include $xcart_dir . DIR_ADMIN . '/search_products_form.php';
} elseif (
    !empty($active_modules[$option])
    && file_exists($module_configuration_file)
) {

    require $module_configuration_file;

} elseif ($REQUEST_METHOD == 'POST') {
    $_condition = "type IN ('checkbox','multiselector') AND category='" . $option . "'";
    $old_configs = func_query("SELECT " . XCConfigSignature::getSignedFields() . " FROM $sql_tbl[config] WHERE " . XCConfigSignature::getApplicableSqlCondition() . " AND $_condition");
    func_array2update(
        'config',
        array(
            'value' => 'N',
        ),
        $_condition
    );
    func_secure_update_config_signatures($old_configs);

    $var_properties = func_query_hash("SELECT name, type, validation, comment FROM $sql_tbl[config] WHERE category='$option'", "name", false, false);

    $section_data = array();

    if (
        $option == 'Appearance'
        && in_array($_POST['alt_skin'], array_keys($altSkinsInfo))
    ) {

        func_array2update(
            'config',
            array(
                'value' => $_POST['alt_skin'],
            ),
            "name='alt_skin' AND category=''"
        );

        unset($_POST['alt_skin']);

    }

    if ($option == 'Shipping' || $option == 'Company') {
        $old_1800c_info = array();
        $new_1800c_info = array();
        $old_1800c_info['company_name'] = $config['Company']['company_name'];
        $old_1800c_info['phone'] = $config['Company']['company_phone'];
        $old_1800c_info['city'] = $config['Company']['location_city'];
        $old_1800c_info['state'] = $config['Company']['location_state'];
        $old_1800c_info['country'] = $config['Company']['location_country'];
        $old_1800c_info['zipcode'] = $config['Company']['location_zipcode'];
        $old_1800c_info['address'] = $config['Company']['location_address'];

        if (file_exists($xcart_dir . '/shipping/mod_1800C.php')) {
            include_once $xcart_dir . '/shipping/mod_1800C.php';
        }

        if (function_exists('func_get_warehouse_info')) {
            $old_1800c_info = func_get_warehouse_info($old_1800c_info);
        }
    }

    foreach ($_POST as $key => $val) {
        if ($option == 'Shipping' || $option == 'Company') {
            if (
                in_array($key, 
                    array(
                        '1800c_username',
                        '1800c_password',
                        '1800c_customerid',
                        '1800c_readytime',
                        '1800c_subsidize',
                        '1800c_business_hours',
                        '1800c_operation_days',
                        'location_city',
                        'location_state',
                        'location_country',
                        'location_zipcode',
                        'location_address',
                        'company_phone',
                        'company_name',
                        '1800c_warehouse_name',
                        '1800c_address',
                        '1800c_state',
                        '1800c_city',
                        '1800c_zipcode',
                        '1800c_country',
                        '1800c_phone',
                        )
                    )
            ) {
                $param_name = 'new_' . $key;
                $$param_name = $val;
            }
        }

        if ($key == 'periodic_logs')
            $val = is_array($val) ? implode(',',$val) : '';

        if (
            $option == 'Special_Offers'
            && !empty($active_modules['Special_Offers'])
            && $key == 'offers_bp_rate'
            && $active_modules['Special_Offers'][$key] == 'udoublez'
        ) {
            $val = func_convert_number($val);
        }

        if (isset($var_properties[$key]['type'])) {

            if (strlen($var_properties[$key]['validation']) > 0) {

                $validation_result = false;

                $is_empty = strlen($val) == 0;

                if ($var_properties[$key]['validation'] == 'email') {

                    // Check email
                    $validation_result = $is_empty || func_check_email($val);

                } elseif ($var_properties[$key]['validation'] == 'email_extended') {
                    // Check email via XCEmailFormat::EXTENDED
                    $validation_result = $is_empty || func_check_email($val, XCEmailFormat::EXTENDED);
                } elseif ($var_properties[$key]['validation'] == 'emails') {

                    // Check emails list
                    $emails = func_array_map('trim', explode(",", $val));
                    $validation_result = $is_empty || count(array_filter($emails, 'func_check_email')) == count($emails);

                } elseif ($var_properties[$key]['validation'] == 'exec') {

                    // Check file system path to executable file
                    $validation_result = $is_empty || (ini_get('open_basedir') != '' || func_is_executable($val));

                } elseif ($var_properties[$key]['validation'] == 'port') {

                    // Check IP port
                    if (func_is_numeric($val)) {
                        $num = func_convert_numeric($val);
                        $validation_result = $num > 0 && $num < 65536;
                    }

                } elseif ($var_properties[$key]['validation'] == 'tz_offset') {

                    // Check timezone offset
                    if (func_is_numeric($val)) {
                        $num = func_convert_numeric($val);
                        $validation_result = $num > -25 && $num < 25;
                    }

                } elseif (
                    in_array(
                        $var_properties[$key]['validation'],
                        array(
                            'int',
                            'uint',
                            'uintz',
                            'double',
                            'udouble',
                            'udoublez',
                        )
                    )
                ) {
                    // Check numeric
                    if ($is_empty)
                        $val = 0;

                    if (func_is_numeric($val)) {

                        $num = func_convert_numeric($val);

                        switch ($var_properties[$key]['validation']) {
                            case 'int':
                                $validation_result = floor($num) == $num;
                                break;

                            case 'uint':
                                $validation_result = floor($num) == $num && $num >= 0;
                                break;

                            case 'uintz':
                                $validation_result = floor($num) == $num && $num > 0;
                                break;

                            case 'udouble':
                                $validation_result = $num >= 0;
                                break;

                            case 'udoublez':
                                $validation_result = $num > 0;
                                break;

                            default:
                                $validation_result = true;
                        }
                    }

                } elseif (preg_match('/^url(?::(https|http|ftp))?$/Ss', $var_properties[$key]['validation'], $m)) {

                    // Check URL
                    $validation_result = is_url($val);
                    if ($validation_result) {
                        $parsed_url = @parse_url($val);
                        $validation_result = is_array($parsed_url) && isset($parsed_url['scheme']) && isset($parsed_url['host']);
                        if ($validation_result && $m[1]) {
                            $validation_result = $m[1] == $parsed_url['scheme'];
                        }
                    }

                } elseif (preg_match('/^php_ver:(\d+.\d+.\d+)$/Ss', $var_properties[$key]['validation'], $m)) {

                    $validation_result = version_compare(PHP_VERSION, $m[1]) >= 0;

                    if (!$validation_result) {
                        $top_message = array(
                            'type'    => 'W',
                            'content' => func_get_langvar_by_name('err_mail_style_inliner_php_ver')
                        );
                    }

                } else {

                    // Check by regular expression
                    $validation_result = preg_match('/'.$var_properties[$key]['validation']."/", $val);

                }

                if ($validation_result) {
                    switch ($key) {
                        case 'max_nav_pages':
                            $max_nav_pages = func_convert_numeric($val);
                            if ($max_nav_pages < 2 || $max_nav_pages > 25)
                                $validation_result = false;
                            break;
                    }
                }

                // Don't store the values, that do not pass validation

                if (!$validation_result) {

                    if (empty($top_message)) {

                        $conf_comment = func_get_langvar_by_name('opt_' . $key, array(), false, true);

                        if (!$conf_comment) {
                            $conf_comment = $var_properties[$key]['comment'];
                        }


                        $top_message = array(
                            'type'    => 'W',
                            'content' => func_get_langvar_by_name(
                                'err_invalid_field_data',
                                array(
                                    'field' => $conf_comment
                                ),false,true
                            )
                        );
                    }

                    continue;
                }
            }

            if ($var_properties[$key]['type'] == "numeric") {

                $val = func_convert_numeric($val);

            } elseif ($var_properties[$key]['type'] == "multiselector") {

                $val = implode(";", $val);

            } elseif ($var_properties[$key]['type'] == "checkbox" && $val=="on") {

                $val = 'Y';

            } elseif ($var_properties[$key]['type'] == "trimmed_text") {

                $val = trim($val);

            } elseif (strlen($val) > 65535) {

                $conf_comment = func_get_langvar_by_name('opt_' . $key, array(), false, true);

                if (!$conf_comment) {
                    $conf_comment = $var_properties[$key]['comment'];
                }

                $top_message = array(
                    'type'    => 'W',
                    'content' => func_get_langvar_by_name(
                        'err_field_text_too_long',
                        array(
                            'field' => $conf_comment
                        )
                    )
                );

                continue;
            }

            if ($config[$option][$key] != $val) {
                x_log_flag('log_activity', 'ACTIVITY', "'$login' user has changed '$option::$key' option value from '".$config[$option][$key]."' to '$val'");
            }

            $_condition = "name='" . $key . "' AND category='" . $option . "'";
            if (XCConfigSignature::isApplicable(array('name' => $key)))
                $old_configs = func_query("SELECT " . XCConfigSignature::getSignedFields() . " FROM $sql_tbl[config] WHERE $_condition");
            else
                $old_configs = array();

            func_array2update(
                'config',
                array(
                    'value' => $val,
                ),
                $_condition
            );
            if (XCConfigSignature::isApplicable(array('name'=> $key)))
                func_secure_update_config_signatures($old_configs);

            $section_data[stripslashes($key)] = stripslashes($val);

        } // if (isset($var_properties[$key]['type']))

    } // foreach ($_POST as $key => $val)

    // Change 'products_order' options value if 'display_productcode_in_list' is changed to 'disable'

    if (
        $option == 'Appearance'
        && !isset($_POST['display_productcode_in_list'])
        && $config['Appearance']['display_productcode_in_list'] == 'Y'
        && $_POST['products_order'] == 'productcode'
    ) {
        func_array2update(
            'config',
            array(
                'value' => 'orderby',
            ),
            "name='products_order' AND category='" . $option . "'"
        );
    }

    if ($option == 'Shipping') {
        if (!empty($new_1800c_username) && !empty($new_1800c_password)) {
            if (
                $new_1800c_username != $old_1800c_info['username']
                || $new_1800c_password != $old_1800c_info['password']
                || $new_1800c_customerid != $old_1800c_info['customerid']
                || $new_1800c_readytime != $old_1800c_info['readytime']
                || $new_1800c_subsidize != $old_1800c_info['subsidize']
                || $new_1800c_business_hours != $old_1800c_info['business_hours']
                || $new_1800c_operation_days != $old_1800c_info['operation_days']
                || $new_1800c_warehouse_name != $old_1800c_info['company_name']
                || $new_1800c_address != $old_1800c_info['address']
                || $new_1800c_state != $old_1800c_info['state']
                || $new_1800c_city != $old_1800c_info['city']
                || $new_1800c_zipcode != $old_1800c_info['zipcode']
                || $new_1800c_country != $old_1800c_info['country']
                || $new_1800c_phone != $old_1800c_info['phone']
           ) {
                $config['Shipping']['1800c_username'] = $new_1800c_username;
                $config['Shipping']['1800c_password'] = $new_1800c_password;
                $config['Shipping']['1800c_customerid'] = $new_1800c_customerid;
                $config['Shipping']['1800c_readytime'] = $new_1800c_readytime;
                $config['Shipping']['1800c_subsidize'] = $new_1800c_subsidize;
                $config['Shipping']['1800c_business_hours'] = $new_1800c_business_hours;
                $config['Shipping']['1800c_operation_days'] = $new_1800c_operation_days;
                $config['Shipping']['1800c_warehouse_name'] = $new_1800c_warehouse_name;
                $config['Shipping']['1800c_address'] = $new_1800c_address;
                $config['Shipping']['1800c_state'] = $new_1800c_state;
                $config['Shipping']['1800c_city'] = $new_1800c_city;
                $config['Shipping']['1800c_zipcode'] = $new_1800c_zipcode;
                $config['Shipping']['1800c_country'] = $new_1800c_country;
                $config['Shipping']['1800c_phone'] = $new_1800c_phone;
                $mode = 'send_info';
                include $xcart_dir . '/include/1800C_registration.php';
            }

        }
    }
    if ($option == 'Company') {
        if (!empty($config['Shipping']['1800c_username']) && !empty($config['Shipping']['1800c_password'])) {
            if (
                $new_location_city != $old_location_city
                || $new_location_state != $old_location_state
                || $new_location_country != $old_location_country
                || $new_location_zipcode != $old_location_zipcode
                || $new_location_address != $old_location_address
                || $new_company_phone != $old_company_phone
                || $new_company_name != $old_company_name
           ) {
                $config['Company']['location_city'] = $new_location_city;
                $config['Company']['location_state'] = $new_location_state;
                $config['Company']['location_country'] = $new_location_country;
                $config['Company']['location_zipcode'] = $new_location_zipcode;
                $config['Company']['location_address'] = $new_location_address;
                $config['Company']['company_phone'] = $new_company_phone;
                $config['Company']['company_name'] = $new_company_name;

                $new_1800c_info['company_name'] = $new_company_name;
                $new_1800c_info['phone'] = $new_company_phone;
                $new_1800c_info['city'] = $new_location_city;
                $new_1800c_info['state'] = $new_location_state;
                $new_1800c_info['country'] = $new_location_country;
                $new_1800c_info['zipcode'] = $new_location_zipcode;
                $new_1800c_info['address'] = $new_location_address;

                if (file_exists($xcart_dir . '/shipping/mod_1800C.php')) {
                    include_once $xcart_dir . '/shipping/mod_1800C.php';
                }

                if (function_exists('func_get_warehouse_info')) {
                    $new_1800c_info = func_get_warehouse_info($new_1800c_info);
                }

                if (md5(serialize($new_1800c_info)) != md5(serialize($old_1800c_info))) {
                    $mode = 'send_info';
                    include $xcart_dir . '/include/1800C_registration.php';
                }
            }
        }
    }

    x_load('image');

    if (func_check_gd()) {

        // Regenerate image cache
    
        if (!empty($active_modules['Detailed_Product_Images'])) {

            if (
                $option == 'Detailed_Product_Images'
                && $config['Detailed_Product_Images']['det_image_box_plugin'] != $_POST['det_image_box_plugin']
                && $_POST['det_image_box_plugin'] == 'Z'
            ) {

                func_configuration_redirect_to_generate_image_cache('D', array('dpthmbn', 'dpicon'), $option);

            } elseif (
                $option == 'Detailed_Product_Images'
                && (
                    $config['Detailed_Product_Images']['det_image_max_width_icon'] != $_POST['det_image_max_width_icon']
                    || $config['Detailed_Product_Images']['det_image_max_height_icon'] != $_POST['det_image_max_height_icon']
                )
            ) {

                func_configuration_redirect_to_generate_image_cache('D', array('dpicon'), $option);

            } elseif (
                $option == 'Appearance'
                && $config['Detailed_Product_Images']['det_image_icons_box'] == 'Y'
                && (
                    $config['Appearance']['image_width'] != $_POST['image_width']
                    || $config['Appearance']['image_height'] != $_POST['image_height']
                )
            ) {

                func_configuration_redirect_to_generate_image_cache('D', array('dpthmbn'), $option);

            } elseif (
                $option == 'Detailed_Product_Images'
                && $config['Detailed_Product_Images']['det_image_icons_box'] != ($_POST['det_image_icons_box'] ? 'Y' : '')
            ) {
                $config['Detailed_Product_Images']['det_image_icons_box'] = $_POST['det_image_icons_box'] ? 'Y' : '';

                if ($_POST['det_image_icons_box']) {

                    func_configuration_redirect_to_generate_image_cache('D', array('dpthmbn', 'dpicon'), $option);

                } else {

                    func_image_cache_remove('D', 'dpthmbn');
                    func_image_cache_remove('D', 'dpicon');

                }
            }

        } elseif (
            !empty($active_modules['Flyout_Menus'])
            && $option == 'Appearance'
            && func_fc_need_regenerate_catthumbn(func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name = 'alt_skin'"))
        ) {

            func_configuration_redirect_to_generate_image_cache('C', array('catthumbn'), 'Appearance&fc_build_categories=Y');

        }
    }

    // Checking whether Blowfish encryption of order details using Merchant key is enabled
    if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[config] WHERE name = 'blowfish_enabled' AND category='$option'")) {

        $new_value = func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name = 'blowfish_enabled' AND category='$option'");

        if ($new_value != $config['Security']['blowfish_enabled']) {

            if ($new_value == 'Y') {

                if (empty($config['mpassword'])) {

                    db_query("UPDATE $sql_tbl[config] SET value='" . $config['Security']['blowfish_enabled'] . "' where name='blowfish_enabled' AND category='$option'");
                    func_header_location($xcart_catalogs['admin'] . "/change_mpassword.php?from_config=" . $option);

                } else {

                    func_data_recrypt();

                }

            } elseif ($new_value != 'Y') {

                if ($merchant_password) {

                    func_data_decrypt();

                    $merchant_password = '';

                    func_array2insert(
                        'config',
                        array(
                            'name'  => 'mpassword',
                            'value' => '',
                        ),
                        true
                    );

                } else {

                    func_array2update(
                        'config',
                        array(
                            'value' => $config['Security']['blowfish_enabled'],
                        ),
                        'name=\'blowfish_enabled\' AND category=\'' . $option . '\''
                    );

                }

            }

        } // if ($new_value != $config['Security']['blowfish_enabled'])

    } // if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[config] WHERE name = 'blowfish_enabled' AND category='$option'"))

    // Apply default values to 'empty' fields excepting value of location_state

    $_condition = "TRIM(value) = '' AND name != 'location_state'";
    $old_configs = func_query("SELECT " . XCConfigSignature::getSignedFields() . " FROM $sql_tbl[config] WHERE " . XCConfigSignature::getApplicableSqlCondition() . " AND $_condition");
    db_query("UPDATE $sql_tbl[config] SET value = defvalue WHERE $_condition");
    func_secure_update_config_signatures($old_configs);
    unset($old_configs);

    if ($option == 'Security') {

        func_pgp_remove_key();

        $config[$option] = $section_data; // no code after func_pgp_add_key() using these settings

        func_pgp_add_key();

    }
    
    $clear_arr = array(
        'General' => array('speedup_js', 'speedup_css', 'use_cached_lng_vars', 'skip_delete_empty_strings', 'ajax_add2cart', 'redirect_to_cart', 'skip_check_compile', 'check_main_category_only', 'skip_categories_checking', 'use_simple_product_sort', 'skip_lng_tables_join', 'use_cached_templates','unlimited_products','currency_symbol'),
        'Appearance' => array('show_in_stock','alt_skin'),
        'Wishlist' => array('add2wl_unlogged_user'),
        'Socialize' => array('soc_fb_like_enabled', 'soc_fb_send_enabled', 'soc_tw_enabled', 'soc_ggl_plus_enabled', 'soc_plist_plain', 'soc_plist_matrix'),
        'Product_Notifications' => array('prod_notif_enabled_B', 'prod_notif_enabled_L', 'prod_notif_show_in_list_L', 'prod_notif_show_in_list_B', 'prod_notif_L_amount', 'prod_notif_enabled_P', 'prod_notif_show_in_list_P'),
        'Klarna_Payments' => array('user_country','invoice_payment_surcharge','klarna_default_eid'),

    );

    if (isset($clear_arr[$option])) {
        foreach ($clear_arr[$option] as $k => $v) {
            if (func_option_is_changed($option, $v)) {
                func_remove_xcart_caches(false, func_get_cache_dirs());
                break;
            }
        }
    }

    // Check incompatible options
    $error_incompatible_message = func_configuration_has_incompatible_options($option);

    // Overwrite only informational top_message
    if (
        !empty($error_incompatible_message)
        && (
            empty($top_message['type'])
            || $top_message['type'] == 'I'
        )
    ) {
        $top_message = array(
            'type'    => 'W',
            'content' => $error_incompatible_message,
        );
    }

    if (
        func_option_is_changed('Appearance', 'thumbnail_width')
        || func_option_is_changed('Appearance', 'thumbnail_height')
    ) {
        $product_ids = db_query("SELECT id FROM $sql_tbl[images_T] INNER JOIN $sql_tbl[images_P] USING (id)");

        if (!empty($product_ids)) {
            x_load('iterations');
            func_init_iteration('generate');

            while ($_productid = db_fetch_array($product_ids)) {
                func_add_iteration_row('generate', $_productid['id'], 'configuration');
            }

            $top_message = array(
                'content' => func_get_langvar_by_name('msg_adm_thumbnail_sizes_changed'),
                'delay_value' => max($config['Appearance']['delay_value'], 20),
            );

            db_free_result($product_ids);
        }
    }

    if ($option == 'Refine_Filters' && !empty($active_modules['Refine_Filters'])) {
        func_rf_trigger_event('module_settings_updated');
    }

    if (!empty($active_modules['Flyout_Menus'])) {
        include $xcart_dir . '/modules/Flyout_Menus/admin_config.php';
    }

    if (!empty($active_modules['TwoFactorAuth']) && $option == 'TwoFactorAuth') {
        func_twofactor_on_config_update();
    }

    // Call module-specific config update events
    if (array_key_exists($option, $active_modules)) {

        // Disabled checkboxes are not posted, check for them
        foreach ($config[$option] as $optname => $optval) {
            if (!isset($_POST[$optname]) && !empty($var_properties[$optname]) && $var_properties[$optname]['type'] == 'checkbox') {
                $section_data[$optname] = 'N';
            }
        }

        func_call_event('module.config.update', $option, $section_data);
    }

    func_header_location("configuration.php?option=$option");
}

/**
 * Select default options tab
 */
if (
    !empty($active_modules['Amazon_Checkout'])
    && in_array($option, array('Amazon_Checkout'))
) {
    $check_active_payments = func_check_active_payments();

    if ($check_active_payments !== true) {
        $smarty->assign(
            'top_message',
            array(
                'type'    => 'W',
                'content' => $check_active_payments
            )
        );
    }
}

$configuration = func_query("SELECT * FROM $sql_tbl[config] WHERE category = '$option' ORDER BY orderby");

if (is_array($options)) {

    // Define data for the navigation within section

    $dt_general = $dt_modules = array();
    $_option_title = '';

    foreach ($options as $catname) {

        $highlighted = ($option == $catname) ? 'hl' : '';

        $tmp = array(
            'link'  => "configuration.php?option=$catname",
            'style' => $highlighted
        );

        if (empty($active_modules[$catname])) {

            $option_title = func_get_langvar_by_name('option_title_' . $catname, false, false, true);

            if (empty($option_title)) {
                $option_title = str_replace('_', " ", $catname) . " options";
            }

            $tmp['title'] = $option_title;
            $dt_general[] = $tmp;

        } else {

            $option_title = func_get_langvar_by_name('module_name_' . $catname, false, false, true);
            $tmp['title'] = $option_title;
            $tmp['link'] .= '&right';
            $dt_modules[] = $tmp;

        }

        if ($highlighted == 'hl') {
            $_option_title = $tmp['title'];
        }

    }

    // Sort dialog tools list by the 'title' field
    function usort_array_cmp_title($a, $b) {
        return strcmp($a['title'], $b['title']);
    }

    usort($dt_general, 'usort_array_cmp_title');
    usort($dt_modules, 'usort_array_cmp_title');

    $dialog_tools_data['left'] = array(
        'data'  => $dt_general,
        'title' => func_get_langvar_by_name('lbl_core_options')
    );

    if (!empty($dt_modules)) {
        $dialog_tools_data['right'] = array(
            'data'  => $dt_modules,
            'title' => func_get_langvar_by_name('option_title_Modules')
        );
    }

    if (isset($_GET['right'])) {

        $dialog_tools_data['show'] = 'right';

    }
}

if (!empty($active_modules[$option])) {

    $fn = $xcart_dir . '/modules/' . $option . '/admin_config.php';

    if (file_exists($fn)) {
        require $fn;
    }
} elseif (
    !empty($active_modules['Flyout_Menus'])
    && $option == 'Appearance'
    && @$fc_build_categories == 'Y'
) {
    include $xcart_dir . '/modules/Flyout_Menus/admin_config.php';
}

if ($option == 'Security') {

    x_load('http', 'tests');

    $https_check_success = false;

    $https_check_success_bouncer = test_active_bouncer();

    if ($https_check_success_bouncer) {

        list($https_check_success, $https_check_error) = test_https_availibility(XC_SKIP_BOUNCER_TEST);
    }
}

if ($option == 'Shipping') {

    x_load('tests');

    $fedex_soap_available = func_is_soap_available();
    $fedex_dom_available = func_is_dom_available();
}

// Postprocessing service array with configuration variables of the current section
foreach ($configuration as $k => $v) {

    // Define array with variable variants
    if (in_array($v['type'], array("selector","multiselector"))) {

        if (is_array($v['variants'])) {

            $vars = $v['variants'];

        } elseif (
            is_string($v['variants'])
            && function_exists($v['variants'])
        ) {
            $_funcname = $v['variants'];
            $vars = $_funcname();
            if (!is_array($vars))
                $configuration[$k]['type'] = 'text';
        } else {

            $vars = func_parse_str(trim($v['variants']), "\n", ":");
            $vars = func_array_map('trim', $vars);

        }

        // Check variable data
        if ($v['type'] == "multiselector") {

            $configuration[$k]['value'] = $v['value'] = explode(";", $v['value']);

            foreach ($v['value'] as $vk => $vv) {
                if (!isset($vars[$vv]))
                    unset($v['value'][$vk]);
            }

            $configuration[$k]['value'] = $v['value'] = array_values($v['value']);
        }

        $configuration[$k]['variants'] = array();

        $vars = XCConfigVars::unsetDisabledVariants($v['name'], $vars);

        foreach ($vars as $vk => $vv) {
            if (!empty($vv) && strpos($vv, "_") !== FALSE && strpos($vv, " ") === FALSE) {
                /* Incorrect variable name */
                assert('addslashes($vv) === $vv');
                $name = func_get_langvar_by_name(addslashes($vv), NULL, false, true);
                if (!empty($name)) {
                    $vv = $name;
                }
            }

            $configuration[$k]['variants'][$vk] = array("name" => $vv);
        }
    }

    $predefined_lng_variables[] = 'opt_' . $v['name'];
    $predefined_lng_variables[] = 'opt_descr_' . $v['name'];

    $cf_currency = null;

    switch ($v['name']) {

        case 'cmpi_currency':
            $currs = func_query_hash("SELECT code, name FROM $sql_tbl[currencies]", 'code', false, false);
            if (!empty($currs)) {
                $configuration[$k]['variants'] = $currs;
            }

            break;

        case 'cron_key':
            $configuration[$k]['note'] = func_get_langvar_by_name("txt_cron_key_opt_note", array('path' => "php ".$xcart_dir."/cron.php --key=" . $config['General']['cron_key']));

            break;

        case 'shop_closed_key':
            $configuration[$k]['note'] = func_get_langvar_by_name("txt_shop_closed_key_opt_note", array('http_location' => $http_location, 'shop_closed_key' => $config['General']['shop_closed_key']));
            break;

        case 'det_image_max_height_icon':
        case 'frf_limit_for_preauth':
        case 'https_proxy':
            $configuration[$k]['note'] = func_get_langvar_by_name('txt_' . $v['name'] . '_opt_note');

            break;

        case 'blowfish_enabled':
            if ($v['value'] == 'Y' && $is_merchant_password != 'Y')
                $configuration[$k]['error'] = func_get_langvar_by_name("txt_no_disable_blowfish");

            break;

        case 'intershipper_username':
            $configuration[$k]['pre_note'] = func_get_langvar_by_name("txt_intershipper_account_note");

            break;

        case 'lock_login_attempts':

            break;

        case 'USPS_username':
            $configuration[$k]['pre_note'] = func_get_langvar_by_name("txt_usps_account_note");

            break;

        case 'CPC_testmode':
            $configuration[$k]['pre_note'] = func_get_langvar_by_name("txt_cpc_account_note");

            break;

        case 'ARB_id':
            $configuration[$k]['pre_note'] = func_get_langvar_by_name("txt_airborne_account_note");

            break;

        case 'dhl_siteid':
            $configuration[$k]['pre_note'] = func_get_langvar_by_name("txt_dhl_account_note");

            break;

        case 'FEDEX_account_number':
            $configuration[$k]['pre_note'] = func_get_langvar_by_name("txt_fedex_account_note");
            if (!$fedex_soap_available) {
                $configuration[$k]['pre_note'] .= func_get_langvar_by_name("txt_soap_check_warning");
            }
            if (!$fedex_dom_available) {
                $configuration[$k]['pre_note'] .= func_get_langvar_by_name("txt_dom_check_warning");
            }
            break;

        case '1800c_username':
            $configuration[$k]['pre_note'] = func_get_langvar_by_name("txt_1800C_account_note");

            break;

        case 'use_https_login':
            if (!$https_check_success_bouncer) {
                $configuration[$k]['pre_note'] = func_get_langvar_by_name("txt_https_check_warning_bouncer", array("https_location" => $https_location));
            } elseif (!$https_check_success) {
                $configuration[$k]['pre_note'] = func_get_langvar_by_name("txt_https_check_warning", array("https_location" => $https_location));
            }

            break;

        case 'small_items_box_length':
            $configuration[$k]['pre_note'] = func_get_langvar_by_name("txt_dimensional_units", array('unit' => $config['General']['dimensions_symbol']));

            break;

        case 'date_format':
            $date_formats = array(
                "%d-%m-%Y",
                "%d/%m/%Y",
                "%d.%m.%Y",
                "%m-%d-%Y",
                "%m/%d/%Y",
                "%Y-%m-%d",
                "%b %e, %Y",
                "%A, %B %e, %Y",
            );

            $t = func_microtime();
            foreach ($date_formats as $format) {
                $configuration[$k]['variants'][$format] = array('name' => func_strftime($format, $t));
            }

            break;

        case 'time_format':
            $time_formats = array(
                '',
                "%H:%M",
                "%H.%M",
                "%I:%M %p",
                "%H:%M:%S",
                "%H.%M.%S",
                "%I:%M:%S %p",
            );

            $t = func_microtime();

            foreach ($time_formats as $format) {
                $configuration[$k]['variants'][$format] = array('name' => func_strftime($format, $t));
            }

            break;

        case 'currency_format':
            $cf_currency = $config['General']['currency_symbol'];

        case 'alter_currency_format':

            if (is_null($cf_currency))
                $cf_currency = $config['General']['alter_currency_symbol'];

            $cf_value = price_format(9.99);

            foreach ($configuration[$k]['variants'] as $vk => $vv) {
                $configuration[$k]['variants'][$vk]['name'] = str_replace(array('x', '$'), array($cf_value, $cf_currency), $vk);
            }

            break;

        case 'default_giftcert_template':

            foreach (func_gc_get_templates($xcart_dir . $smarty_skin_dir) as $t) {
                $configuration[$k]['variants'][$t] = array(
                    'name' => $t,
                );
            }

            break;

        case 'spambot_arrest_img_generator':

            include_once $xcart_dir . '/modules/Image_Verification/spambot_requirements.php';

            $handle = @opendir($xcart_dir . '/modules/Image_Verification/img_generators/');

            if ($handle) {

                while (($file = readdir($handle)) != false) {

                    if (
                        $file != '.'
                        && $file != '..'
                        && @is_dir($xcart_dir . "/modules/Image_Verification/img_generators/$file")
                        && $file != 'CVS'
                    ) {
                        $configuration[$k]['variants'][$file] = array('name' => $file);

                    }

                }

                closedir($handle);
            }

            break;

        case 'line_language_selector':
            // Disable 'single-line select box (icon)' if some languages hasn't flags icons
            if (!func_check_languages_flags()) {
                func_unset($configuration[$k]['variants'], 'F');

                $configuration[$k]['warning'] = func_get_langvar_by_name("txt_displaying_language_icons_disabled_conf_note");
            }

            break;

        case 'partner_register_moderated':
        case 'display_backoffice_link':
            foreach ($configuration as $c) {
                if ($c['name'] == 'partner_register') {
                    $configuration[$k]['disabled'] = $c['value'] != 'Y';
                    break;
                }
            }

            break;

        case 'gift_wrap_taxes':
            $taxes_array = func_query_column("SELECT CONCAT(taxid,':',tax_name) FROM $sql_tbl[taxes]");
            if (!empty($taxes_array)) {
                $configuration[$k]['variants'] = implode("\n",$taxes_array);
            }

            break;

        case 'sum_up_wrapping_cost':
            if ($single_mode) {
                func_unset($configuration,$k);
            }

            break;

        case 'cloud_search_settings_help_text':
            $configuration[$k]['comment'] = func_get_langvar_by_name('txt_cloud_search_settings_help_text');
            break;

        case 'abcr_coupon_type':

            if (!empty($active_modules['Abandoned_Cart_Reminder'])) {
                func_abcr_redefine_configuration($configuration[$k]);
            }

            break;

        case 'bml_enable_banners':

            $configuration[$k]['note'] = func_get_langvar_by_name('txt_bml_agree_terms');

            break;

        case 'bml_paypal_email':
            if (!empty($config['paypal_bml_publisherid'])) {
                $configuration[$k]['raw_note'] = func_get_langvar_by_name('lbl_bml_your_publisherid', array('pubid' => strtoupper($config['paypal_bml_publisherid'])));
            }

            break;

        case 'AP_apikey':
            $configuration[$k]['raw_note'] = func_get_langvar_by_name('txt_ap_apikey_note');

            break;

        case 'AP_testmode':
            $configuration[$k]['raw_note'] = func_get_langvar_by_name('txt_ap_testmode_note');

            break;
    }

    if (!empty($active_modules['Klarna_Payments'])) {
        
        if ($v['name'] == 'klarna_invoice_tax') {   
            $taxes_array = func_query("SELECT taxid, tax_name FROM $sql_tbl[taxes]");
            if (!empty($taxes_array)) {
                foreach ($taxes_array as $tax) {
                    $configuration[$k]['variants'][$tax['taxid']] = array('name' => $tax['tax_name']);
                }
            }
        }

    }

    if (!empty($active_modules['TwoFactorAuth']) && $option == 'TwoFactorAuth') {
        func_twofactor_redefine_config($configuration, $k);
    }

    if (!isset($configuration[$k])) {
        continue;
    }

    if ($v['type'] == 'state') {
        $found = false;

        if (preg_match('/^(.+)_state$/Ss', $v['name'], $m)) {

            $cname = $m[1] . '_country';
            $found = false;

            foreach ($configuration as $v2) {
                if ($v2['name'] == $cname && $v2['type'] == 'country') {
                    $found = true;
                    $configuration[$k]['country_value'] = $v2['value'];
                    $configuration[$k]['prefix'] = $m[1];
                    break;
                }
            }
        }

        if (!$found) {
            $configuration[$k]['type'] = 'text';
        }

    } elseif ($v['type'] == 'country') {

        $configuration[$k]['prefix'] = preg_replace('/_country$/Ss', '', $v['name']);

    } elseif ($option == 'Logging' && preg_match('/^log_/', $v['name'])) {

        $configuration[$k]['variants'] = array(
            'N'     => array(
                'name' => func_get_langvar_by_name("lbl_log_act_nothing"),
            ),
            'L'     => array(
                'name' => func_get_langvar_by_name("lbl_log_act_log"),
            ),
            'E'     => array(
                'name' => func_get_langvar_by_name("lbl_log_act_email"),
            ),
            'LE'     => array(
                'name' => func_get_langvar_by_name("lbl_log_act_log_n_email"),
            ),
        );
    }

    if (
        $configuration[$k]['type'] == 'selector'
        || $configuration[$k]['type'] == 'multiselector'
    ) {

        if (
            !is_array($configuration[$k]['variants'])
            || count($configuration[$k]['variants']) == 0
        ) {
            unset($configuration[$k]);

            continue;
        }

        foreach ($configuration[$k]['variants'] as $vk => $vv) {

            $configuration[$k]['variants'][$vk]['selected'] = $configuration[$k]['type'] == "selector"
                ? $configuration[$k]['value'] == $vk
                : in_array($vk, $configuration[$k]['value']);

        }

    }

}

if ($option) {

    $predefined_lng_variables[] = 'option_title_' . $option;

}

if ($option == 'Shipping') {

    $is_realtime = (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[shipping] WHERE code != ''") > 0);

    if ($is_realtime) {

        $smarty->assign('is_realtime', $is_realtime);

    }

} elseif ($option == 'SEO') {

    $unallowed_dirs = array('payment');

    foreach (
        array(
            'ADMIN',
            'PROVIDER',
            'PARTNER',
        ) as $area
    ) {
        $area_directory = constant('DIR_' . $area);

        if (
            !zerolen($area_directory)
            && preg_match('/^\/.+/', $area_directory)
        ) {

            $unallowed_dirs[] = preg_quote(ltrim($area_directory, '/'), '/');

        }

    }

    $unallowed_dirs = join("|", $unallowed_dirs);

    $apache_401_issue = func_get_apache_401_issue();
    if (
        ($dirs = func_is_used_ssl_shared_cert($http_location, $https_location))
        && func_apache_check_module('setenv')
    ) {
        $_htaccess = <<<SHTACCESS
            RewriteCond %{HTTPS} on
            RewriteRule .* - [E=FULL_WEB_DIR:$dirs[https]]
            RewriteCond %{HTTPS} !on
            RewriteRule .* - [E=FULL_WEB_DIR:$dirs[http]]

            $apache_401_issue
            RewriteCond %{REQUEST_URI} !^%{ENV:FULL_WEB_DIR}/($unallowed_dirs)/
            RewriteCond %{REQUEST_URI} !\.(gif|jpe?g|png|js|css|swf|php|ico|svg|tif|avi|mp3|mp4|flv)$
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteCond %{REQUEST_FILENAME} !-l
            RewriteRule ^(.*)$ %{ENV:FULL_WEB_DIR}/dispatcher.php [L]
SHTACCESS;
    } else {
        $rewrite_base = func_get_rewrite_base();
        $_htaccess = <<<SHTACCESS
            RewriteBase $rewrite_base

            $apache_401_issue
            RewriteCond %{REQUEST_URI} !^$rewrite_base($unallowed_dirs)/
            RewriteCond %{REQUEST_URI} !\.(gif|jpe?g|png|js|css|swf|php|ico|svg|tif|avi|mp3|mp4|flv)$
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteCond %{REQUEST_FILENAME} !-l
            RewriteRule ^(.*)$ dispatcher.php [L]
SHTACCESS;
    }    
    $_htaccess = preg_replace("/^[ ]*(?=[a-z#])/mi", "\t", $_htaccess);

    $clean_url_htaccess = <<<EHTACCESS
# Clean URLs [[[
Options +FollowSymLinks -MultiViews -Indexes
&lt;IfModule mod_rewrite.c&gt;
\tRewriteEngine On

$_htaccess
&lt;/IfModule&gt;
# /Clean URLs ]]]
EHTACCESS;

    $smarty->assign('clean_url_htaccess',         $clean_url_htaccess);
    $smarty->assign('clean_url_htaccess_path',     $xcart_dir . XC_DS . '.htaccess');
    $smarty->assign('clean_url_test_url',         $http_location . DIR_CUSTOMER . "/clean-url-test");

} elseif ($option == 'Maintenance_Agent') {

    $periodical_log_labels = array();

    foreach (explode(',', $config['Maintenance_Agent']['periodic_logs']) as $k=>$v) {

        $periodical_log_labels[$v] = true;

    }

    $smarty->assign('periodical_log_labels', $periodical_log_labels);
    $smarty->assign('periodical_logs_names', x_log_get_names());

} elseif ($option == 'General') {

    $speedUpHtaccess = <<<SHTACCESS
&lt;FilesMatch "\.(css|js)$"&gt;
    Allow from all
&lt;/FilesMatch&gt;
SHTACCESS;

    $smarty->assign('speed_up_htaccess', $speedUpHtaccess);
    $smarty->assign('htaccess_file',     str_replace("/", XC_DS, $var_dirs['cache']) . XC_DS . '.htaccess');

} elseif ($option == 'PayPal_Login') {

    $smarty->assign('paypal_login_return_url', ($config['Security']['use_https_login'] == 'Y' ? $https_location : $http_location) . '/pplogin_return.php');
    $smarty->assign('paypal_login_terms_url', $http_location . '/pages.php?alias=conditions');
    $smarty->assign('paypal_login_privacy_url', $http_location . '/pages.php?alias=business');
}

if (
    !empty($active_modules[$option])
) {
    $fn = $xcart_dir . '/modules/' . $option . '/admin_config_display.php';

    if (file_exists($fn)) {
        require $fn;
    }
}

$smarty->assign('htaccess_path', $xcart_dir . XC_DS . '.htaccess');
$smarty->assign('configuration', array_values($configuration));
$smarty->assign('options',       $options);
$smarty->assign('option',        $option);
$smarty->assign('option_title',  $_option_title);
$smarty->assign('main',          'configuration');
$smarty->assign('email_extended_validation_regexp', func_email_validation_regexp(XCEmailFormat::EXTENDED));

if (!empty($_option_title))
    $location[] = array( $_option_title, '');

// Assign the current location line
$smarty->assign('location',         $location);

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir . '/modules/gold_display.php')) {
    include $xcart_dir . '/modules/gold_display.php';
}

func_display('admin/home.tpl', $smarty);
?>
