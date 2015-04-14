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
 * This script contains soap classes
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Cart
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v14 (xcart_4_7_0), 2015-02-17 23:56:28, func.soap.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

/**
 * SOAP Service Singleton Class
 */
abstract class XC_SOAP_Service extends XC_Singleton {

    const ERROR_CODE_TYPE_RESPONSE  = 'response_error';
    const ERROR_CODE_TYPE_EXCEPTION = 'exception_error';

    const ERROR_CODE_RETURN_CODE        = 'return_code';
    const ERROR_CODE_RETURN_DESCRIPTION = 'return_description';

    const SOAP_TEST_MODE    = 'SOAP_TEST_MODE';
    const SOAP_PRODUCTION_MODE  = 'SOAP_PRODUCTION_MODE';

    private $wsdl_client = null; // object should not be used directly

    protected $last_response = null;

    protected $last_error = null;

    protected $last_exception = null;

    protected $mode = XC_SOAP_Service::SOAP_TEST_MODE; // working mode test / production

    protected abstract function defineWsdlFile();

    protected abstract function defineTestServer();

    protected abstract function defineProductionServer();

    protected abstract function defineResponseCodePath();

    protected abstract function defineResponseDescriptionPath();

    protected abstract function defineExceptionCodePath();

    protected abstract function defineExceptionDescriptionPath();

    protected abstract function defineValidResponseCodes();

    private function getWsdlFile()
    {
        return $this->defineWsdlFile();
    }

    private function getTestServer()
    {
        return $this->defineTestServer();
    }

    private function getProductionServer()
    {
        return $this->defineProductionServer();
    }

    private function getResponseCodePath()
    {
        return $this->defineResponseCodePath();
    }

    private function getResponseDescriptionPath()
    {
        return $this->defineResponseDescriptionPath();
    }

    private function getExceptionCodePath()
    {
        return $this->defineExceptionCodePath();
    }

    private function getExceptionDescriptionPath()
    {
        return $this->defineExceptionDescriptionPath();
    }

    private function getValidResponseCodes()
    {
        return $this->defineValidResponseCodes();
    }

    private function createSoapClient()
    {
        return new SoapClient($this->getWsdlFile(), array('trace' => 1));
    }

    /**
     * Format SOAP data
     *
     * @param string/xml $soap_data
     *
     * @return mixed
     */
    private function formatSoapData($soap_data)
    {
        if (!empty($soap_data)) {

            if (class_exists('DOMDocument')) {

                $dom = new DOMDocument;

                $dom->preserveWhiteSpace = FALSE;
                $dom->formatOutput = TRUE;

                $dom->loadXML($soap_data);

                return $dom->saveXml();
            }

            return $soap_data;
        }

        return false;
    }

    protected function are_php_requirements_met() { //{{{
        static $result;

        if (isset($result)) {
            return $result;
        }

        x_load('tests');
        $result = func_is_soap_available();

        // check SOAP requirements
        if (!$result) {
            assert('FALSE /*SOAP extension is not enabled in PHP configuration*/');
        }

        // check if OPENSSL is required
        if ($this->is_openssl_extension_required() && !func_is_openssl_available()) {
            assert('FALSE /*HTTPS URLs are used in SOAP, but OPENSSL extension is not enabled in PHP configuration*/');

            $result = false;
        }

        return $result;
    } //}}}

    protected function getSoapClientProperty($property_name) { //{{{
        if (
            !empty($this->wsdl_client)
            && !empty($property_name)
            && property_exists($this->wsdl_client, $property_name)
        ) {
            return $this->wsdl_client->$property_name;
        }

        return null;
    } //}}}

    /**
     * Call SOAP client function
     *
     * @param string $function_name Function name
     *
     * @example callSoapClientFunction('__soapCall', arg1, arg2, ...)
     *
     * @return mixed
     */
    protected function callSoapClientFunction() {//{{{
        $argumanets = func_get_args();

        $function_name = array_shift($argumanets);

        if (
            !empty($this->wsdl_client)
            && !empty($function_name)
            && method_exists($this->wsdl_client, $function_name)
        ) {
            return call_user_func_array(array($this->wsdl_client, $function_name), $argumanets);
        }

        return null;
    } //}}}

