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
 * 1-800Courier shipping library
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v9 (xcart_4_7_0), 2015-02-17 23:56:28, mod_1800C.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_SESSION_START')) {
    header('Location: ../');
    die('Access denied');
}

if (!class_exists('SoapClient'))
    return false;

define('XC_1800C_SOAP_LOCATION', 'http://cms.a1express.e-courier.com/a1express/software/xml/xml.asp');
define('XC_1800C_SOAP_URI', 'http://www.e-courier.com/schemas/');

x_load('xml');

class XSoapClient extends SoapClient {

    protected $options;
    protected $last_error;


    private function setError($code, $msg) {
        $this->last_error = sprintf('(Error code: %s) %s', $code, $msg);
        x_log_flag('log_shipping_errors', 'SHIPPING', '1-800Courier module: ' . $this->last_error, true);
    }

    public function getError() {
        return  $this->last_error;
    }

    function __construct($wsdl, $options) {
        parent::__construct($wsdl, $options);
        if (empty($options['soap_version'])) {
            $options['soap_version'] = SOAP_1_1;
        }
        $this->options = $options;
    }

    function __doRequest($request, $location, $action, $version, $one_way = 0) {
        $res = parent::__doRequest($request, $location, $action, $version, $one_way);
        return $res;
    }

    private function request($request, $xml_path) {
        try {
            $response = $this->__doRequest($request, $this->options['location'], '', $this->options['soap_version']);
        } catch(SoapFault $ex) {
            $this->setError($ex->getCode(), $ex->getMessage());
            return false;
        }
        // Parse XML reply
        $parse_error = false;
        $options = array(
                'XML_OPTION_CASE_FOLDING' => 1,
                'XML_OPTION_TARGET_ENCODING' => 'ISO-8859-1'
                );
        $parsed = func_xml_parse($response, $parse_error, $options);
        if (!$parse_error) {
            $entries = func_array_path($parsed, $xml_path);
            if ($entries) {
                return $entries;
            } else {
                $code = func_array_path($parsed, 'SOAP:ENVELOPE/SOAP:BODY/FAULTCODE');
                $msg = func_array_path($parsed, 'SOAP:ENVELOPE/SOAP:BODY/FAULTSTRING');
                if ($code && $msg) {
                    $this->setError($code[0]['#'], $msg[0]['#']);
                }
            }
        }
        return false;
    }

    public function login($username, $password, $loginmode='Public') {
        if ($loginmode != 'Private') {
            $loginmode = 'Public';
        }
        $request = '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
            <SOAP:Body>
            <m:Login xmlns:m="%s" UserName="%s" Password="%s" WebSite="a1express" LoginMode="%s">
            </m:Login>
            </SOAP:Body>
            </SOAP:Envelope>';
        $request = sprintf($request, $this->options['uri'], $username, $password, $loginmode);

        $result = false;
        $response = $this->request($request, 'SOAP:ENVELOPE/SOAP:BODY/M:LOGINRESPONSE');
        if ($response) {
            $result = $response[0]['@'];
        }
        return $result;
    }

    public function quoteOrder($userguid, $customerid, $zipcode_from, $zipcode_to, $weight, $pieces) {
        $uri = $this->options['uri'];
        $request = '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/">
            <SOAP:Body UserGUID="%s" >
            <m:QuoteOrder xmlns:m="%s">
            	<Order xmlns:m="%s" CustomerID="%s" Weight="%s" Pieces="%s">
        		    <Stops>
    			        <Stop Sequence="1" Zip="%s"/>
    			        <Stop Sequence="2" Zip="%s"/>
	        	    </Stops>
                </Order>
            </m:QuoteOrder>
            </SOAP:Body>
            </SOAP:Envelope>';
        $request = sprintf($request, $userguid, $uri, $uri, $customerid, $weight, $pieces, $zipcode_from, $zipcode_to);
        $result = false;
        $response = $this->request($request, 'SOAP:ENVELOPE/SOAP:BODY/M:QUOTEORDERRESPONSE');
        if (is_array($response) && !empty($response[0]['#']['ORDER']) && is_array($response[0]['#']['ORDER'])) {
            foreach ($response[0]['#']['ORDER'] as $o) {
                $result[] = $o['@'];
            }
        }
        return $result;
    }

