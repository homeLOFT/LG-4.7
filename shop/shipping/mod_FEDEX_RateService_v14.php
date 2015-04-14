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
 * FedEx shipping library
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v24 (xcart_4_7_0), 2015-02-17 23:56:28, mod_FEDEX_RateService_v14.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header('Location: ../'); die('Access denied'); }

if (!func_is_soap_available() && !func_is_dom_available() && !func_is_openssl_available()) {
    return;
}

function func_shipper_FEDEX($items, $userinfo, $orig_address, $debug, $cart)
{
    global $config;
    global $allowed_shipping_methods, $intershipper_rates;
    global $intershipper_error, $shipping_calc_service;

    if (func_fedex_is_disabled()) {
        return;
    }

    $f_smart = ($userinfo['s_country'] == $orig_address['country']) ? '175' : '194';
    $f_gr = ($userinfo['s_country'] == $orig_address['country']) ? '43' : '78';

    $fedex_services = array(
        'EUROPE_FIRST_INTERNATIONAL_PRIORITY'   => '138',
        'FEDEX_1_DAY_FREIGHT'                   => '133',   #FedEx 1Day##R## Freight
        'FEDEX_2_DAY'                           => '41',    #FedEx 2Day##R##
        'FEDEX_2_DAY_AM'                        => '174',   #FedEx 2Day##R## A.M.
        'FEDEX_2_DAY_FREIGHT'                   => '134',   #FedEx 2Day##R## Freight
        'FEDEX_3_DAY_FREIGHT'                   => '135',   #FedEx 3Day##R## Freight
        'FEDEX_EXPRESS_SAVER'                   => '42',    #FedEx Express Saver##R##
        'FEDEX_GROUND'                          => $f_gr,   #FedEx Ground##R## & #FedEx International Ground##R##
        'FIRST_OVERNIGHT'                       => '47',    #FedEx First Overnight##R##
        'GROUND_HOME_DELIVERY'                  => '44',    #FedEx Home Delivery##R##
        'INTERNATIONAL_ECONOMY'                 => '49',    #FedEx International Economy##R##
        'INTERNATIONAL_ECONOMY_FREIGHT'         => '137',   #FedEx International Economy##R## Freight
        'INTERNATIONAL_FIRST'                   => '96',    #FedEx International First##R##
        'INTERNATIONAL_PRIORITY'                => '48',    #FedEx International Priority##R##
        'INTERNATIONAL_PRIORITY_FREIGHT'        => '136',   #FedEx International Priority Freight##R##
        'PRIORITY_OVERNIGHT'                    => '45',    #FedEx Priority Overnight##R##
        'SMART_POST'                            => $f_smart,#FedEx SmartPost##R##/FedEx SmartPost##R## International
        'STANDARD_OVERNIGHT'                    => '46',    #FedEx Standard Overnight#R##
        'FEDEX_FREIGHT'                         => '176',   #FedEx Freight
        'FEDEX_NATIONAL_FREIGHT'                => '177',   #FedEx National Freight
        'INTERNATIONAL_GROUND'                  => '78',    #FedEx International Ground##R##
    );

    $fedex_options = func_fedex_get_options($userinfo, $debug, $cart, $orig_address);

    if ($debug == 'Y') {
        print "<h1>FedEx Debug Information</h1>";
    }

    $_fedex_rates = array();

    $package_limits = func_get_package_limits_FEDEX($fedex_options);
    $pack_limits = $package_limits[$fedex_options['packaging']];

    // FedEx RateFinder has limitation to package cost
    // The same limit set in X-Cart. BT:133176#727383
    if (!isset($pack_limits['price'])) {
        $pack_limits['price'] = 50000;
    }

    $packages = func_get_packages($items, $pack_limits, ($fedex_options['param01'] == 'Y') ? 200 : 1);

    if (!empty($packages) && is_array($packages)) {

        $soap_query = func_fedex_prepare_soap_query($packages, $fedex_options, $userinfo);

        $md5_request = md5(serialize($soap_query));

        if ($debug != 'Y' && func_is_shipping_result_in_cache($md5_request)) {

            // Get shipping rates from the cache
            $_fedex_rates = func_get_shipping_result_from_cache($md5_request);
        }

        if (empty($_fedex_rates)) {

            // Enable test server if specified

            XC_FEDEX_Rate_Service::getInstance()->setMode($config['Shipping']['FEDEX_test_server'] === 'Y'
                ? XC_SOAP_Service::SOAP_TEST_MODE
                : XC_SOAP_Service::SOAP_PRODUCTION_MODE
            );

            // Get shipping rates from FedEx server

            $result = XC_FEDEX_Rate_Service::getInstance()->getRates($soap_query);

            if (!$result) {
                // Error of SOAP reply from FedEx
                x_log_flag('log_shipping_errors', 'SHIPPING', "FedEx module (rates): Server returned no data to be processed.", true);
                return false;
            }

            $reply_msg = func_fedex_reply_messages($result);

            if (!empty($reply_msg['error'])) {
                // FedEx returned an error
                if (defined('DEVELOPMENT_MODE')) {
                    $intershipper_error = $reply_msg['error']['msg'];
                    $shipping_calc_service = 'FedEx';
                } elseif (!empty($reply_msg['error']['msg_to_customer'])) {
                    $intershipper_error = $reply_msg['error']['msg_to_customer'];
                    $shipping_calc_service = 'FedEx';
                }

                // Disable cache key
                if ($reply_msg['disable_cache']) {
                    $md5_request = 'disabled_cache_result';
                }
            } else {
                // FedEx returned a valid reply, get the rates

                // Disable cache key
                if (
                    !empty($reply_msg['disable_cache'])
                    && $reply_msg['disable_cache']
                ) {
                    $md5_request = 'disabled_cache_result';
                }

                $entries = array();

                if (property_exists($result, 'RateReplyDetails')) {
                    // assign rate reply
                    $entries = $result->RateReplyDetails;
                    // check if we have a single detail
                    if (!is_array($entries)) {
                        // in case of single detail object is returned, creating an array
                        $entries = array($entries);
                    }
                }

                if (!empty($entries) && is_array($entries)) {
                    foreach ($entries as $k => $entry) {
                        $service_type = $entry->ServiceType;

                        $estimated_rate = func_fedex_get_estimated_rate($entry, $fedex_options['currency_code'], $fedex_options['rate_request_types']);
                        $estimated_time = func_fedex_get_estimated_time($entry);

                        $rated_shipment_details = is_array($entry->RatedShipmentDetails)
                                ? $entry->RatedShipmentDetails : array($entry->RatedShipmentDetails);

                        $total_variable_handlings = property_exists($rated_shipment_details[0]->ShipmentRateDetail, 'TotalVariableHandlingCharges')
                                ? ($rated_shipment_details[0]->ShipmentRateDetail->TotalVariableHandlingCharges) : false;

                        $total_variable_handling = is_array($total_variable_handlings)
                                ? $total_variable_handlings : array($total_variable_handlings);

                        $variable_handling_charge = !empty($total_variable_handling[0]->VariableHandlingCharge->Amount)
                                ? $total_variable_handling[0]->VariableHandlingCharge->Amount : false;

                        if (floatval($variable_handling_charge) > 0) {
                            $estimated_rate += $variable_handling_charge;
                        }

                        foreach ($allowed_shipping_methods as $key => $value) {
                            if ($value['code'] == 'FDX'
                                && $value['subcode'] == $fedex_services[$service_type]
                            ) {
                                $_fedex_rates[] = array('methodid' => $value['subcode'], 'rate' => $estimated_rate, 'shipping_time' => $estimated_time);
                            }
                        }

                    }
                    assert('count($entries) == count($_fedex_rates) /*Some methods are skipped, check $fedex_services var*/');
                }
            }

            if ($debug == 'Y') {
            // Display a debug information (on testing real-time shipping page)

                if ($soap_query) {

                    $display_query = XC_FEDEX_Rate_Service::getInstance()->getLastRequestText();
                    $display_result = XC_FEDEX_Rate_Service::getInstance()->getLastResponseText();

                    print '<h2>FedEx Request</h2>';
                    print '<pre>' . htmlspecialchars($display_query) . '</pre>';
                    print '<h2>FedEx Response</h2>';
                    print '<pre>' . htmlspecialchars($display_result) . '</pre>';
                }
                else {
                    print 'It seems, you have forgotten to fill in a FedEx account information, or destination information (City, State, Country or ZipCode). Please check it, and try again.';
                }
            }

            // Save calculated rates to the cache
            if ($debug != 'Y') {
                func_save_shipping_result_to_cache($md5_request, $_fedex_rates);
            }

        } // endif (empty($_fedex_rates))
    } else {
        x_log_add('fedex_rates', 'The cart cannot be packed. Use define(\'PACKING_DEBUG\', 1); and check HMTL source code for  "Packing debug information"');
    }// endif if (!empty($packages) && is_array($packages)) {

    if (!empty($_fedex_rates)) {
        $methodids = array();
        foreach ($_fedex_rates as $fedex_rate) {
            if (!in_array($fedex_rate['methodid'], $methodids)) {
                $methodids[] = $fedex_rate['methodid'];
                $intershipper_rates[] = $fedex_rate;
            }
        }
    }

    return true;
}

