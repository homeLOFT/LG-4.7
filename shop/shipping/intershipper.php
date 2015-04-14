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
 * Functions to grab shipping methods with calculated rates (intershipper)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v112 (xcart_4_7_0), 2015-02-17 23:56:28, intershipper.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

if (
    file_exists($xcart_dir.'/shipping/shipping_cache.php')
    && is_readable($xcart_dir.'/shipping/shipping_cache.php')
) {
    require_once $xcart_dir.'/shipping/shipping_cache.php';
}

x_load('xml','http','tests');

function func_shipper($items, $userinfo, $orig_address, $debug="N", $cart=false)
{
    global $allowed_shipping_methods, $intershipper_rates, $intershipper_error;
    global $sql_tbl;
    global $config;

    $__intershipper_userinfo = $userinfo;

    $intershipper_rates = array ();

    $intershipper_countries = array (
        'IE' => 'IR',    // IRELAND
        'VA' => 'IT',    // ITALY AND VATICAN CITY STATE
        'FX' => 'FR',    // FRANCE
        'PR' => 'US'    // PUERTO RICO
    );

    // Intershipper depends on XML parser (EXPAT extension)

    if (test_expat() == '')
        return;

    if (func_cart_is_empty_address_fields($userinfo) && ($config['General']['apply_default_country']=="Y" || $debug=="Y")) {
        $__intershipper_userinfo['s_country'] = $config['General']['default_country'];
        $__intershipper_userinfo['s_state'] = $config['General']['default_state'];
        $__intershipper_userinfo['s_zipcode'] = $config['General']['default_zipcode'];
        $__intershipper_userinfo['s_city'] = $config['General']['default_city'];
    }
    elseif (func_cart_is_empty_address_fields($userinfo)) {
        return array();
    }


    $username=$config['Shipping']['intershipper_username'];
    $password=$config['Shipping']['intershipper_password'];

    $params = func_query_first ("SELECT * FROM $sql_tbl[shipping_options] WHERE carrier='INTERSHIPPER'");

    $specified_dims = array();
    foreach(array('length'=>'param02', 'width'=>'param03', 'height'=>'param04') as $k => $p) {
        $dim = doubleval($params[$p]);
        if($dim>0) $specified_dims[$k] = $dim;
    }

    $package_limits = func_get_package_limits_INTERSHIPPER();

    $packages = func_get_packages($items, $package_limits, 100);
    if(empty($packages) || !is_array($packages)) return;

    if($params['param10']=="Y") {
        foreach($packages as $p => $package)
            $packages[$p] = func_array_merge($package, $specified_dims);
    }

    $delivery=$params['param00'];
    $shipmethod=$params['param01'];

    $options = (!empty($params['param05']))? explode('|', $params["param05"]) : array();

    $CO = $orig_address['country'];
    $ZO = $orig_address['zipcode'];

    $CD=$__intershipper_userinfo['s_country'];
    $ZD=$__intershipper_userinfo['s_zipcode'];

    if (!empty($intershipper_countries[$CD])) $CD = $intershipper_countries[$CD];
    if (!empty($intershipper_countries[$CO])) $CO = $intershipper_countries[$CO];

    $__intershipper_userinfo['s_country'] = $CD;
    $config['Company']['location_country'] = $CO;

    $packaging=$params['param06'];
    $contents=$params['param07'];

    $codvalue = (double)$params['param08'];
    $queryid = substr(uniqid(mt_rand()),0,15);

    $allowed_shipping_methods = func_query ("SELECT * FROM $sql_tbl[shipping] WHERE active='Y'");

    $carriers = func_query_column("SELECT DISTINCT(code) FROM $sql_tbl[shipping] WHERE code<>'' AND intershipper_code!='' AND active='Y'");

    if (!$carriers || !$username || !$password)
        return array();

    $post[] = "Version=2.0.0.0";
    $post[] = "ShipmentID=";
    $post[] = "QueryID=1";
    $post[] = "Username=$username";
    $post[] = "Password=$password";
    $post[] = "TotalClasses=4";
    $post[] = "ClassCode1=GND";
    $post[] = "ClassCode2=1DY";
    $post[] = "ClassCode3=2DY";
    $post[] = "ClassCode4=3DY";
    $post[] = "DeliveryType=$delivery";
    $post[] = "ShipMethod=$shipmethod";
    $post[] = "OriginationPostal=$ZO";
    $post[] = "OriginationCountry=$CO";
    $post[] = "DestinationPostal=$ZD";
    $post[] = "DestinationCountry=$CD";
    $post[] = "Currency=USD";                // Currently, supported only 'USD'. maxlen=3
    $post[] = "TotalPackages=".count($packages);

    foreach($packages as $k => $package) {
        $i = $k + 1;
        $post[] = "BoxID$i=box$i";
        $post[] = "Weight$i=".max(0.001, func_units_convert(func_weight_in_grams($package['weight']), 'g', 'kg', 3));
        $post[] = "WeightUnit$i=KG";
        $post[] = "Length$i=".max(0.1, round(func_dim_in_centimeters($package['length']), 1));
        $post[] = "Width$i=".max(0.1, round(func_dim_in_centimeters($package['width']), 1));
        $post[] = "Height$i=".max(0.1, round(func_dim_in_centimeters($package['height']), 1));
        $post[] = "DimensionalUnit$i=CM";    // DimensionalUnit    ::= CM | IN
        $post[] = "Packaging$i=$packaging";        // Packaging        ::= BOX | ENV | LTR | TUB
        $post[] = "Contents$i=$contents";
        $post[] = "Cod$i=$codvalue";
        $post[] = "Insurance$i=".round($package['price']*100);
    }

    $carriers_count = 0;
    foreach ($carriers as $k => $v) {
        $v = func_intship_carrier_alias($v, 'for_request');
        if (!empty($v)) {
            $i = $k + 1;
            $post[] = "CarrierCode$i=$v";
            $carriers_count++;
        }
    }

    $post[] = "TotalCarriers=".$carriers_count;

    if ($carriers_count == 0)
        return array();

    $post[] = "TotalOptions=".count($options);

    foreach($options as $k => $v) {
        $i = $k + 1;
        $post[] = "OptionCode$i=$v";
    }
    
    $query = implode('&',$post);
    $md5_request = md5($query);

    if ((func_is_shipping_result_in_cache($md5_request)) && ($debug != 'Y')){
        return func_get_shipping_result_from_cache($md5_request);
    }

    $post_url = 'https://www.intershipper.com/Interface/Intershipper/XML/v2.0/HTTP.jsp';
    list($header, $result) = func_https_request('POST', $post_url, $post);

    if (defined('INTERSHIPPER_DEBUG'))
        x_log_add('intershipper_rates', print_r($post, true) . "\n\n" . $header . "\n\n" . $result);

    $result = preg_replace("/^<\?xml\s+[^>]+>/s", '', trim($result));

    $parse_errors = false;
    $options = array(
        'XML_OPTION_CASE_FOLDING' => 1,
        'XML_OPTION_TARGET_ENCODING' => 'ISO-8859-1'
    );

    $parsed = func_xml_parse($result, $parse_errors, $options);

    $packages =& func_array_path($parsed, 'SHIPMENT/PACKAGE');
    $skipped_services = '';
    if (is_array($packages)) {
        $rates = array();
        foreach ($packages as $pkginfo) {
            if (empty($pkginfo['#']) || !is_array($pkginfo['#']) || empty($pkginfo['#']['QUOTE']))
                continue;

            foreach ($pkginfo['#']['QUOTE'] as $quote) {
                $carrier = func_array_path($quote, 'CARRIER/CODE/0/#');
                $service = func_array_path($quote, 'SERVICE/NAME/0/#');
                $sn = func_array_path($quote, 'SERVICE/CODE/0/#');
                $rate = func_array_path($quote, 'RATE/AMOUNT/0/#') / 100.0;

                $carrier = func_intship_carrier_alias($carrier, 'for_response');
                $sn = func_intship_servicecode_alias($sn);

                if (!$carrier || !($service || $sn)) {
                    continue;
                }

                if (
                    defined('INTERSHIPPER_DEBUG')
                    && !func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[shipping] WHERE intershipper_code='".addslashes($sn)."'")
                ) {
                    $skipped_services .= 'Skipped service:' . $service . ',code=' . $sn . ',rate=' . $rate . "\n";
                }    

                $saved = -1;

                foreach ($allowed_shipping_methods as $sk=>$sv) {
                    if ($sv['code'] != $carrier)
                        continue;

                    if ((!$sn || $sv['intershipper_code'] != $sn) && (!$service || !stristr($sv['shipping'],$service))) {
                        continue;
                    }                        

                    // Suppressing duplicates
                    if ($saved < 0 || strlen($allowed_shipping_methods[$saved]['shipping']) > strlen($sv['shipping']))
                        $saved = $sk;
                }

                if ($saved >= 0) {
                    if (isset($rates[$allowed_shipping_methods[$saved]['subcode']])) {
                        $rates[$allowed_shipping_methods[$saved]['subcode']] += $rate;
                    } else {
                        $rates[$allowed_shipping_methods[$saved]['subcode']] = $rate;
                    }
                }
            }
        }

        if (!empty($skipped_services)) {
            x_log_add('intershipper_rates', $skipped_services);
        }    

        if (!empty($rates)) {
            foreach ($rates as $k=>$v) {
                $intershipper_rates[]= array ('methodid'=>$k, 'rate'=>$v);
            }
            if ($debug != 'Y')
                func_save_shipping_result_to_cache($md5_request, $intershipper_rates);
        }
    }

    if ($debug=="Y") {
        print "<table width=\"800\"><tr><td width=\"800\">";
        print "<h1>InterShipper Debug Information</h1>";
        if ($query) {
            $query=preg_replace("/([&?])(Username[=][^&]*)/i","\\1Username=xxx",$query);
            $query=preg_replace("/([&?])(Password[=][^&]*)/i","\\1Password=xxx",$query);
            print "<h2>InterShipper Request</h2>";
            print "<pre>".htmlspecialchars($query)."</pre>";
            print "<h2>InterShipper Response</h2>";
            $out = $result;
            $out = preg_replace("/(>)(<[^\/])/", "\\1\n\\2", $out);
            $out = preg_replace("/(<\/[^>]+>)([^\n])/", "\\1\n\\2", $out);
            print "<pre>".htmlspecialchars($out)."</pre>";
            if (!empty($intershipper_error)){
                print "<h1>Error processing request at Intershipper</h1>";
                print $intershipper_error;
            }
            else {
                func_shipper_show_rates($intershipper_rates);
            }
        }
        else {
            print "It seems, you have forgotten to fill in an InterShipper account information.";
        }

        print "</td></tr></table>";
    }

    return $intershipper_rates;
}

