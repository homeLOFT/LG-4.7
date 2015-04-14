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
 * Registration wizard for UPS Developer Kit module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v51 (xcart_4_7_0), 2015-02-17 23:56:28, ups_register.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

/**
 * Registration and Licensing within UPS
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_load('crypt','user');

/**
 * Passing through register pages
 */
x_session_register('ups_reg_step', 0);
x_session_register('ups_licensetext');
x_session_register('ups_userinfo');

/**
 * Maximum number of attempts to register with UserId is not available
 */
$max_post_counter = 3;

if ($ups_reg_step > 0)
    $ups_title = func_get_langvar_by_name('lbl_ups_wizard');

$page_title = $ups_title . ' - ' . func_get_langvar_by_name('lbl_ups_wizard_step_' . $ups_reg_step);

$location[] = array($page_title, 'ups.php');

$smarty->assign('location', $location);
$smarty->assign('ups_reg_step', $ups_reg_step);
$smarty->assign('title', $page_title);

if ($mode == 'cancel') {

    // Cancel the licensing and registering with UPS

    x_session_unregister('ups_reg_step');
    x_session_unregister('ups_licensetext');
    x_session_unregister('ups_userinfo');

    func_header_location('ups.php');
}

if ($mode == 'showlicense') {

    // Display the License Agreement text

    $pre = $ups_licensetext;
    $pre = func_convert_encoding($pre, 'ISO-8859-1', $default_charset);
    $pre = nl2br($pre);
    $pre = preg_replace("/^([0-9]{1,2}\.[0-9]*)([^0-9]+)/Um", "<b>\\1</b>\\2\\3", $pre);

    $pre = str_replace("DO YOU AGREE", "<br /><br />DO YOU AGREE", $pre);
    if (@$raw == 1) {
        // Display text directly in the new window
        if (empty($ups_licensetext)) {
            print "Sorry, license agreement is temporary unavailable. Try again later.";
        }
        else {
            echo "<div align='justify'><font style='FONT-FAMILY: Courier; FONT-SIZE: 12px;'>";
            print $pre;
            echo "</font></div>";
        }
    }
    else {
        // Display via template (getting the printable version)
        $pre = "<div align='justify'><font style='FONT-FAMILY: Courier; FONT-SIZE: 12px;'><b>UPS LICENSE AGREEMENT</b><br /><br />$pre</font></div>";
        $smarty->assign('pre', $pre);
        $smarty->assign('popup_title', 'UPS License Agreement');
        func_display('help/popup_info.tpl',$smarty);
    }
    exit;
}

if ($mode == 'fillform') {
    $userinfo = func_userinfo($logged_userid, (empty($active_modules['Simple_Mode']) ? "A" : "P"));
    if (!empty($userinfo)) {
        $ups_userinfo['contact_name'] = $userinfo['firstname']." ".$userinfo['lastname'];
        $ups_userinfo['company'] = $userinfo['company'];

        $ups_userinfo['address'] = $userinfo['s_address'];
        $ups_userinfo['city'] = $userinfo['s_city'];
        $ups_userinfo['state'] = $userinfo['s_state'];
        $ups_userinfo['country'] = $userinfo['s_country'];
        $ups_userinfo['postal_code'] = $userinfo['s_zipcode'];
        $ups_userinfo['phone'] = $userinfo['phone'];
        $ups_userinfo['email'] = $userinfo['email'];
        $ups_userinfo['url'] = $http_location;
    }
    func_header_location('ups.php');
}

/**
 * Process the POST request
 */