/**
 * This function prepares the SOAP query
 */
function func_fedex_prepare_soap_query($packages, $fedex_options, $userinfo)
{
    $soap_query = array();

    $soap_query['WebAuthenticationDetail'] = array(
        'UserCredential' => array(
            'Key' => $fedex_options['key'],
            'Password' => $fedex_options['password']
        )
    );

    $soap_query['ClientDetail'] = array(
        'AccountNumber' => $fedex_options['account_number'],
        'MeterNumber' => $fedex_options['meter_number']
    );

    $soap_query['TransactionDetail'] = array('CustomerTransactionId' => ' *** Rate Request v14 using X-Cart 4.x ***');

    $soap_query['Version'] = array(
        'ServiceId' => 'crs',
        'Major' => '14',
        'Intermediate' => '0',
        'Minor' => '0'
    );

    $soap_query['ReturnTransitAndCommit'] = true;

    $soap_query['CarrierCodes'] = $fedex_options['carrier_codes'];

    $soap_query['RequestedShipment'] = array(
        'ShipTimestamp' => $fedex_options['ship_date_ready'],
        'DropoffType' => $fedex_options['dropoff_type'],
        'PackagingType' => $fedex_options['packaging'],
        'PreferredCurrency' => $fedex_options['currency_code'],

        'Shipper' => array(
            'Address' => array(
                'StateOrProvinceCode' => $fedex_options['original_state_code'],
                'PostalCode' => $fedex_options['original_postal_code'],
                'CountryCode' => $fedex_options['original_country_code'],
            )
        ),

        'Recipient' => array(
            'Address' => array(
                'StateOrProvinceCode' => $fedex_options['destination_state_code'],
                'PostalCode' => $fedex_options['destination_postal_code'],
                'CountryCode' => $fedex_options['destination_country_code'],

                'Residential' => $fedex_options['residential_delivery'] == 'Y',
            )
        ),

        'ShippingChargesPayment' => array(
            'PaymentType' => 'SENDER',

            'Payor' => array(
                'ResponsibleParty' => array(
                    'AccountNumber' => $fedex_options['account_number'],
                    'Address' => array(
                        'CountryCode' => $fedex_options['original_country_code'],
                    )
                )
            )
        ),

        'PackageDetail' => 'INDIVIDUAL_PACKAGES',

        'RateRequestTypes' => $fedex_options['rate_request_types'],
        'PackageCount' => count($packages),

        'SpecialServicesRequested' => func_fedex_prepare_special_services_shipment_soap($fedex_options, $userinfo)
    );

    if (!empty($fedex_options['purpose_type'])) {
        $soap_query['RequestedShipment']['CustomsClearanceDetail'] = func_fedex_prepare_customs_clearance_detail_soap($packages, $fedex_options);
    }

    $soap_query['RequestedShipment'] = func_array_merge(
        $soap_query['RequestedShipment'],
        func_fedex_prepare_items_soap($packages, $fedex_options, $userinfo)
    );

    if (!empty($fedex_options['add_smartpost_detail'])
        && $fedex_options['add_smartpost_detail'] == 'Y'
    ) {
        $soap_query['RequestedShipment']['SmartPostDetail'] = array(
            'Indicia' => $fedex_options['smartpost_indicia'],
            'HubId' => $fedex_options['smartpost_hubid'],
        );
        if (!empty($fedex_options['smartpost_ancillaryendorsement'])) {
            $soap_query['RequestedShipment']['SmartPostDetail']['AncillaryEndorsement'] = $fedex_options['smartpost_ancillaryendorsement'];
        }
    }

    return $soap_query;
}

