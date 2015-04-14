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
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v4 (xcart_4_7_0), 2015-02-17 23:56:28, mod_FEDEX_RateService_v9.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header('Location: ../'); die('Access denied'); }

x_load('http','xml');

function func_shipper_FEDEX($items, $userinfo, $orig_address, $debug, $cart)
{
    global $config;
    global $allowed_shipping_methods, $intershipper_rates;
    global $intershipper_error, $shipping_calc_service;

    if (func_fedex_is_disabled())
        return;

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

    // FedEx host
    $fedex_host = ($config['Shipping']['FEDEX_test_server'] == 'Y' ? 'wsbeta.fedex.com:443/xml' : 'ws.fedex.com:443/xml');

    if ($debug == 'Y')
        print "<h1>FedEx Debug Information</h1>";

    $_fedex_rates = array();

    $package_limits = func_get_package_limits_FEDEX($fedex_options);
    $pack_limits = $package_limits[$fedex_options['packaging']];

    if (!isset($pack_limits['price']))
        $pack_limits['price'] = 50000;

    $packages = func_get_packages($items, $pack_limits, ($fedex_options['param01'] == 'Y') ? 200 : 1);

    if (!empty($packages) && is_array($packages)) {

        $xml_query = func_fedex_prepare_xml_query($packages, $fedex_options, $userinfo);

        $md5_request = md5($xml_query);

        if ($debug != 'Y' && func_is_shipping_result_in_cache($md5_request)) {

            // Get shipping rates from the cache
            $_fedex_rates = func_get_shipping_result_from_cache($md5_request);
        }

        if (empty($_fedex_rates)) {

        // Get shipping rates from FedEx server

            $data = preg_split("/(\r\n|\r|\n)/",$xml_query, -1, PREG_SPLIT_NO_EMPTY);
            $host = "https://".$fedex_host;
            list($header, $result) = func_https_request('POST', $host, $data,'','','text/xml');

            if (defined('XC_FEDEX_DEBUG'))
                x_log_add('fedex_rates', $xml_query . "\n\n" . $header . "\n\n" . $result);

            // Parse XML reply
            $parse_error = false;
            $options = array(
                'XML_OPTION_CASE_FOLDING' => 1,
                'XML_OPTION_TARGET_ENCODING' => 'ISO-8859-1'
            );

            $parsed = func_xml_parse($result, $parse_error, $options);


            if (empty($parsed)) {
            // Error of parsing XML reply from FedEx
                x_log_flag('log_shipping_errors', 'SHIPPING', "FedEx module (rates): Received data could not be parsed correctly.", true);
                return false;
            }

            $reply_msg = func_fedex_reply_messages($parsed);

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
                if ($reply_msg['disable_cache'])
                    $md5_request = 'disabled_cache_result';

            } else {
                // FedEx returned a valid reply, get the rates

                // Disable cache key
                if ($reply_msg['disable_cache'])
                    $md5_request = 'disabled_cache_result';

                $prefix = $reply_msg['prefix'];
                $entries = func_array_path($parsed, "{$prefix}RATEREPLY/{$prefix}RATEREPLYDETAILS");

                if (is_array($entries)) {
                    foreach ($entries as $k=>$entry) {
                        $service_type = func_array_path($entry, "{$prefix}SERVICETYPE/0/#");
                        $estimated_rate = func_fedex_get_estimated_rate($entry, $fedex_options['currency_code'], $prefix);
                        $estimated_time = func_fedex_get_estimated_time($entry, $prefix);

                        $variable_handling_charge = func_array_path($entry, "{$prefix}RATEDSHIPMENTDETAILS/{$prefix}SHIPMENTRATEDETAIL/{$prefix}TOTALVARIABLEHANDLINGCHARGES/{$prefix}VARIABLEHANDLINGCHARGE/{$prefix}AMOUNT/0/#");
                        if (floatval($variable_handling_charge) > 0)
                            $estimated_rate += $variable_handling_charge;

                        foreach ($allowed_shipping_methods as $key=>$value) {
                            if ($value['code'] == 'FDX' && $value['subcode'] == $fedex_services[$service_type])
                                $_fedex_rates[] = array('methodid' => $value['subcode'], 'rate' => $estimated_rate, 'shipping_time' => $estimated_time);
                        }

                    }
                    assert('count($entries) == count($_fedex_rates) /*Some methods are skipped, check $fedex_services var*/');
                }
            }

            if ($debug == 'Y') {
            // Display a debug information (on testing real-time shipping page)

                if ($xml_query) {
                    $display_query = preg_replace('|<AccountNumber>.+</AccountNumber>|i','<AccountNumber>xxx</AccountNumber>',$xml_query);
                    $display_query = preg_replace('|<MeterNumber>.+</MeterNumber>|i','<MeterNumber>xxx</MeterNumber>',$display_query);

                    $display_result = preg_replace('|><|', ">\n<", $result);

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
 * This function prepares the XML query
 */
function func_fedex_prepare_xml_query($packages, $fedex_options, $userinfo)
{
    global $config;


    // Carrier codes

    $carriers_xml = '';
    foreach ($fedex_options['carrier_codes'] as $carrier) {
        $carriers_xml .= "<ns:CarrierCodes>{$carrier}</ns:CarrierCodes>\n\t";
    }

    // Special services

    $special_services_types = $special_services = array(
        'package'     => array(),
        'shipment'     => array()
    );

    if (floatval($fedex_options['cod_value']) > 0) {
        $special_services['shipment'][] = <<<OUT

            <ns:CodDetail>
                <ns:CodCollectionAmount>
                    <ns:Currency>{$fedex_options['currency_code']}</ns:Currency>
                    <ns:Amount>{$fedex_options['cod_value']}</ns:Amount>
                </ns:CodCollectionAmount>
                <ns:CollectionType>{$fedex_options['cod_type']}</ns:CollectionType>
            </ns:CodDetail>
OUT;
        $special_services_types['shipment'][] = 'COD';
    }

    if ($fedex_options['hold_at_location'] == 'Y') {
        $special_services_types['shipment'][] = 'HOLD_AT_LOCATION';
        $special_services['shipment'][] = "<ns:HoldAtLocationDetail><ns:PhoneNumber>$userinfo[phone]</ns:PhoneNumber></ns:HoldAtLocationDetail>";
    }

    if (!empty($fedex_options['dg_accessibility'])) {
        $special_services['package'][] = <<<OUT
        <ns:DangerousGoodsDetail>
            <ns:Accessibility>{$fedex_options['dg_accessibility']}</ns:Accessibility>
        </ns:DangerousGoodsDetail>
OUT;
        $special_services_types['package'][] = 'DANGEROUS_GOODS';
    }

    if ($fedex_options['dry_ice'] == 'Y') {
        $special_services['package'][] = <<<OUT
        <ns:DryIceWeight>
            <ns:Units>LB</ns:Units>
            <ns:Value>{{fedex_weight}}</ns:Value>
        </ns:DryIceWeight>
OUT;
        $special_services_types['package'][] = 'DRY_ICE';
    }

    if ($fedex_options['inside_pickup'] == 'Y')
        $special_services_types['shipment'][] = 'INSIDE_PICKUP';

    if ($fedex_options['inside_delivery'] == 'Y')
        $special_services_types['shipment'][] = 'INSIDE_DELIVERY';

    if ($fedex_options['saturday_pickup'] == 'Y')
        $special_services_types['shipment'][] = 'SATURDAY_PICKUP';

    if ($fedex_options['saturday_delivery'] == 'Y')
        $special_services_types['shipment'][] = 'SATURDAY_DELIVERY';

    if ($fedex_options['nonstandard_container'] == 'Y')
        $special_services_types['package'][] = 'NON_STANDARD_CONTAINER';

    if (!empty($fedex_options['signature']))
        $special_services['package'][] = <<<OUT
        <ns:SignatureOptionDetail>
            <ns:OptionType>{$fedex_options['signature']}</ns:OptionType>
        </ns:SignatureOptionDetail>
OUT;

    foreach ($special_services_types as $k => $ss_types) {
        if (!empty($ss_types)) {
            foreach ($ss_types as $key => $ss_type) {
                $special_services_types[$k][$key] = "<ns:SpecialServiceTypes>".$ss_type."</ns:SpecialServiceTypes>";
            }
        }
        $special_services[$k] = func_array_merge($special_services_types[$k], $special_services[$k]);
    }

    foreach ($special_services as $k => $ss) {
        if (!empty($ss)) {
            $special_services_xml[$k] = '';
            foreach ($ss as $_service) {
                $special_services_xml[$k] .= "\t\t".$_service."\n";
            }

            $special_services_xml[$k] = "<ns:SpecialServicesRequested>\n\t".$special_services_xml[$k]."\t\t</ns:SpecialServicesRequested>";
        }
        else
            $special_services_xml[$k] = '';
    }

    $specified_dims = array();
    foreach (array('length' => 'dim_length', 'width' => 'dim_width', 'height' => 'dim_height') as $k => $o) {
        $dim = floatval($fedex_options[$o]);
        if ($dim > 0) {
            $specified_dims[$k] = $dim;
        }
    }

    // Packages query
    $package_count = count($packages);

    $i = 1;
    $items_xml = '';
    $is_smartpost_request = in_array('FXSP', $fedex_options['carrier_codes']);

    foreach ($packages as $pack) {

        if ($fedex_options['param02'] == 'Y')
            $pack = func_array_merge($pack, $specified_dims);

        $dimensions_xml = func_fedex_prepare_dimensions_xml($pack, $fedex_options);

        // Declared value
        $declared_value_xml = '';

        if (
            !empty($pack['price']) 
            && floatval($pack['price']) > 0
            && @$fedex_options['send_insured_value'] == 'Y'
            && !$is_smartpost_request
        ) {
            $declared_value_xml = <<<OUT

            <ns:InsuredValue>
                <ns:Currency>{$fedex_options['currency_code']}</ns:Currency>
                <ns:Amount>{$pack['price']}</ns:Amount>
            </ns:InsuredValue>
OUT;
        }

        $pack['weight'] = func_units_convert(func_weight_in_grams($pack['weight']), 'g', 'lbs', 1);
        $special_services_xml['package'] = str_replace('{{fedex_weight}}', $pack['weight'], $special_services_xml['package']);

        $items_xml .= <<<EOT

        <ns:RequestedPackageLineItems>
            <ns:SequenceNumber>{$i}</ns:SequenceNumber>
            {$declared_value_xml}
            <ns:Weight>
                <ns:Units>LB</ns:Units>
                <ns:Value>{$pack['weight']}</ns:Value>
            </ns:Weight>
            {$dimensions_xml}
            {$special_services_xml['package']}
        </ns:RequestedPackageLineItems>
EOT;
        $i++;
    } // foreach ($packages as $pack) 

    $residential = ($fedex_options['residential_delivery'] == 'Y') ? "<ns:Residential>true</ns:Residential>" : '';

    // Handling charges

    if (!empty($fedex_options['handling_charges_amount']) && floatval($fedex_options['handling_charges_amount']) > 0) {
        $_handling_type = ($fedex_options['handling_charges_type'] == 'FIXED_AMOUNT') ? "<ns:FixedValue><ns:Currency>$fedex_options[currency_code]</ns:Currency><ns:Amount>$fedex_options[handling_charges_amount]</ns:Amount></ns:FixedValue>" : "<ns:PercentValue>$fedex_options[handling_charges_amount]</ns:PercentValue>";

        $handling_charges_xml = <<<OUT

        <ns:VariableHandlingChargeDetail>
            <ns:VariableHandlingChargeType>{$fedex_options['handling_charges_type']}</ns:VariableHandlingChargeType>
            $_handling_type
        </ns:VariableHandlingChargeDetail>
OUT;
    } else {
        $handling_charges_xml = '';
    }    

    if (@$fedex_options['add_smartpost_detail'] == 'Y') {
        $smart_post_detail = <<<OUT

        <ns:SmartPostDetail>
            <ns:Indicia>{$fedex_options['smartpost_indicia']}</ns:Indicia>
            <ns:AncillaryEndorsement>{$fedex_options['smartpost_ancillaryendorsement']}</ns:AncillaryEndorsement>
            <ns:HubId>{$fedex_options['smartpost_hubid']}</ns:HubId>
        </ns:SmartPostDetail>
OUT;
    } else {
        $smart_post_detail = '';
    }

    // Prepare the XML request

    $xml_query = <<<OUT
<?xml version="1.0" encoding="UTF-8" ?>
<ns:RateRequest xmlns:ns="http://fedex.com/ws/rate/v9" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
    <ns:WebAuthenticationDetail>
        <ns:UserCredential>
            <ns:Key>{$fedex_options['key']}</ns:Key>
            <ns:Password>{$fedex_options['password']}</ns:Password>
        </ns:UserCredential>
    </ns:WebAuthenticationDetail>

    <ns:ClientDetail>
        <ns:AccountNumber>{$fedex_options['account_number']}</ns:AccountNumber>
        <ns:MeterNumber>{$fedex_options['meter_number']}</ns:MeterNumber>
    </ns:ClientDetail>

    <ns:TransactionDetail>
        <ns:CustomerTransactionId>Basic Rate</ns:CustomerTransactionId>
    </ns:TransactionDetail>

    <ns:Version>
        <ns:ServiceId>crs</ns:ServiceId>
        <ns:Major>9</ns:Major>
        <ns:Intermediate>0</ns:Intermediate>
        <ns:Minor>0</ns:Minor>
    </ns:Version>

    <ns:ReturnTransitAndCommit>true</ns:ReturnTransitAndCommit>
    {$carriers_xml}
    <ns:RequestedShipment>
        <ns:ShipTimestamp>{$fedex_options['ship_date_ready']}</ns:ShipTimestamp>
        <ns:DropoffType>{$fedex_options['dropoff_type']}</ns:DropoffType>
        <ns:PackagingType>{$fedex_options['packaging']}</ns:PackagingType>
        <ns:PreferredCurrency>{$fedex_options['currency_code']}</ns:PreferredCurrency>
        <ns:Shipper>
            <ns:Address>
                <ns:StateOrProvinceCode>{$fedex_options['original_state_code']}</ns:StateOrProvinceCode>
                <ns:PostalCode>{$fedex_options['original_postal_code']}</ns:PostalCode>
                <ns:CountryCode>{$fedex_options['original_country_code']}</ns:CountryCode>
            </ns:Address>
        </ns:Shipper>

        <ns:Recipient>
            <ns:Address>
                <ns:StateOrProvinceCode>{$fedex_options['destination_state_code']}</ns:StateOrProvinceCode>
                <ns:PostalCode>{$fedex_options['destination_postal_code']}</ns:PostalCode>
                <ns:CountryCode>{$fedex_options['destination_country_code']}</ns:CountryCode>
                {$residential}
            </ns:Address>
        </ns:Recipient>

        <ns:ShippingChargesPayment>
            <ns:PaymentType>SENDER</ns:PaymentType>
            <ns:Payor>
                <ns:AccountNumber>{$fedex_options['account_number']}</ns:AccountNumber>
                <ns:CountryCode>{$fedex_options['original_country_code']}</ns:CountryCode>
            </ns:Payor>
        </ns:ShippingChargesPayment>
        {$special_services_xml['shipment']}
        {$handling_charges_xml}
        {$smart_post_detail}
        <ns:RateRequestTypes>{$fedex_options['rate_request_types']}</ns:RateRequestTypes>
        <ns:PackageCount>{$package_count}</ns:PackageCount>
        <ns:PackageDetail>INDIVIDUAL_PACKAGES</ns:PackageDetail>

        {$items_xml}

    </ns:RequestedShipment>
</ns:RateRequest>
OUT;

    return $xml_query;
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
            'FEDEX_PAK'         => array('weight' => 20),
            'FEDEX_BOX'         => array('weight' => 20),
            'FEDEX_TUBE'         => array('weight' => 20),
            'FEDEX_10KG_BOX'     => array('weight' => 22),
            'FEDEX_25KG_BOX'     => array('weight' => 55)
    );

    // Convert default limits to store's units of weight and measure

    foreach($limits as $k1 => $v1)
        $limits[$k1] = func_correct_dimensions($v1);

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

            if ($key != 'weight') 
                $dims_specified &= ($user_limit > 0 && $user_limit == $limits[$k1][$key]);
        }

        if ($dims_specified) 
            unset($limits[$k1]['girth']);
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
 * Prepare dimensions xml query
 */
function func_fedex_prepare_dimensions_xml($pack, $fedex_options)
{
    if ($fedex_options['packaging'] == 'YOUR_PACKAGING') {
        $dims = array($pack['length'], $pack['width'], $pack['height']);

        foreach($dims as $k=>$v)
            $dims[$k] = intval(func_units_convert(func_dim_in_centimeters($v), 'cm', $fedex_options['dim_units'], 1));

        list($dim_length, $dim_width, $dim_height) = $dims;

        $dimensions_xml = <<<OUT

            <ns:Dimensions>
                <ns:Length>{$dim_length}</ns:Length>
                <ns:Width>{$dim_width}</ns:Width>
                <ns:Height>{$dim_height}</ns:Height>
                <ns:Units>{$fedex_options['dim_units']}</ns:Units>
            </ns:Dimensions>
OUT;
    } else {
        $dimensions_xml = '';
    }

    return $dimensions_xml;
}

/**
 * Check if FEDEX is disabled
 */
function func_fedex_is_disabled()
{
    global $config;
    global $allowed_shipping_methods;

    if (empty($config['Shipping']['FEDEX_account_number']))
        return true;

    $FEDEX_FOUND = false;
    if (is_array($allowed_shipping_methods)) {
        foreach ($allowed_shipping_methods as $key=>$value) {
            if ($value['code'] == 'FDX') {
                $FEDEX_FOUND = true;
                break;
            }
        }
    }

    if (!$FEDEX_FOUND)
        return true;

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
        'rate_request_types'    => 'ACCOUNT',
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
    // Change timestamp in xml_query to update cache every 30th minutes
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
function func_fedex_reply_messages($parsed) {

    // Get prefix
    $root_node = array_keys($parsed);
    $root_node = array_shift($root_node);
    if (strpos($root_node, ':') !== FALSE) {
        $prefix = preg_replace('/:.*/s', '', $root_node) . ':';
    } else {
        $prefix = '';
    }

    $reply_msg = array(
        'error' => array(),
        'disable_cache' => false,
        'prefix' => $prefix,
    );
    $error = array();

    $valid_codes = array('NOTE','SUCCESS');
    $error_codes = array('FAILURE','ERROR');

    $msg = func_array_path($parsed, 'SOAPENV:FAULT/FAULTSTRING/0/#');
    $highest_severity = func_array_path($parsed, "{$prefix}RATEREPLY/{$prefix}HIGHESTSEVERITY/0/#");
        
    if (!empty($msg)) {
        $error['code'] = func_array_path($parsed, 'SOAPENV:FAULT/FAULTCODE/0/#');
        $error['msg'] = $msg;
    } elseif (!in_array($highest_severity, $valid_codes)) {

        $notifications = func_array_path($parsed, "{$prefix}RATEREPLY/{$prefix}NOTIFICATIONS");

        // Check error codes
        foreach ($notifications as $key=>$value) {
            $severity = func_array_path($value, "{$prefix}SEVERITY/0/#");
            if (in_array($severity, $error_codes)) {
                $msg = func_array_path($value, "{$prefix}MESSAGE/0/#") . ',';
                settype($error['code'], 'string');
                settype($error['msg'], 'string');
                $error['code'] .= func_array_path($value, "{$prefix}CODE/0/#") . ',';
                $error['msg'] .= $msg;
            }
        }

        if (!empty($error)) {
            $error['code'] = rtrim($error['code'], ',');
            $error['msg'] = rtrim($error['msg'], ',');
            if ($highest_severity == 'ERROR')
                $error['msg_to_customer'] = $error['msg'];
        }

        // Check temporarily unavailable services to disable cache
        foreach ($notifications as $key => $value) {
            $severity = func_array_path($value, "{$prefix}SEVERITY/0/#");
            if (!in_array($severity, $valid_codes)) {
                $msg = strtolower(func_array_path($value, "{$prefix}MESSAGE/0/#"));
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

    if (!empty($error['msg'])) {
        x_log_flag('log_shipping_errors', 'SHIPPING', "FedEx module error: [{$error['code']}] {$error['msg']}", true);
    } 

    $reply_msg['error'] = $error;

    return $reply_msg;
}

/**
 * Return transit/delivery day
 */
function func_fedex_get_estimated_time($entry, $prefix)
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

    $estimated_time = func_array_path($entry, "{$prefix}TRANSITTIME/0/#");
    $estimated_time = empty($estimated_time) ? func_array_path($entry, "{$prefix}MAXIMUMTRANSITTIME/0/#") : $estimated_time;

    if (!empty($estimated_time))
        $estimated_time = $transit_time_types[$estimated_time];

    if (empty($estimated_time)) {
        $estimated_time = func_array_path($entry, "{$prefix}DELIVERYTIMESTAMP/0/#");
        if (!empty($estimated_time))
            $estimated_time = strftime('%a %b %e', func_strtotime($estimated_time));
    }

    return $estimated_time;        
}

function func_fedex_get_estimated_rate($entry, $currency_code, $prefix)
{
    $rate_currency = func_array_path($entry, "{$prefix}RATEDSHIPMENTDETAILS/{$prefix}SHIPMENTRATEDETAIL/{$prefix}TOTALNETCHARGE/{$prefix}CURRENCY/0/#");

    if ($rate_currency != $currency_code) {
        // Currency conversion is needed 
        $rated_shipment_details = func_array_path($entry, "{$prefix}RATEDSHIPMENTDETAILS");

        // Try to find extact rate value
        $precise_rate_found = false;
        foreach ($rated_shipment_details as $key=>$shipment_rate_detail) {
            $CurrencyExchangeRate = func_array_path($shipment_rate_detail, "{$prefix}SHIPMENTRATEDETAIL/{$prefix}CURRENCYEXCHANGERATE/{$prefix}RATE/0/#");
            $FromCurrency = func_array_path($shipment_rate_detail, "{$prefix}SHIPMENTRATEDETAIL/{$prefix}CURRENCYEXCHANGERATE/{$prefix}FROMCURRENCY/0/#");
            $rate_currency = func_array_path($shipment_rate_detail, "{$prefix}SHIPMENTRATEDETAIL/{$prefix}TOTALNETCHARGE/{$prefix}CURRENCY/0/#");
            $estimated_rate = func_array_path($shipment_rate_detail, "{$prefix}SHIPMENTRATEDETAIL/{$prefix}TOTALNETCHARGE/{$prefix}AMOUNT/0/#");
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
            // Rate type without conversion is not found/ Use conversion
            foreach ($rated_shipment_details as $key=>$shipment_rate_detail) {
                $CurrencyExchangeRate = func_array_path($shipment_rate_detail, "{$prefix}SHIPMENTRATEDETAIL/{$prefix}CURRENCYEXCHANGERATE/{$prefix}RATE/0/#");
                if ($CurrencyExchangeRate == 0)
                    countinue;

                $FromCurrency = func_array_path($shipment_rate_detail, "{$prefix}SHIPMENTRATEDETAIL/{$prefix}CURRENCYEXCHANGERATE/{$prefix}FROMCURRENCY/0/#");
                $IntoCurrency = func_array_path($shipment_rate_detail, "{$prefix}SHIPMENTRATEDETAIL/{$prefix}CURRENCYEXCHANGERATE/{$prefix}INTOCURRENCY/0/#");
                $rate_currency = func_array_path($shipment_rate_detail, "{$prefix}SHIPMENTRATEDETAIL/{$prefix}TOTALNETCHARGE/{$prefix}CURRENCY/0/#");
                $estimated_rate = func_array_path($shipment_rate_detail, "{$prefix}SHIPMENTRATEDETAIL/{$prefix}TOTALNETCHARGE/{$prefix}AMOUNT/0/#");
                if ($FromCurrency == $rate_currency) {
                    $estimated_rate *= $CurrencyExchangeRate;
                    break;
                } elseif ($IntoCurrency == $rate_currency) {
                    $estimated_rate /= $CurrencyExchangeRate;
                    break;
                }
            }
        }

    } // if ($rate_currency != $currency_code) {

    if (empty($estimated_rate))
        $estimated_rate = func_array_path($entry, "{$prefix}RATEDSHIPMENTDETAILS/{$prefix}SHIPMENTRATEDETAIL/{$prefix}TOTALNETCHARGE/{$prefix}AMOUNT/0/#");

    return $estimated_rate;
}

?>
