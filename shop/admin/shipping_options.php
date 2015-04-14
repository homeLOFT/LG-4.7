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
 * Shipping options
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v117 (xcart_4_7_0), 2015-02-17 13:29:01, shipping_options.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

x_session_register('saved_user_data');

$location[] = array(func_get_langvar_by_name('lbl_shipping_options'), '');

$carriers = func_get_carriers();

$carrier_valid = false;

foreach ($carriers as $k => $v) {
    if ($v[0] == $carrier) {
        $carrier_valid = true;
        break;
    }
}

$carrier = $carrier_valid ? $carrier : '';

if ($carrier_valid && $REQUEST_METHOD == 'POST') {
/**
 * Update the shipping options
 */
    $topMessage = '';
    $suffix = '';
    $shippingOptions = array();

    if ($carrier == 'FDX') {

    // FEDEX options update

        if (isset($update_options)) {

            // Update the FedEx options
            $carrier_codes = isset($carrier_codes) && !empty($carrier_codes) ? implode('|', $carrier_codes) : '';

            $rate_request_types = isset($rate_request_types) ? $rate_request_types : 'ACCOUNT';

            $fedex_options = array(
                'carrier_codes' => $carrier_codes,
                'packaging' => $packaging,
                'dropoff_type' => $dropoff_type,
                'ship_date' => intval($ship_date),
                'dim_length' => sprintf("%.2f", $dim_length),
                'dim_width' => sprintf("%.2f", $dim_width),
                'dim_height' => sprintf("%.2f", $dim_height),
                'max_weight' => abs(doubleval($max_weight)),
                'cod_value' => sprintf("%.2f", $cod_value),
                'cod_type' => $cod_type,
                'alcohol' => (empty($alcohol) ? 'N' : 'Y'),
                'hold_at_location' => (empty($hold_at_location) ? 'N' : 'Y'),
                'dry_ice' => (empty($dry_ice) ? 'N' : 'Y'),
                'nonstandard_container' => (empty($nonstandard_container) ? 'N' : 'Y'),
                'inside_pickup' => (empty($inside_pickup) ? 'N' : 'Y'),
                'inside_delivery' => (empty($inside_delivery) ? 'N' : 'Y'),
                'saturday_pickup' => (empty($saturday_pickup) ? 'N' : 'Y'),
                'saturday_delivery' => (empty($saturday_delivery) ? 'N' : 'Y'),
                'residential_delivery' => (empty($residential_delivery) ? 'N' : 'Y'),
                'dg_accessibility' => $dg_accessibility,
                'signature' => $signature,
                'handling_charges_amount' => sprintf("%.2f", $handling_charges_amount),
                'handling_charges_type' => $handling_charges_type,
                'currency_code' => $currency_code,
                'purpose_type' => @$purpose_type,
                'param01' => @$param01,
                'param02' => @$param02,
                'add_smartpost_detail' => @$add_smartpost_detail,
                'smartpost_indicia' => $smartpost_indicia,
                'smartpost_ancillaryendorsement' => $smartpost_ancillaryendorsement,
                'smartpost_hubid' => $smartpost_hubid,
                'send_insured_value' => @$send_insured_value,
                'rate_request_types' => $rate_request_types,
            );

            $shippingOptions['param00'] = addslashes(serialize($fedex_options));

        }

    } elseif ($carrier == 'USPS') {

    // USPS options update

        $dim = abs(doubleval($dim_length)) . ':' . abs(doubleval($dim_width)) . ':' . abs(doubleval($dim_height)) . ':' . abs(doubleval($dim_girth));

        if ($value_of_content_type == 'fixed_value')
            $value_of_content_type = abs(round($value_of_content_fixed, 2));

        settype($use_maximum_dimensions, 'string');
        settype($status_new_method, 'string');
        settype($param11, 'string');
        settype($selected_services, 'array');
        $_param00 = array(
            'mailtype' => $mailtype,
            'ground_only' => $ground_only,
            'selected_services' => $selected_services,
            'use_commercial_rate' => empty($use_commercial_rate) ? 'N' : $use_commercial_rate,
            'use_commercial_plus_rate' => empty($use_commercial_plus_rate) ? 'N' : $use_commercial_plus_rate,
        );
        $shippingOptions = array(
            'param00' => addslashes(serialize($_param00)),
            'param01' => $status_new_method,
            'param02' => $machinable,
            'param03' => $container_express,
            'param04' => $container_priority,
            'param05' => $firstclassmailtype,
            'param06' => $dim,//common
            'param07' => $value_of_content_type,
            'param08' => abs(doubleval($max_weight)),//common
            'param09' => ('Y' !== $use_maximum_dimensions) ? 'N' : $use_maximum_dimensions,//common
            'param10' => $container_intl,
            'param11' => empty($param11) ? 'N' : $param11,//common: Split the shipment into multiple packages if...
        );

    } elseif ($carrier == 'CPC') {

        // Canada Post options update
        $dim = abs(doubleval($dim_length)) . ':' . abs(doubleval($dim_width)) . ':' . abs(doubleval($dim_height));

        if ($coverage_type == 'fixed_value')
            $coverage_type = abs(round($coverage_fixed, 2));

        if ($cod_type == 'fixed_value')
            $cod_type = abs(round($cod_fixed, 2));

        settype($use_maximum_dimensions, 'string');
        settype($customer_number, 'string');
        settype($status_new_method, 'string');
        settype($param11, 'string');

        $customer_number = ltrim($customer_number, '0');
        $shippingOptions = array(
            'param00' => (!empty($options) ? implode('|', $options) : ''),
            'param01' => $status_new_method, 
            'param02' => $cod_type,
            'param03' => $customer_number,
            'param04' => $contract_id,
            'param05' => $quote_type,
            'param06' => $dim,//common
            'param07' => $coverage_type,
            'param08' => abs(doubleval($max_weight)),// common
            'param09' => ('Y' !== $use_maximum_dimensions) ? 'N' : $use_maximum_dimensions,//common
            'param10' => '',
            'param11' => empty($param11) ? 'N' : $param11,//common: Split the shipment into multiple packages if...
        );

    } elseif ($carrier == 'Intershipper') {

    // INTERSHIPPER options update

        $shippingOptions = array(
            'param00' => $delivery,
            'param01' => $shipmethod,
            'param02' => abs(doubleval($length)),
            'param03' => abs(doubleval($width)),
            'param04' => abs(doubleval($height)),
            'param05' => is_array($options) ? implode('|', $options) : '',
            'param06' => $packaging,
            'param07' => $contents,
            'param08' => abs(doubleval($codvalue)),
            'param09' => abs(doubleval($weight)),
            'param10' => ('Y' !== $use_maximum_dimensions) ? 'N' : $use_maximum_dimensions,
        );

    } elseif ($carrier == 'ARB') {

    // Airborne ShipIt options update

        $shippingOptions = array(
            'param00' => $param00,
            'param01' => intval($param01),
            'param02' => intval($param02),
            'param03' => intval($param03),
            'param04' => intval($param04),
            'param05' => (intval($param06) < 1) ? 'NR' : $param05,
            'param06' => intval($param06),
            'param07' => $opt_haz . ',' . $opt_own_account,
            'param08' => !in_array($param08, array('M', 'P')) ? 'M' : $param08,
            'param09' => intval($param09),
            'param10' => abs(doubleval($param10)),
            'param11' => isset($param11) ? $param11 : 'N',
        );

    } elseif ($carrier == 'APOST') {

    // Australia Post options update
        $dim = abs(doubleval($dim_length)) . ':' . abs(doubleval($dim_width)) . ':' . abs(doubleval($dim_height));

        settype($use_maximum_dimensions, 'string');
        settype($status_new_method, 'string');
        settype($param11, 'string');

        $shippingOptions = array(
            'param00' => '',
            'param01' => $status_new_method,
            'param02' => strval($param02),
            'param03' => strval($param03),
            'param04' => empty($param04_checkbox) ? 0 : intval($param04),
            'param05' => '',
            'param06' => $dim, // common
            'param07' => '',
            'param08' => abs(doubleval($max_weight)), // common
            'param09' => ('Y' !== $use_maximum_dimensions) ? 'N' : $use_maximum_dimensions, //common
            'param10' => '',
            'param11' => empty($param11) ? 'N' : $param11, //common: Split the shipment into multiple packages if...
        );

    } elseif ($carrier == '1800C') {
        include $xcart_dir . '/include/1800C_registration.php';
        $topMessage = func_get_langvar_by_name('msg_1800c_info_sent');
    }

    if (!empty($shippingOptions)) {

        $shippingOptions['currency_rate'] = isset($currency_rate) ? abs(doubleval($currency_rate)) : 1;
        $shippingOptions['carrier'] = $carrier;

        func_array2insert(
            'shipping_options',
            $shippingOptions,
            true
        );

    }

    $top_message['content'] = empty($topMessage)
        ? func_get_langvar_by_name('msg_adm_shipping_option_upd')
        : $topMessage;

    func_header_location('shipping_options.php?carrier=' . $carrier . $suffix);

} // /if ($REQUEST_METHOD == 'POST')