/**
 * Return package limits for FedEx
 */
function func_get_package_limits_FEDEX($fedex_options)
{

    // Default limits (in pounds and inches)

    $limits = array(
            'YOUR_PACKAGING'     => array('weight' => 150, 'girth' => 165),
            'FEDEX_ENVELOPE'     => array('weight' => 1.1, 'price' => 100),
            'FEDEX_PAK'          => array('weight' => 20),
            'FEDEX_BOX'          => array('weight' => 20),
            'FEDEX_TUBE'         => array('weight' => 20),
            'FEDEX_10KG_BOX'     => array('weight' => 22),
            'FEDEX_25KG_BOX'     => array('weight' => 55)
    );

    // Convert default limits to store's units of weight and measure

    foreach ($limits as $k1 => $v1) {
        $limits[$k1] = func_correct_dimensions($v1);
    }

    // User-defined limens (in store's units of weight and measure)

    $max_weight = floatval($fedex_options['max_weight']);
    $max_length = floatval($fedex_options['dim_length']);
    $max_width = floatval($fedex_options['dim_width']);
    $max_height = floatval($fedex_options['dim_height']);

    // Merge user-defined limits and default limits

    foreach($limits as $k1 => $v1) {
        $dims_specified = true;

        foreach (array('weight', 'length', 'width', 'height') as $key) {
            $max_key = "max_$key";
            $user_limit = $$max_key;
            settype($v1[$key], 'float');
            $default_limit = floatval($v1[$key]);
            if ($user_limit > 0) {
                $limits[$k1][$key] = ($default_limit > 0) ? min($user_limit, $default_limit) : $user_limit;
            }

            if ($key != 'weight') {
                $dims_specified &= ($user_limit > 0 && $user_limit == $limits[$k1][$key]);
            }
        }

        if ($dims_specified) {
            unset($limits[$k1]['girth']);
        }
    }

    return $limits;
}

