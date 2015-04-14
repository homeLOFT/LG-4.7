<?php
/**
 * ATConfig.class.php
 */

/**
 * Contains various service configuration parameters as class static variables.
 *
 * {@link AddressServiceSoap} and {@link TaxServiceSoap} read this file during initialization.
 *
 * @author    Avalara
 * @copyright © 2004 - 2011 Avalara, Inc.  All rights reserved.
 * @package   Base
 */
class ATConfig
{
    private static $Configurations = array();
    private $_ivars;

    public function __construct($name, $values = null)
    {
        if ($values)
            ATConfig::$Configurations[$name] = $values;
        $this->_ivars = ATConfig::$Configurations[$name];
    }

    public function __get($n)
    {
        if ($n == '_ivars')
					return parent::__get($n);

        if (isset($this->_ivars[$n]))
            return $this->_ivars[$n];
        elseif (isset(ATConfig::$Configurations['Default'][$n])) // read missing values from default
            return ATConfig::$Configurations['Default'][$n];
				return null;
		}
}

/* Specify configurations by name here.  You can specify as many as you like */
$__wsdldir = dirname(__FILE__)."/wsdl";

global $config;

/* This is the default configuration - it is used if no other configuration is specified */
new ATConfig('Default', array(
    'url'               => $config['AvaTax']['avatax_development_mode'] == 'Y'
        ? 'https://development.avalara.net': 'https://avatax.avalara.net',
    'addressService'    => '/Address/AddressSvc.asmx',
    'taxService'        => '/Tax/TaxSvc.asmx',
    'batchService'      => '/Batch/BatchSvc.asmx',
    'avacertService'    => '/AvaCert/AvaCertSvc.asmx',
    'addressWSDL'       => 'file://'.$__wsdldir.'/Address.wsdl',
    'taxWSDL'           => 'file://'.$__wsdldir.'/Tax.wsdl',
	'batchWSDL'         => 'file://'.$__wsdldir.'/BatchSvc.wsdl',
    'avacertWSDL'       => 'file://'.$__wsdldir.'/AvaCertSvc.wsdl',
    'account'           => $config['AvaTax']['avatax_account_number'],
    'license'           => $config['AvaTax']['avatax_license_key'],
    'adapter'           => 'avatax4php,5.10.0.0',
    'client'            => 'X-Cart AvaTax Module,' . constant('AVATAX_CONNECTOR_VERSION'),
    'name'              => 'PHPAdapter',
    'trace'             => true, // change to false for production
));