    public function saveOrder($userguid, $service, $address_from, $address_to, $weight, $pieces) {

        $uri = $this->options['uri'];
        $request = '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/" >
            <SOAP:Body UserGUID="%s" >
            <m:SaveOrder xmlns:m="%s">
	            <Order Service="%s" Weight="%s" Pieces="%s" CallerPhone="%s" CallerEmail="%s" OtherReference="X-CART">
            		<Stops>
			            <Stop Sequence="1" StopType="P" Address="%s" Zip="%s" Name="%s" City="%s" State="%s" Country="%s" Phone="%s"/>
            			<Stop Sequence="2" StopType="D" Address="%s" Zip="%s" Name="%s" City="%s" State="%s" Country="%s" Phone="%s"/>
            		</Stops>
                </Order>
            </m:SaveOrder>
            </SOAP:Body>
            </SOAP:Envelope>';
        $request = sprintf($request, $userguid, $uri, $service, $weight, $pieces, $address_from['phone'], $address_from['email'], $address_from['address'],  $address_from['zipcode'], $address_from['company_name'], $address_from['city'], $address_from['state'], $address_from['country'], $address_from['phone'], $address_to['s_address'], $address_to['s_zipcode'], $address_to['s_firstname'] . ' ' . $address_to['s_lastname'] , $address_to['s_city'], $address_to['s_state'], $address_to['s_country'], $address_to['s_phone']);
        $result = false;
        $response = $this->request($request, 'SOAP:ENVELOPE/SOAP:BODY/ORDER');
        if ($response) {
            $result = $response[0]['@'];
        }
        return $result;

    }

    public function deleteOrder($userguid, $tracking) {

        $uri = $this->options['uri'];
        $request = '<SOAP:Envelope xmlns:SOAP="http://schemas.xmlsoap.org/soap/envelope/" >
            <SOAP:Body UserGUID="%s" >
            <m:DeleteOrder xmlns:m="%s">
                <Order OrderID="%s"/>
            </m:DeleteOrder>
            </SOAP:Body>
            </SOAP:Envelope>';
        $request = sprintf($request, $userguid, $uri, $tracking);
        $result = false;
        $response = $this->request($request, 'SOAP:ENVELOPE/SOAP:BODY');
        if ($response) {
            $result = empty($response[0]['#']);
        }
        return $result;
    }

}

