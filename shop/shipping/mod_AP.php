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
 * Australia Post shipping library
 * (only from Australia)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v52 (xcart_4_7_0), 2015-02-17 23:56:28, mod_AP.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 *
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_load('xml','http');

function func_shipper_AP($items, $userinfo, $orig_address, $debug, $cart)
{
    global $config;
    global $allowed_shipping_methods;
    global $intershipper_rates;

    if ($orig_address['country'] != 'AU' || ($config['Shipping']['AP_testmode'] != 'Y' && empty($config['Shipping']['AP_apikey'])))
        return;

    XC_AP_DiscoverServices::getInstance()->setPrintDebug($debug == 'Y');

    $shipping_data = $ap_methods = $rates = array();
    foreach ($allowed_shipping_methods as $v) {
        if ($v['code'] == 'APOST') {
            $ap_methods[] = $v;
        }
    }

    if (empty($ap_methods))
        return;

    $oAPOptions = XC_AP_Options::getInstance();

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

    // Check if delivery available to the destination country
    if (!XC_AP_DiscoverServices::getInstance()->isDeliveryAvailable($shipping_data['dest_address']['country'])) {
        return;
    }

    // Get specified_dims
    $specified_dims1 = array();
    list($specified_dims1['length'], $specified_dims1['width'], $specified_dims1['height'], $specified_dims1['girth']) = $oAPOptions->getDims();
    $specified_dims = array_filter($specified_dims1);

    $package_limits = func_get_package_limits_AP($shipping_data['dest_address']['country'], $debug);

    // Get rates instance
    $objGetRates = XC_AP_GetRates::getInstance();

    $used_packs = array();

    foreach ($package_limits as $package_limit) {

        $ap_rates = array();

        // Get packages
        $packages = func_get_packages($items, $package_limit, ($oAPOptions->useMultiplePackages() == 'Y') ? 100 : 1);

        if (empty($packages) || !is_array($packages))
            continue;

        foreach ($packages as $pack_num => $pack) {
            $_pack = $pack;

            $pack_key = md5(serialize($_pack));

            if (isset($used_packs[$pack_key])) {
                $ap_rates[$pack_num] = $used_packs[$pack_key];
                continue;
            }

            if ($oAPOptions->useMaximumDimensions() == 'Y') {
                $pack = func_array_merge($pack, $specified_dims);
            }

            foreach (array('length', 'width', 'height', 'girth') as $dim) {
                if (!empty($pack[$dim])) {
                    $pack[$dim] = round(func_dim_in_centimeters($pack[$dim]), 1);
                }
            }

            if (!empty($pack['weight'])) {
                $pack['weight'] = func_units_convert(func_weight_in_grams($pack['weight']), 'g', 'kg', 3); // The weight of the parcel in kilograms.
            }

            $shipping_data['pack'] = $pack;

            list($parsed_rates, $new_methods) = func_AP_find_methods($objGetRates->getRates($shipping_data), $ap_methods);

            if (!empty($new_methods)) {
                $intl_use = ($shipping_data['dest_address']['country'] != $shipping_data['orig_address']['country']);
                func_AP_add_new_methods($new_methods, $intl_use);
            }

            if (empty($parsed_rates)) {
                // Do not calculate all other packs from pack_set if any Pack from the pack_set cannot be calculated
                $ap_rates = array();
                break;
            }

            $ap_rates[$pack_num] = $parsed_rates;
            $ap_rates[$pack_num] = func_normalize_shipping_rates($ap_rates[$pack_num], 'APOST');

            $used_packs[$pack_key] = $ap_rates[$pack_num];
        } // foreach $packages

        $rates = func_array_merge($rates, func_intersect_rates($ap_rates));
        $rates = func_shipping_min_rates($rates);
    } // foreach $package_limits

    $intershipper_rates = func_array_merge($intershipper_rates, $rates);
}

/**
 * Return package limits for Australia POST
 */