/**
 * Check if FedEx allows box
 */
function func_check_limits_FEDEX($box)
{
    global $sql_tbl;

    $params = unserialize(func_query_first_cell("SELECT param00 FROM $sql_tbl[shipping_options] WHERE carrier='FDX'"));
    $package_limits = func_get_package_limits_FEDEX($params);
    $avail = false;
    $box['weight'] = (isset($box['weight'])) ? $box['weight'] : 0;

    foreach ($package_limits as $pack_limit) {
        $avail = $avail || (func_check_box_dimensions($box, $pack_limit) && $pack_limit['weight'] > $box['weight']);
    }

    return $avail;
}

/**
 * Prepare dimensions soap query
 */
function func_fedex_prepare_dimensions_soap($pack, $fedex_options)
{
    $dimensions_soap = array();

    if ($fedex_options['packaging'] == 'YOUR_PACKAGING') {
        $dims = array($pack['length'], $pack['width'], $pack['height']);

        foreach ($dims as $k => $v) {
            $dims[$k] = ceil(func_units_convert(func_dim_in_centimeters($v), 'cm', $fedex_options['dim_units'], 2));
        }

        list($dim_length, $dim_width, $dim_height) = $dims;

        $dimensions_soap = array(
            'Length' => $dim_length,
            'Width' => $dim_width,
            'Height' => $dim_height,
            'Units' => $fedex_options['dim_units'],
        );
    }

    return $dimensions_soap;
}

/**
 * Prepare insurence soap query
 */
function func_fedex_prepare_insured_value_soap($pack, $fedex_options)
{
    $insured_value_soap = array();

    if (
        !empty($pack['price'])
        && floatval($pack['price']) > 0
        && !empty($fedex_options['send_insured_value'])
        && $fedex_options['send_insured_value'] == 'Y'
    ) {
        $insured_value_soap = array(
            'Currency' => $fedex_options['currency_code'],
            'Amount' => $pack['price'],
        );
    }

    return $insured_value_soap;
}

/**
 * Prepare special services shipment soap query
 */
function func_fedex_prepare_special_services_shipment_soap($fedex_options, $userinfo)
{
    $special_services_soap = array();

    // for shipment

    if ($fedex_options['inside_pickup'] == 'Y') {
        $special_services_soap['SpecialServiceTypes'][] = 'INSIDE_PICKUP';
    }

    if ($fedex_options['inside_delivery'] == 'Y') {
        $special_services_soap['SpecialServiceTypes'][] = 'INSIDE_DELIVERY';
    }

    if ($fedex_options['saturday_pickup'] == 'Y') {
        $special_services_soap['SpecialServiceTypes'][] = 'SATURDAY_PICKUP';
    }

    if ($fedex_options['saturday_delivery'] == 'Y') {
        $special_services_soap['SpecialServiceTypes'][] = 'SATURDAY_DELIVERY';
    }

    if (floatval($fedex_options['cod_value']) > 0) {
        $special_services_soap['SpecialServiceTypes'][] = 'COD';
        $special_services_soap['CodDetail'] = array(
            'CodCollectionAmount' => array(
                'Currency' => $fedex_options['currency_code'],
                'Amount' => $fedex_options['cod_value']
            ),
            'CollectionType' => $fedex_options['cod_type']
        );
    }

    if ($fedex_options['hold_at_location'] == 'Y') {
        $special_services_soap['SpecialServiceTypes'][] = 'HOLD_AT_LOCATION';
        $special_services_soap['HoldAtLocationDetail'] = array(
            'PhoneNumber' => $userinfo['phone'],
        );
    }

    return $special_services_soap;
}

