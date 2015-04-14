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
 * Crypt functions
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v5 (xcart_4_7_0), 2015-02-17 23:56:28, class.XCSignature.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

abstract class XCSignature {
    protected $sqlTable = '';
    protected $featureEnabled = FALSE;
    protected $updateCondition = '';
    protected $storedSignature = '';
    protected $securityKey = FALSE;

    private $raw_data = array();

    public function __construct($in_raw_data) { // {{{

        assert('!empty($in_raw_data) /* '.__METHOD__.': in_raw_data is not provided */');

        if (!empty($in_raw_data['signature'])) {
            $this->storedSignature = $in_raw_data['signature'];
        }

        $this->raw_data = $in_raw_data;

    } // }}}

    public function checkSignature() { // {{{
        if (!$this->featureEnabled)
            return TRUE;

        if (!$this->isApplicable($this->raw_data))
            return FALSE;// Must return FALSE for empty raw_data for security reason;

        assert('!empty($this->storedSignature) /* '.__METHOD__.': Signature is empty*/');

        $calculated_signature = $this->calculateSignature($this->raw_data);
        $result = !empty($calculated_signature) && $calculated_signature === $this->storedSignature;
        assert('$result /* '.__METHOD__.': checkSignature was failed*/');
        return $result;
    } // }}}

    public function updateSignature() { // {{{
        global $sql_tbl;
        assert('!empty($this->updateCondition) && !empty($this->raw_data) /* '.__METHOD__.': raw_data is not provided */');

        if (!$this->isApplicable($this->raw_data))
            return FALSE;

        $new_signature = $this->calculateSignature($this->raw_data);
        if (!empty($new_signature)) {
            if ($new_signature != $this->storedSignature) {
                $res = db_query("UPDATE $this->sqlTable SET signature='".addslashes($new_signature)."' WHERE {$this->updateCondition}");
            } else {
                $res = TRUE;
            }
        }

        return !empty($res);
    } // }}}

    // For x_log_add function
    public static function removeSecureKeysFromString($message) { // {{{
        $message = preg_replace('/securityKey.*/i', '***securityKey**removed**', $message);
        $message = preg_replace('/[^_]signature.*/', '***signature**removed**', $message);
        $message = preg_replace('/storedSignature.*/', '***storedSignature**removed**', $message);
        return $message;
    } // }}}

    protected function calculateSignature($signed_data) { // {{{

        $key_fields = $this->getSignedFields('data2sign');

        $signed_data = array_intersect_key($signed_data, $key_fields);
        ksort($signed_data);

        $is_valid = (!in_array(null, $signed_data, TRUE) && count($key_fields) == count($signed_data));
        assert('$is_valid && !empty($this->securityKey)/* '.__METHOD__.': Protected data have null values or securityKey is not set*/');

        if ($is_valid) {
            $signature = implode('', $signed_data);
            $res = sha1($signature . $this->securityKey);
        } else {
            $res = '';
        }

        assert('!empty($res) /* '.__METHOD__.': Signature is empty*/');

        return $res;
            
    } // }}}

} // abstract class XCSignature

class XCUserSignature extends XCSignature {

    public function __construct($in_profile) { // {{{
        global $xc_security_key_session;

        $this->securityKey = $xc_security_key_session;

        $this->sqlTable = XC_TBL_PREFIX . 'customers';
        $this->featureEnabled = XCSecurity::CHECK_CUSTOMERS_INTEGRITY;

        if (!empty($in_profile['id'])) {
            $this->updateCondition = ('id=' . intval($in_profile['id']));
        }

        return parent::__construct($in_profile);
    } // }}}

    public static function getApplicableSqlCondition() { // {{{
        global $sql_tbl;
        return " $sql_tbl[customers].usertype IN ('A','P') ";
    } // }}}

    public static function getSignedFields($format = 'sql') { // {{{
        $signedFields = array('email' => 1, 'id' => 1, 'login' => 1, 'password' => 1, 'status' => 1, 'usertype' => 1, 'signature' => 1);
        if ($format == 'sql') {
            return ' ' . implode(", ", array_keys($signedFields)) . ' ';
        } else {
            unset($signedFields['signature']);
            return $signedFields;
        }
    } // }}}

    public static function isApplicable($profile) { // {{{
        return isset($profile['usertype']) && in_array($profile['usertype'], array('A', 'P'));
    } // }}}

    // Overwrite parent assert to check null data2sign data
    protected function calculateSignature($profile) { // {{{
        
        if (defined('DEVELOPMENT_MODE')) {
            $key_fields = $this->getSignedFields('data2sign');
            $profile = array_intersect_key($profile, $key_fields);
            assert('count(array_filter($profile)) == count($profile) && !empty($this->securityKey)/* '.__METHOD__.': Protected data have empty values or securityKey is not set*/');
        }

        $res = parent::calculateSignature($profile);
        return $res;
            
    } // }}}

}

class XCUserXauthIdsSignature extends XCSignature {

    public function __construct($in_profile) { // {{{
        global $xc_security_key_session;

        $this->securityKey = $xc_security_key_session;

        $this->sqlTable = XC_TBL_PREFIX . 'xauth_user_ids';
        $this->featureEnabled = XCSecurity::CHECK_XAUTH_USER_IDS_INTEGRITY;

        if (!empty($in_profile['auth_id'])) {
            $this->updateCondition = ('auth_id=' . intval($in_profile['auth_id']));
        }

        return parent::__construct($in_profile);
    } // }}}

    public static function getApplicableSqlCondition() { // {{{
        global $sql_tbl;
        return " $sql_tbl[customers].usertype IN ('A','P') ";
    } // }}}

