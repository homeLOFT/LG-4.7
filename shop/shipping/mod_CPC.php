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
 * Canada Post shipping library
 * (only from Canada)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v66 (xcart_4_7_0), 2015-02-17 23:56:28, mod_CPC.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 *
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_load('xml','http');

function func_shipper_CPC($items, $userinfo, $orig_address, $debug, $cart)
{
    global $config, $sql_tbl;
    global $allowed_shipping_methods;
    global $shipping_calc_service, $intershipper_error, $intershipper_rates;

    if ($orig_address['country'] != 'CA' || empty($config['Shipping']['CPC_username']) || empty($config['Shipping']['CPC_password']))
        return;

    $shipping_data = $cpc_methods = $rates = array();
    foreach ($allowed_shipping_methods as $v) {
        if ($v['code'] == 'CPC') {
            $cpc_methods[] = $v;
        }
    }

    if (empty($cpc_methods)) 
        return;


    $oCPCOptions = new XC_CPC_Options();

    $shipping_data['dest_address'] = array(
        'country' => $userinfo['s_country'],
        'zipcode' => $userinfo['s_zipcode'],
    );

    if (
        $config['General']['zip4_support'] == 'Y' 
        && !empty($userinfo['s_zip4'])
    ) {
        $shipping_data['dest_address']['zip4'] = $userinfo['s_zip4'];
    }


    $shipping_data['orig_address'] = array(
        'country' => $orig_address['country'],
        'zipcode' => $orig_address['zipcode'],
    );

    x_load('http','xml');
    

    // Get specified_dims
    $specified_dims = array();
    list($specified_dims['length'], $specified_dims['width'], $specified_dims['height'], $specified_dims['girth']) = $oCPCOptions->getDims();
    $specified_dims = array_filter($specified_dims);

    $package_limits = func_get_package_limits_CPC($shipping_data['dest_address']['country'], $debug);

    $objGetRates = new XC_CPC_GetRates( func_CPC_get_request_settings($debug) );
    $used_packs = array();
    foreach ($package_limits as $pack_limit_key => $package_limit) {

        $cpc_rates = array();

        // Get packages
        $packages = func_get_packages($items, $package_limit, ($oCPCOptions->useMultiplePackages() == 'Y') ? 100 : 1);

        if (empty($packages) || !is_array($packages)) 
            continue;

        foreach ($packages as $pack_num => $pack) {
            $_pack = $pack;
            if ( ! $oCPCOptions->isOptionsDependsOnPrice()) {
                unset($_pack['price']);
            }
            $pack_key = md5(serialize($_pack));

            if (isset($used_packs[$pack_key])) {
                $cpc_rates[$pack_num] = $used_packs[$pack_key];
                continue;
            }

            if ($oCPCOptions->useMaximumDimensions() == 'Y')
                $pack = func_array_merge($pack, $specified_dims);

            foreach(array('length', 'width', 'height', 'girth') as $dim) {
                if (!empty($pack[$dim]))
                    $pack[$dim] = round(func_dim_in_centimeters($pack[$dim]), 1);
            }

            if (!empty($pack['weight']))
                $pack['weight'] = func_units_convert(func_weight_in_grams($pack['weight']), 'g', 'kg', 3); // The weight of the parcel in kilograms. 

            $shipping_data['pack'] = $pack;

            list($parsed_rates, $new_methods) = func_CPC_find_methods($objGetRates->getRates($shipping_data), $cpc_methods);

            if (!empty($new_methods)) {
                // According to https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/getrates/default.jsf 'international' element

                $intl_use = ($shipping_data['dest_address']['country'] != $shipping_data['orig_address']['country']);
                func_CPC_add_new_methods($new_methods, $intl_use);
            }

            if (empty($parsed_rates)) {
                // Do not calculate all other packs from pack_set if any Pack from the pack_set cannot be calculated
                $cpc_rates = array();
                break; 
            }

            $cpc_rates[$pack_num] = $parsed_rates;
            $cpc_rates[$pack_num] = func_normalize_shipping_rates($cpc_rates[$pack_num], 'CPC');
            $used_packs[$pack_key] = $cpc_rates[$pack_num];
        } // foreach $packages

        $rates = func_array_merge($rates, func_intersect_rates($cpc_rates));
        $rates = func_shipping_min_rates($rates);
    } // foreach $package_limits

    $intershipper_rates = func_array_merge($intershipper_rates, $rates);
}

