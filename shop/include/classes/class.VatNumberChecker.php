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
 * VAT number checking functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    c97612cbc7d78e3603978832158bfb3e9e2b4532, v10 (xcart_4_7_1), 2015-03-26 11:41:30, class.VatNumberChecker.php, mixon
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

/**
 * Abstract tax number checker class
 */
abstract class AXCTaxNumberChecker extends XC_Singleton { // {{{

    const VALID_TAX_NUMBER = 'valid';
    const INVALID_TAX_NUMBER = 'invalid';

    const NO_VALID_RESPONSE = 'no_response';

    /**
     * Check if vat number is valid
     *
     * @param string $countryCode Country code
     * @param string $vatNumber   VAT number
     *
     * @return boolean
     */
    abstract public function isValid($countryCode, $vatNumber);

    /**
     * Add log
     *
     * @param mixed $data Data to log
     *
     * @return void
     */
    protected function addLog($data) { // {{{
        x_log_add(get_class($this), $data);
    } // }}}
} // }}}

/**
 * Check vat number at http://isvat.appspot.com/
 */
class XCIsVATAppSpot extends AXCTaxNumberChecker { // {{{

    const END_POINT = 'isvat.appspot.com';

    protected function __construct() { // {{{
        // Load http(s) functions
        x_load('http');
        // Call parent constructor
        parent::__construct();
    } // }}}

    public static function getInstance() { // {{{
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    } // }}}

    /**
     * Check if VAT number is valid
     *
     * @param string $countryCode Country code
     * @param string $vatNumber   VAT number
     *
     * @return string (one of AXCTaxNumberChecker::VALID_TAX_NUMBER,
     *                        AXCTaxNumberChecker::INVALID_TAX_NUMBER
     *                        AXCTaxNumberChecker::NO_VALID_RESPONSE)
     */
    public function isValid($countryCode, $vatNumber) { // {{{

        $result = AXCTaxNumberChecker::NO_VALID_RESPONSE;

        if ($countryCode && $vatNumber) {

            $host = static::END_POINT;
            $url = '/' . $countryCode . '/' . $vatNumber . '/';

            $response = $this->doRequest($host, $url);

            switch ($response) {
                case 'true':
                    $result = AXCTaxNumberChecker::VALID_TAX_NUMBER;
                    break;
                case 'false':
                    $result = AXCTaxNumberChecker::INVALID_TAX_NUMBER;
                    break;
            }
            //
            // Define constants to emulate valid response
            // for debugging purpose
            //
            if (
                defined('XC_VAT_NUMBER_CHECKER_DEBUG')
                && (
                    defined('XC_VAT_NUMBER_CHECKER_EMULATE_VALID')
                    ||
                    defined('XC_VAT_NUMBER_CHECKER_EMULATE_INVALID')
                )
            ) {
                $result = defined('XC_VAT_NUMBER_CHECKER_EMULATE_VALID')
                    ? AXCTaxNumberChecker::VALID_TAX_NUMBER
                    : AXCTaxNumberChecker::INVALID_TAX_NUMBER;
            }
        }

        return $result;
    } // }}}

    /**
     * Do request
     *
     * @param string $host Host
     * @param string $url Url
     *
     * @return string ( Response or AXCTaxNumberChecker::NO_VALID_RESPONSE)
     */
    protected function doRequest($host, $url) { // {{{

        $result = AXCTaxNumberChecker::NO_VALID_RESPONSE;

        list($headers, $data) = func_http_get_request($host, $url, '', array(), false, false, 5);

        // headers are returned as an array in case libcurl is not available
        // ==> func_fsockopen_request is used in func_http_get_request
        $headers = is_array($headers) ? $headers['ERROR'] : $headers;
        
        $response = array('code' => null, 'body' => $data);

        if (
            !empty($headers)
            && preg_match("/HTTP.*\s(\d{3})\s/i", $headers, $matches)
            && !empty($matches[1]) // response code
        ) {

            $response['code'] = intval($matches[1]);

            if (200 == $response['code'] && !empty($response['body'])) {
                $result = $response['body'];
            }
        }

        if (
            defined('XC_VAT_NUMBER_CHECKER_DEBUG')
            || defined('DEVELOPMENT_MODE')
        ) {
            $this->addLog(
                array(
                    'class'     => __CLASS__,
                    'host'      => $host,
                    'url'       => $url,
                    'result'    => $result,
                    'raw'       => array (
                        'headers'   => $headers,
                        'data'      => $data
                    )
                )
            );
        }

        return $result;
    } // }}}
} // }}}

