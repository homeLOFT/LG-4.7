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
 * USPS shipping library
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    6c76495a41d0ada8c685cc967b72ebac563d321a, v150 (xcart_4_7_0), 2015-03-04 15:33:29, mod_USPS.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

function func_shipper_USPS($items, $userinfo, $orig_address, $debug, $cart)
{
    global $config, $sql_tbl;
    global $allowed_shipping_methods, $intershipper_rates, $active_modules, $ship_packages_uniq;

    if (
        empty($config['Shipping']['USPS_username'])
        || !is_array($allowed_shipping_methods)
    ) {
        return;
    }    

    foreach ($allowed_shipping_methods as $key=>$value) {
        if ($value['code'] == 'USPS') {
            $USPS_FOUND = true;
            break;
        }
    }

    if (empty($USPS_FOUND)) {
        return;
    }

    x_load('http','xml');

    $dst_country = USPS_get_country($userinfo['s_country']);

    if (empty($dst_country)) {
        return;
    }

    $usps_options = func_query_first("SELECT * FROM $sql_tbl[shipping_options] WHERE carrier='USPS'");
    $_params = @unserialize($usps_options['param00']);
    if (!empty($_params)) {
        foreach ($_params as $key=>$value) {
            $usps_options[$key] = $value;
        }
    }
    $usps_options['use_commercial_rate'] = !empty($usps_options['use_commercial_rate']) ? $usps_options['use_commercial_rate'] : 'Y';
    $usps_options['use_commercial_plus_rate'] = !empty($usps_options['use_commercial_plus_rate']) ? $usps_options['use_commercial_plus_rate'] : 'Y';

    $userinfo['s_country'] = func_USPS_country_normalize($userinfo['s_country']);
    $orig_address['country'] = func_USPS_country_normalize($orig_address['country']);
    
    $intl_use = $userinfo['s_country'] != $orig_address['country'];

    $package_limits = func_get_package_limits_USPS($intl_use);

    $rates = $used_requests = array();

    // The items are related to one provider only
    $provider = $items[0]['provider'];

    // Pass info about packages to Func_place_order, using ship_packages_uniq variable
    x_session_register('ship_packages_uniq');
    $ship_packages_uniq[$provider . 'USPS'] = $previous_pack = array();
    $previous_pack_limit_key = '';

    foreach ($package_limits as $pack_limit_key => $package_limit) {

        $is_first_class = false;
        if (isset($package_limit['first_class']) && $package_limit['first_class'] == 'Y') {
            unset($package_limit['first_class']);
            $is_first_class = true;
        }

        $usps_rates = array();

        $packages = func_get_packages($items, $package_limit, ($usps_options['param11'] == "Y") ? 100 : 1);

        if (!empty($packages) && is_array($packages)) {

            $ship_packages_uniq[$provider . 'USPS'][$pack_limit_key] = array();

            foreach ($packages as $pack_num => $pack) {

                // Save packages configuration for Shipping Label Generator

                if (!empty($active_modules['Shipping_Label_Generator'])) {
                    list($ship_packages_uniq, $previous_pack, $previous_pack_limit_key) = func_usps_save_pack_configuration_slg($ship_packages_uniq, $provider, $pack, $previous_pack, $pack_limit_key, $previous_pack_limit_key);
                }

                $query = func_USPS_prepare_rate_query(
                    $pack, 
                    $usps_options, 
                    $intl_use, 
                    substr(preg_replace('/[^0-9]/Si', '', $orig_address['zipcode']), 0, 5),
                    substr(preg_replace('/[^0-9]/Si', '', $userinfo['s_zipcode']), 0, 5),
                    $dst_country, 
                    $is_first_class
                );

                // Some services are disabled by admin
                if (empty($query)) {
                    $usps_rates = array();
                    break;
                }

                $md5_request = md5($query);

                // Send the same requests within one pack configuration bt #91321
                // Do not send the same requests within different configuration
                if (isset($used_requests[$md5_request]) && $used_requests[$md5_request] != $pack_limit_key)
                    continue;

                $used_requests[$md5_request] = $pack_limit_key;

                if ((func_is_shipping_result_in_cache($md5_request)) &&  ($debug != 'Y')) {
                    $usps_rates[$pack_num] = func_get_shipping_result_from_cache($md5_request);
                    continue;
                }

                $rate_api = ($intl_use) ? 'IntlRateV2' : 'RateV4';

                if (!defined('USPS_TEST_FUTURE_CHANGES')) {
                    list($header, $result) = func_http_get_request('production.shippingapis.com', '/ShippingAPI.dll', 'API=' . $rate_api . '&XML=' . urlencode($query));
                } else {
                    list($header, $result) = func_http_get_request('stg-production.shippingapis.com', '/ShippingAPI.dll', 'API=' . $rate_api . '&XML=' . urlencode($query));
                }

                $err = FALSE;
                $xml = func_xml_parse($result, $err);

                if (func_USPS_has_response_error($xml, $rate_api, $intl_use)) {
                    if ($debug != 'Y') 
                        func_save_shipping_result_to_cache($md5_request, array());

                    func_USPS_debug($query, $result, $debug);

                    $usps_rates = array();
                    break; // Break foreach packages cycle as one package is fault
                }

                // Get <Package> elements
                $xml_packages = func_array_path($xml, $rate_api.'Response/Package'.($intl_use ? '/Service' : ''));

                if (is_array($xml_packages)) {
                    list($usps_rates[$pack_num], $new_method_is_added) = func_USPS_parse_methods($xml_packages, $intl_use, $allowed_shipping_methods, $pack_limit_key, $usps_options);

                    $usps_rates[$pack_num] = func_normalize_shipping_rates($usps_rates[$pack_num], 'USPS');

                    if (
                        $debug != 'Y'
                        && !$new_method_is_added
                    ) {
                        func_save_shipping_result_to_cache($md5_request, $usps_rates[$pack_num]);
                    }

                    assert('!$new_method_is_added /*'.__FUNCTION__.' New shipping method is added. Check DB and add it to sql/xcart_data.sql*/');
                }    

                    func_USPS_debug($query, $result, $debug);


            } // foreach $packages

        } //if (!empty($packages) && is_array($packages)) {

        $rates = func_array_merge($rates, func_intersect_rates($usps_rates));
        $rates = func_shipping_min_rates($rates);

    } // foreach $package_limits

    if (
        substr($cart['delivery'], 0, 4) == 'USPS'
        && is_array($rates)
        && !empty($active_modules['Shipping_Label_Generator'])
    ) {

        // Correlate current USPS shipping method with related package configuration

        $found = false;
        foreach($rates as $rate) {
            if ($rate['slg_shippingid'] == $cart['shippingid']) {
                $ship_packages_uniq[$provider . 'USPS'] = $ship_packages_uniq[$provider . 'USPS'][$rate['slg_pack_limit_key']];
                $found = true;
                break;
            }
        }

        // Choose last package limit configuration
        if (!$found && !empty($ship_packages_uniq[$provider . 'USPS']))
            $ship_packages_uniq[$provider . 'USPS'] = array_pop($ship_packages_uniq[$provider . 'USPS']);
    } else {
        $ship_packages_uniq[$provider . 'USPS'] = array();
    }

    $intershipper_rates = func_array_merge($intershipper_rates, $rates);
}