/**
 * Collect options for current carrier
 */
$shipping_options = array ();

$shipping_options [strtolower($carrier)] = func_query_first("SELECT * FROM $sql_tbl[shipping_options] WHERE carrier='$carrier'");

if ($carrier == '1800C') {
    $default_seller_address = array(
            'city'      => $config['Company']['location_city'],
            'state'     => $config['Company']['location_state'],
            'country'   => $config['Company']['location_country'],
            'zipcode'   => $config['Company']['location_zipcode'],
            'address'   => $config['Company']['location_address'],
            'phone'     => $config['Company']['company_phone'],
            );

    if (!$single_mode && $current_area == 'P') {
        $seller_address = func_query_first("SELECT * FROM $sql_tbl[seller_addresses] WHERE userid='$userinfo[id]'");
    }
    if (empty($seller_address)) {
        $seller_address = $default_seller_address;
    }
    $seller_address['company_name'] = $config['Company']['company_name'];
    $seller_address['username'] = $config['Shipping']['1800c_username'];
    $seller_address['readytime'] = $config['Shipping']['1800c_readytime'];
    $seller_address['subsidize'] = $config['Shipping']['1800c_subsidize'];
    $seller_address['business_hours'] = $config['Shipping']['1800c_business_hours'];
    $seller_address['operation_days'] = $config['Shipping']['1800c_operation_days'];

    if (file_exists($xcart_dir . '/shipping/mod_1800C.php')) {
        include_once $xcart_dir . '/shipping/mod_1800C.php';
    }

    if (function_exists('func_get_warehouse_info')) {
        $seller_address = func_get_warehouse_info($seller_address);
    }

    $smarty->assign('seller_address', $seller_address);
    $smarty->assign('send_is_avail', (!empty($config['Shipping']['1800c_username']) && !empty($config['Shipping']['1800c_password'])));

}