/**
 * Prepare special services shipment package soap query
 */
function func_fedex_prepare_special_services_package_soap($pack, $fedex_options, $userinfo)
{
    $special_services_soap = array();

    if (!empty($fedex_options['dg_accessibility'])) {
        $special_services_soap['SpecialServiceTypes'][] = 'DANGEROUS_GOODS';
        $special_services_soap['DangerousGoodsDetail'] = array(
            'Accessibility' => $fedex_options['dg_accessibility']
        );
    }

    if ($fedex_options['dry_ice'] == 'Y') {
        $special_services_soap['SpecialServiceTypes'][] = 'DRY_ICE';
        $special_services_soap['DryIceWeight'] = array(
            'Units' => 'KG',
            'Value' => func_units_convert($pack['weight'], 'lbs', 'kg')
        );
    }

    if ($fedex_options['nonstandard_container'] == 'Y') {
        $special_services_soap['SpecialServiceTypes'][] = 'NON_STANDARD_CONTAINER';
    }

    if (!empty($fedex_options['signature'])) {
        $special_services_soap['SpecialServiceTypes'][] = 'SIGNATURE_OPTION';
        $special_services_soap['SignatureOptionDetail'] = array(
            'OptionType' => $fedex_options['signature']
        );
    }

    return $special_services_soap;
}

function func_fedex_prepare_customs_clearance_detail_soap($packages, $fedex_options)
{
    $customs_clearance_detail_soap = array();

    $customs_clearance_detail_soap['CustomsValue'] = array(
        'Currency' => $fedex_options['currency_code'],
        'Amount' => 0,
    );

    $customs_clearance_detail_soap['CommercialInvoice'] = array(
        'Purpose' => $fedex_options['purpose_type'],
    );

    foreach ($packages as $pack) {
        $customs_clearance_detail_soap['CustomsValue']['Amount'] += $pack['price'];
    }

    return $customs_clearance_detail_soap;
}

/**
 * Prepare items soap query
 */
function func_fedex_prepare_items_soap($packages, $fedex_options, $userinfo)
{
    $items_soap = array();

    $is_smartpost_request = in_array('FXSP', $fedex_options['carrier_codes']);

    $specified_dims = array();

    foreach (array('length' => 'dim_length', 'width' => 'dim_width', 'height' => 'dim_height') as $k => $o) {
        $dim = floatval($fedex_options[$o]);
        if ($dim > 0) {
            $specified_dims[$k] = $dim;
        }
    }

    $i = 1;

    foreach ($packages as $pack) {

        if ($fedex_options['param02'] == 'Y') {
            $pack = func_array_merge($pack, $specified_dims);
        }

        $pack['weight'] = func_units_convert(func_weight_in_grams($pack['weight']), 'g', 'lbs', 2);

        $dimensions_soap = func_fedex_prepare_dimensions_soap($pack, $fedex_options);
        $special_services = func_fedex_prepare_special_services_package_soap($pack, $fedex_options, $userinfo);

        $items_soap = array(
            'RequestedPackageLineItems' => array(
                'SequenceNumber' => $i,
                'GroupPackageCount' => 1,
                'Weight' => array(
                    'Units' => 'LB',
                    'Value' => $pack['weight'],
                ),
                'Dimensions' => $dimensions_soap,
                'SpecialServicesRequested' => $special_services
            )
        );

        if (!$is_smartpost_request) {
            $items_soap['RequestedPackageLineItems']['InsuredValue'] = func_fedex_prepare_insured_value_soap($pack, $fedex_options);
        }

        $i++;
    }

    return $items_soap;
}

/**
 * Check if FEDEX is disabled
 */
function func_fedex_is_disabled()
{
    global $config;
    global $allowed_shipping_methods;

    if (empty($config['Shipping']['FEDEX_account_number'])) {
        return true;
    }

    $FEDEX_FOUND = false;
    if (is_array($allowed_shipping_methods)) {
        foreach ($allowed_shipping_methods as $key=>$value) {
            if ($value['code'] == 'FDX') {
                $FEDEX_FOUND = true;
                break;
            }
        }
    }

    if (!$FEDEX_FOUND) {
        return true;
    }

    return false;
}

/**
 * Return fedex options
 */