/**
 * Return package limits for Canada POST
 */
function func_get_package_limits_CPC($country = '', $debug = 'N')
{
    global $config;

    $save_result_in_cache = TRUE;

    $cpc_request_settings = func_CPC_get_request_settings($debug);
    $oCPCOptions = new XC_CPC_Options(array('param06', 'param08'));

    $md5_args = $country . md5(serialize(array(
        $oCPCOptions->getUsedParams(), 
        $cpc_request_settings, 
        $config['General']['dimensions_symbol_cm'], // From func_correct_dimensions
        $config['General']['weight_symbol_grams'], // From func_correct_dimensions
    )));

    if ($data = func_get_cache_func($md5_args, 'get_package_limits_CPC')) {
        return $data['data'];
    }

    $dim = array();
    list($dim['length'], $dim['width'], $dim['height'], $dim['girth']) = $oCPCOptions->getDims();

    $dimensions_array = array();
    foreach (array('width', 'height', 'length', 'girth') as $_dim) {
        if (!empty($dim[$_dim])) {
            $dimensions_array[$_dim] = $dim[$_dim]; // Must be in inch to work with func_correct_dimensions
        }
    }

    $max_weight = $oCPCOptions->getMaxWeight();
    if ($max_weight > 0) {
        $dimensions_array['weight'] = $max_weight; // Must be in inch to work with func_correct_dimensions
    }


    $objDiscoverServices = new XC_CPC_DiscoverServices($cpc_request_settings, $country);

    $avalaible_services = $objDiscoverServices->getAvalaibleServices();
    if (empty($avalaible_services)) {
        $avalaible_services = array();
        $save_result_in_cache = FALSE;
    }

    $package_limits = $uniq_limit_hashes = array();
    $objGetService = new XC_CPC_GetService($cpc_request_settings);

    foreach ($avalaible_services as $service) {

        $service_limits = $objGetService->getServiceLimits($service);// Results are in g and cm
        if (empty($service_limits)) {
            $save_result_in_cache = FALSE;
            continue;
        }

        // Convert from CPC responce to lbs and in
        if (!empty($service_limits['weight']))
            $service_limits['weight'] = func_units_convert($service_limits['weight'], 'g', 'lbs', 64);

        foreach (array('width', 'height', 'length', 'girth') as $_dim) {
            if (!empty($service_limits[$_dim]))
                $service_limits[$_dim] = func_units_convert($service_limits[$_dim], 'cm', 'in', 64);
        }

        // Overwrite limits from CPC settings in admin area
        foreach (array('width', 'height', 'length', 'weight', 'girth') as $_dim) {
            if (
                !empty($dimensions_array[$_dim])
                && !empty($service_limits[$_dim])
            ) {
                $service_limits[$_dim] = min($service_limits[$_dim], $dimensions_array[$_dim]);
            } elseif (!empty($dimensions_array[$_dim])) {
                $service_limits[$_dim] = $dimensions_array[$_dim];
            }
        }

        $hash = serialize($service_limits);

        if (empty($uniq_limit_hashes[$hash]))
            $package_limits[] = $service_limits;

        $uniq_limit_hashes[$hash] = 1;
    }


    foreach($package_limits as $k => $v) {
        $package_limits[$k] = func_correct_dimensions($v);
    }

    if ($save_result_in_cache) {
        func_save_cache_func($package_limits, $md5_args, 'get_package_limits_CPC');
    }
    return $package_limits;
}

/**
 * Check if Canada POST allows box
 */