if ($REQUEST_METHOD == 'POST') {
    if ($current_step == 0) {

        // Start registering process and get License Agreement text from UPS

        $ups_reg_step = 1;
        func_header_location('ups.php');
    }

    if ($current_step == 1) {

        // Step 1: Start registering process and get License Agreement text from UPS

        $request=<<<EOT
<?xml version='1.0' encoding='ISO-8859-1'?>
<AccessLicenseAgreementRequest xml:lang='en-US'>
    <Request>
        <TransactionReference>
            <CustomerContext>License Test</CustomerContext>
            <XpciVersion>1.0001</XpciVersion>
        </TransactionReference>
        <RequestAction>AccessLicense</RequestAction>
        <RequestOption>AllTools</RequestOption>
    </Request>
    <DeveloperLicenseNumber>$devlicense</DeveloperLicenseNumber>
    <AccessLicenseProfile>
        <CountryCode>US</CountryCode>
        <LanguageCode>EN</LanguageCode>
    </AccessLicenseProfile>
    <OnLineTool>
        <ToolID>TrackXML</ToolID>
        <ToolVersion>1.0</ToolVersion>
    </OnLineTool>
</AccessLicenseAgreementRequest>
EOT;
        u_process($request, 'u_elem_data_agree', 'License');
        $ups_licensetext = $ps['licensetext'];

        $ups_reg_step = 2;
    }
    elseif ($current_step == 2) {

        // Step 2: Accept the License Agreement

        if ($confirmed == 'Y' && !empty($ups_licensetext)) {
            $ups_reg_step = 3;
        }
        elseif (empty($ups_licensetext)) {
            x_session_register('message');
            $message = 'no_agreement';
        }
        else {
            x_session_register('message');
            $message = 'need_to_agree';
        }

        func_header_location('ups.php');
    }
    elseif ($current_step == 3) {

        // Step 3: Fill the register form

        $fillerror = false;
        if (is_array($posted_data)) {
            // Check the entered data
            $posted_data['email'] = $email;
            $posted_data['software_installer'] = (in_array(@$posted_data['software_installer'], array('yes','no')) ? $posted_data['software_installer'] : '');
            foreach ($posted_data as $k=>$v) {
                $userinfo[$k] = stripslashes(trim($v));
                if ($k != 'state' && $k != 'shipper_number')
                $fillerror = $fillerror || empty($userinfo[$k]);
            }

            $ups_userinfo = $userinfo;

        }

        if (!$fillerror) {

            // Accepting UPS License agreement

            $version = func_query_first_cell("SELECT value FROM $sql_tbl[config] WHERE name='version'");
            $version = substr($version, 0, 16);

            $userinfo_orig = $userinfo;

            // Validate $userinfo for XML requests

            $userinfo = func_ups_xml_quote($userinfo);

            // Define proper constant for PHP < 5.4.0 version
            if (!defined('ENT_HTML401')) {define('ENT_HTML401', 0);}

            // Prepare Access License Request
            $ups_license_charset = 'ISO-8859-1';
            $ups_licensetext = htmlspecialchars($ups_licensetext, ENT_COMPAT | ENT_HTML401, $ups_license_charset);

            $request=<<<EOT
<?xml version='1.0' encoding='$ups_license_charset'?>
<AccessLicenseRequest xml:lang='en-US'>
    <Request>
        <TransactionReference>
            <CustomerContext>License Test</CustomerContext>
            <XpciVersion>1.0001</XpciVersion>
        </TransactionReference>
        <RequestAction>AccessLicense</RequestAction>
        <RequestOption>AllTools</RequestOption>
    </Request>
    <CompanyName>$userinfo[company]</CompanyName>
    <Address>
        <AddressLine1>$userinfo[address]</AddressLine1>
        <City>$userinfo[city]</City>
        <StateProvinceCode>$userinfo[state]</StateProvinceCode>
        <PostalCode>$userinfo[postal_code]</PostalCode>
        <CountryCode>$userinfo[country]</CountryCode>
    </Address>
    <PrimaryContact>
        <Name>$userinfo[contact_name]</Name>
        <Title>$userinfo[title_name]</Title>
        <EMailAddress>$userinfo[email]</EMailAddress>
        <PhoneNumber>$userinfo[phone]</PhoneNumber>
    </PrimaryContact>
    <CompanyURL>$userinfo[url]</CompanyURL>
    <ShipperNumber>$userinfo[shipper_number]</ShipperNumber>
    <DeveloperLicenseNumber>$devlicense</DeveloperLicenseNumber>
    <AccessLicenseProfile>
        <CountryCode>US</CountryCode>
        <LanguageCode>EN</LanguageCode>
        <AccessLicenseText>$ups_licensetext</AccessLicenseText>
    </AccessLicenseProfile>
    <OnLineTool>
        <ToolID>RateXML</ToolID>
        <ToolVersion>1.0</ToolVersion>
    </OnLineTool>
    <OnLineTool>
        <ToolID>TrackXML</ToolID>
        <ToolVersion>1.0</ToolVersion>
    </OnLineTool>
    <ClientSoftwareProfile>
        <SoftwareInstaller>$userinfo[software_installer]</SoftwareInstaller>
        <SoftwareProductName>X-Cart</SoftwareProductName>
        <SoftwareProvider>Creative Development LLC.</SoftwareProvider>
        <SoftwareVersionNumber>$version</SoftwareVersionNumber>
    </ClientSoftwareProfile>
</AccessLicenseRequest>
EOT;

            // Make Access License Request

            u_process($request, 'u_elem_data_accept', 'License');

            // Process Access License Response

            if (@$ps['statuscode'] <> '1' || empty($ps['licensenum'])) {
                x_session_register('message');
                $message = "UPS Access License Response: ".$ps['errordesc']." (".$ps['errorcode'].")";
            }
            else {
                db_query("UPDATE $sql_tbl[config] SET value='".addslashes(text_crypt($ps['licensenum']))."' WHERE name='UPS_accesskey'");
                $ups_userinfo = $userinfo_orig;

                // Initial sets

                $post_counter = 0;
                
                $suggest_username = false;

                $ups_username = u_generate_unique_string(0, 12);
                $ups_password = u_generate_unique_string(16, 10);

                $licensenum = $ps['licensenum'];

                while (true) {

                    // Prepare Registration Request

                    $request = XC_UPS_Register_Service::getInstance()->prepareRegisterAccountRequest($ups_username, $ups_password, $licensenum, $userinfo, $suggest_username);

                    // Make Registration Request

                    $ups_ra_result = XC_UPS_Register_Service::getInstance()->processRegisterAccount($request);

                    if ((XC_UPS_Register_Service::getInstance()->hasErrors()
                            || XC_UPS_Register_Service::getInstance()->hasExceptions()
                        )
                        && $post_counter <= $max_post_counter
                    ) {
                        // If the $ups_username is not available, repeat the request
                        // with 'SuggestUsernameIndicator' as RequestOption
                        $suggest_username = true;

                        $post_counter++;
                    }
                    else {

                        // The response have 'successful' status

                        if (
                            $suggest_username
                            && !empty($ups_ra_result)
                            && property_exists($ups_ra_result, 'SuggestedUsername')
                        ) {
                            // Use suggested name
                            $ups_username = $ups_ra_result->SuggestedUsername;
                            // Reset flag
                            $suggest_username = false;
                            // Register with suggested username
                            continue;
                        }

                        break;
                    }

                }

                if (XC_UPS_Register_Service::getInstance()->hasExceptions()) {
                    x_session_register('message');
                    $message = "UPS Registration Response: ".XC_UPS_Register_Service::getInstance()->getLastExceptionErrorCode()." (".XC_UPS_Register_Service::getInstance()->getLastExceptionErrorDescription().")";
                }
                else {
                    db_query("UPDATE $sql_tbl[config] SET value='".addslashes(text_crypt($ups_username))."' WHERE name='UPS_username'");
                    db_query("UPDATE $sql_tbl[config] SET value='".addslashes(text_crypt($ups_password))."' WHERE name='UPS_password'");
                    x_session_unregister('ups_licensetext');

                    // Save registration data for further usage

                    $ups_reg_data = array(
                        'company' => $userinfo['company'],
                        'address' => $userinfo['address'],
                        'city' => $userinfo['city'],
                        'state' => $userinfo['state'],
                        'postal_code' => $userinfo['postal_code'],
                        'country' => $userinfo['country'],
                        'contact_name' => $userinfo['contact_name'],
                        'title_name' => $userinfo['title_name'],
                        'email' => $userinfo['email'],
                        'phone' => $userinfo['phone'],
                        'url' => $userinfo['url'],
                        'shipper_number' => $userinfo['shipper_number'],
                        'software_installer' => $userinfo['software_installer']
                    );

                    db_query("REPLACE INTO $sql_tbl[config] (name, value, defvalue, variants) VALUES ('UPS_reginfo', '".addslashes(serialize($ups_reg_data))."','','')");

                    $ups_reg_step = 4;
                }
            }
        }
        else {
            // If $fillerror = true
            x_session_register('message');
            $message = 'fillerror';
            func_header_location('ups.php');
        }
    }
    elseif ($current_step == 4) {

        // Step 4: Registratioin successful

        x_session_unregister('ups_reg_step');
        x_session_unregister('ups_licensetext');
        x_session_unregister('ups_userinfo');
        func_header_location('ups.php');
    }

    func_header_location('ups.php');
}