/**
 * Return package limits for Intershipper
 */
function func_get_package_limits_INTERSHIPPER()
{
    global $config, $sql_tbl;
    $package_limits = array();

    $params = func_query_first ("SELECT * FROM $sql_tbl[shipping_options] WHERE carrier='INTERSHIPPER'");

    // weight limit 150lbs , (can be overwriten in InterShipper configuration)
    $package_limits['weight'] = 150 * XCPhysics::GRAMS_PER_1LB / $config['General']['weight_symbol_grams'];
    if ($params['param09'] > 0) $package_limits['weight'] = $params['param09'];

    // dimension limits (specified in InterShipper configuration)
    if ($params['param02'] > 0) $package_limits['length'] = $params['param02'];
    if ($params['param03'] > 0) $package_limits['width'] = $params['param03'];
    if ($params['param04'] > 0) $package_limits['height'] = $params['param04'];
    return $package_limits;
}

/**
 * Check if Intershipper allows box
 */
function func_check_limits_INTERSHIPPER($box)
{
    $package_limits = func_get_package_limits_INTERSHIPPER();
    $box['weight'] = (isset($box['weight'])) ? $box['weight'] : 0;

    return (func_check_box_dimensions($box, $package_limits) && $package_limits['weight'] > $box['weight']);
}

function func_intship_carrier_alias($code, $order = 'for_response')
{
    $codes = array(
        'DHL' => 'ARB',
        'FDX' => 'FDX',
        'UPS' => 'UPS',
        'USP' => 'USPS',
        'CAN' => 'CPC',
        
    );

    if ($order == 'for_response') {
        return $codes[$code];
    } else {
        return array_search($code, $codes);
    }

}

function func_intship_servicecode_alias($code)
{
    $codes = array(
        'UPX' => 'UCX',//UPS Domestic Canadian Expedited -> UPS Expedited##SM##
        'UCN' => 'UCE', //UPS Canadian Express 1NA -> UPS Express
        'FCF' => 'FIF', //FedEx International First -> FedEx International First##R##
    );

    if (isset($codes[$code]))
        return $codes[$code];

    return $code;
}

// http://www.intershipper.net/Shipping/Intershipper/Website/MainPage.jsp?Page=Integrate

?>