function func_check_limits_CPC($box)
{
    global $sql_tbl;

    $avail = false;
    $box['weight'] = isset($box['weight']) ? $box['weight'] : 0;

    foreach (array('CA', 'US', ' ') as $country) {
        $pack_limit = func_get_package_limits_CPC($country);
        $avail = $avail || (func_check_box_dimensions($box, $pack_limit) && $pack_limit['weight'] > $box['weight']);
    }
    return $avail;
}

function func_CPC_add_new_methods($new_methods, $intl_use) { // {{{
    static $added_methods = array();

    if (empty($new_methods))
        return FALSE;

    $oCPCOptions = new XC_CPC_Options();

    foreach($new_methods as $m) {
        $method_key = md5(serialize($m));
        if (isset($added_methods[$method_key]))
            continue;
        else
            $added_methods[$method_key] = 1;

        // Add new shipping method
        $_params = array();
        $_params['destination'] = ($intl_use ? 'I' : 'L');
        $_params['subcode'] = $m['service-code'];

        if ($oCPCOptions->isNewMethodEnabled()) {
            $_params['active'] = 'Y';
        }

        func_add_new_smethod('Canada Post ' . $m['service-name'], 'CPC', $_params);
    }

    return TRUE;
} // }}}

function func_CPC_find_methods($rates, $cpc_methods) { // {{{
    if (empty($rates) || empty($cpc_methods))
        return array(array(), array());

    $founded_rates = $new_methods = array();
    foreach ($rates as $rate) {
        $is_found = false;

        // Try to find known method
        foreach ($cpc_methods as $sm) {
            if ($rate['service-code'] == $sm['subcode']) {
                $is_found = true;

                $founded_rate = array(
                    'methodid'           => $sm['subcode'],
                    'rate'               => $rate['price'],
                );

                if (!empty($rate['expected-transit-time']))
                    $founded_rate['shipping_time'] = $rate['expected-transit-time'];

                $founded_rates[] = $founded_rate;

                break;
            }
        }

        if (!$is_found) {
            $new_methods[] = $rate;
        }

    }

    return array($founded_rates, $new_methods);
} // }}}

function func_CPC_get_request_settings($debug = 'N') { // {{{
    global $config;

    static $res = array();

    if (!empty($res))
        return $res;

    $res['user'] = $config['Shipping']['CPC_username'];
    $res['password'] = $config['Shipping']['CPC_password'];
    $res['is_test_mode'] = $config['Shipping']['CPC_testmode'] == 'Y';

    $res['accept_language'] = ($config['default_admin_language'] == 'fr' ? 'fr-CA' : 'en-CA');

    $res['debug'] = $debug;

    return $res;

} // }}}

abstract class XC_CPC_Request {
    protected $baseUrl;
    protected $url;
    protected $httpMethod = 'GET';
    protected $ContentType = '';
    protected $printDebug;

    private $Accept = 'application/vnd.cpc.ship.rate-v2+xml';
    private $Acceptlanguage;
    private $password;
    private $user;

    public function __construct($cpc_request_settings, $param = '') { // {{{
        if (!empty($cpc_request_settings['is_test_mode']))
            $this->baseUrl = 'https://ct.soa-gw.canadapost.ca';
        else
            $this->baseUrl = 'https://soa-gw.canadapost.ca';

        $this->user = $cpc_request_settings['user'];
        $this->password = $cpc_request_settings['password'];
        $this->Acceptlanguage = $cpc_request_settings['accept_language'];
        $this->printDebug = ($cpc_request_settings['debug'] == 'Y');
    } // }}}