if ($carrier == 'FDX') {

    // Prepare FedEx (API) information
    if (!empty($error))
        $smarty->assign('fill_error', 'Y');

        $prepared_user_data = $saved_user_data;

    if (empty($prepared_user_data)) {
        $prepared_user_data = array();
        $prepared_user_data['person_name'] = $user_account['firstname'] . ' ' . $user_account['lastname'];
        $prepared_user_data['company_name'] = $config['Company']['company_name'];
        $prepared_user_data['phone_number'] = preg_replace("/[^\d]/", "", $config['Company']['company_phone']);
        $prepared_user_data['email'] = $config['Company']['site_administrator'];
        $prepared_user_data['address_1'] = $config['Company']['location_address'];
        $prepared_user_data['city'] = $config['Company']['location_city'];
        $prepared_user_data['state'] = $config['Company']['location_state'];
        $prepared_user_data['zipcode'] = $config['Company']['location_zipcode'];
        $prepared_user_data['country'] = $config['Company']['location_country'];
    }

    $smarty->assign('prepared_user_data', $prepared_user_data);

    include_once $xcart_dir.'/include/countries.php';
    include_once $xcart_dir.'/include/states.php';

    $fedex_options = $shipping_options['fdx']['param00'];
    $shipping_options['fdx'] = @unserialize($fedex_options);
}
#####################################