/**
 * Get USPS country code
 */
function USPS_get_country($code)
{
    global $sql_tbl, $shop_language;

    static $usps_countries = array( //{{{
        'AF' => 'Afghanistan',
        'AX' => 'Aland Island (Finland)',
        'NZ' => 'New Zealand',
        'FI' => 'Finland',
        'AL' => 'Albania',
        'DZ' => 'Algeria',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AG' => 'Antigua and Barbuda',
        'AR' => 'Argentina',
        'AM' => 'Armenia',
        'AW' => 'Aruba',
        'AU' => 'Australia',
        'AT' => 'Austria',
        'AZ' => 'Azerbaijan',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BY' => 'Belarus',
        'BE' => 'Belgium',
        'BZ' => 'Belize',
        'BJ' => 'Benin',
        'BM' => 'Bermuda',
        'BT' => 'Bhutan',
        'BO' => 'Bolivia',
        'BA' => 'Bosnia-Herzegovina',
        'BW' => 'Botswana',
        'BR' => 'Brazil',
        'VG' => 'British Virgin Islands',
        'BN' => 'Brunei Darussalam',
        'BG' => 'Bulgaria',
        'BF' => 'Burkina Faso',
        'MM' => 'Burma',
        'BI' => 'Burundi',
        'KH' => 'Cambodia',
        'CM' => 'Cameroon',
        'CA' => 'Canada',
        'CV' => 'Cape Verde',
        'KY' => 'Cayman Islands',
        'CF' => 'Central African Republic',
        'TD' => 'Chad',
        'CL' => 'Chile',
        'CN' => 'China',
        'CO' => 'Colombia',
        'KM' => 'Comoros',
        'CG' => 'Congo, Republic of the',
        'CR' => 'Costa Rica',
        'CI' => 'Cote d\'Ivoire',
        'HR' => 'Croatia',
        'CU' => 'Cuba',
        'CY' => 'Cyprus',
        'CZ' => 'Czech Republic',
        'DK' => 'Denmark',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominican Republic',
        'EC' => 'Ecuador',
        'EG' => 'Egypt',
        'SV' => 'El Salvador',
        'GQ' => 'Equatorial Guinea',
        'ER' => 'Eritrea',
        'EE' => 'Estonia',
        'ET' => 'Ethiopia',
        'FK' => 'Falkland Islands',
        'FO' => 'Faroe Islands',
        'FJ' => 'Fiji',
        'FR' => 'France',
        'GF' => 'French Guiana',
        'PF' => 'French Polynesia',
        'GA' => 'Gabon',
        'GM' => 'Gambia',
        'GE' => 'Georgia, Republic of',
        'DE' => 'Germany',
        'GH' => 'Ghana',
        'GI' => 'Gibraltar',
        'GB' => 'United Kingdom (Great Britain and Northern Ireland)',
        'GR' => 'Greece',
        'GL' => 'Greenland',
        'GD' => 'Grenada',
        'GP' => 'Guadeloupe',
        'GU' => 'Guam, United States',// Guam, United States (Domestic Mail)
        'GT' => 'Guatemala',
        'GG' => 'Guernsey (Channel Islands) (Great Britain and Northern Ireland)',
        'GN' => 'Guinea',
        'GW' => 'Guinea-Bissau',
        'GY' => 'Guyana',
        'HT' => 'Haiti',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungary',
        'IS' => 'Iceland',
        'IN' => 'India',
        'ID' => 'Indonesia',
        'IR' => 'Iran',
        'IQ' => 'Iraq',
        'IE' => 'Ireland',
        'IM' => 'Isle of Man (Great Britain and Northern Ireland)',
        'IL' => 'Israel',
        'IT' => 'Italy',
        'JM' => 'Jamaica',
        'JP' => 'Japan',
        'JE' => 'Jersey (Channel Islands) (Great Britain and Northern Ireland)',
        'JO' => 'Jordan',
        'KZ' => 'Kazakhstan',
        'KE' => 'Kenya',
        'KI' => 'Kiribati',
        'KP' => 'Korea, Democratic People\'s Republic of',
        'KR' => 'Korea, Republic of (South Korea)',
        'KW' => 'Kuwait',
        'KG' => 'Kyrgyzstan',
        'LA' => 'Laos',
        'LV' => 'Latvia',
        'LB' => 'Lebanon',
        'LS' => 'Lesotho',
        'LR' => 'Liberia',
        'LY' => 'Libya',
        'LI' => 'Liechtenstein',
        'LT' => 'Lithuania',
        'LU' => 'Luxembourg',
        'MO' => 'Macau (Macao)',
        'MK' => 'Macedonia, Republic of',
        'MG' => 'Madagascar',
        'MW' => 'Malawi',
        'MY' => 'Malaysia',
        'MV' => 'Maldives',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'MH' => 'Marshall Islands, Republic of the, United States',#Marshall Islands, Republic of the, United States (Domestic Mail)',
        'MQ' => 'Martinique',
        'MR' => 'Mauritania',
        'MU' => 'Mauritius',
        'MX' => 'Mexico',
        'FM' => 'Micronesia, Federated States of, United States',#Micronesia, Federated States of, United States (Domestic Mail)', 
        'MD' => 'Moldova',
        'MN' => 'Mongolia',
        'MS' => 'Montserrat',
        'MA' => 'Morocco',
        'MZ' => 'Mozambique',
        'NA' => 'Namibia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NL' => 'Netherlands',
        'AN' => 'Netherlands Antilles',
        'NC' => 'New Caledonia',
        'NI' => 'Nicaragua',
        'NE' => 'Niger',
        'NG' => 'Nigeria',
        'MP' => 'Northern Mariana Islands, Commonwealth of, United States',#Northern Mariana Islands, Commonwealth of, United States (Domestic Mail)', // Rota Island, Saipan Island, Tinian Island
        'NO' => 'Norway',
        'OM' => 'Oman',
        'AS' => 'American Samoa',//Manua Island, Swain's Island, Tutuila Island (Domestic Mail)
        'PK' => 'Balochistan (Pakistan)',
        'PW' => 'Palau, United States',#Palau, United States (Domestic Mail)',
        'PA' => 'Panama',
        'PG' => 'Papua New Guinea',
        'PY' => 'Paraguay',
        'PE' => 'Peru',
        'PH' => 'Philippines',
        'PN' => 'Pitcairn Island',
        'PL' => 'Poland',
        'PT' => 'Portugal',
        'PR' => 'Puerto Rico, United States',#Puerto Rico, United States (Domestic Mail)',
        'QA' => 'Qatar',
        'RE' => 'Reunion',
        'RO' => 'Romania',
        'RU' => 'Russia',
        'RW' => 'Rwanda',
        'BL' => 'Saint Barthelemy (Guadeloupe)',
        'KN' => 'Saint Christopher (St. Kitts) and Nevis',
        'SH' => 'Saint Helena',
        'LC' => 'Saint Lucia',
        'MF' => 'Saint Martin (French) (Guadeloupe)',
        'PM' => 'Saint Pierre and Miquelon',
        'VC' => 'Saint Vincent and the Grenadines',
        'WS' => 'Samoa, American',
        'SM' => 'San Marino',
        'ST' => 'Sao Tome and Principe',
        'SA' => 'Saudi Arabia',
        'SN' => 'Senegal',
        'RS' => 'Serbia, Republic of',
        'SC' => 'Seychelles',
        'SL' => 'Sierra Leone',
        'SG' => 'Singapore',
        'SK' => 'Slovak Republic (Slovakia)',
        'SI' => 'Slovenia',
        'SB' => 'Santa Cruz Islands (Solomon Islands)',
        'SO' => 'Somalia',
        'ZA' => 'South Africa',
        'ES' => 'Spain',
        'LK' => 'Sri Lanka',
        'SD' => 'Sudan',
        'SR' => 'Suriname',
        'SZ' => 'Swaziland',
        'SE' => 'Sweden',
        'CH' => 'Switzerland',
        'SY' => 'Syrian Arab Republic (Syria)',
        'TW' => 'Taiwan',
        'TJ' => 'Tajikistan',
        'TZ' => 'Tanzania',
        'TH' => 'Thailand',
        'TG' => 'Togo',
        'TO' => 'Tonga',
        'TT' => 'Trinidad and Tobago',
        'TN' => 'Tunisia',
        'TR' => 'Turkey',
        'TM' => 'Turkmenistan',
        'TC' => 'Turks and Caicos Islands',
        'TV' => 'Tuvalu',
        'UG' => 'Uganda',
        'UA' => 'Ukraine',
        'UY' => 'Uruguay',
        'UZ' => 'Uzbekistan',
        'VU' => 'Vanuatu',
        'VA' => 'Vatican City',
        'VE' => 'Venezuela',
        'VN' => 'Vietnam',
        'VI' => 'Virgin Islands (US), United States',#Virgin Islands (US), United States (Domestic Mail)', //St. Croix Island, St. John Island, St. Thomas Island
        'WF' => 'Wallis and Futuna Islands',
        'YE' => 'Yemen',
        'YU' => 'Yugoslavia',
        'ZM' => 'Zambia',
        'ZW' => 'Zimbabwe',
        'CC' => 'Cocos Island (Australia)',
        'CK' => 'Cook Islands (New Zealand)',
        'TP' => 'East Timor',
        'YT' => 'Mayotte (France)',
        'MC' => 'Monaco (France)',
        'NU' => 'Niue (New Zealand)',
        'NF' => 'Norfolk Island (Australia)',
        'TK' => 'Tokelau (Western Samoa)',
        'UK' => 'United Kingdom',
        'CX' => 'Christmas Island (Australia)',
        'US' => 'United States',#United States (Domestic Mail)',
    ); //}}}

    if (isset($usps_countries[$code]))
        return $usps_countries[$code];

    $l_dst_country = func_query_first_cell("SELECT value FROM $sql_tbl[languages] WHERE name = 'country_".$code."' AND code = '$shop_language'");

    return $l_dst_country;
}

