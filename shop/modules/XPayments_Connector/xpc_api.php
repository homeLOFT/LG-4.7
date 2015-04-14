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
 * Functions for X-Payments Connector module that are essential for connection API
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v13 (xcart_4_7_0), 2015-02-17 23:56:28, xpc_api.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../../"); die("Access denied"); }

/**
 * Max API version supported
 */
define('XPC_API_VERSION', '1.4');

/**
 * Validate received XML
 *
 * @param stirng $xml    XML to validate
 * @param string $schema XML schema
 * @param string $error  error message
 *
 * @return bool
 */
function xpc_validate_xml_against_schema($xml, $schema, &$error)
{
    // For debug purposes - to get error occured in schemaValidateSource() function
    libxml_use_internal_errors(true);

    // We use DOMDocument object to validate XML againest schema
    $dom = new DOMDocument();
    $dom->loadXML($xml);

    // Add common schema elements
    $schema = '
<xsd:schema xmlns:xsd="http://www.w3.org/2001/XMLSchema">

 <xsd:element name="' . XPC_TAG_ROOT . '">

  <xsd:complexType>
   <xsd:sequence>

    ' . $schema . '
    <xsd:element name="error" type="xsd:string"/>
    <xsd:element name="error_message" type="xsd:string"/>
    <xsd:element name="is_error_message" type="xsd:string" minOccurs="0"/>
    <xsd:element name="version" type="xsd:string" minOccurs="0" maxOccurs="1"/>

   </xsd:sequence>
  </xsd:complexType>

 </xsd:element>

</xsd:schema>';

    // Validate XML againest schema
    $result = @$dom->schemaValidateSource($schema);

    // Retrieve errors
    if (!$result) {
        $errors = libxml_get_errors();
        $error = $errors[0]->message;
    }

    return $result;
}

/**
 * Return validation schema for payment modules list
 *
 * @return array
 */
function xpc_request_get_payment_methods_schema()
{
    return '
<xsd:element name="' . XPC_MODULE_INFO . '" minOccurs="0" maxOccurs="unbounded">
 <xsd:complexType>
  <xsd:sequence>

   <xsd:element name="name" type="xsd:string"/>

   <xsd:element name="id" type="xsd:positiveInteger"/>

   <xsd:element name="transactionTypes">
    <xsd:complexType>
     <xsd:sequence>
       <xsd:element name="' . XPC_TRAN_TYPE_SALE . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_AUTH . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_CAPTURE . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_CAPTURE_PART . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_CAPTURE_MULTI . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_VOID . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_VOID_PART . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_VOID_MULTI . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_REFUND . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_REFUND_PART . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_REFUND_MULTI . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_GET_INFO . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_ACCEPT . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_DECLINE . '" type="xsd:boolean" default="0"/>
       <xsd:element name="' . XPC_TRAN_TYPE_TEST . '" type="xsd:boolean" default="0"/>
     </xsd:sequence>
    </xsd:complexType>
   </xsd:element>

   <xsd:element name="authCaptureInfo">
    <xsd:complexType>
     <xsd:sequence>
       <xsd:element name="authExp" type="xsd:nonNegativeInteger"/>
       <xsd:element name="captMinLimit" type="xsd:string"/>
       <xsd:element name="captMaxLimit" type="xsd:string"/>
     </xsd:sequence>
    </xsd:complexType>
   </xsd:element>

   <xsd:element name="moduleName" type="xsd:string"/>

   <xsd:element name="settingsHash" type="xsd:string"/>

   <xsd:element name="currency" type="xsd:string" minOccurs="0"/>

   <xsd:element name="canSaveCards" type="xsd:string" minOccurs="0"/>

   <xsd:element name="class" type="xsd:string" minOccurs="0"/>

   <xsd:element name="isTestMode" type="xsd:string" minOccurs="0"/>

  </xsd:sequence>

  <xsd:attribute name="type" type="xsd:string"/>

 </xsd:complexType>
</xsd:element>
';
}

/**
 * Return validation schema for the "init payment" action
 *
 * @return string
 * @see    ____func_see____
 * @since  1.0.0
 */
function xpc_request_payment_init_schema()
{
    return '
<xsd:element name="token" minOccurs="0">

 <xsd:simpleType>
  <xsd:restriction base="xsd:string">

   <xsd:maxLength value="32"/>
   <xsd:minLength value="32"/>

  </xsd:restriction>
 </xsd:simpleType>

</xsd:element>
<xsd:element name="txnId" minOccurs="0">

 <xsd:simpleType>
  <xsd:restriction base="xsd:string">

   <xsd:maxLength value="32"/>
   <xsd:minLength value="32"/>

  </xsd:restriction>
 </xsd:simpleType>

</xsd:element>';

}

/**
 * Return validation schema for test request
 *
 * @return array
 */
function xpc_request_test_schema()
{
    return '
<xsd:element name="hashCode" minOccurs="0">

 <xsd:simpleType>
  <xsd:restriction base="xsd:string">

   <xsd:maxLength value="32"/>
   <xsd:minLength value="32"/>

  </xsd:restriction>
 </xsd:simpleType>

</xsd:element>';
}

?>