    protected function makeRequest($url = '', $accept = '', $data = '') { // {{{
        $headers = array(
            'Authorization' => 'Basic ' . base64_encode($this->user . ':' . $this->password),
            'Accept' => empty($accept) ? $this->Accept : $accept,
            'Accept-language' => $this->Acceptlanguage,
        );

        $url = empty($url) ? $this->url : $url;
        list($a, $result) = func_https_request(
            $this->httpMethod, 
            $url,
            $data, // data
            '&', // join
            '', // cookie
            $this->ContentType,
            '', // referer
            '', // cert
            '', // kcert
            $headers
        );


        if (defined('CPC_DEBUG') || $this->printDebug) {
            $headers['Authorization'] = '**removed**';
            $data = preg_replace('%<customer-number>.*</customer-number>%', '<customer-number>**removed**</customer-number>', $data);
            $data = preg_replace('%<contract-id>.*</contract-id>%', '<contract-id>**removed**</contract-id>', $data);

            if ($this->printDebug) {
                // Display debug info
                $class = defined('X_PHP530_COMPAT') ? get_called_class().' ' : '';

                print "<h2>{$class}{$this->httpMethod} Request to $url &nbsp;&nbsp;&nbsp;&nbsp;{$this->ContentType}</h2>";
                print "<pre>".htmlspecialchars($data)."</pre>";
                print "<h2>Canada Post Response</h2>";
                $result = preg_replace("/(>)(<[^\/])/", "\\1\n\\2", $result);
                $result = preg_replace("/(<\/[^>]+>)([^\n])/", "\\1\n\\2", $result);
                print "<pre>".htmlspecialchars(($result))."</pre>";
            }

            if (defined('CPC_DEBUG')) {
                x_log_add('cpc_requests', print_r($headers, true) .  $this->httpMethod . "\n" . $url . "\n" . $data . "\n" . $this->ContentType . "\n" . $a . "\n" . $result);
            }
        }

        assert('!empty($result) && preg_match("/HTTP\/.*\s*200\s*OK/i", $a) /* '.__METHOD__.': Some errors with HTTP request to CPC */');

        $is_success =  preg_match("/HTTP\/.*\s*200\s*OK/i", $a);

        // Parse XML reply
        $parse_error = false;
        $options = array(
            'XML_OPTION_CASE_FOLDING' => 1,
            'XML_OPTION_TARGET_ENCODING' => 'UTF-8'
        );

        $parsed = func_xml_parse($result, $parse_error, $options);
        $err_codes = -1;
        $err_descr = 'Unknown Canada Post module error';
        if (
            !$is_success
            || empty($parsed)
        ) {
            if (!empty($parsed)) {
                $messages = func_array_path($parsed, 'MESSAGES/#/MESSAGE');
                if (!empty($messages)) {
                    $err_codes = $err_descr = '';
                    foreach ($messages as $message) {
                        $err_codes .= func_array_path($message, '#/CODE/0/#') . "\n";
                        $err_descr .= func_array_path($message, '#/DESCRIPTION/0/#') . "\n";
                    }
                }

                if (empty($err_codes)) {
                    $err_codes = -1;
                    x_log_flag('log_shipping_errors', 'SHIPPING', "Unknown Canada Post module error1: " . print_r($a, true) . print_r($result, true), true);
                } else {
                    x_log_flag('log_shipping_errors', 'SHIPPING', "Canada Post module error: " . $err_codes . "\n" . $err_descr, true);
                }

            } else {
                x_log_flag('log_shipping_errors', 'SHIPPING', "Unknown Canada Post module error2: " . print_r($a, true) . print_r($result, true), true);
            }

            $parsed = '';
        }

        return $parsed;
    } // }}}
}

class XC_CPC_DiscoverServices extends XC_CPC_Request {

    public function __construct($cpc_request_settings, $country = '') { // {{{
        // Set baseUrl
        parent::__construct($cpc_request_settings, $country);

        if (empty($country))
            $this->url = $this->baseUrl . '/rs/ship/service';
        else
            $this->url = $this->baseUrl . '/rs/ship/service?country=' . $country;
    } // }}}

    public function getAvalaibleServices() { // {{{
        $parsed = $this->makeRequest();
        $return_services = array();

        $services = func_array_path($parsed, 'SERVICES/#/SERVICE');
        if (empty($parsed) || empty($services)) {
            assert('FALSE /* '.__METHOD__.': Empty SERVICES array for for Discover Services */');
            return array();
        }

        foreach ($services as $service) {
            $return_services[] = array(
                'url' => func_array_path($service, '#/LINK/0/@/HREF'),
                'accept' => func_array_path($service, '#/LINK/0/@/MEDIA-TYPE'),
            );
        }

        return $return_services;
    } // }}}
}