/**
 * Return package limits for USPS
 */
function func_get_package_limits_USPS($intl_use)
{
    global $sql_tbl;

    $l_usps_options = func_query_first("SELECT param06, param04, param03, param05, param08 FROM $sql_tbl[shipping_options] WHERE carrier='USPS'");

    if ($intl_use) {
        $package_limits = #nolint
            array(
                array('weight' => 0.21875, 'length' => 11.5, 'height' => 6.125),//First-Class Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International Letter**
                array('weight' => 4, 'length' => 10, 'height' => 5),//Priority Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International Window Flat Rate Envelope**
                array('weight' => 4, 'length' => 10, 'height' => 6),//Priority Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International Small Flat Rate Envelope**
                array('weight' => 4, 'length' => 10, 'height' => 7),//Priority Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International Gift Card Flat Rate Envelope**
                array('weight' => 4, 'length' => 12.5, 'height' => 9.5),//Priority Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International Padded Flat Rate Envelope** and Priority Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International Flat Rate Envelope**
                array('weight' => 4, 'length' => 15.5, 'height' => 9.5),//Priority Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International Legal Flat Rate Envelope**
                array('weight' => 20),
                array('weight' => 4, 'length' => 12.5, 'height' => 9.5),//Express Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International Flat Rate Envelope
                array('weight' => 44, 'length' => 15, 'height' => 9.5),//Express Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International Legal Flat Rate Envelope
                array('weight' => 44, 'girth' => 79, 'length' => 36),//Express Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International
                array('weight' => 44, 'girth' => 79, 'length' => 42),//Priority Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International
                array('weight' => 66, 'girth' => 79, 'length' => 42),//Express Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International
                array('weight' => 66, 'girth' => 108, 'length' => 79),//Priority Mail&lt;sup&gt;&amp;reg;&lt;/sup&gt; International
                array('weight' => 70, 'length' => 12.5, 'height' => 9.5),//USPS GXG&lt;sup&gt;&amp;trade;&lt;/sup&gt; Envelopes**
                array('weight' => 70, 'length' => 15.5, 'height' => 12.5),//USPS GXG&lt;sup&gt;&amp;trade;&lt;/sup&gt; Envelopes**
                array('weight' => 70, 'girth' => 108, 'length' => 46, 'width' => 35, 'height' => 46),//Global Express Guaranteed&lt;sup&gt;&amp;reg;&lt;/sup&gt; (GXG)**
            );
    } else {
        $l_first_class_mail_type = empty($l_usps_options['param05']) ? 'LETTER' : $l_usps_options['param05'];
        $package_limits = #nolint
            in_array($l_first_class_mail_type, array('LETTER', 'POSTCARD')) 
                ? array(array('weight' => 0.21875, 'girth' => 108, 'first_class' => 'Y'))
                : array(array('weight' => 0.8125, 'girth' => 108, 'first_class' => 'Y'));
    }

    $dim = array(
        'length' => 0, 
        'width' => 0, 
        'height' => 0, 
        'girth' => 0
    );
    if (!empty($l_usps_options['param06']))
        list($dim['length'], $dim['width'], $dim['height'], $dim['girth']) = explode(':', $l_usps_options['param06']);

    // Convert user-defined dimensions to inches
    foreach($dim as $k=>$v)
        $dim[$k] = func_units_convert(func_dim_in_centimeters($v), 'cm', 'in', 2);

    if (!$intl_use) {

        $dimensions_array = array();
        foreach (array('width', 'height', 'length') as $_dim) {

            if (isset($dim[$_dim]) && $dim[$_dim] != '') {

                $dimensions_array[$_dim] = $dim[$_dim];
            }

        }
        $l_container_express['FLAT RATE BOX'] = array(
            array('weight' => 70, 'length' => 11, 'width' => 8.5,  'height' => 5.5),
            array('weight' => 70, 'length' => 13.625, 'width' => 11.875,  'height' => 3.375)
        );
        $l_container_express['FLAT RATE ENVELOPE'] = array(array('weight' => 70, 'length' => 12.5, 'height' => 9.5));
        $l_container_express['LEGAL FLAT RATE ENVELOPE'] = array(array('weight' => 70, 'length' => 15, 'height' => 9.5));
        $l_container_express['PADDED FLAT RATE ENVELOPE'] = array(array('weight' => 70, 'length' => 12.5, 'height' => 9.5));


        $l_container_priority['LG FLAT RATE BOX'] = array(
            array('weight' => 70, 'length' => 12, 'width' => 12,  'height' => 5.5),
            array('weight' => 70, 'length' => 23.6875, 'width' => 11.75,  'height' => 3)
        );
        $l_container_priority['MD FLAT RATE BOX'] = array(
            array('weight' => 70, 'length' => 11, 'width' => 8.5,  'height' => 5.5),
            array('weight' => 70, 'length' => 13.625, 'width' => 11.875,  'height' => 3.375)
        );
        $l_container_priority['SM FLAT RATE BOX'] = array(array('weight' => 70, 'length' => 8.625, 'width' => 5.375,  'height' => 1.625));
        $l_container_priority['FLAT RATE ENVELOPE'] = array(array('weight' => 70, 'length' => 12.5, 'height' => 9.5));
        $l_container_priority['LEGAL FLAT RATE ENVELOPE'] = array(array('weight' => 70, 'length' => 15, 'height' => 9.5));
        $l_container_priority['PADDED FLAT RATE ENVELOPE'] = array(array('weight' => 70, 'length' => 12.5, 'height' => 9.5));
        $l_container_priority['GIFT CARD FLAT RATE ENVELOPE'] = array(array('weight' => 70, 'length' => 10, 'height' => 7));
        $l_container_priority['SM FLAT RATE ENVELOPE'] = array(array('weight' => 70, 'length' => 10, 'height' => 6));
        $l_container_priority['WINDOW FLAT RATE ENVELOPE'] = array(array('weight' => 70, 'length' => 10, 'height' => 5));
        // https://www.usps.com/business/priority-mail-regional-rate.htm
        $l_container_priority['REGIONALRATEBOXA'] = array(
            array('weight' => 15, 'girth' => 108, 'length' => 12.8125, 'width' => 10.9375,  'height' => 2.375),
            array('weight' => 15, 'girth' => 108, 'length' => 10, 'width' => 7,  'height' => 4.75),
        );
        $l_container_priority['REGIONALRATEBOXB'] = array(
            array('weight' => 20, 'girth' => 108, 'length' => 15.875, 'width' => 14.375,  'height' => 2.875),
            array('weight' => 20, 'girth' => 108, 'length' => 12, 'width' => 10.25,  'height' => 5),
        );
        $l_container_priority['REGIONALRATEBOXC'] = array(array('weight' => 25, 'girth' => 108, 'length' => 14.75, 'width' => 11.75,  'height' => 11.5));


        $l_container_priority['RECTANGULAR'] = 
        $l_container_express['RECTANGULAR'] =
        $l_container_express['NONRECTANGULAR'] =
        $l_container_priority['NONRECTANGULAR'] = array(array_merge(array('weight' => 70, 'girth' => 108), $dimensions_array));

        if (isset($l_container_express[$l_usps_options['param03']])) {
            $package_limits = func_array_merge($package_limits, $l_container_express[$l_usps_options['param03']]);
            $_is_express_set = true;
        } else {
            // Default pack configuration for domestic rates
            $package_limits = func_array_merge($package_limits, array(array_merge(array('weight' => 70, 'girth' => 108), $dimensions_array)));
        }

        if (isset($l_container_priority[$l_usps_options['param04']])) {
            if (
                empty($_is_express_set) 
                || $l_container_priority[$l_usps_options['param04']] != $l_container_express[$l_usps_options['param03']]
            ) {
                $package_limits = func_array_merge($package_limits, $l_container_priority[$l_usps_options['param04']]);
            }
        } elseif (!empty($_is_express_set)) {
            // Default pack configuration for domestic rates
            $package_limits = func_array_merge($package_limits, array(array_merge(array('weight' => 70, 'girth' => 108), $dimensions_array)));
        }
    }

    $max_weight = doubleval($l_usps_options['param08']);
    $max_weight = func_weight_in_lbs($max_weight);
    foreach($package_limits as $k => $v) {
        if($max_weight>0 && !$intl_use)
            $v['weight'] = min($v['weight'], $max_weight);
        $package_limits[$k] = func_correct_dimensions($v);
    }

    return $package_limits;
}