function func_fedex_get_options($userinfo, $debug, $cart, $orig_address)
{
    global $active_modules, $sql_tbl;
    global $products, $config;

    // Default FedEx shipping options (if it wasn't defined yet by admin)
    $fedex_options = array (
        'carrier_codes'         => array(),
        'dropoff_type'          => 'REGULAR_PICKUP',
        'packaging'             => 'FEDEX_ENVELOPE',
        'list_rate'             => 'false',
        'ship_date'             => 0,
        'package_count'         => 1,
        'currency_code'         => 'USD',
        'param01'               => 'Y',
        'param02'               => 'Y',
        'original_state_code'   => '',
        'destination_state_code'=> '',
    );

    // Get stored FedEx options.
    $params = func_query_first("SELECT param00 FROM $sql_tbl[shipping_options] WHERE carrier='FDX'");

    $fedex_options_saved = @unserialize($params['param00']);
    if (is_array($fedex_options_saved)) {
        $fedex_options = func_array_merge($fedex_options, $fedex_options_saved);

        if (!empty($fedex_options['carrier_codes'])) {
            $fedex_options['carrier_codes'] = explode('|', $fedex_options['carrier_codes']);
        } else {
            $fedex_options['carrier_codes'] = array();
        }
    }

    // Get the declared value of package
    if ($debug == 'Y') {
        $decl_value = '1.00';
    }
    else {
        $is_admin = defined('AREA_TYPE') && (AREA_TYPE == 'A' || AREA_TYPE == 'P' && !empty($active_modules['Simple_Mode']));

        if ($is_admin && !empty($active_modules['Advanced_Order_Management']) && x_session_is_registered('cart_tmp')) {
            global $cart_tmp;

            if (!isset($cart_tmp) && is_array($cart_tmp))
                $cart = $cart_tmp;
        }

        $cart2 = func_calculate($cart, $products, @$userinfo['id'], @$userinfo['usertype']);
        $decl_value = $cart2['subtotal'];
    }

    $fedex_options['declared_value'] = $decl_value;

    $fedex_options['dim_units'] = 'IN';

    $_time = XC_TIME + $config['Appearance']['timezone_offset'] + intval($fedex_options['ship_date'])*24*3600;

    // Change timestamp in soap_query to update cache every 30th minutes
    $minutes = intval(date('i', $_time));
    $minutes = sprintf("%02d", floor($minutes / 30) * 30);

    $fedex_options['ship_date_ready'] = date("Y-m-d", $_time).'T'.date('H', $_time).":$minutes:00";

    $fedex_options['account_number'] = $config['Shipping']['FEDEX_account_number'];
    $fedex_options['meter_number'] = $config['Shipping']['FEDEX_meter_number'];
    $fedex_options['key'] = $config['Shipping']['FEDEX_key'];
    $fedex_options['password'] = $config['Shipping']['FEDEX_password'];

    $fedex_options['original_country_code'] = $orig_address['country'];
    if (
        in_array($fedex_options['original_country_code'], array('US', 'CA'))
        && $orig_address['state'] != 'Other'
    ) {
        $fedex_options['original_state_code'] = $orig_address['state'];
    }

    $fedex_options['original_postal_code'] = preg_replace('/[^A-Za-z0-9]/', '', $orig_address['zipcode']);

    $fedex_options['destination_country_code'] = $userinfo['s_country'];
    $fedex_options['destination_postal_code'] = preg_replace('/[^A-Za-z0-9]/', '', $userinfo['s_zipcode']);

    if (
        in_array($fedex_options['destination_country_code'], array('US', 'CA'))
        && $userinfo['s_state'] != 'Other'
    ) {
        $fedex_options['destination_state_code'] = $userinfo['s_state'];
    }

    return $fedex_options;
}

/**
 * Analyze notifications from fedex
 */