class XC_CPC_GetService extends XC_CPC_Request {

    public function getServiceLimits($service) { // {{{
        $service_descr = $this->makeRequest($service['url'], $service['accept']);

        $parsed = func_array_path($service_descr, 'SERVICE/#/RESTRICTIONS/0/#');
        if (empty($service_descr) || empty($parsed)) {
            assert('FALSE /* '.__METHOD__.': Empty SERVICE array for for Get Service */');
            return array();
        }
       
        $limits['weight'] = func_array_path($parsed, 'WEIGHT-RESTRICTION/0/@/MAX');

        $limits['length'] = func_array_path($parsed, 'DIMENSIONAL-RESTRICTIONS/0/#/LENGTH/0/@/MAX');
        $limits['height'] = func_array_path($parsed, 'DIMENSIONAL-RESTRICTIONS/0/#/HEIGHT/0/@/MAX');
        $limits['width'] = func_array_path($parsed, 'DIMENSIONAL-RESTRICTIONS/0/#/WIDTH/0/@/MAX');
        $limits['girth'] = func_array_path($parsed, 'DIMENSIONAL-RESTRICTIONS/0/LENGTH-PLUS-GIRTH-MAX/0/#');

        $limits = array_filter($limits);

        return $limits;
    } // }}}

}

class XC_CPC_GetRates extends XC_CPC_Request {
    private $connectionHash;
    private $oCPCOptions;

    public function __construct($cpc_request_settings) { // {{{
        // Set baseUrl
        parent::__construct($cpc_request_settings);
        $this->url = $this->baseUrl . '/rs/ship/price';

        $this->ContentType = $this->Accept = 'application/vnd.cpc.ship.rate-v2+xml';
        $this->httpMethod = 'POST';
        $this->oCPCOptions = new XC_CPC_Options();
        $this->connectionHash = md5(serialize($cpc_request_settings));
    } // }}}

    public function getRates($in_data) { // {{{

        $data = $this->prepareQuery($in_data);

        $md5_request = md5($data . $this->connectionHash);
        if (func_is_shipping_result_in_cache($md5_request) && !$this->printDebug) {
            $rates = func_get_shipping_result_from_cache($md5_request);
        } else {
            $parsed = $this->makeRequest($this->url, $this->Accept, $data);
            $rates = $this->parseResponse($parsed);
            if (!empty($rates)) {
                func_save_shipping_result_to_cache($md5_request, $rates);
            }
        }

        return $rates;
    } // }}}

    private function getOptionsXML($price) { // {{{
        $options = $this->oCPCOptions->getOptions($price);

        if (empty($options))
            return '';

        $query = "<options>\n";
        foreach ($options as $option) {
            $query .= "\t<option>\n";
            $query .= "\t\t<option-code>{$option['option-code']}</option-code>\n";
            if (!empty($option['option-amount'])) {
                $query .= "\t\t<option-amount>{$option['option-amount']}</option-amount>\n";
            }
            $query .= "\t</option>\n";
        }
        $query .= "\t</options>\n";

        return $query;
    } // }}}

    private function getDimensionsXML($pack) { // {{{

        $dims = array('width'=>1, 'length'=>1, 'height'=>1);
        $pack = array_intersect_key($pack, $dims);
        
        // length and width and height are required
        if (count(array_filter($pack)) != 3) {
            return '';
        }

        $query = '<dimensions>';
        foreach ($pack as $name => $value) {
            $query .= "<$name>$value</$name>";
        }
        $query .= '</dimensions>';

        return $query;
    } // }}}

    private function prepareCAzipcode($zipcode) { // {{{
        $zipcode = strtoupper($zipcode);
        $zipcode = preg_replace('/\s/', '', $zipcode);
        return $zipcode;
    } // }}}

