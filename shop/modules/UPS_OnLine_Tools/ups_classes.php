<?php
/* vim: set ts=4 sw=4 sts=4 et: */
/* * ***************************************************************************\
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
  \**************************************************************************** */

/**
 * UPS Developer Kit module classes
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v9 (xcart_4_7_0), 2015-02-17 23:56:28, ups_classes.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

// Load common classes

x_load('soap', 'crypt');

class XC_UPS_Register_Service extends XC_SOAP_Service {

    protected function defineProductionServer()
    {
        global $UPS_REGISTRATION_API_URL;

        return $UPS_REGISTRATION_API_URL;
    }

    protected function defineTestServer()
    {
        global $UPS_REGISTRATION_TEST_API_URL;

        return $UPS_REGISTRATION_TEST_API_URL;
    }

    protected function defineWsdlFile()
    {
        global $xcart_dir;

        return $xcart_dir . '/modules/UPS_OnLine_Tools/API/Registration/RegistrationWebService.wsdl';
    }

    protected function defineResponseCodePath()
    {
        return 'Response/ResponseStatus/Code';
    }

    protected function defineResponseDescriptionPath()
    {
        return 'Response/ResponseStatus/Description';
    }

    protected function defineExceptionCodePath()
    {
        return 'detail/Errors/ErrorDetail/PrimaryErrorCode/Code';
    }

    protected function defineExceptionDescriptionPath()
    {
        return 'detail/Errors/ErrorDetail/PrimaryErrorCode/Description';
    }

    protected function defineValidResponseCodes()
    {
        return array('1');
    }

    protected function preProcessRequest($request_data)
    {

        $display_request1 = preg_replace('/(<ns\d+:AccessLicenseNumber>)(.*)(<\/ns\d+:AccessLicenseNumber>)/', '$1xxxxx$3', $request_data);
        $display_request2 = preg_replace('/(<ns\d+:Username>)(.*)(<\/ns\d+:Username>)/', '$1xxxxx$3', $display_request1);
        $display_request3 = preg_replace('/(<ns\d+:Password>)(.*)(<\/ns\d+:Password>)/', '$1xxxxx$3', $display_request2);
        $display_request = preg_replace('/(<ns\d+:DeveloperLicenseNumber>)(.*)(<\/ns\d+:DeveloperLicenseNumber>)/', '$1xxxxx$3', $display_request3);

        return $display_request;
    }

    protected function preProcessResponse($response_data)
    {

        $display_request1 = preg_replace('/(<ns\d+:AccessLicenseNumber>)(.*)(<\/ns\d+:AccessLicenseNumber>)/', '$1xxxxx$3', $response_data);
        $display_request2 = preg_replace('/(<ns\d+:Username>)(.*)(<\/ns\d+:Username>)/', '$1xxxxx$3', $display_request1);
        $display_request3 = preg_replace('/(<ns\d+:Password>)(.*)(<\/ns\d+:Password>)/', '$1xxxxx$3', $display_request2);
        $display_request = preg_replace('/(<ns\d+:DeveloperLicenseNumber>)(.*)(<\/ns\d+:DeveloperLicenseNumber>)/', '$1xxxxx$3', $display_request3);

        return $display_request;
    }

    private function prepareRequestHeaders($license_num)
    {
        global $config;

        // create soap header
        $usernameToken['Username'] = text_decrypt(trim($config['UPS_OnLine_Tools']['UPS_regusr']));
        $usernameToken['Password'] = text_decrypt(trim($config['UPS_OnLine_Tools']['UPS_regpwd']));

        if ($this->getMode() === XC_SOAP_Service::SOAP_TEST_MODE) {
            // use test access key in test mode
            $license_num = text_decrypt(trim($config['UPS_OnLine_Tools']['UPS_taskey']));
        }

        $serviceAccessLicense['AccessLicenseNumber'] = $license_num;

        $upss['UsernameToken'] = $usernameToken;
        $upss['ServiceAccessToken'] = $serviceAccessLicense;

        // check system requirements
        if (class_exists('SoapHeader')) {
            // create soap headers
            $header = new SoapHeader('http://www.ups.com/XMLSchema/XOLTWS/UPSS/v1.0', 'UPSSecurity', $upss);
            // set soap headers
            $this->callSoapClientFunction('__setSoapHeaders', $header);
        }
    }

    public static function getInstance()
    {
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    }

    /**
     * Switch mode (XC_SOAP_Service::SOAP_TEST_MODE,
     *              XC_SOAP_Service::SOAP_PRODUCTION_MODE)
     *
     * @param const $mode
     */
    public function setMode($mode)
    {
        global $UPS_url, $UPS_XML_URL, $UPS_XML_TEST_URL;

        // UPS url used in mod_UPS.php
        if ($mode === XC_SOAP_Service::SOAP_TEST_MODE) {
            $UPS_url = $UPS_XML_TEST_URL;
        } else {
            $UPS_url = $UPS_XML_URL;
        }

        parent::setMode($mode);
    }

    public function prepareRegisterAccountRequest($ups_username, $ups_password, $license_num, $userinfo, $suggest_username = false)
    {
        global $CLIENT_IP;

        //create soap header
        $this->prepareRequestHeaders($license_num);

        //create soap request
        $requestoption['RequestOption'] = 'N';
        $soap_request['Request'] = $requestoption;

        $soap_request['Username'] = $suggest_username ? 'SuggestUser' : $ups_username;
        $soap_request['Password'] = $ups_password;
        $soap_request['CompanyName'] = $userinfo['company'];
        $soap_request['CustomerName'] = $userinfo['contact_name'];
        $soap_request['Title'] = $userinfo['title_name'];

        $address['AddressLine'] = $userinfo['address'];
        $address['City'] = $userinfo['city'];
        $address['StateProvinceCode'] = $userinfo['state'];
        $address['PostalCode'] = $userinfo['postal_code'];
        $address['CountryCode'] = $userinfo['country'];
        $soap_request['Address'] = $address;

        $soap_request['PhoneNumber'] = $userinfo['phone'];
        $soap_request['EmailAddress'] = $userinfo['email'];

        $soap_request['SuggestUsernameIndicator'] = $suggest_username ? 'Y' : 'N';
        $soap_request['NotificationCode'] = '01';

        $soap_request['EndUserIPAddress'] = $CLIENT_IP;

        return $soap_request;
    }

    public function processRegisterAccount($soap_request)
    {
        return $this->processRequest('ProcessRegister', $soap_request);
    }
}