function func_fedex_reply_messages($response)
{
    $valid_codes = array('NOTE','SUCCESS');
    $error_codes = array('FAILURE','ERROR');

    $reply_msg = array(
        'error' => array(),
        'disable_cache' => false
    );

    $error = array();

    if (!in_array($response->HighestSeverity, $valid_codes)) {

        // Check error codes
        foreach ($response->Notifications as $key => $value) {
            if (property_exists($value, 'Severity')) {
                $severity = $value->Severity;
                if (in_array($severity, $error_codes)) {
                    settype($error['code'], 'string');
                    settype($error['msg'], 'string');
                    $error['code'] .= $value->Code . ',';
                    $error['msg'] .= $value->Message . ',';
                }
            }
        }

        if (!empty($error)) {
            $error['code'] = rtrim($error['code'], ',');
            $error['msg'] = rtrim($error['msg'], ',');
            if ($response->HighestSeverity == 'ERROR')
                $error['msg_to_customer'] = $error['msg'];
        }

        // Check temporarily unavailable services to disable cache
        foreach ($response->Notifications as $key => $value) {
            if (property_exists($value, 'Severity')) {
                $severity = $value->Severity;
                if (!in_array($severity, $valid_codes)) {
                    $msg = strtolower($value->Message);
                    if (
                        $severity == 'FAILURE'
                        || strpos($msg, 'try again later') !== false
                        || strpos($msg, 'temporarily unavailable') !== false
                    ) {
                        $reply_msg['disable_cache'] = true;
                        break;
                    }
                }
            }
        }
    }

    if (empty($error)) {
        // Check for errors
        if (!empty($response->Notifications->Severity)
            && !empty($response->Notifications->Code)
            && !empty($response->Notifications->Message)
            && in_array($response->Notifications->Severity, $error_codes)
        ) {
            $error['code'] = $response->Notifications->Code;
            $error['msg'] = $response->Notifications->Message;
        }
    }

    if (!empty($error['msg'])) {
        x_log_flag('log_shipping_errors', 'SHIPPING', "FedEx module error: [{$error['code']}] {$error['msg']}", true);
    }

    $reply_msg['error'] = $error;

    return $reply_msg;
}

/**
 * Return transit/delivery day
 */
function func_fedex_get_estimated_time($entry)
{
    global $config;

    $transit_time_types = array(
        'ONE_DAY' => '1 day',
        'TWO_DAYS' => '2 days',
        'THREE_DAYS' => '3 days',
        'FOUR_DAYS' => '4 days',
        'FIVE_DAYS' => '5 days',
        'SIX_DAYS' => '6 days',
        'SEVEN_DAYS' => '7 days',
        'EIGHT_DAYS' => '8 days',
        'NINE_DAYS' => '9 days',
        'TEN_DAYS' => '10 days',
        'ELEVEN_DAYS' => '11 days',
        'TWELVE_DAYS' => '12 days',
        'THIRTEEN_DAYS' => '13 days',
        'FOURTEEN_DAYS' => '14 days',
        'FIFTEEN_DAYS' => '15 days',
        'SIXTEEN_DAYS' => '16 days',
        'SEVENTEEN_DAYS' => '17 days',
        'EIGHTEEN_DAYS' => '18 days',
        'NINETEEN_DAYS' => '19 days',
        'TWENTY_DAYS' => '20 days',
        'UNKNOWN' => '',
    );

    $transit_time = !property_exists($entry, 'TransitTime') ? false : $entry->TransitTime;
    $maximum_transit_time = !property_exists($entry, 'MaximumTransitTime') ? false : $entry->MaximumTransitTime;

    $estimated_time = !empty($transit_time) ? $transit_time : $maximum_transit_time;

    $delivery_timestamp = !property_exists($entry, 'DeliveryTimestamp') ? false : $entry->DeliveryTimestamp;

    if (!empty($estimated_time)) {
        $estimated_time = $transit_time_types[$estimated_time];
    }

    if (empty($estimated_time) && !empty($delivery_timestamp)) {
        $estimated_time = strftime('%a %b %e', func_strtotime($delivery_timestamp));
    }

    return $estimated_time;
}