/**
 * Check if USPS allows box
 */
function func_check_limits_USPS($box)
{
    $avail = false;
    $box['weight'] = isset($box['weight']) ? $box['weight'] : 0;

    foreach (array(false, true) as $intl_use) {
        $pack_limits = func_get_package_limits_USPS($intl_use);
        foreach ($pack_limits as $pack_limit) {
            if (!$intl_use) {
                unset($pack_limit['package_size']);
            }

            if (isset($pack_limit['first_class']))
                unset($pack_limit['first_class']);

            $avail = (func_check_box_dimensions($box, $pack_limit) && $pack_limit['weight'] > $box['weight']);

            if ($avail)
                break 2;
        }
    }
    return $avail;
}

/**
 * Changes several country codes into US one (GU, PR, VI, AS, MP)
 *
 * @param string $country country code
 *
 * @return string new normalized country code
 * @see    ____func_see____
 * @since  1.0.0
 */
function func_USPS_country_normalize($country)
{
    return in_array($country, array('GU', 'PR', 'VI', 'AS', 'MP'))
        ? 'US'
        : $country;
}

function func_USPS_is_predefined_container($container) {
    $container = trim($container);
    return 
        !empty($container)
        && $container != 'None'
        && $container != 'NONE'
        && $container != 'RECTANGULAR'
        && $container != 'NONRECTANGULAR'
        && $container != 'VARIABLE';
            
}