    public static function getSignedFields($format = 'sql') { // {{{
        global $sql_tbl;

        if ($format == 'sql') {
            $signedFields = array("$sql_tbl[xauth_user_ids].auth_id" => 1, "$sql_tbl[xauth_user_ids].id" => 1, "$sql_tbl[xauth_user_ids].identifier" => 1, "$sql_tbl[xauth_user_ids].provider" => 1, "$sql_tbl[xauth_user_ids].service" => 1, "$sql_tbl[customers].usertype" => 1, "$sql_tbl[xauth_user_ids].signature" => 1);
            return ' ' . implode(", ", array_keys($signedFields)) . ' ';
        } else {
            $signedFields = array('auth_id' => 1, 'id' => 1, 'identifier' => 1, 'provider' => 1, 'service' => 1, 'usertype' => 1);
            return $signedFields;
        }
    } // }}}

    public static function isApplicable($profile) { // {{{
        return isset($profile['usertype']) && in_array($profile['usertype'], array('A', 'P'));
    } // }}}

    // Overwrite parent assert to check null data2sign data
    protected function calculateSignature($profile) { // {{{
        
        if (defined('DEVELOPMENT_MODE')) {
            $key_fields = $this->getSignedFields('data2sign');
            $profile = array_intersect_key($profile, $key_fields);
            assert('count(array_filter($profile)) == count($profile) && !empty($this->securityKey)/* '.__METHOD__.': Protected data have empty values or securityKey is not set*/');
        }

        $res = parent::calculateSignature($profile);
        return $res;
            
    } // }}}

}

class XCResetPasswordSignature extends XCSignature {

    public function __construct($in_reset_password_row) { // {{{
        global $xc_security_key_session;

        $this->securityKey = $xc_security_key_session;

        $this->sqlTable = XC_TBL_PREFIX . 'reset_passwords';
        $this->featureEnabled = XCSecurity::CHECK_RESET_PASSWORDS_INTEGRITY;

        if (!empty($in_reset_password_row['userid'])) {
            $this->updateCondition = ('userid=' . intval($in_reset_password_row['userid']));
        }

        return parent::__construct($in_reset_password_row);
    } // }}}

    public static function getApplicableSqlCondition() { // {{{
        return " 1 ";
    } // }}}

    public static function getSignedFields($format = 'sql') { // {{{
        $signedFields = array('password_reset_key' => 1, 'password_reset_key_date' => 1, 'userid' => 1, 'signature' => 1);
        if ($format == 'sql') {
            return ' ' . implode(", ", array_keys($signedFields)) . ' ';
        } else {
            unset($signedFields['signature']);
            return $signedFields;
        }
    } // }}}

    public static function isApplicable($reset_password_row) { // {{{
        return isset($reset_password_row['password_reset_key']) && isset($reset_password_row['userid']);
    } // }}}

    // Overwrite parent assert to check empty data2sign data
    protected function calculateSignature($reset_password_row) { // {{{
        
        if (defined('DEVELOPMENT_MODE')) {
            $key_fields = $this->getSignedFields('data2sign');
            $reset_password_row = array_intersect_key($reset_password_row, $key_fields);
            assert('count(array_filter($reset_password_row)) == count($reset_password_row) && !empty($this->securityKey)/* '.__METHOD__.': Protected data have empty values or securityKey is not set*/');
        }

        $res = parent::calculateSignature($reset_password_row);
        return $res;
            
    } // }}}

}

class XCConfigSignature extends XCSignature {
    private static $validatedConfigs = array( // {{{
        'site_administrator' => 'Site administrator email address', // without category
        'ip_register_codes' => 'List of IP addresses awaiting registration', // without category
        'allowed_ips' => 'Allowed IP addresses', // without category
        'xpc_allowed_ip_addresses' => 'IP addresses for X-Payments callbacks', // XPayments_Connector
        'smtp_server' => 'SMTP server', // Email
        'use_smtp' => 'Use SMTP server instead of internal PHP mailer', // Email
        'unallowed_request_notify' => 'Notify the site administrator by email if unallowed request to site occurs', // Email_Note
        'eml_login_error' => 'Login error notification to site administrator', // Email_Note
        'admin_sqlerror_notify' => 'Notify the site administrator about SQL errors in the store by email', // Email_Note
        'allow_ips' => 'Check if payment gateway response is coming from the IP\'s specified here (enter a comma separated list)', // Security
        'HTTPS_test_passed' => 'Check if HTTPS connection to site is available', // Secutiry
    ); // }}}


    public function __construct($in_config_row) { // {{{
        global $xc_security_key_config;

        $this->securityKey = $xc_security_key_config;

        $this->sqlTable = XC_TBL_PREFIX . 'config';
        $this->featureEnabled = XCSecurity::CHECK_CONFIG_INTEGRITY;

        if (!empty($in_config_row['name'])) {
            $this->updateCondition = ("name='" . addslashes($in_config_row['name']) . "'");
        }

        return parent::__construct($in_config_row);
    } // }}}

    public static function getApplicableSqlCondition() { // {{{
        return " name IN ('" . implode("','", array_keys(self::$validatedConfigs)) . "') ";
    } // }}}

    public static function getSignedFields($format = 'sql') { // {{{
        $signedFields = array('category' => 1, 'name' => 1, 'type'=> 1, 'value' => 1, 'signature' => 1);
        if ($format == 'sql') {
            return ' ' . implode(", ", array_keys($signedFields)) . ' ';
        } else {
            unset($signedFields['signature']);
            return $signedFields;
        }
    } // }}}

    public static function isApplicable($config_data) { // {{{
        return isset($config_data['name']) && in_array($config_data['name'], array_keys(self::$validatedConfigs));
    } // }}}


    public static function getSignedConfigs() { // {{{
        return self::$validatedConfigs;
    } // }}}

}

?>
