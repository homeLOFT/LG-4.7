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
 * Some definitions for Socialize module
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v16 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if (!defined('XCART_START')) {
    header('Location: ../../');
    die('Access denied');
}

$css_files['Socialize'][] = array();
$css_files['Socialize'][] = array('altskin' => true);

$_module_dir = $xcart_dir . XC_DS . 'modules' . XC_DS . 'Socialize';

$fb_langs = array(
    'Afrikaans' => 'af_ZA',
    'Albanian' => 'sq_AL',
    'Arabic' => 'ar_AR',
    'Armenian' => 'hy_AM',
    'Azerbaijani' => 'az_AZ',
    'Basque' => 'eu_ES',
    'Belarusian' => 'be_BY',
    'Bengali' => 'bn_IN',
    'Bosnian' => 'bs_BA',
    'Bulgarian' => 'bg_BG',
    'Catalan' => 'ca_ES',
    'Chinese' => 'zh_CN',
    'Croatian' => 'hr_HR',
    'Czech' => 'cs_CZ',
    'Danish' => 'da_DK',
    'Dutch' => 'nl_NL',
    'English' => 'en_US',
    'Esperanto' => 'eo_EO',
    'Estonian' => 'et_EE',
    'Faroese' => 'fo_FO',
    'Filipino' => 'tl_PH',
    'Finnish' => 'fi_FI',
    'French' => 'fr_FR',
    'Frisian' => 'fy_NL',
    'Galician' => 'gl_ES',
    'Georgian' => 'ka_GE',
    'German' => 'de_DE',
    'Greek' => 'el_GR',
    'Hebrew' => 'he_IL',
    'Hindi' => 'hi_IN',
    'Hungarian' => 'hu_HU',
    'Icelandic' => 'is_IS',
    'Indonesian' => 'id_ID',
    'Irish' => 'ga_IE',
    'Italian' => 'it_IT',
    'Japanese' => 'ja_JP',
    'Khmer' => 'km_KH',
    'Korean' => 'ko_KR',
    'Kurdish' => 'ku_TR',
    'Latin' => 'la_VA',
    'Latvian' => 'lv_LV',
    'Lithuanian' => 'lt_LT',
    'Macedonian' => 'mk_MK',
    'Malay' => 'ms_MY',
    'Malayalam' => 'ml_IN',
    'Nepali' => 'ne_NP',
    'Norwegian' => 'nn_NO',
    'Pashto' => 'ps_AF',
    'Persian' => 'fa_IR',
    'Polish' => 'pl_PL',
    'Portuguese' => 'pt_PT',
    'Punjabi' => 'pa_IN',
    'Romanian' => 'ro_RO',
    'Russian' => 'ru_RU',
    'Serbian' => 'sr_RS',
    'Slovak' => 'sk_SK',
    'Slovenian' => 'sl_SI',
    'Spanish' => 'es_LA',
    'Swahili' => 'sw_KE',
    'Swedish' => 'sv_SE',
    'Tamil' => 'ta_IN',
    'Telugu' => 'te_IN',
    'Thai' => 'th_TH',
    'Turkish' => 'tr_TR',
    'Ukrainian' => 'uk_UA',
    'Vietnamese' => 'vi_VN',
    'Welsh' => 'cy_GB',
);

/*
  Load module functions
 */
if (!empty($include_func)) {
    require_once $_module_dir . XC_DS . 'func.php';
    if (!empty($include_init)) {
        func_soc_init();
    }
}

?>