function func_USPS_has_response_error($xml, $rate_api, $intl_use)
{
    if (empty($xml)) {
        assert('FALSE /*'.__FUNCTION__.' Empty XML.Check x-errors_shipping-<date>.php file*/');
        x_log_flag('log_shipping_errors', 'SHIPPING', "USPS module error: Empty parsed XML, check HTTP connection", true);
        return true;
    }
        
    $common_xml_error = func_array_path($xml, 'Error');
    if (!empty($common_xml_error)) {
        func_USPS_log_error($common_xml_error);
    }

    if (!empty($common_xml_error)) {
        return true;
    }

    if ($intl_use) {
        $_package_err = func_array_path($xml, $rate_api.'Response/Package/Error');

        if (!empty($_package_err))
            func_USPS_log_error($_package_err);
    }

    return !empty($_package_err);
}

function func_USPS_log_debug_information($query, $result)
{
    if (stripos($result, 'error') !== FALSE)
        x_log_flag('log_shipping_errors', 'SHIPPING', "USPS Debug Information: QUERY $query\n RESULT : $result", true);

    return true;        
}

function func_USPS_log_error($err)
{
    static $error_count = 0;

    if (!empty($err)) {
        $USPS_error_description = func_array_path($err, 'Description/0/#');
        x_log_flag('log_shipping_errors', 'SHIPPING', "USPS module error: [".func_array_path($err, 'Number/0/#')."] " . func_array_path($err, 'Source/0/#') ."\n". "USPS_error_description:" . $USPS_error_description ."\n". func_array_path($err, 'HelpFile/0/#') . func_array_path($err, 'HelpContext/0/#'), true);
        $error_count++;
        if ($USPS_error_description != 'Service not available.') {
            assert('FALSE /*'.__FUNCTION__.' USPS returns errors.Check x-errors_shipping-{date}.php file*/');
        }
    }    

    return $error_count;
}

/*
 Get XML girth for combinations of Size ant Container parametrs
*/
function func_USPS_get_xml_girth($_package_size, $container, $def)
{
    $res = $def;

    if ($_package_size == 'LARGE') {
        if ($container == 'RECTANGULAR')
            $res = '';
        else
            $res = $def;
    } else {
        // Ignored for Regular size
        $res = '';
    }

    return $res;
}

/*
https://www.usps.com/business/web-tools-apis/rate-calculator-api.htm#OLE_LINK8
Get element for XML request
    IntlRateV2Request / Package / Size
    RateV4Request / Package / Size
        enumeration=LARGE
        enumeration=REGULAR

Defined as follows:
 REGULAR: Package dimensions are 12'' or less;
 LARGE: Any package dimension is larger than 12''.
*/
function func_USPS_get_xml_package_size($dims)
{//{{{
    $min = min($dims);
    $max = max($dims);

    // All dims are required for LARGE value
    if ($min == 0)
        return 'REGULAR';

    if ($max <= 12)
        return 'REGULAR';
    
    //At least one dim is great then 12''
    return 'LARGE';
}//}}}

/**
 * Implement suggestion from Evan(@USPS) "I recommend using 'Large' any time you provide dimensions in the request."
 * To work 'filter out Flat Rate options' feature works properly 
 */