    private function getDestinationXML($dest_address) { // {{{

        switch ($dest_address['country']) {
            case 'CA': // domestic
                $query = "<domestic><postal-code>" . $this->prepareCAzipcode($dest_address['zipcode']) . "</postal-code></domestic>";
                break;
            case 'US': // united-states
                if (!empty($dest_address['zip4']))
                    $zipcode = $dest_address['zipcode'] . '-' . $dest_address['zip4'];
                else
                    $zipcode = $dest_address['zipcode'];

                $query = "<united-states><zip-code>$zipcode</zip-code></united-states>";
                break;
            default:
                $query = "<international><country-code>$dest_address[country]</country-code></international>";
        }

        return $query;
    } // }}}

    private function prepareQuery($in_data) { // {{{

        $contract_id_xml = $quote_type_xml = '';
        if ($this->oCPCOptions->getContractId())
            $contract_id_xml = '<contract-id>' . $this->oCPCOptions->getContractId() . '</contract-id>';

        if ($this->oCPCOptions->getQuoteType())
            $quote_type_xml = '<quote-type>' . $this->oCPCOptions->getQuoteType() . '</quote-type>';

        $optionsXML = $this->getOptionsXML($in_data['pack']['price']);

        // weight is required
        $weight = empty($in_data['pack']['weight']) ? '0.1' : $in_data['pack']['weight'];
        $dimensions = $this->getDimensionsXML($in_data['pack']);
        $destination = $this->getDestinationXML($in_data['dest_address']);
        $origin_postal_code = $this->prepareCAzipcode($in_data['orig_address']['zipcode']);

        $query = <<<EOT
<mailing-scenario xmlns="http://www.canadapost.ca/ws/ship/rate-v2">
    <customer-number>{$this->oCPCOptions->getCustomerNumber()}</customer-number>
    {$contract_id_xml}
    {$quote_type_xml}
    {$optionsXML}
    <parcel-characteristics>
        <weight>{$weight}</weight>
        {$dimensions}
    </parcel-characteristics>
    <origin-postal-code>{$origin_postal_code}</origin-postal-code>
    <destination>
        {$destination}
    </destination>
</mailing-scenario>
EOT;

        return $query;
    } // }}}

    private function parseResponse($parsed) { // {{{
        $rates = func_array_path($parsed, 'PRICE-QUOTES/#/PRICE-QUOTE');
        if (empty($rates) || empty($parsed)) {
            assert('FALSE /* '.__METHOD__.': Empty PRICE-QUOTES array for for Get Rates */');
            return array();
        }

        $return_rates = array();

        foreach ($rates as $rate) {
            $parsed_rate = array();
            $parsed_rate['service-code'] = func_array_path($rate, '#/SERVICE-CODE/0/#');
            $parsed_rate['service-name'] = func_array_path($rate, '#/SERVICE-NAME/0/#');
            $parsed_rate['price'] = func_array_path($rate, '#/PRICE-DETAILS/DUE/0/#');
            $parsed_rate['service-link'] = array(
                'url' => func_array_path($rate, '#/SERVICE-LINK/0/@/HREF'),
                'accept' => func_array_path($rate, '#/SERVICE-LINK/0/@/MEDIA-TYPE'),
            );
            $parsed_rate['expected-transit-time'] = intval(func_array_path($rate, '#/SERVICE-STANDARD/EXPECTED-TRANSIT-TIME/0/#'));

            $return_rates[] = $parsed_rate;
        }

        return $return_rates;
    } // }}}

}

class XC_CPC_Options {
    private $params;
    private $fields;

    public function __construct($fields = array()) { // {{{
        $this->params = $this->getAllParams();

        if (empty($fields))
            $fields = $this->params;
        else 
            $fields = array_flip($fields);

        $this->fields = $fields;
    } // }}}

    public function getUsedParams() { // {{{
        return array_intersect_key($this->params, $this->fields);
    } // }}}

    public function getDims() { // param06 {{{
        assert('isset($this->fields["param06"]) /* '.__METHOD__.': getHashKey will return key without param06 value */');

        $dim = array();
        list($dim['length'], $dim['width'], $dim['height']) = explode(':', $this->params['param06']);
        $dim = array_map('doubleval', $dim);

        if (count(array_filter($dim)) == 3) {
            $dim['girth'] = func_girth($dim);
        } else {
            $dim['girth'] = 0;
        }

        return array($dim['length'], $dim['width'], $dim['height'], $dim['girth']);
    } // }}}