/**
 * Check VAT number
 */
class XCVatNumberChecker extends XC_Singleton { // {{{

    protected function __construct() { // {{{
        // Call parent constructor
        parent::__construct();
        // Enable caching for checkVATnumber function
        func_register_cache_function('checkVATnumber',
            array(
                'class' => __CLASS__,
                'dir'   => 'vat_number_cache',
                'hashedDirectoryLevel' => 2,
            )
        );
    } // }}}

    public static function getInstance() { // {{{
        // Call parent getter
        return parent::getClassInstance(__CLASS__);
    } // }}}

    public function getAllowedCountries() { // {{{
        return array(
            'AT', 'BE', 'CZ', 'DE', 'CY',
            'DK', 'EE', 'GR', 'ES', 'FI',
            'FR', 'GB', 'HU', 'IE', 'IT',
            'LT', 'LU', 'LV', 'MT', 'NL',
            'PL', 'PT', 'SE', 'SI', 'SK',
            'BG', 'HR', 'RO'
        );
    } // }}}

    /**
     * Check if VAT number is valid
     *
     * @param string  $countryCode Country code
     * @param string  $vatNumber   VAT number
     *
     * @return string (one of AXCTaxNumberChecker::VALID_TAX_NUMBER,
     *                        AXCTaxNumberChecker::INVALID_TAX_NUMBER)
     */
    public function checkVATnumber($countryCode, $vatNumber) { // {{{
        global $check_vat_number_request_delay, $top_message;

        $status = AXCTaxNumberChecker::INVALID_TAX_NUMBER; // initial value

        $cacheKey = md5($countryCode . $vatNumber); // cache key

        x_session_register('check_vat_number_request_delay', 0);

        $top_message['content'] = !empty($top_message['content']) ? $top_message['content'] : ''; // initial value

        if ($check_vat_number_request_delay - XC_TIME > 0) {
            // Inform customer
            $top_message['content'] = func_get_langvar_by_name('txt_vat_number_checking_service_not_available', FALSE, FALSE, TRUE);
            return $status;
        }

        if ($cacheData = func_get_cache_func($cacheKey, 'checkVATnumber')) {
            $cacheData = $cacheData['data'];
            // Inform customer
            $top_message['content'] = $cacheData !== AXCTaxNumberChecker::INVALID_TAX_NUMBER
            ? $top_message['content'] // keep original message
            : func_get_langvar_by_name('txt_vat_number_is_invalid', FALSE, FALSE, TRUE);
            // Return cached data
            return $cacheData;
        }

        $service = XCIsVATAppSpot::getInstance(); // get service instance

        if (
            $service
            && in_array($countryCode, $this->getAllowedCountries())
        ) {
            $status = $service->isValid($countryCode, $vatNumber);

            if ($status == AXCTaxNumberChecker::NO_VALID_RESPONSE) {
                // It seems service is dead, wait...
                $delay_in_min = 10;
                $check_vat_number_request_delay = XC_TIME + SECONDS_PER_MIN*$delay_in_min;
                // Inform customer
                $top_message['content'] = func_get_langvar_by_name('txt_vat_number_checking_service_not_available', FALSE, FALSE, TRUE);
            } else {
                // Valid response, cache the results
                $check_vat_number_request_delay = 0;
                func_save_cache_func($status, $cacheKey, 'checkVATnumber');
                // Inform customer
                $top_message['content'] = $status !== AXCTaxNumberChecker::INVALID_TAX_NUMBER
                    ? $top_message['content'] // keep original message
                    : func_get_langvar_by_name('txt_vat_number_is_invalid', FALSE, FALSE, TRUE);
            }
        }

        return $status;
    } // }}}
} // }}}