function func_fedex_get_estimated_rate($entry, $currency_code, $rate_request_type)
{
    $rated_shipment_details = is_array($entry->RatedShipmentDetails)
        ? $entry->RatedShipmentDetails : array($entry->RatedShipmentDetails);

    if (defined('XC_FEDEX_SOAP_USD_RATES')) {
        $currency_code = 'USD';
    }

    $precise_rate_found = false;
    $estimated_rate = 0;

    foreach ($rated_shipment_details as $key => $shipment_rate_detail) {

        if (strpos($shipment_rate_detail->ShipmentRateDetail->RateType, $rate_request_type) === false) {
            // skip estimation for rates with another type
            continue;
        }

        $CurrencyExchangeRate = '1.0';
        $FromCurrency = $currency_code;

        if (property_exists($shipment_rate_detail->ShipmentRateDetail, 'CurrencyExchangeRate')) {
            $CurrencyExchangeRate = $shipment_rate_detail->ShipmentRateDetail->CurrencyExchangeRate->Rate;
            $FromCurrency = $shipment_rate_detail->ShipmentRateDetail->CurrencyExchangeRate->FromCurrency;
        }

        $rate_currency = $shipment_rate_detail->ShipmentRateDetail->TotalNetCharge->Currency;
        $estimated_rate = $shipment_rate_detail->ShipmentRateDetail->TotalNetCharge->Amount;

        if (
            $CurrencyExchangeRate == '1.0'
            && $FromCurrency == $currency_code
            && $rate_currency == $currency_code
        ) {
            // This rate type can be used without conversion
            $precise_rate_found = true;
            break;
        }

    }

    if (!$precise_rate_found) {

        // Rate type without conversion is not found / Use conversion
        foreach ($rated_shipment_details as $key => $shipment_rate_detail) {

            if (strpos($shipment_rate_detail->ShipmentRateDetail->RateType, $rate_request_type) === false) {
                // skip estimation for rates with another type
                continue;
            }

            if (property_exists($shipment_rate_detail->ShipmentRateDetail, 'CurrencyExchangeRate')) {

                $CurrencyExchangeRate = $shipment_rate_detail->ShipmentRateDetail->CurrencyExchangeRate->Rate;

                if ($CurrencyExchangeRate == 0) {
                    countinue;
                }

                $FromCurrency = $shipment_rate_detail->ShipmentRateDetail->CurrencyExchangeRate->FromCurrency;
                $IntoCurrency = $shipment_rate_detail->ShipmentRateDetail->CurrencyExchangeRate->IntoCurrency;

                $rate_currency = $shipment_rate_detail->ShipmentRateDetail->TotalNetCharge->Currency;
                $estimated_rate = $shipment_rate_detail->ShipmentRateDetail->TotalNetCharge->Amount;

                if (defined('XC_FEDEX_SOAP_USD_RATES')) {
                    if ($rate_currency == 'USD') {
                        break;
                    }
                }

                if ($FromCurrency == $rate_currency) {
                    $estimated_rate *= $CurrencyExchangeRate;
                    break;
                } elseif ($IntoCurrency == $rate_currency) {
                    $estimated_rate /= $CurrencyExchangeRate;
                    break;
                }
            }
        }
    }

    return $estimated_rate;
}

x_load('soap');

class XC_FEDEX_Rate_Service extends XC_SOAP_Service {

    protected function defineExceptionCodePath()
    {
        return 'Notifications/Code';
    }

    protected function defineExceptionDescriptionPath()
    {
        return 'Notifications/Message';
    }

    protected function defineProductionServer()
    {
        return 'https://ws.fedex.com:443/web-services/rate';
    }

    protected function defineResponseCodePath()
    {
        return 'Notifications/Code';
    }

    protected function defineResponseDescriptionPath()
    {
        return 'Notifications/Message';
    }

    protected function defineTestServer()
    {
        return 'https://wsbeta.fedex.com:443/web-services/rate';
    }

    protected function defineValidResponseCodes()
    {
        return array('0');
    }

    protected function defineWsdlFile()
    {
        global $xcart_dir;

        return $xcart_dir . '/shipping/mod_FEDEX_RateService_v14.wsdl';
    }

    protected function preProcessRequest($request_data)
    {
        $display_request1 = preg_replace('/(<ns\d+:Key>)(.*)(<\/ns\d+:Key>)/', '$1xxxxx$3', $request_data);
        $display_request2 = preg_replace('/(<ns\d+:Password>)(.*)(<\/ns\d+:Password>)/', '$1xxxxx$3', $display_request1);
        $display_request3 = preg_replace('/(<ns\d+:AccountNumber>)(.*)(<\/ns\d+:AccountNumber>)/', '$1xxxxx$3', $display_request2);
        $display_request = preg_replace('/(<ns\d+:MeterNumber>)(.*)(<\/ns\d+:MeterNumber>)/', '$1xxxxx$3', $display_request3);

        return $display_request;
    }

    protected function preProcessResponse($response_data)
    {
        $display_request1 = preg_replace('/(<ns\d+:Key>)(.*)(<\/ns\d+:Key>)/', '$1xxxxx$3', $response_data);
        $display_request2 = preg_replace('/(<ns\d+:Password>)(.*)(<\/ns\d+:Password>)/', '$1xxxxx$3', $display_request1);
        $display_request3 = preg_replace('/(<ns\d+:AccountNumber>)(.*)(<\/ns\d+:AccountNumber>)/', '$1xxxxx$3', $display_request2);
        $display_request = preg_replace('/(<ns\d+:MeterNumber>)(.*)(<\/ns\d+:MeterNumber>)/', '$1xxxxx$3', $display_request3);

        return $display_request;
    }

    public static function getInstance()
    {
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    }

    public function getRates($soap_request)
    {
        return $this->processRequest('getRates', $soap_request);
    }
}

?>
