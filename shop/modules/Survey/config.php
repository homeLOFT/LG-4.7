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
 * Module configuration
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Modules
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v27 (xcart_4_7_0), 2015-02-17 23:56:28, config.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header('Location: ../../'); die('Access denied'); }

$addons['Survey'] = true;

$css_files['Survey'][] = array();

$sql_tbl['surveys'] = "xcart_surveys";
$sql_tbl['survey_questions'] = "xcart_survey_questions";
$sql_tbl['survey_answers'] = "xcart_survey_answers";
$sql_tbl['survey_events'] = "xcart_survey_events";
$sql_tbl['survey_maillist'] = "xcart_survey_maillist";
$sql_tbl['survey_results'] = "xcart_survey_results";
$sql_tbl['survey_result_answers'] = "xcart_survey_result_answers";

$survey_types = array('D', 'P', 'R', 'H');
$survey_events = array('OPL', 'OPP', 'OPC', 'OPB');
$answers_types = array('R', 'C', 'N');

x_session_register('allowed_surveys');

if (defined('TOOLS')) {
    $tbl_keys['survey_questions.surveyid'] = array(
        'keys' => array('survey_questions.surveyid' => 'surveys.surveyid'),
        'fields' => array('surveyid','questionid')
    );
    $tbl_keys['survey_answers.questionid'] = array(
        'keys' => array('survey_answers.questionid' => 'survey_questions.questionid'),
        'fields' => array('questionid','answerid')
    );
    $tbl_keys['survey_events.surveyid'] = array(
        'keys' => array('survey_events.surveyid' => 'surveys.surveyid'),
        'fields' => array('surveyid')
    );
    $tbl_keys['survey_maillist.surveyid'] = array(
        'keys' => array('survey_maillist.surveyid' => 'surveys.surveyid'),
        'fields' => array('surveyid', 'email')
    );
    $tbl_keys['survey_results.surveyid'] = array(
        'keys' => array('survey_results.surveyid' => 'surveys.surveyid'),
        'fields' => array('surveyid', 'sresultid')
    );
    $tbl_keys['survey_result_answers.sresultid'] = array(
        'keys' => array('survey_result_answers.sresultid' => 'survey_results.sresultid'),
        'fields' => array('sresultid', 'questionid')
    );
    $tbl_keys['survey_result_answers.questionid'] = array(
        'keys' => array('survey_result_answers.questionid' => 'survey_questions.questionid'),
        'fields' => array('sresultid', 'questionid')
    );
    $tbl_keys['survey_result_answers.answerid'] = array(
        'keys' => array('survey_result_answers.answerid' => 'survey_answers.answerid'),
        'where' => "survey_result_answers.answerid > 0",
        'fields' => array('sresultid', 'questionid')
    );
    $tbl_demo_data['Survey'] = array(
        'surveys' => '',
        'survey_questions' => '',
        'survey_answers' => '',
        'survey_events' => '',
        'survey_results' => '',
        'survey_result_answers' => '',
        'survey_maillist' => ''
    );
}

$_module_dir  = $xcart_dir . XC_DS . 'modules' . XC_DS . 'Survey';
/*
 Load module functions
*/
if (!empty($include_func)) {
    require_once $_module_dir . XC_DS . 'func.php';
    if (!empty($include_init)) {
        func_survey_init();
    }
}

?>