function func_get_package_limits_AP($country = '', $debug = 'N')
{
    global $config;

    // Enable caching for func_get_package_limits_AP function
    func_register_cache_function('func_get_package_limits_AP');

    $save_result_in_cache = true;

    $oAPOptions = XC_AP_Options::getInstance();

    $md5_args = $country . md5(serialize(array(
        $oAPOptions->getAllParams(),
        $config['General']['dimensions_symbol_cm'], // From func_correct_dimensions
        $config['General']['weight_symbol_grams'], // From func_correct_dimensions
    )));

    if ($cacheData = func_get_cache_func($md5_args, 'func_get_package_limits_AP')) {
        return $cacheData['data'];
    }

    $dim = array();
    list($dim['length'], $dim['width'], $dim['height'], $dim['girth']) = $oAPOptions->getDims();

    $dimensions_array = array();
    foreach (array('width', 'height', 'length', 'girth') as $_dim) {
        if (!empty($dim[$_dim])) {
            $dimensions_array[$_dim] = $dim[$_dim]; // Must be in inch to work with func_correct_dimensions
        }
    }

    $max_weight = $oAPOptions->getMaxWeight();
    if ($max_weight > 0) {
        $dimensions_array['weight'] = $max_weight; // Must be in lbs to work with func_correct_dimensions
    }

    $objDiscoverServices = XC_AP_DiscoverServices::getInstance();

    $avalaible_services = $objDiscoverServices->getAvailableServices($country);
    if (empty($avalaible_services)) {
        $avalaible_services = array();
        $save_result_in_cache = false;
    }

    $package_limits = $uniq_limit_hashes = array();

    foreach ($avalaible_services as $service) {

        $service_limits = $objDiscoverServices->getServiceLimits($service);// Results are in g and cm
        if (empty($service_limits)) {
            $save_result_in_cache = false;
            continue;
        }

        // Convert from AP responce to lbs and in
        if (!empty($service_limits['weight']))
            $service_limits['weight'] = func_units_convert($service_limits['weight'], 'g', 'kg', 64);

        foreach (array('width', 'height', 'length', 'girth') as $_dim) {
            if (!empty($service_limits[$_dim]))
                $service_limits[$_dim] = func_units_convert($service_limits[$_dim], 'mm', 'cm', 64);
        }

        // Overwrite limits from AP settings in admin area
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

        if (empty($uniq_limit_hashes[$hash])) {
            $package_limits[] = $service_limits;
        }

        $uniq_limit_hashes[$hash] = 1;
    }


    foreach ($package_limits as $k => $v) {
        $package_limits[$k] = func_correct_dimensions($v);
    }

    if ($save_result_in_cache) {
        func_save_cache_func($package_limits, $md5_args, 'func_get_package_limits_AP');
    }

    return $package_limits;
}

/**
 * Check if Ausralia POST allows box
 */
function func_check_limits_AP($box)
{
    global $sql_tbl;

    $avail = false;
    $box['weight'] = isset($box['weight']) ? $box['weight'] : 0;

    foreach (array('CA', 'US', ' ') as $country) {
        $pack_limit = func_get_package_limits_AP($country);
        $avail = $avail || (func_check_box_dimensions($box, $pack_limit) && $pack_limit['weight'] > $box['weight']);
    }

    return $avail;
}

/**
 * Add new shipping methods
 *
 * @staticvar array $added_methods
 *
 * @param array $new_methods
 * @param string $intl_use (I / L)
 *
 * @return boolean
 */
function func_AP_add_new_methods($new_methods, $intl_use) { // {{{
    static $added_methods = array();

    if (empty($new_methods))
        return false;

    $oAPOptions = XC_AP_Options::getInstance();

    foreach ($new_methods as $m) {
        $method_key = md5(serialize($m));
        if (isset($added_methods[$method_key]))
            continue;
        else
            $added_methods[$method_key] = 1;

        // Add new shipping method
        $_params = array();
        $_params['destination'] = ($intl_use ? 'I' : 'L');
        $_params['subcode'] = $m['service-code'];

        if ($oAPOptions->isNewMethodEnabled()) {
            $_params['active'] = 'Y';
        }

        func_add_new_smethod('Australia Post ' . $m['service-name'], 'APOST', $_params);
    }

    return true;
} // }}}

/**
 * Find shipping methods
 *
 * @param array $rates
 * @param array $ap_methods
 *
 * @return array
 */