    public function getMaxWeight() { // param08 {{{
        assert('isset($this->fields["param08"]) /* '.__METHOD__.': getHashKey will return key without param08 value */');
        return doubleval($this->params['param08']);
    } // }}}

    public function getCustomerNumber() { // param03 {{{
        assert('isset($this->fields["param03"]) /* '.__METHOD__.': getHashKey will return key without param03 value */');
        return $this->params['param03'];
    } // }}}

    public function getContractId() { // param04 {{{
        assert('isset($this->fields["param04"]) /* '.__METHOD__.': getHashKey will return key without param04 value */');
        return trim($this->params['param04']);
    } // }}}

    public function getQuoteType() { // param05 {{{
        assert('isset($this->fields["param05"]) /* '.__METHOD__.': getHashKey will return key without param05 value */');
        return $this->params['param05'];
    } // }}}

    public function getOptions($price) { // {{{
        assert('isset($this->fields["param00"]) /* '.__METHOD__.': getHashKey will return key without param00 value */');

        if (empty($this->params['param00']))
            $data = array();
        else
            $data = explode('|', $this->params['param00']);

        if (!empty($data)) {
            foreach ($data as $option_code) {
                $options[] = array(
                    'option-code' => $option_code,
                );
            }
        } else {
            $options = array();
        }

        $COV_price = $this->getInsuranceCoverage($price);
        if (!empty($COV_price)) {
            $options[] = array(
                'option-code' => 'COV',
                'option-amount' => round($COV_price, 2),
            );
        }

        $COD_price = $this->getCOD($price);
        if (!empty($COD_price)) {
            $options[] = array(
                'option-code' => 'COD',
                'option-amount' => round($COD_price, 2),
            );
        }
            
        return $options;
    } // }}}

    public function useMaximumDimensions() { // param09 {{{
        assert('isset($this->fields["param09"]) /* '.__METHOD__.': getHashKey will return key without param09 value */');
        return $this->params['param09'];
    } // }}}

    public function useMultiplePackages() { // param11 {{{
        assert('isset($this->fields["param11"]) /* '.__METHOD__.': getHashKey will return key without param11 value */');
        return $this->params['param11'];
    } // }}}

    public function isNewMethodEnabled() { // param01 {{{
        assert('isset($this->fields["param01"]) /* '.__METHOD__.': getHashKey will return key without param01 value */');
        return $this->params['param01'] == 'new_method_is_enabled';
    } // }}}

    public function isOptionsDependsOnPrice() { // getOptions(111) !== getOptions(0) {{{
        static $res;
        if (isset($res))
            return $res;

        $res = $this->getOptions(111) !== $this->getOptions(0);

        return $res;
    } // }}}

    private function getQualifier($value, $price) { // param07 {{{
        if ($value == 'disabled')
            return '';

        if (strpos($value, '%') === false) {
            $value_of_content = $value;
        } else {
            $value_of_content = $price * intval($value) / 100;
        }

        return $value_of_content;
    } // }}}

    private function getInsuranceCoverage($price) { // param07 {{{
        assert('isset($this->fields["param07"]) /* '.__METHOD__.': getHashKey will return key without param07 value */');
        return $this->getQualifier($this->params['param07'], $price);
    } // }}}

    private function getCOD($price) { // param02 {{{
        assert('isset($this->fields["param02"]) /* '.__METHOD__.': getHashKey will return key without param02 value */');
        return $this->getQualifier($this->params['param02'], $price);
    } // }}}

    private static function getAllParams() { // $sql_tbl[shipping_options] {{{
        global $sql_tbl;
        static $all_params = array();

        if (empty($all_params))
            $all_params = func_query_first("SELECT * FROM $sql_tbl[shipping_options] WHERE carrier='CPC'");

        return $all_params;
    } // }}}
}

// https://www.canadapost.ca/cpo/mc/business/productsservices/developers/services/rating/default.jsf

?>