if ($carrier == 'Intershipper') {
/**
 * Get the shipping options for Intershipper service
 */
    $options = explode('|',$shipping_options["intershipper"]["param05"]);
    foreach($options as $option) {
        $shipping_options['intershipper']['options'][$option] = 'Y';
    }

    $smarty->assign('max_intershipper_weight', round(150 * XCPhysics::GRAMS_PER_1LB / $config['General']['weight_symbol_grams'], 4));
}

if ($carrier == 'ARB') {
    $_data = explode(',',$shipping_options["arb"]["param07"]);
    $shipping_options['arb']['opt_haz'] = @$_data[0];
    $shipping_options['arb']['opt_own_account'] = @$_data[1];
    $smarty->assign('max_arb_weight', round(149 * XCPhysics::GRAMS_PER_1LB / $config['General']['weight_symbol_grams'], 4));
}

if ($carrier == 'USPS') {
    $lower_carrier = strtolower($carrier);
    $_dim = explode(':', $shipping_options[$lower_carrier]['param06']);

    if (func_array_empty($_dim))
        $_dim = array_fill(0, 4, 0);

    $shipping_options[$lower_carrier]['dim_length'] = $_dim[0];
    $shipping_options[$lower_carrier]['dim_width'] = $_dim[1];
    $shipping_options[$lower_carrier]['dim_height'] = $_dim[2];
    $shipping_options[$lower_carrier]['dim_girth'] = $_dim[3];

    if (is_numeric($shipping_options[$lower_carrier]['param07']))
        $shipping_options[$lower_carrier]['fixed_value'] = 'Y';

    $all_usps_services = array(
        'FIRST CLASS',
        'FIRST CLASS METERED',
        'FIRST CLASS COMMERCIAL',
        'FIRST CLASS HFP COMMERCIAL',
        'PRIORITY',
        'PRIORITY COMMERCIAL',
        'PRIORITY CPP',
        'PRIORITY HFP COMMERCIAL',
        'PRIORITY HFP CPP',
        'PRIORITY MAIL EXPRESS',
        'PRIORITY MAIL EXPRESS COMMERCIAL',
        'PRIORITY MAIL EXPRESS CPP',
        'PRIORITY MAIL EXPRESS SH',
        'PRIORITY MAIL EXPRESS SH COMMERCIAL',
        'PRIORITY MAIL EXPRESS HFP',
        'PRIORITY MAIL EXPRESS HFP COMMERCIAL',
        'PRIORITY MAIL EXPRESS HFP CPP',
        'STANDARD POST',
        'MEDIA',
        'LIBRARY',
//        'PLUS',
    );
    $smarty->assignByRef('all_usps_services', $all_usps_services);

    $_params = @unserialize($shipping_options[$lower_carrier]['param00']);
    if (!empty($_params)) {
        $shipping_options[$lower_carrier]['mailtype'] = $_params['mailtype'];
        $shipping_options[$lower_carrier]['ground_only'] = $_params['ground_only'];
        $shipping_options[$lower_carrier]['use_commercial_plus_rate'] = $_params['use_commercial_plus_rate'];
        $shipping_options[$lower_carrier]['use_commercial_rate'] = $_params['use_commercial_rate'];

        if (!empty($_params['selected_services'])) {
            $shipping_options[$lower_carrier]['selected_services'] = array_combine($_params['selected_services'], array_fill(0, count($_params['selected_services']), TRUE));
        }
    } else {
        $shipping_options[$lower_carrier]['selected_services'] = array();
    }
}