function func_USPS_correct_xml_package_size($_package_size, $_services, $_dims, $_default_domestic_service)
{//{{{
    if (
        !empty($_services)
        && count($_services) == 1
        && in_array($_default_domestic_service, $_services)
        && count(array_filter($_dims)) == 3
    ) {
        return 'LARGE';
    }

    return $_package_size;

}//}}}

function func_USPS_get_xml_value_of_content($value, $price)
{
    if ($value == 'disabled')
        return '';

    if (strpos($value, '%') === false) {
        $value_of_content = $value;
    } else {
        $value_of_content = $price * intval($value) / 100;
    }

    $_value_of_content_xml = ($value_of_content > 0) ? "<ValueOfContents>$value_of_content</ValueOfContents>" : '<ValueOfContents/>';

    return $_value_of_content_xml;
}

/*
 Parse returned shipping methods from USPS response, add new methods to xcart_shipping table, handle errors
*/
function func_USPS_parse_methods($_packages, $intl_use, $_allowed_shipping_methods, $_pack_limit_key, $_usps_options)
{
    assert('/*'.__FUNCTION__.' @params*/ is_array($_packages) && is_array($_allowed_shipping_methods)');

    $packages_conf_rates = array();
    $_new_method_is_added = false;
    $method_count = 0;

    $method_container = (($intl_use) ? 'SvcDescription' : 'MailService');
    foreach ($_packages as $_package) {

        if (!$intl_use) {
            // Get <Error> element for RateV4 requests
            $package_err = func_array_path($_package, 'Error');
            if (!empty($package_err)) {
                func_USPS_log_error($package_err);
                continue;
            }    
        }

        if ($intl_use) {
            $postages = array($_package);
        } else {
            $postages = func_array_path($_package, 'Postage');
        }

        foreach ($postages as $p) {
            $method_count++;
            // Get shipping method name
            $sname = func_array_path($p, "$method_container/0/#");
            $sname = trim($sname);

            $sname = func_convert_trademark($sname);
            $sname = preg_replace("/(.*)\*\*$/s", "\\1", $sname);

            // Get delivery time
            $delivery_time = ($intl_use) ? func_array_path($p, 'SvcCommitments/0/#') : '';
            // Get rate
            $rate = func_array_path($p, ($intl_use) ? "Postage/0/#" : "Rate/0/#");


            if (empty($sname) || zerolen($rate))
                continue;

            if ($_usps_options['use_commercial_rate'] == 'Y') {
                $CommercialRate = floatval(func_array_path($p, ($intl_use) ? "CommercialPostage/0/#" : "CommercialRate/0/#"));
            } else {
                $CommercialRate = 0;
            }
            if ($_usps_options['use_commercial_plus_rate'] == 'Y') {
                $CommercialPlusRate = floatval(func_array_path($p, ($intl_use) ? "CommercialPlusPostage/0/#" : "CommercialPlusRate/0/#"));
            } else {
                $CommercialPlusRate = 0;
            }

            // Do not return 0.00 rates if there are non zero rates
            $_all_rates = array_filter(array(floatval($rate), $CommercialRate, $CommercialPlusRate));
            if (!empty($_all_rates)) {
                $rate = min($_all_rates);
            }

            // Try to get CommercialRate for commercial services like 'PRIORITY HFP COMMERCIAL'
            if (!floatval($rate)) {
                $rate = func_array_path($p, ($intl_use) ? "CommercialPostage/0/#" : "CommercialRate/0/#");
            }

            // Define shipping method
            $is_found = false;
            foreach ($_allowed_shipping_methods as $sm) {
                if (
                    $sm['code'] == 'USPS'
                    && $sm['destination'] == (($intl_use) ? 'I' : 'L')
                    && preg_match('/^' . preg_quote($sm['shipping'], '/') . '$/S', 'USPS ' . $sname)
                ) {
                    $packages_conf_rates[] = array(
                        'methodid'           => $sm['subcode'],
                        'rate'               => $rate,
                        'slg_shippingid'     => $sm['shippingid'],
                        'slg_pack_limit_key' => $_pack_limit_key,
                        'warning'            => ''
                    );

                    $is_found = true;
                    break;
                }
            }

            if (!$is_found) {

                // Add new shipping method
                $_params = array();
                $_params['destination'] = (($intl_use) ? 'I' : 'L');
                if (!empty($delivery_time)) {
                    $_params['shipping_time'] = $delivery_time;
                }

                if ($_usps_options['param01'] == 'new_method_is_enabled')
                    $_params['active'] = 'Y';

                if (func_add_new_smethod('USPS ' . $sname, 'USPS', $_params))
                    $_new_method_is_added = true;
            }
        }
    }
    assert('preg_match_all("/$method_container/", print_r($_packages, TRUE), $arr) && $method_count == count($arr[0]) /*'.__FUNCTION__.' Some USPS services were not parsed */');

    return array($packages_conf_rates, $_new_method_is_added);
}