if ($ups_reg_step == 3) {

    // Prepare to fill register form

    $userinfo = '';
    if (!empty($ups_userinfo)) {
        $userinfo = $ups_userinfo;
    }
    else {

        $ups_reg_data = unserialize($config['UPS_reginfo']);

        if (!empty($ups_reg_data) && is_array($ups_reg_data))
            $userinfo = func_array_merge($userinfo, $ups_reg_data);
        else {
            $userinfo['address'] = $config['Company']['location_address'];
            $userinfo['city'] = $config['Company']['location_city'];
            $userinfo['state'] = $config['Company']['location_state'];
            $userinfo['country'] = $config['Company']['location_country'];
            $userinfo['postal_code'] = $config['Company']['location_zipcode'];
            $userinfo['phone'] = $config['Company']['company_phone'];
            $userinfo['email'] = $config['Company']['site_administrator'];
            $userinfo['url'] = $http_location;
        }
    }

    $smarty->assign('userinfo', $userinfo);
    include $xcart_dir . '/modules/UPS_OnLine_Tools/ups_countries.php';
    include $xcart_dir . '/modules/UPS_OnLine_Tools/ups_states.php';
}

if (x_session_is_registered('message')) {
    x_session_register('message');
    $smarty->assign('message', $message);
    x_session_unregister('message');
}

?>