    /**
     * Request pre processor
     *
     * @param string $request_data
     *
     * @return string
     */
    protected function preProcessRequest($request_data)
    {
        return $request_data;
    }

    /**
     * Request pre processor
     *
     * @param string $request_data
     *
     * @return string
     */
    protected function preProcessResponse($response_data)
    {
        return $response_data;
    }

    protected function getCalledClass() { // {{{
        $called_class = __CLASS__;

        // X_PHP530_COMPAT
        if (function_exists('get_called_class')) {
            $called_class = get_called_class();
        }

        return strtolower($called_class);
    } //}}}

    protected function debugLog()
    {
        x_log_add($this->getCalledClass(), array(
            'Post to: ' . $this->getSoapClientProperty('location'),
            'Response' => array(
                'Code' => $this->getLastResponseErrorCode(),
                'Description' => $this->getLastResponseErrorDescription()
            ),
            'request' => $this->getLastRequestText(),
            'response' => $this->getLastResponseText())
        );
    }

    protected function logException()
    {
        x_log_add($this->getCalledClass(), array(
            'Post to: ' . $this->getSoapClientProperty('location'),
            'Exception' => array(
                'Code' => $this->last_exception->faultcode,
                'String' => $this->last_exception->faultstring
            ),
            'Error' => array(
                'Code' => $this->getLastExceptionErrorCode(),
                'Description' => $this->getLastExceptionErrorDescription(),
            ),
            'request' => $this->getLastRequestText(),
            'response' => $this->getLastResponseText())
        );
    }

    private function enableTestMode()
    {
        $this->mode = self::SOAP_TEST_MODE;

        $this->callSoapClientFunction('__setLocation', $this->getTestServer());
    }

    private function enableProductionMode()
    {
        $this->mode = self::SOAP_PRODUCTION_MODE;

        $this->callSoapClientFunction('__setLocation', $this->getProductionServer());
    }

    private function is_openssl_extension_required()
    {
        $working_url = '';

        switch ($this->getMode()) {
            case self::SOAP_TEST_MODE:
                $working_url = $this->getTestServer();
                break;
            case self::SOAP_PRODUCTION_MODE:
                $working_url = $this->getProductionServer();
                break;
        }

        return strtolower(substr($working_url, 0, 5)) === 'https';
    }

    /**
     * Get last error code by type
     *
     * @param string $error_type Error type, one of ERROR_CODE_TYPE_RESPONSE / ERROR_CODE_TYPE_EXCEPTION
     * @param string $return_type Return type, one of ERROR_CODE_RETURN_CODE / ERROR_CODE_RETURN_DESCRIPTION
     *
     * @return mixed Error code string or false
     */
    private function getLastErrorByType($error_type, $return_type)
    {
        $codePath = null;
        $result = null;

        switch ($error_type) {
            case self::ERROR_CODE_TYPE_RESPONSE:
                // set response context
                $result = $this->last_response;

                if ($return_type == self::ERROR_CODE_RETURN_CODE) {
                    $codePath = $this->getResponseCodePath();
                } else {
                    $codePath = $this->getResponseDescriptionPath();
                }
                break;
            case self::ERROR_CODE_TYPE_EXCEPTION:
                // set exception context
                $result = $this->last_exception;

                if ($return_type == self::ERROR_CODE_RETURN_CODE) {
                    $codePath = $this->getExceptionCodePath();
                } else {
                    $codePath = $this->getExceptionDescriptionPath();
                }
                break;
        }

        if (!empty($codePath) && !empty($result)) {

            $path = explode('/', $codePath);

            foreach ($path as $element) {
                if (is_array($result)) {
                    // get only last element in the list
                    $result = array_pop($result);
                }
                if (!is_object($result) || !property_exists($result, $element)) {
                    return false;
                }
                $result = $result->$element;
            }

            return strval($result);
        }

        return false;
    }

    private function getLastRequest()
    {
        return $this->callSoapClientFunction('__getLastRequest');
    }

    private function getLastResponse()
    {
        return $this->callSoapClientFunction('__getLastResponse');
    }