function func_AP_find_methods($rates, $ap_methods) { // {{{
    if (empty($rates) || empty($ap_methods))
        return array(array(), array());

    $founded_rates = $new_methods = array();
    foreach ($rates as $rate) {
        $is_found = false;

        // Try to find known method
        foreach ($ap_methods as $sm) {
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

class XC_AP_Options extends XC_Singleton {

    const AUS_SERVICE_OPTION_STANDARD = 'AUS_SERVICE_OPTION_STANDARD';
    const AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY = 'AUS_SERVICE_OPTION_SIGNATURE_ON_DELIVERY';

    const USER_DEFINED_BOX = 'AUS_PARCEL_TYPE_BOXED_OTH';

    const ENABLE_NEW_METHODS = 'new_method_is_enabled';

    const AP_TEST_MODE    = 'AP_TEST_MODE';
    const AP_PRODUCTION_MODE = 'AP_PRODUCTION_MODE';

    private $params;

    protected function __construct() { // {{{
        // Call parent constructor
        parent::__construct();
        // Load params
        $this->params = $this->getAllParams();
    } // }}}

    public static function getInstance() { // {{{
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    } // }}}

    public function getAPIkey() { // {{{
        return $this->params['APIKEY'];
    } // }}}

    public function getDims() { // param06 {{{

        if ($this->params['param03'] != XC_AP_Options::USER_DEFINED_BOX) {
            $this->params['param06'] = XC_AP_GetSizes::getInstance()->getPackageSizeByCode($this->params['param03']);
        }

        $tmp_dim = array();
        list($tmp_dim['length'], $tmp_dim['width'], $tmp_dim['height']) = explode(':', $this->params['param06']);
        $dim = array_map('doubleval', $tmp_dim);

        if (count(array_filter($dim)) == 3) {
            $dim['girth'] = func_girth($dim);
        } else {
            $dim['girth'] = 0;
        }

        return array($dim['length'], $dim['width'], $dim['height'], $dim['girth']);
    } // }}}

    public function getExtraCover() { // param04 {{{
        return doubleval($this->params['param04']);
    } // }}}

    public function getMode() { // {{{
        global $config;
        // One of XC_AP_Options::AP_TEST_MODE, XC_AP_Options::AP_TEST_MODE
        return $config['Shipping']['AP_testmode'] == 'Y'
               ? XC_AP_Options::AP_TEST_MODE
               : XC_AP_Options::AP_PRODUCTION_MODE;
    } // }}}

    public function getMaxWeight() { // param08 {{{
        return doubleval($this->params['param08']);
    } // }}}

    public function getServiceOption() { // param02 {{{
        return $this->params['param02'];
    } // }}}

    public function getAllParams() { // $sql_tbl[shipping_options] {{{
        global $config, $sql_tbl;
        static $all_params = array();

        if (empty($all_params)) {
            $all_params = func_query_first("SELECT * FROM $sql_tbl[shipping_options] WHERE carrier='APOST'");
            $all_params['APIKEY'] = $config['Shipping']['AP_apikey'];
            $all_params['MODE'] = $this->getMode();
        }

        return $all_params;
    } // }}}

    public function isNewMethodEnabled() { // param01 {{{
        return $this->params['param01'] == XC_AP_Options::ENABLE_NEW_METHODS;
    } // }}}

    public function useMaximumDimensions() { // param09 {{{
        return ($this->params['param03'] == XC_AP_Options::USER_DEFINED_BOX) ? $this->params['param09'] : 'N';
    } // }}}

    public function useMultiplePackages() { // param11 {{{
        return $this->params['param11'];
    } // }}}

    public function isConfigured() { // {{{
        $apikey = $this->getAPIkey();
        $mode = $this->getMode();

        return (XC_AP_Options::AP_PRODUCTION_MODE == $mode && !empty($apikey))
               || XC_AP_Options::AP_TEST_MODE == $mode;
    } // }}}
}

class XC_AP_Request extends XC_Singleton {

    const SHIPPING_PROVIDER_NAME = 'Australia Post';

    const METHOD_PREFIX = 'getRequestData';
    const DOMESTIC_COUNTRY_CODE = 'AU';

    protected $baseUrl;
    protected $url;
    protected $data;
    protected $httpMethod = 'GET';
    protected $ContentType = '';

    protected $printDebug;

    protected $baseCacheKey;
    protected $workCacheKey;

    protected $shippingOptions;

    protected $mode;

    private $baseTestUrl = 'https://test.npe.auspost.com.au';
    private $baseProductionUrl = 'https://auspost.com.au';

    private $APIkey;

    // Australia Post non-production API key (use in test mode only)
    private $testApiKey = '28744ed5982391881611cca6cf5c2409';

    protected function __construct() { // {{{
        // Call parent constructor
        parent::__construct();
        // Set API key
        $this->APIkey = XC_AP_Options::getInstance()->getAPIkey();
        // Load configuration options from DB
        $this->shippingOptions = XC_AP_Options::getInstance()->getAllParams();
        // Set Cache Keys
        $this->workCacheKey = $this->baseCacheKey = md5(serialize($this->shippingOptions));
        // Set proper working mode
        $this->setMode(XC_AP_Options::getInstance()->getMode());
        // Enable caching for func_make_request_AP function
        func_register_cache_function('func_make_request_AP', array('class' => __CLASS__));
    } // }}}

    public static function getInstance() { // {{{
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    } // }}}

    /**
     * Set mode (XC_AP_Options::AP_TEST_MODE,
     *           XC_AP_Options::AP_PRODUCTION_MODE)
     *
     * @param const $mode
     */
    public function setMode($mode = XC_AP_Options::AP_TEST_MODE) { // {{{
        switch ($mode) {
            case XC_AP_Options::AP_TEST_MODE: $this->baseUrl = $this->baseTestUrl;
                $this->APIkey = $this->testApiKey;
                break;
            case XC_AP_Options::AP_PRODUCTION_MODE: $this->baseUrl = $this->baseProductionUrl;
                break;
        }
        $this->mode = $mode;
    } // }}}

    /**
     * Set print debug flag
     *
     * @param boolean $boolean
     */
    public function setPrintDebug($boolean) { // {{{
        $this->printDebug = $boolean;
    } // }}}

    protected function prepareRequestData($requestData) { // {{{

        if (is_array($requestData)) {
            foreach ($requestData as $k => $v) {
                $requestData[$k] = $k . '=' . urlencode($v);
            }
            $requestData = join('&', $requestData);
        }

        return $requestData;
    } // }}}

    protected function prepareDataFromPackageInfo($shippingData) { // {{{
        $data = array();

        $data['country_code']   = $shippingData['dest_address']['country'];

        $data['from_postcode']  = $shippingData['orig_address']['zipcode'];
        $data['to_postcode']    = $shippingData['dest_address']['zipcode'];

        $data['length'] = $shippingData['pack']['length'];
        $data['width']  = $shippingData['pack']['width'];
        $data['height'] = $shippingData['pack']['height'];

        $data['weight'] = $shippingData['pack']['weight'];

        return $data;
    } // }}}

    protected function callServiceSpecificMethod($serviceCode, &$request_data) { // {{{

        $methodName = XC_AP_Request::METHOD_PREFIX . $serviceCode;

        if (method_exists($this, $methodName)) {
            // Call method to prepare request data
            $request_data = $this->$methodName($request_data);
        }

        // Remove options param from request data
        unset($request_data['options']);

        return $request_data;
    } // }}}

    protected function func_make_request_AP($request_params = '') { // {{{
        global $intershipper_error, $shipping_calc_service;

        $request_url = $url = $this->baseUrl . $this->url; // Make request URL

        $this->workCacheKey = $this->baseCacheKey . md5($url . '|' . serialize($request_params));

        if ($cacheData = func_get_cache_func($this->workCacheKey, 'func_make_request_AP')) {
            return $cacheData['data'];
        }

        $headers = array(
            'AUTH-KEY: ' . $this->APIkey
        );

        if (!empty($request_params)) {
            $request_params = $this->prepareRequestData($request_params);
            $request_url .= '?' . $request_params;
        }

        list($a, $result) = func_https_request(
            $this->httpMethod,
            $request_url,
            '', // data
            '', // join
            '', // cookie
            $this->ContentType,
            '', // referer
            '', // cert
            '', // kcert
            $headers
        );

        if (defined('XC_AP_DEBUG') || $this->printDebug) {
            $headers[0] = 'AUTH-KEY: ***removed***';

            if ($this->printDebug) {
                // Display debug info
                $class = defined('X_PHP530_COMPAT') ? get_called_class().' ' : '';

                print "<h2>{$class}{$this->httpMethod} Request to $url &nbsp;&nbsp;&nbsp;&nbsp;{$this->ContentType}</h2>";
                print "<pre>".htmlspecialchars($request_params)."</pre>";
                print "<h2>Ausralia Post Response</h2>";
                $result1 = preg_replace("/(>)(<[^\/])/", "\\1\n\\2", $result);
                $result2 = preg_replace("/(<\/[^>]+>)([^\n])/", "\\1\n\\2", $result1);
                print "<pre>".htmlspecialchars(($result2))."</pre>";
            }

            if (defined('XC_AP_DEBUG')) {
                x_log_add('ap_requests', print_r($headers[0], true) .  $this->httpMethod . "\n" . $url . "\n" . $request_params . "\n" . $this->ContentType . "\n" . $a . "\n" . $result);
            }
        }

        assert('!empty($result) && preg_match("/HTTP\/.*\s*200\s*OK/i", $a) /* '.__METHOD__.': Some errors with HTTP request to AP */');

        $is_success =  preg_match("/HTTP\/.*\s*200\s*OK/i", $a);

        // Parse XML reply
        $parse_error = false;
        $options = array(
            'XML_OPTION_CASE_FOLDING' => 1,
            'XML_OPTION_TARGET_ENCODING' => 'UTF-8'
        );

        $parsed = func_xml_parse($result, $parse_error, $options);

        if (
            !$is_success
            || empty($parsed)
        ) {
            if (!empty($parsed)) {
                $error_message = func_array_path($parsed, 'ERROR/#/ERRORMESSAGE/0/#');

                if (!empty($error_message)) {
                    // Australia Post returned an error
                    $intershipper_error = $error_message;
                    $shipping_calc_service = XC_AP_Request::SHIPPING_PROVIDER_NAME;

                    x_log_flag('log_shipping_errors', 'SHIPPING', "Ausralia Post module error: " . $error_message . "\n", true);
                }

            } else {
                x_log_flag('log_shipping_errors', 'SHIPPING', "Unknown Ausralia Post module error: " . print_r($a, true) . print_r($result, true), true);
            }

            $parsed = '';
        }

        if (!empty($parsed)) {
            func_save_cache_func($parsed, $this->workCacheKey, 'func_make_request_AP');
        }

        return $parsed;
    } // }}}
}

class XC_AP_GetCountries extends XC_AP_Request {

    protected $url = '/api/postage/country.xml';

    protected function __construct() { // {{{
        // Call parent constructor
        parent::__construct();
        // Set Cache Keys
        $this->workCacheKey = $this->baseCacheKey = md5($this->mode);
        // Enable caching for func_get_available_countries_AP function
        func_register_cache_function('func_get_available_countries_AP', array('class' => __CLASS__));
    } // }}}

    public static function getInstance() { // {{{
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    } // }}}

    public function func_get_available_countries_AP() { // {{{
        if ($cacheData = func_get_cache_func($this->workCacheKey, 'func_get_available_countries_AP')) {
            return $cacheData['data'];
        }

        $countries_list = $this->func_make_request_AP();

        $parsed = func_array_path($countries_list, 'COUNTRIES/#/COUNTRY');

        if (empty($countries_list) || empty($parsed)) {
            assert('FALSE /* '.__METHOD__.': Empty COUNTRIES array for GetCountries */');
            return array();
        }


        $countries = array();

        foreach ($parsed as $entity) {
            $country_code = func_array_path($entity, '#/CODE/0/#');
            if (!empty($country_code)) {
                $countries[] = $country_code;
            }
        }

        if (!empty($countries)) {
            func_save_cache_func($countries, $this->workCacheKey, 'func_get_available_countries_AP');
        }

        return $countries;
    } // }}}
}

class XC_AP_GetSizes extends XC_AP_Request {

    private $availableServices = array (
//        'DomesticLetterEnvelope' => array(
//            'uri'  => '/api/postage/letter/domestic/size.xml',
//        ),
        'DomesticParcelBoxSize' => array(
            'uri' => '/api/postage/parcel/domestic/size.xml',
        )
    );

    protected function __construct() { // {{{
        // Call parent constructor
        parent::__construct();
        // Set Cache Keys
        $this->workCacheKey = $this->baseCacheKey = md5($this->mode);
        // Enable caching for func_get_available_sizes_AP function
        func_register_cache_function('func_get_available_sizes_AP', array('class' => __CLASS__));
    } // }}}

    public static function getInstance() { // {{{
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    } // }}}

    public function func_get_available_sizes_AP() { // {{{

        if ($cacheData = func_get_cache_func($this->workCacheKey, 'func_get_available_sizes_AP')) {
            return $cacheData['data'];
        }

        $available_sizes_list = array();

        foreach ($this->availableServices as $serviceInfo) {
            // Set service URI
            $this->url = $serviceInfo['uri'];
            // Get sizes for service
            $available_sizes = $this->func_make_request_AP();

            $parced = func_array_path($available_sizes, 'SIZES/#/SIZE');

            if (empty($available_sizes) || empty($parced)) {
                if (XC_AP_Options::getInstance()->isConfigured()) {
                    assert('FALSE /* '.__METHOD__.': Empty SIZES array for GetSizes */');
                } else {
                    global $top_message;

                    $top_message = array(
                        'content' => func_get_langvar_by_name('msg_ap_options_are_empty', null, false, true),
                        'type' => 'W'
                    );
                }
                return array();
            }

            foreach ($parced as $available_size) {

                $code = func_array_path($available_size, '#/CODE/0/#');
                $name = func_array_path($available_size, '#/NAME/0/#');
                $value = func_array_path($available_size, '#/VALUE/0/#');

                if (!empty($code) && !empty($name) && !empty($value)) {
                    $available_sizes_list[] = array (
                        'code' => $code,
                        'name' => $name,
                        'value' => $value,
                    );
                }
            }
        }

        if (!empty($available_sizes_list)) {
            func_save_cache_func($available_sizes_list, $this->workCacheKey, 'func_get_available_sizes_AP');
        }

        return $available_sizes_list;
    } // }}}

    public function getPackageSizeByCode($code) { // {{{

        $available_sizes = $this->func_get_available_sizes_AP();

        foreach ($available_sizes as $size) {
            if ($size['code'] == $code) {
                list($height, $width, $length) = explode('x', $size['value']);
                return implode(':', array($length, $width, $height));
            }
        }

        return false;
    } // }}}
}

class XC_AP_DiscoverServices extends XC_AP_Request {

    protected $availableServices = array (
        'DomesticLetterService' => array(
            'uri'  => '/api/postage/letter/domestic/service.xml',
            'validation' => array(
                'length'    => 260, // mm
                'width'     => 360, // mm
                'thickness' => 20,  // mm
                'weight'    => 500, // g
            ),
        ),
        'DomesticParcelService' => array(
            'uri'  => '/api/postage/parcel/domestic/service.xml',
            'validation' => array(
                'weight'    => 20000, // 20 kg
            ),
        ),
        'InternationalLetterService' => array(
            'uri'  => '/api/postage/letter/international/service.xml',
            'validation' => array(
                'weight'    => 500, // g
            ),
        ),
        'InternationalParcelService' => array(
            'uri'  => '/api/postage/parcel/international/service.xml',
            'validation' => array(
                'weight'    => 20000, // 20 kg
            ),
        ),
    );

    protected function __construct() { // {{{
        // Call parent constructor
        parent::__construct();
    } // }}}

    protected function isDomesticService($serviceCode) { // {{{
        return (substr($serviceCode, 0, 8) === 'Domestic');
    } // }}}

    protected function getRequestDataDomesticLetterService($params) { // {{{
        return array(
            'length'    => $params['length'],
            'width'     => $params['width'],
            'thickness' => $params['height'],
            'weight'    => $params['weight'],
        );
    } // }}}

    protected function getRequestDataDomesticParcelService($params) { // {{{
        return array(
            'from_postcode' => $params['from_postcode'],
            'to_postcode'   => $params['to_postcode'],
            'length'        => $params['length'],
            'width'         => $params['width'],
            'height'        => $params['height'],
            'weight'        => $params['weight'],
        );
    } // }}}

    protected function getRequestDataInternationalLetterService($params) { // {{{
        return array(
            'country_code' => $params['country_code'],
            'weight'       => $params['weight'],
        );
    } // }}}

    protected function getRequestDataInternationalParcelService($params) { // {{{
        return array(
            'country_code' => $params['country_code'],
            'weight'       => $params['weight'],
        );
    } // }}}

    public static function getInstance() { // {{{
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    } // }}}

    public function isDeliveryAvailable($countryCode) { // {{{
        static $countries_list = array();

        if (empty($countries_list)) {
            // Get all countries
            $countries_list = XC_AP_GetCountries::getInstance()->func_get_available_countries_AP();
            $countries_list[] = XC_AP_Request::DOMESTIC_COUNTRY_CODE;
        }

        // Check if the provided country is in the list
        return in_array($countryCode, $countries_list);
    } // }}}

    public function getAvailableServices($countryCode) { // {{{

        static $available_services_list = array();

        if (empty($available_services_list[$countryCode])) {

            foreach ($this->availableServices as $serviceKey => $serviceInfo) {
                if ($countryCode === XC_AP_Request::DOMESTIC_COUNTRY_CODE) {
                    if (!$this->isDomesticService($serviceKey)) {
                        continue;
                    }
                    $available_services_list[$countryCode][] = $serviceKey;
                }
                else {
                    if ($this->isDomesticService($serviceKey)) {
                        continue;
                    }
                    $available_services_list[$countryCode][] = $serviceKey;
                }
            }

        }

        return $available_services_list[$countryCode];
    } // }}}

    public function getServiceLimits($serviceCode) { // {{{

        static $sereviceLimits = array();

        if (empty($sereviceLimits[$serviceCode])) {
            if (!empty($this->availableServices[$serviceCode]['validation'])) {
                if (!empty($this->availableServices[$serviceCode]['validation']['length'])) {
                    $sereviceLimits[$serviceCode]['length'] = $this->availableServices[$serviceCode]['validation']['length'];
                }
                if (!empty($this->availableServices[$serviceCode]['validation']['width'])) {
                    $sereviceLimits[$serviceCode]['width'] = $this->availableServices[$serviceCode]['validation']['width'];
                }
                if (!empty($this->availableServices[$serviceCode]['validation']['thickness'])) {
                    $sereviceLimits[$serviceCode]['height'] = $this->availableServices[$serviceCode]['validation']['thickness'];
                }
                if (!empty($this->availableServices[$serviceCode]['validation']['weight'])) {
                    $sereviceLimits[$serviceCode]['weight'] = $this->availableServices[$serviceCode]['validation']['weight'];
                }
            }
        }

        return $sereviceLimits[$serviceCode];
    } // }}}

    public function getAvailableServicesForPackage($packageInfo) { // {{{

        $available_services_list = array();

        $available_service_codes = $this->getAvailableServices($packageInfo['dest_address']['country']);

        foreach ($available_service_codes as $serviceCode) {
            // Prepare request data
            $request_data = $this->prepareDataFromPackageInfo($packageInfo);

            // Get known weight limitations
            $limits = $this->getServiceLimits($serviceCode);
            // Check service weight limits
            if (!empty($limits['weight']) && $request_data['weight'] > $limits['weight']) {
                // Skip services that exceed weight limits
                continue;
            }

            // Call service specific method to prepare request data
            $this->callServiceSpecificMethod($serviceCode, $request_data);

            // Set service URI
            $this->url = $this->availableServices[$serviceCode]['uri'];
            // Get services
            $available_services = $this->func_make_request_AP($request_data);

            $parced = func_array_path($available_services, 'SERVICES/#/SERVICE');

            if (empty($available_services) || empty($parced)) {
                assert('FALSE /* '.__METHOD__.': Empty SERVICES array for getAvailableServicesForPackage */');
                return array();
            }

            foreach ($parced as $available_service) {

                $code = func_array_path($available_service, '#/CODE/0/#');
                $name = func_array_path($available_service, '#/NAME/0/#');

                $max_extra_cover = func_array_path($available_service, '#/MAX_EXTRA_COVER/0/#');

                $options = func_array_path($available_service, '#/OPTIONS/0/#/OPTION');

                if (!empty($code) && !empty($name)) {

                    $available_services_list[$code] = array (
                        'provider_code' => $serviceCode,
                        'code' => $code,
                        'name' => $name,
                    );

                    if (!empty($max_extra_cover)) {
                        $available_services_list[$code]['max_extra_cover'] = $max_extra_cover;
                    }

                    if (!empty($options)) {
                        // Set options array data
                        foreach ($options as $option) {

                            $option_code = func_array_path($option, '#/CODE/0/#');
                            $option_name = func_array_path($option, '#/NAME/0/#');
                            $option_suboptions = func_array_path($option, '#/SUBOPTIONS/0/#/OPTION');

                            if (!empty($option_code) && !empty($option_name)) {
                                $available_services_list[$code]['options'][$option_code] = array (
                                    'code' => $option_code,
                                    'name' => $option_name,
                                );

                                if (!empty($option_suboptions)) {
                                    // Set options array data
                                    foreach ($option_suboptions as $option_sub_option) {

                                        $sub_option_code = func_array_path($option_sub_option, '#/CODE/0/#');
                                        $sub_option_name = func_array_path($option_sub_option, '#/NAME/0/#');

                                        if (!empty($option_code) && !empty($option_name)) {
                                            $available_services_list[$code]['options'][$option_code]['suboptions'] = array (
                                                'code' => $sub_option_code,
                                                'name' => $sub_option_name,
                                            );
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $available_services_list;
    } // }}}

}

class XC_AP_GetRates extends XC_AP_Request {

    const INTL_SERVICE_OPTION_EXTRA_COVER = 'INTL_SERVICE_OPTION_EXTRA_COVER';
    const AUS_SERVICE_OPTION_EXTRA_COVER = 'AUS_SERVICE_OPTION_EXTRA_COVER';

    protected $availableServices = array (
        'DomesticLetterPostage' => array(
            'uri'  => '/api/postage/letter/domestic/calculate.xml',
        ),
        'DomesticParcelPostage' => array(
            'uri'  => '/api/postage/parcel/domestic/calculate.xml',
        ),
        'InternationalLetterPostage' => array(
            'uri'  => '/api/postage/letter/international/calculate.xml',
        ),
        'InternationalParcelPostage' => array(
            'uri'  => '/api/postage/parcel/international/calculate.xml',
        ),
    );

    protected function __construct() { // {{{
        // Call parent constructor
        parent::__construct();
    } // }}}

    protected function getPostageCode($serviceCode) { // {{{
        return str_replace('Service', 'Postage', $serviceCode);
    } // }}}

    protected function getRequestDataDomesticLetterPostage($params) { // {{{
        $data = array(
            'service_code' => $params['service_code'],
            'weight'       => $params['weight'],
        );

        if (!empty($params['options'])) {
            $this->getPackageServiceOptions($params, $data);
        }

        return $data;
    } // }}}

    protected function getRequestDataDomesticParcelPostage($params) { // {{{
        $data = array(
            'service_code'  => $params['service_code'],
            'from_postcode' => $params['from_postcode'],
            'to_postcode'   => $params['to_postcode'],
            'length'        => $params['length'],
            'width'         => $params['width'],
            'height'        => $params['height'],
            'weight'        => $params['weight'],
        );


        if (!empty($params['options'])) {
            $this->getPackageServiceOptions($params, $data);
        }

        return $data;
    } // }}}

    protected function getRequestDataInternationalLetterPostage($params) { // {{{
        $data = array(
            'service_code' => $params['service_code'],
            'country_code' => $params['country_code'],
            'weight'       => $params['weight'],
        );

        if (!empty($params['options'])) {
            $this->getPackageServiceOptions($params, $data);
        }

        return $data;
    } // }}}

    protected function getRequestDataInternationalParcelPostage($params) { // {{{
        $data = array(
            'service_code' => $params['service_code'],
            'country_code' => $params['country_code'],
            'weight'       => $params['weight'],
        );

        if (!empty($params['options'])) {
            $this->getPackageServiceOptions($params, $data);
        }

        return $data;
    } // }}}

    protected function getPackageServiceOptions($params, &$data) { // {{{

        foreach($params['options'] as $option) {

            if (XC_AP_GetRates::INTL_SERVICE_OPTION_EXTRA_COVER == $option['code']) {

                if (XC_AP_Options::getInstance()->getExtraCover()) {
                    $data['option_code'] = $option['code'];
                }

            } elseif ($option['code'] == XC_AP_Options::getInstance()->getServiceOption()) {

                $data['option_code'] = $option['code'];

                if (!empty($option['suboptions'])) {
                    foreach($option['suboptions'] as $suboption) {

                        if (XC_AP_GetRates::AUS_SERVICE_OPTION_EXTRA_COVER == $suboption && XC_AP_Options::getInstance()->getExtraCover()) {
                            $data['suboption_code'] = $suboption;
                        }
                    }
                }
            }
        }

        if (XC_AP_Options::getInstance()->getExtraCover()) {

            $data['extra_cover'] = 0 < doubleval(XC_AP_Options::getInstance()->getExtraCover())
                ? round(XC_AP_Options::getInstance()->getExtraCover(), 2)
                : $params['subtotal'];

            $data['extra_cover'] = min($data['extra_cover'], 5000);
        }

        return $data;
    } // }}}

    protected function getServiceRate($serviceInfo, $packageInfo) { // {{{

        $service_rate_data = array();

        $postageCode = $this->getPostageCode($serviceInfo['provider_code']);

        if (!empty($this->availableServices[$postageCode]['uri'])) {

            $request_data = $this->prepareDataFromPackageInfo($packageInfo);

            $request_data['service_code'] = $serviceInfo['code'];

            if (!empty($serviceInfo['options'])) {
                $request_data['options'] = $serviceInfo['options'];
            }

            $this->callServiceSpecificMethod($postageCode, $request_data);

            // Set service URI
            $this->url = $this->availableServices[$postageCode]['uri'];
            // Get services
            $rate_data = $this->func_make_request_AP($request_data);

            $parced = func_array_path($rate_data, 'POSTAGE_RESULT/#');

            if (empty($rate_data) || empty($parced)) {
                assert('FALSE /* '.__METHOD__.': Empty POSTAGE_RESULT array for getServiceRate */');
                return array();
            }

            $service_rate_data['service-code'] = $serviceInfo['code'];
            $service_rate_data['service-name'] = func_array_path($parced['SERVICE'], '0/#');
            $service_rate_data['expected-transit-time'] = func_array_path($parced['DELIVERY_TIME'], '0/#');
            $service_rate_data['price'] = func_array_path($parced['TOTAL_COST'], '0/#');
        }

        return $service_rate_data;
    } // }}}

    public static function getInstance() { // {{{
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    } // }}}

    public function getRates($packageInfo) { // {{{

        $available_rates = array();

        $available_services = XC_AP_DiscoverServices::getInstance()->getAvailableServicesForPackage($packageInfo);

        foreach($available_services as $serviceInfo) {
            $available_rates[] = $this->getServiceRate($serviceInfo, $packageInfo);
        }

        return $available_rates;

    } // }}}
}
