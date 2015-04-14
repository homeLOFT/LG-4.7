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
 * SQL patch applying interface
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v38 (xcart_4_7_0), 2015-02-17 23:56:28, patch_sql.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: home.php"); die("Access denied"); }

define('XC_BREAK_ON_SQLERROR', TRUE);
define('XC_CONTINUE_ON_SQLERROR', !XC_BREAK_ON_SQLERROR);
function SplitSqlFile(&$ret, $sql) {
    $sql = trim($sql);
    $sql_len = strlen($sql);
    $char = '';
    $string_start = '';
    $in_string = FALSE;

    for ($i = 0; $i < $sql_len; ++$i) {
        $char = $sql[$i];

        // We are in a string, check for not escaped end of strings except for
        // backquotes that can't be escaped
        if ($in_string) {
            for (;;) {
                $i = strpos($sql, $string_start, $i);
                // No end of string found -> add the current substring to the
                // returned array
                if (!$i) {
                    $ret[] = $sql;
                    return TRUE;
                }
                elseif ($string_start == '`' || $sql[$i-1] != '\\') {
                    // Backquotes or no backslashes before quotes: it's indeed the
                    // end of the string -> exit the loop
                    $string_start = '';
                    $in_string = FALSE;
                    break;
                }
                else {
                    // one or more Backslashes before the presumed end of string...
                    // ... first checks for escaped backslashes
                    $j = 2;
                    $escaped_backslash = FALSE;
                    while ($i-$j > 0 && $sql[$i-$j] == '\\') {
                        $escaped_backslash = !$escaped_backslash;
                        $j++;
                    }

                    // ... if escaped backslashes: it's really the end of the
                    // string -> exit the loop
                    if ($escaped_backslash) {
                        $string_start = '';
                        $in_string = FALSE;
                        break;
                    }
                    else {
                        // ... else loop
                        $i++;
                    }
                }
            }
        }
        elseif ($char == ';') {
            // We are not in a string, first check for delimiter...
            // if delimiter found, add the parsed part to the returned array
            $ret[] = substr($sql, 0, $i);
            $sql = ltrim(substr($sql, min($i + 1, $sql_len)));
            $sql_len = strlen($sql);
            if ($sql_len) {
                $i = -1;
            }
            else {
                // The submited statement(s) end(s) here
                return TRUE;
            }
        }
        elseif (($char == '"') || ($char == '\'') || ($char == '`')) {
            // ... then check for start of a string,...
            $in_string    = TRUE;
            $string_start = $char;
        }
        elseif ($char == '#' || ($i >= 1 && $sql[$i-1] . $sql[$i] == '--')) {
            // ... for start of a comment (and remove this comment if found)...
            // starting position of the comment depends on the comment type
            $start_of_comment = (($sql[$i] == '#') ? $i : $i-1);
            // if no "\n" exits in the remaining string, checks for "\r"
            // (Mac eol style)
            $end_of_comment  = (strpos(' ' . $sql, "\012", $i+1))
                ? strpos(' ' . $sql, "\012", $i+1)
                : strpos(' ' . $sql, "\015", $i+1);

            if (!$end_of_comment) {
                // no eol found after '#', add the parsed part to the returned
                // array if required and exit
                if ($start_of_comment > 0) {
                    $ret[] = trim(substr($sql, 0, $start_of_comment));
                }
                return TRUE;
            }
            else {
                $sql = substr($sql, 0, $start_of_comment).ltrim(substr($sql, $end_of_comment));
                $sql_len = strlen($sql);
                $i--;
            }
        }
    }

    // add any rest to the returned array
    if (!empty($sql) && preg_match('/\S+/Ss', $sql)) {
        $ret[] = $sql;
    }

    return TRUE;
}

/**
 * This function executes multiple queries
 */
function ExecuteSqlQuery($sql_query, $on_error = XC_BREAK_ON_SQLERROR) {
    static $header_displayed = false;
    $pieces = array();
    SplitSqlFile($pieces, $sql_query);
    $pieces_count = count($pieces);

    // Runs multiple queries
    $my_die = array();
    for ($i = 0; $i < $pieces_count; $i++) {
        $a_sql_query = $pieces[$i];
        $result = db_query($a_sql_query);
        if ($result == FALSE) { // readdump failed
            $my_die[] = $a_sql_query;
            if ($on_error == XC_BREAK_ON_SQLERROR)
                break;
        }

        db_free_result($result);

        if (!$header_displayed) {
            echo "Applying SQL patch ";
            $header_displayed = true;
        }

        func_flush(". ");
    }
    unset($pieces);

    return $my_die;
}

?>