if ($carrier == 'CPC') {
    $_dim = explode(':', $shipping_options[strtolower($carrier)]["param06"]);

    if (func_array_empty($_dim))
        $_dim = array_fill(0, 3, 0);

    $shipping_options[strtolower($carrier)]['dim_length'] = $_dim[0];
    $shipping_options[strtolower($carrier)]['dim_width'] = $_dim[1];
    $shipping_options[strtolower($carrier)]['dim_height'] = $_dim[2];

    $shipping_options['cpc']['options'] = array();
    if (!empty($shipping_options['cpc']['param00'])) {
        $ccodes = explode('|', $shipping_options["cpc"]["param00"]);
        foreach ($ccodes as $code) {
            $shipping_options['cpc']['options'][$code] = 1;
        }
    }

    if (is_numeric($shipping_options[strtolower($carrier)]["param07"]))
        $shipping_options[strtolower($carrier)]['coverage_fixed_value'] = 'Y';

    if (is_numeric($shipping_options[strtolower($carrier)]["param02"]))
        $shipping_options[strtolower($carrier)]['cod_fixed_value'] = 'Y';
}

if ($carrier == 'FDX') {
    if (!empty($shipping_options['fdx']['carrier_codes'])) {
        $ccodes = explode('|', $shipping_options["fdx"]["carrier_codes"]);
        $shipping_options['fdx']['carrier_codes'] = array();
        foreach ($ccodes as $code) {
            $shipping_options['fdx']['carrier_codes'][$code] = 1;
        }
    } else {
        if (empty($shipping_options['fdx'])) {
            $shipping_options['fdx'] = array();
        }
        $shipping_options['fdx']['carrier_codes'] = array();
    }
}

if ($carrier == 'APOST') {
    $_dim = explode(':', $shipping_options[strtolower($carrier)]['param06']);

    if (func_array_empty($_dim))
        $_dim = array_fill(0, 3, 0);

    $shipping_options[strtolower($carrier)]['dim_length'] = $_dim[0];
    $shipping_options[strtolower($carrier)]['dim_width'] = $_dim[1];
    $shipping_options[strtolower($carrier)]['dim_height'] = $_dim[2];

    if (file_exists($xcart_dir . '/shipping/mod_AP.php')) {
        include_once $xcart_dir . '/shipping/mod_AP.php';
    }

    $shipping_options[strtolower($carrier)]['service_options'] = array(
        XC_AP_Options::AUS_SERVICE_OPTION_STANDARD => 'Standard Service',
        XC_AP_Options::AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY => 'Signature on Delivery'
    );

    $available_package_sizes = XC_AP_GetSizes::getInstance()->func_get_available_sizes_AP();

    $shipping_options[strtolower($carrier)]['package_types'][XC_AP_Options::USER_DEFINED_BOX] = 'My own box';

    foreach ($available_package_sizes as $package_size) {
        if (!empty($package_size['code']) && !empty($package_size['name'])) {
            $shipping_options[strtolower($carrier)]['package_types'][$package_size['code']] = $package_size['name'];
        }
    }
}

$smarty->assign('carriers', $carriers);
$smarty->assign('carrier', $carrier);

$smarty->assign ('shipping_options', $shipping_options);

$smarty->assign('main','shipping_options');

// Assign the current location line
$smarty->assign('location', $location);

include $xcart_dir . DIR_ADMIN . '/shipping_tools.php';

// Assign the section navigation data
$smarty->assign('dialog_tools_data', $dialog_tools_data);

if (is_readable($xcart_dir.'/modules/gold_display.php')) {
    include $xcart_dir.'/modules/gold_display.php';
}
func_display('admin/home.tpl',$smarty);
?>