function func_USPS_prepare_rate_query($_pack, $_usps_options, $intl_use, $ZO, $ZD, $dst_country, $is_first_class) { // {{{
    global $config; 

    // Get pack weight in oz
    if ($intl_use) {
        // For international rates The ounces field must be less than 5 digits
        $ounces = func_units_convert(func_weight_in_grams(max($_pack['weight'], 0.01)), 'g', 'oz', 1);
        if (strlen(preg_replace('/[^0-9]/', '', $ounces)) >= 5)
            $ounces = round($ounces);
    } else {
        // For national rates totalDigits=10
        $ounces = func_units_convert(func_weight_in_grams(max($_pack['weight'], 0.01)), 'g', 'oz', 2);
    }

    $default_domestic_service = $_usps_options['use_commercial_plus_rate'] == 'Y' ? 'PLUS' : 'ONLINE';
    $_usps_options['mailtype'] = !empty($_usps_options['mailtype']) ? $_usps_options['mailtype'] : 'All';
    $_usps_options['ground_only'] = isset($_usps_options['ground_only']) ? $_usps_options['ground_only'] : 'true';
    $_usps_options['selected_services'] = !empty($_usps_options['selected_services']) ? $_usps_options['selected_services'] : array($default_domestic_service);

    $_USPS_username = $config['Shipping']['USPS_username'];

    $machinable = strtolower($_usps_options['param02']);

    // Get specified_dims
    $specified_dims = array();
    if (!empty($_usps_options["param06"])) {
        list($specified_dims['length'], $specified_dims['width'], $specified_dims['height'], $specified_dims['girth']) = explode(':', $_usps_options["param06"]);

        foreach($specified_dims as $k => $v) {
            if ($v > 0)
                $specified_dims[$k] = doubleval($v);
            else
                unset($specified_dims[$k]);
        }
    }

    if ($_usps_options['param09'] == "Y")
        $_pack = func_array_merge($_pack, $specified_dims);

    if (empty($specified_dims['girth']))
        $specified_dims['girth'] = func_girth($_pack);

    $dim_girth_xml = "<Girth>".func_units_convert(func_dim_in_centimeters(@$specified_dims['girth']), 'cm', 'in', 2)."</Girth>";

    $dim_width = func_units_convert(func_dim_in_centimeters($_pack['width']), 'cm', 'in', 2);
    $dim_length = func_units_convert(func_dim_in_centimeters($_pack['length']), 'cm', 'in', 2);
    $dim_height = func_units_convert(func_dim_in_centimeters($_pack['height']), 'cm', 'in', 2);

    $dim_xml = "<Width>$dim_width</Width><Length>$dim_length</Length><Height>$dim_height</Height>";
    $dims = array($dim_length, $dim_width, $dim_height);
    $package_size = func_USPS_get_xml_package_size($dims);
    $query = $CommercialFlagXMl = $CommercialPlusFlagXML = '';

    if ($intl_use) {
        $container_intl = (empty($_usps_options['param10']) || 'None' === $_usps_options['param10']) ? 'RECTANGULAR' : strtoupper($_usps_options['param10']);
        if ($_usps_options['use_commercial_rate'] == 'Y') {
            $CommercialFlagXMl = "<CommercialFlag>Y</CommercialFlag>";
        }
        if ($_usps_options['use_commercial_plus_rate'] == 'Y') {
            $CommercialPlusFlagXML = "<CommercialPlusFlag>Y</CommercialPlusFlag>";
        }

        $value_of_content_xml = func_USPS_get_xml_value_of_content($_usps_options['param07'], $_pack['price']);
        $origin_zip_intl = func_check_zip($ZO, 'US', false) ? "<OriginZip>$ZO</OriginZip>" : "";
        $query = <<<EOT
<IntlRateV2Request USERID="$_USPS_username">
<Revision>2</Revision>
<Package ID="0">
<Pounds>0</Pounds>
<Ounces>$ounces</Ounces>
<MailType>$_usps_options[mailtype]</MailType>
<GXG>
<POBoxFlag>N</POBoxFlag>
<GiftFlag>N</GiftFlag>
</GXG>
$value_of_content_xml
<Country>$dst_country</Country>
<Container>$container_intl</Container>
<Size>$package_size</Size>
$dim_xml
$dim_girth_xml
$origin_zip_intl
$CommercialFlagXMl
$CommercialPlusFlagXML
</Package>
</IntlRateV2Request>
EOT;
    } elseif ($is_first_class) {
        $first_class_mail_type = empty($_usps_options['param05']) ? 'LETTER' : $_usps_options['param05'];
        $common_dim_girth_xml = func_USPS_get_xml_girth($package_size, '', $dim_girth_xml);

        if ($first_class_mail_type == 'PACKAGE SERVICE') {
            $allowed_services = array_intersect(
                array('FIRST CLASS COMMERCIAL', 'FIRST CLASS HFP COMMERCIAL'),
                $_usps_options['selected_services']
            );
        } else {
            $allowed_services = array_intersect(
                array('FIRST CLASS', 'FIRST CLASS METERED'),
                $_usps_options['selected_services']
            );
        }

        if (!empty($allowed_services)) {

            $service_packages = '';
            $k = 0;
            $global_machinable = $machinable;
            $global_first_class_mail_type = $first_class_mail_type;

            $exeption_replaces['FIRST CLASS METERED']['FLAT']['FirstClassMailType'] = 'LETTER';
            $exeption_replaces['FIRST CLASS METERED']['PARCEL']['FirstClassMailType'] = 'LETTER';
            $exeption_replaces['FIRST CLASS METERED']['POSTCARD']['FirstClassMailType'] = 'LETTER';
            $exeption_replaces['FIRST CLASS METERED']['PACKAGE SERVICE']['FirstClassMailType'] = 'LETTER';
            // Force enable Machinable for FIRST CLASS-FLAT combination according to https://www.usps.com/business/web-tools-apis/rate-calculator-api.htm#_Toc378922332 to avoid error like 'The requested Mail Service is not available for the specified request attributes'
            // Can be removed in future if machinable=false will work correctly for this combination
            $exeption_replaces['FIRST CLASS']['FLAT']['Machinable'] = 'true';

            foreach ($allowed_services as $first_class_service) {
                if (isset($exeption_replaces[$first_class_service][$first_class_mail_type]['Machinable'])) {
                    $machinable = $exeption_replaces[$first_class_service][$first_class_mail_type]['Machinable'];
                } else {
                    $machinable = $global_machinable;
                }

                if (isset($exeption_replaces[$first_class_service][$first_class_mail_type]['FirstClassMailType'])) {
                    $first_class_mail_type = $exeption_replaces[$first_class_service][$first_class_mail_type]['FirstClassMailType'];
                } else {
                    $first_class_mail_type = $global_first_class_mail_type;
                }

                $service_packages .=<<<EOT
<Package ID="$k">
<Service>$first_class_service</Service>
<FirstClassMailType>$first_class_mail_type</FirstClassMailType>
<ZipOrigination>$ZO</ZipOrigination>
<ZipDestination>$ZD</ZipDestination>
<Pounds>0</Pounds>
<Ounces>$ounces</Ounces>
<Container></Container>
<Size>$package_size</Size>
$dim_xml
$common_dim_girth_xml
<Machinable>$machinable</Machinable>
</Package>\n
EOT;
                $k++;
            }
            $query =<<<EOT
    <RateV4Request USERID="$_USPS_username">
    <Revision>2</Revision>
    $service_packages
    </RateV4Request>
EOT;
        }
    } else {

        $container_express = (empty($_usps_options['param03']) || 'None' === $_usps_options['param03']) ? '' :  strtoupper($_usps_options['param03']);
        $container_priority = (empty($_usps_options['param04']) || 'None' === $_usps_options['param04']) ? '' :  strtoupper($_usps_options['param04']);

        // Force change size to LARGE for ONLINE/PLUS service to work 'filter out Flat Rate options' feature to work properly
        $package_size = func_USPS_correct_xml_package_size($package_size, $_usps_options['selected_services'], $dims, $default_domestic_service);

        // Define 'Size' XML parametr
        if (stripos($container_express, 'RECTANGULAR') !== FALSE) {
            $package_size_express = 'LARGE';
        } elseif (func_USPS_is_predefined_container($container_express)) {
            $package_size_express = 'REGULAR';
        } else {
            $package_size_express = $package_size;
        }

        if ($package_size_express == 'LARGE') {
            $container_express = 'RECTANGULAR';
        } 
            
        // Define 'Size' XML parametr
        if (stripos($container_priority, 'RECTANGULAR') !== FALSE) {
            $package_size_priority = 'LARGE';
        } else if (func_USPS_is_predefined_container($container_priority)) {
            $package_size_priority = 'REGULAR';
        } else {
            $package_size_priority = $package_size;
        }

        // https://www.usps.com/business/web-tools-apis/rate-calculator-api.htm#OLE_LINK8
        // Note: RECTANGULAR or NONRECTANGULAR must be indicated when <Size>LARGE</Size>.
        if ($package_size_priority == 'LARGE') {
            $container_priority = 'RECTANGULAR';
        } 

        $dim_girth_xml_express = func_USPS_get_xml_girth($package_size_express, $container_express, $dim_girth_xml);
        $dim_girth_xml_priority = func_USPS_get_xml_girth($package_size_priority, $container_priority, $dim_girth_xml);
        $common_dim_girth_xml = func_USPS_get_xml_girth($package_size, '', $dim_girth_xml);
        $services = array(
            'STANDARD POST' => array('container' => '', 'machinable' => "<Machinable>$machinable</Machinable>", 'size' => $package_size, '_girth' => $common_dim_girth_xml),
        );
        $services['ONLINE'] = $services['PLUS'] = $services['LIBRARY'] = $services['MEDIA'] = $services['STANDARD POST'];
        $services['STANDARD POST']['ground_only'] = "<GroundOnly>$_usps_options[ground_only]</GroundOnly>";

        $express_services = array(
            'PRIORITY MAIL EXPRESS',
            'PRIORITY MAIL EXPRESS COMMERCIAL',
            'PRIORITY MAIL EXPRESS SH',
            'PRIORITY MAIL EXPRESS SH COMMERCIAL',
            'PRIORITY MAIL EXPRESS HFP',
            'PRIORITY MAIL EXPRESS HFP COMMERCIAL',
        );

        $express_services_value = array(
            'container' => $container_express,
            'machinable' => '',
            'size' => $package_size_express,
            '_girth' => $dim_girth_xml_express
        );

        foreach ($express_services as $_svc) {
            $services[$_svc] = $express_services_value;
        }

        $priority_services = array(
            'PRIORITY',
            'PRIORITY HFP COMMERCIAL',
        );

        $priority_services_value = array(
            'container' => $container_priority,
            'machinable' => '',
            'size' => $package_size_priority,
            '_girth' => $dim_girth_xml_priority
        );

        foreach ($priority_services as $_svc) {
            $services[$_svc] = $priority_services_value;
        }
        
        if (stripos($container_priority, 'REGIONALRATEBOX') !== FALSE)
            unset($services['PRIORITY']);

        // Remove services unselected by admin
        $services = array_intersect_key($services, array_flip($_usps_options['selected_services']));

        $service_packages = '';
        $k = 20;
        foreach ($services as $service => $service_data) {
            $service_data['ground_only'] = isset($service_data['ground_only']) ? $service_data['ground_only'] : '';
            $service_packages .=<<<EOT
<Package ID="$k">
<Service>$service</Service>
<ZipOrigination>$ZO</ZipOrigination>
<ZipDestination>$ZD</ZipDestination>
<Pounds>0</Pounds>
<Ounces>$ounces</Ounces>
<Container>$service_data[container]</Container>
<Size>$service_data[size]</Size>
$dim_xml
$service_data[_girth]
$service_data[ground_only]
$service_data[machinable]
</Package>\n\n
EOT;
            $k++;
        }

        if (!empty($service_packages)) {
            $query =<<<EOT
<RateV4Request USERID="$_USPS_username">
<Revision>2</Revision>
$service_packages
</RateV4Request>
EOT;
        }
    }

    return $query;
} // }}}

function func_USPS_debug($query, $result, $debug)
{
    if ($debug == 'Y') {
        // Display debug info
        print "<h1>USPS Debug Information</h1>";
        if ($query) {
            $query = preg_replace("/(USERID[=][^ \t<>]*)/i", "USERID=\"xxx\"", $query);
            $query = preg_replace("/(PASSWORD[=][^ \t<>]*)/i", "PASSWORD=\"xxx\"", $query);
            print "<h2>USPS Request</h2>";
            print "<pre>".htmlspecialchars($query)."</pre>";
            print "<h2>USPS Response</h2>";
            $result = preg_replace("/(>)(<[^\/])/", "\\1\n\\2", $result);
            $result = preg_replace("/(<\/[^>]+>)([^\n])/", "\\1\n\\2", $result);
            print "<pre>".htmlspecialchars($result)."</pre>";

        } else {
            print "It seems, you have forgotten to fill in an USPS account information.";
        }
    }

    if (defined('DEVELOPMENT_MODE'))
        func_USPS_log_debug_information($query, $result);

    return true;
}

?>