    public static function getInstance()
    {
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    }

    public function getLastResponseErrorCode()
    {
        return $this->getLastErrorByType(self::ERROR_CODE_TYPE_RESPONSE, self::ERROR_CODE_RETURN_CODE);
    }

    public function getLastResponseErrorDescription()
    {
        return $this->getLastErrorByType(self::ERROR_CODE_TYPE_RESPONSE, self::ERROR_CODE_RETURN_DESCRIPTION);
    }

    public function getLastExceptionErrorCode()
    {
        // get soap exception by path
        $result = $this->getLastErrorByType(self::ERROR_CODE_TYPE_EXCEPTION, self::ERROR_CODE_RETURN_CODE);
        // in case of client exception return exception code
        return !empty($result) ? $result : $this->last_exception->faultcode;
    }

    public function getLastExceptionErrorDescription()
    {
        // get soap exception by path
        $result = $this->getLastErrorByType(self::ERROR_CODE_TYPE_EXCEPTION, self::ERROR_CODE_RETURN_DESCRIPTION);
        // in case of client exception return exception description
        return !empty($result) ? $result : $this->last_exception->faultstring;
    }

    /**
     * Last request text
     *
     * @return string Formatted request text
     */
    public function getLastRequestText()
    {
        $last_request = $this->formatSoapData($this->getLastRequest());

        return $this->preProcessRequest($last_request);
    }

    /**
     * Last response text
     *
     * @return string Formatted response text
     */
    public function getLastResponseText()
    {
        $last_response = $this->formatSoapData($this->getLastResponse());

        return $this->preProcessResponse($last_response);
    }

    /**
     * Has exceptions
     *
     * @return SoapFault or false in case there are no exceptions
     */
    public function hasExceptions()
    {
        return !empty($this->last_exception) ? $this->last_exception : false;
    }

    /**
     * Has errors
     *
     * @return ErrorObject or false in case there are no exceptions
     */
    public function hasErrors()
    {
        return !empty($this->last_error) ? $this->last_error : false;
    }

    public function printRequestResponse()
    {
        echo '<h2>Request</h2>' . "\n";
	echo '<pre>' . htmlspecialchars($this->getLastRequestText()). '</pre>';
	echo "<br>\n";

	echo '<h2>Response</h2>'. "\n";
	echo '<pre>' . htmlspecialchars($this->getLastResponseText()). '</pre>';
	echo "<br>\n";
    }

    /**
     * Switch mode (XC_SOAP_Service::SOAP_TEST_MODE,
     *              XC_SOAP_Service::SOAP_PRODUCTION_MODE)
     *
     * @param const $mode
     */
    public function setMode($mode)
    {
        if ($mode === XC_SOAP_Service::SOAP_TEST_MODE) {
            $this->enableTestMode();
        } else {
            $this->enableProductionMode();
        }
    }

    /**
     * Get mode
     *
     * @return const one of (XC_SOAP_Service::SOAP_TEST_MODE,
     *                       XC_SOAP_Service::SOAP_PRODUCTION_MODE)
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // check system requirements
        if ($this->are_php_requirements_met()) {
            // create SOAP client
            $this->wsdl_client = $this->createSoapClient();
        }

        // use test mode by default
        $this->setMode(XC_SOAP_Service::SOAP_TEST_MODE);
    }

    /**
     * Process request
     *
     * @param string $request_type
     * @param array $soap_request
     *
     * @return boolean
     */
    public function processRequest($request_type, $soap_request)
    {
        try {

            $this->last_response = $this->callSoapClientFunction('__soapCall', $request_type, array($soap_request));

            if (!in_array($this->getLastResponseErrorCode(), $this->getValidResponseCodes())) {
                // Save last error
                $this->last_error = $this->last_response;
            }

            if (defined('XC_SOAP_DEBUG') || defined('XC_UPS_DEBUG') || defined('XC_FEDEX_DEBUG')) {
                // Debug log
                $this->debugLog();
            }

            // Return response
            return $this->last_response;

        } catch (SoapFault $exception) {
            // Save last exception
            $this->last_exception = $exception;
            // Log exceptions
            $this->logException();
        }

        return false;
    }
}

?>
