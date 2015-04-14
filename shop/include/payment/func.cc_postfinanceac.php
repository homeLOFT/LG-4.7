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
 * Functions for "Post Finance (Advanced e-Commerce)" payment module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v21 (xcart_4_7_0), 2015-02-17 23:56:28, func.cc_postfinanceac.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

/**
 * Get currencies list
 */
function func_cc_postfinanceac_get_currencies()
{
    return array(
          'AED' => 'United Arab Emirates Dirham',
          'ANG' => 'Netherlands Antillean guilder',
          'AUD' => 'Australian Dollar',
          'AWG' => 'Aruban florin',
          'BGN' => 'Bulgarian lev',
          'BRL' => 'Brazilian real',
          'BYR' => 'Belarussian ruble',
          'CAD' => 'Canadian Dollar',
          'CHF' => 'Swiss Franc',
          'CNY' => 'Yuan Renminbi',
          'CZK' => 'Czech Koruna',
          'DKK' => 'Danish Kroner',
          'EGP' => 'Egyptian pound',
          'EUR' => 'EURO',
          'GBP' => 'British pound',
          'GEL' => 'Georgian Lari',
          'HKD' => 'Hong Kong Dollar',
          'HRK' => 'Croatian Kuna',
          'HUF' => 'Hungarian Forint',
          'ILS' => 'New Shekel',
          'ISK' => 'Iceland Krona',
          'JPY' => 'Japanese Yen',
          'LTL' => 'Litas',
          'MAD' => 'Moroccan Dirham',
          'MXN' => 'Peso',
          'NOK' => 'Norwegian Kroner',
          'NZD' => 'New Zealand Dollar',
          'PLN' => 'Polish Zloty',
          'RON' => 'Romanian leu',
          'RUB' => 'Russian Rouble',
          'SEK' => 'Swedish Krone',
          'SGD' => 'Singapore Dollar',
          'THB' => 'Thai Bath',
          'TRY' => 'Turkey New Lira',
          'UAH' => 'Ukraine Hryvnia',
          'USD' => 'US Dollar',
          'XAF' => 'CFA Franc BEAC',
          'XOF' => 'CFA Franc BCEAO',
          'XPF' => 'CFP Franc',
          'ZAR' => 'South African Rand',
    );
}

?>