function func_shipper_1800C($items, $userinfo, $orig_address, $debug, $cart)
{
    global $config;
    global $allowed_shipping_methods, $intershipper_rates;

    $weight = 0;
    $amount = 0;
    foreach ($items as $i) {
        if (!empty($i['weight']) && !empty($i['amount'])) {
            $weight += $i['weight'] * $i['amount'];
            $amount += $i['amount'];
        }
    }

    if (func_1800C_is_disabled() || $userinfo['s_country'] != 'US') {
        return;
    }

    $md5_request = md5(serialize($items) . serialize($userinfo) . $weight);

    $_1800c_rates = array();

    if ($debug != 'Y' && func_is_shipping_result_in_cache($md5_request)) {

        // Get shipping rates from the cache
        $_1800c_rates = func_get_shipping_result_from_cache($md5_request);
    }

    $orig_address = func_get_warehouse_info($orig_address);

    if (empty($_1800c_rates)) {

        $options = array(
                'location' => XC_1800C_SOAP_LOCATION,
                'uri' => XC_1800C_SOAP_URI,
                'trace' => true,
                'exceptions' => true);
        $soap = new XSoapClient(null, $options);
        $username = $config['Shipping']['1800c_username'];
        $password = $config['Shipping']['1800c_password'];
        $customerid = $config['Shipping']['1800c_customerid'];
        $ready_time_to_pickup = $config['Shipping']['1800c_readytime'];
        if (!$ready_time_to_pickup) {
            $ready_time_to_pickup = 0;
        }
        $login_info = $soap->login($username, $password);
        if (!$login_info) {
            return;
        }

        $rate_info = $soap->quoteOrder($login_info['USERGUID'], $customerid, $orig_address['zipcode'], $userinfo['s_zipcode'], $weight, $amount);
        if (!is_array($rate_info)) {
            return;
        }

        $sdate_format = '%m/%d/%Y %H:%M';
        $edate_format = '%m/%d/%y %H:%M';

        foreach ($rate_info as $o) {
            $estimated_rate = $o['AMOUNTCHARGED'];
            $estimated_rate = str_replace('$', '', $estimated_rate);

            $estimated_rate = floatval($estimated_rate);

            $estimated_rate -= $config['Shipping']['1800c_subsidize'];

            if ($estimated_rate < 0 || empty($estimated_rate)) {
                $estimated_rate = 0;
            }

            unset($hours);
            $startdate = 0;
            $enddate = 0;

            $sdate = strptime($o['READYDATETIME'], $sdate_format);
            if ($sdate) {
                $startdate = mktime($sdate['tm_hour'], $sdate['tm_min'], 0, $sdate['tm_mon']+1, $sdate['tm_mday'], $sdate['tm_year']+1900);
            }
            $adate = $o['DUEDATETIME'];
            $edate = strptime($o['DUEDATETIME'], $edate_format);
            if ($edate) {
                $enddate = mktime($edate['tm_hour'], $edate['tm_min'], 0, $edate['tm_mon']+1, $edate['tm_mday'], $edate['tm_year']+1900);
            }

            if (!empty($startdate) && !empty($enddate)) {
                $_now = XC_TIME;
                $hours = round(($enddate + ($ready_time_to_pickup * 60) - $startdate) / 3600);
                $adate = strftime($edate_format, $enddate + ($ready_time_to_pickup * 60));
            }

            unset($estimated_time);
            if ($hours) {
                $estimated_time = "Appro $hours Hr - $adate";
            }
            
            foreach ($allowed_shipping_methods as $key=>$value) {
                if ($value['code'] == '1800C' && strtolower($value['subcode']) == strtolower($o['SERVICE'])) {
                    if (empty($estimated_time)) {
                        $estimated_time = $value['shipping_time'];
                    }
                    $_1800c_rates[] = array('methodid' => $value['subcode'], 'rate' => $estimated_rate, 'shipping_time' => $estimated_time);
                }
            }
        }
        // Save calculated rates to the cache
        if ($debug != 'Y' && !empty($_1800c_rates)) {
            func_save_shipping_result_to_cache($md5_request, $_1800c_rates);
        }
    }
    if (!empty($_1800c_rates)) {
        $methodids = array();
        foreach ($_1800c_rates as $rate) {
            if (!in_array($rate['methodid'], $methodids)) {
                $methodids[] = $rate['methodid'];
                $intershipper_rates[] = $rate;
            }
        }
    }
    return true;
}

function func_1800C_is_disabled()
{
    global $allowed_shipping_methods;

    $shipping_found = false;
    if (is_array($allowed_shipping_methods)) {
        foreach ($allowed_shipping_methods as $key => $value) {
            if ($value['code'] == '1800C') {
                $shipping_found = true;
                break;
            }
        }
    }

    if (!$shipping_found) {
        return true;
    }

    if (!func_1800C_is_configured()) {
        return true;
    }

    return false;
}

function func_1800C_is_configured()
{
    global $config;

    if (empty($config['Shipping']['1800c_username']) || empty($config['Shipping']['1800c_password']))
        return false;

    if (!extension_loaded('libxml')) {
        return false;
    }

    return true;
}

function func_1800C_get_tracking_number($shipping_subcode, $orig_address, $userinfo, $weight, $amount)
{
    global $config;

    if (!func_1800C_is_configured()) {
        return false;
    }

    $options = array(
            'location' => XC_1800C_SOAP_LOCATION,
            'uri' => XC_1800C_SOAP_URI,
            'trace' => true,
            'exceptions' => true);
    $soap = new XSoapClient(null, $options);
    $username = $config['Shipping']['1800c_username'];
    $password = $config['Shipping']['1800c_password'];
    $login_info = $soap->login($username, $password);
    if (!$login_info) {
        return false;
    }

    $orig_address['company_name'] = $config['Company']['company_name'];
    $orig_address['phone'] = $config['Company']['company_phone'];
    $orig_address = func_get_warehouse_info($orig_address);
    $result = $soap->saveOrder($login_info['USERGUID'], $shipping_subcode, $orig_address, $userinfo, $weight, $amount);

    if (!is_array($result)) {
        return false;
    }
    return array('tracking' => $result['ORDERNUMBER'], 'shipping_orderid' => $result['ORDERID']);
}

function func_1800C_cancel_shipping_order($tracking)
{
    global $config;

    if (!func_1800C_is_configured()) {
        return false;
    }

    $options = array(
            'location' => XC_1800C_SOAP_LOCATION,
            'uri' => XC_1800C_SOAP_URI,
            'trace' => true,
            'exceptions' => true);
    $soap = new XSoapClient(null, $options);
    $username = $config['Shipping']['1800c_username'];
    $password = $config['Shipping']['1800c_password'];
    $login_info = $soap->login($username, $password);
    if (!$login_info) {
        return false;
    }

    $result = $soap->deleteOrder($login_info['USERGUID'], $tracking);
    return $result;
}

function func_1800C_send_reg_info()
{
    global $config, $single_mode, $current_area, $mail_smarty, $sql_tbl, $userinfo;

    $is_allowed = func_query_first_cell("SELECT count(*) FROM $sql_tbl[shipping] WHERE code='1800C' AND active='Y'");

    if (!$is_allowed && in_array($current_area, array('A'))) {
        return;
    }

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

    $seller_address = func_get_warehouse_info($seller_address);

    $mail_smarty->assign('seller_address',  $seller_address);

    x_load('mail');

    func_send_mail(
                'mark@1-800courier.com, lisa@1-800courier.com, don@1-800courier.com',
                'mail/1800C_reg_info_subj.tpl',
                'mail/1800C_reg_info.tpl',
                $config['Company']['support_department'],
                false
            );
}

function func_get_warehouse_info($default_address)
{
    global $config;

    $w_name = $config['Shipping']['1800c_warehouse_name'];
    $w_address = $config['Shipping']['1800c_address'];
    $w_state = $config['Shipping']['1800c_state'];
    $w_city = $config['Shipping']['1800c_city'];
    $w_zipcode = $config['Shipping']['1800c_zipcode'];
    $w_country = $config['Shipping']['1800c_country'];
    $w_phone = $config['Shipping']['1800c_phone'];

    if (!empty($w_name)) {
        $default_address['company_name'] = $w_name;
    }

    if (!empty($w_phone)) {
        $default_address['phone'] = $w_phone;
    }

    if (!empty($w_address) && !empty($w_city) && !empty($w_zipcode) && !empty($w_country)) {
        $default_address['address'] = $w_address;
        $default_address['city'] = $w_city;
        $default_address['state'] = $w_state;
        $default_address['country'] = $w_country;
        $default_address['zipcode'] = $w_zipcode;
    }
    $default_address['business_hours'] = $config['Shipping']['1800c_business_hours'];
    $default_address['operation_days'] = $config['Shipping']['1800c_operation_days'];
    $default_address['readytime'] = $config['Shipping']['1800c_readytime'];
    $default_address['subsidize'] = $config['Shipping']['1800c_subsidize'];
    $default_address['customerid'] = $config['Shipping']['1800c_customerid'];
    $default_address['username'] = $config['Shipping']['1800c_username'];
    $default_address['password'] = $config['Shipping']['1800c_password'];

    ksort($default_address);

    return $default_address;
}

?>
