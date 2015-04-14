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
 * Benchmark library
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v58 (xcart_4_7_0), 2015-02-17 23:56:28, bench.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_START') ) { header("Location: ../"); die("Access denied"); }

define('BENCH_FILE_PREFIX', "x-bench-");

define('BENCH_RECORD_LENGTH', 30);

$__smarty_start = FALSE;

function __add_mark($data = '', $type = 'PHP', $is_force = FALSE) { // {{{
    global $bench_profilier, $bench_counts, $__bench_last_time, $xcart_dir, $bench_max_memory, $__smarty_start, $config, $__bench_last_mem;

    if (
        defined('BENCH_BLOCK')
        || !defined('BENCH')
        || !constant('BENCH')
        || (defined('BENCH_DISPLAY') && !$is_force)
    ) {
        return FALSE;
    }

    if ($__smarty_start && $type == 'PHP') {
        $type = 'SMARTY';
    }

    if (empty($__bench_last_time)) {
        list($_usec, $_sec) = explode(" ", constant('XCART_START_TIME'));
        $__bench_last_time = ((float)$_usec + (float)$_sec);
        $__bench_last_mem = constant('XCART_START_MEM');
    }

    $trace = array();
    list($usec, $sec) = explode(" ",microtime());
    $t = ((float)$usec + (float)$sec);
    $mem = function_exists('memory_get_usage') ? memory_get_usage() : 0;
    if ($bench_max_memory < $mem)
        $bench_max_memory = $mem;

    if (
        defined('BENCH_SIMPLE')
        && !constant('BENCH_SIMPLE')
    ) {

        if (
            defined('BENCH_BACKTRACE')
            && constant('BENCH_BACKTRACE')
        ) {

            // Get trace information
            $trace = debug_backtrace();

            foreach ($trace as $k => $v) {

                if (!empty($v['file'])) {

                    $trace[$k] = str_replace($xcart_dir.'/', '', $v['file']).":".$v['line'];

                } else {

                    unset($trace[$k]);

                }

            }

            $trace = array_values(array_reverse($trace));

        }

        if (
            !defined('BENCH_LOG_ONLY')
            || !constant('BENCH_LOG_ONLY')
        ) {
            $bench_profilier[] = array(
                'mark' => $t,
                'type' => $type,
                'data' => $data,
                'mem' => $mem,
                'trace' => $trace
            );
        }

//        assert('!isset($config)');
        if (TRUE
            && (empty($config['Logging']['log_bench_reports']) || $config['Logging']['log_bench_reports'] != 'N')
            && defined('BENCH_DISPLAY_TYPE')
            && constant('BENCH_DISPLAY_TYPE') != 'T'
            && function_exists('x_log_flag')
        ) {

            $max = defined('BENCH_TIME_LIMIT') ? constant('BENCH_TIME_LIMIT') : 0.05;

            // Add to log benchmark report
            static $limit_types = FALSE;
            if (defined('BENCH_LOG_TYPE_LIMIT') && $limit_types === FALSE) {
                $limit_types = explode(",", constant('BENCH_LOG_TYPE_LIMIT'));
                $limit_types = func_array_map('trim', $limit_types);
                $limit_types = preg_grep("/.+/S", $limit_types);
            }

            $delta_t = ($t-$__bench_last_time);

            if (
                (!defined('BENCH_LOG_TIME_LIMIT') || constant('BENCH_LOG_TIME_LIMIT') <= 0 || constant('BENCH_LOG_TIME_LIMIT') < $delta_t) &&
                (empty($limit_types) || in_array($type, $limit_types))
            ) {

                $mem_max = defined('BENCH_MEM_LIMIT') ? constant('BENCH_MEM_LIMIT') : 0.1;
                $delta_m = round(($mem-$__bench_last_mem)/1048576, 3);
                $msg = $type." (time: ".($delta_t > $max ? "! " : '').sprintf("%.4f", $delta_t)."; mem: ".sprintf("%.3f", $delta_m).")\n";

                if ($type == 'SQL') {
                    $msg .= "\tQuery: ".$data['query']."\n";

                } elseif (!empty($data)) {
                    $msg .= "\tData: ".(is_array($data) ? serialize($data) : $data)."\n";
                }

                if (!empty($trace)) {
                    $msg .= "\tTrace: ".implode(" > ", $trace)."\n";
                }

                x_log_flag('log_bench_reports', 'BENCH', $msg);
            }
        }

    }

    if (!isset($bench_counts[$type])) {
        $bench_counts[$type] = array('time' => 0, 'points' => 0);
    }

    $bench_counts[$type]['time'] += $t - $__bench_last_time;
    $bench_counts[$type]['points']++;
    $__bench_last_time = $t;
} // }}}

function __add_mark_smarty($tpl = FALSE) { // {{{
    global $__smarty_start;

    if ($tpl === FALSE) {
        __add_mark();
    } else {
        __add_mark($tpl, 'SMARTY');
    }

    $__smarty_start = !$__smarty_start;
} // }}}

function func_bench_depack($s) { // {{{
    if (strlen($s) != constant('BENCH_RECORD_LENGTH'))
        return FALSE;

    return unpack('Lpageid/Ldate/fsql/fphp/fsmarty/Smax_memory/Lmax_session/Lsize', $s);
} //}}}

function func_bench_next($fp, $pageid = FALSE) { // {{{
    if (!is_resource($fp) || !$fp)
        return FALSE;

    if ($pageid === FALSE) {
        return func_bench_depack(fread($fp, constant('BENCH_RECORD_LENGTH')));

    } else {
        do {
            $s = fread($fp, constant('BENCH_RECORD_LENGTH'));
            if (strlen($s) != constant('BENCH_RECORD_LENGTH'))
                return FALSE;
        } while (array_shift(unpack('L', substr($s, 0, 4))) != $pageid);

        return func_bench_depack($s);
    }
} //}}}

function func_bench_seek($fp, $pos) { // {{{
    if (!is_resource($fp) || !$fp)
        return FALSE;

    return fseek($fp, strlen(constant('X_LOG_SIGNATURE'))+$pos*constant('BENCH_RECORD_LENGTH'));
} //}}}

if (defined('BENCH_BLOCK') || !defined('BENCH') || !constant('BENCH'))
    return TRUE;

/**
 * Display and save benchmark data
 */
function __debug() { //{{{
    global $bench_profilier, $bench_counts, $bench_max_memory, $bench_max_session, $__smarty_size, $config;
    global $PHP_SELF, $REQUEST_METHOD, $sql_tbl, $var_dirs, $xcart_dir, $xcart_web_dir, $QUERY_STRING;
    define('BENCH_DISPLAY', TRUE);
    // Add benchmark result to file
    if (
        function_exists('db_query')
        && !empty($sql_tbl['benchmark_pages'])
        && (
            !defined('BENCH_BLOCK_SAVE_BIN')
            || !constant('BENCH_BLOCK_SAVE_BIN')
        )
    ) {
        $fn = $var_dirs['log'] . '/' . constant('BENCH_FILE_PREFIX') . date('ymd') . '.php';
        $fp = fopen($fn, 'ab');
        if (FALSE !== $fp) {
            if (filesize($fn) == 0) {
                fwrite($fp, constant('X_LOG_SIGNATURE'));
            }

            $script = str_replace($xcart_web_dir.'/', '', $PHP_SELF);
            $method = ($REQUEST_METHOD == 'POST') ? 'P' : 'G';
            if (
                $method == 'P'
                && !empty($_POST['mode'])
                && strpos($QUERY_STRING, "mode=") === FALSE
            ) {
                $QUERY_STRING .= (empty($QUERY_STRING) ? '' : "&")."mode=".$_POST['mode'];
            }

            // Detect page
            $pid = func_query_first_cell("SELECT pageid FROM $sql_tbl[benchmark_pages] WHERE script = '".addslashes($script)."' AND method = '$method' AND data = '" . addslashes($QUERY_STRING) . "'");
            // Add page info
            if (empty($pid)) {
                $query_data = array(
                    'script'     => addslashes($script),
                    'method'     => $method,
                    'data'         => addslashes($QUERY_STRING),
                );

                $pid = func_array2insert('benchmark_pages', $query_data);
            }

            // Write benchmark result to file
            if( !isset( $bench_counts['SQL'])) {
                $bench_counts['SQL']['time'] = 0;
            }

            if( !isset( $bench_counts['PHP'])) {
                $bench_counts['PHP']['time'] = 0;
            }

            if( !isset( $bench_counts['SMARTY'])) {
                $bench_counts['SMARTY']['time'] = 0;
            }

            fwrite($fp, pack('LLfffSLL', $pid, XC_TIME, $bench_counts['SQL']['time'], $bench_counts['PHP']['time'], $bench_counts['SMARTY']['time'], round($bench_max_memory/1024, 0), $bench_max_session, $__smarty_size));
            fclose($fp);
            func_chmod_file($fn);
        }
    }

    if (!defined('BENCH_SIMPLE') || constant('BENCH_SIMPLE') || defined('QUICK_START')) {
        return TRUE;
    }

    list($usec, $sec) = explode(" ", constant('XCART_START_TIME'));
    $last_t = $xst = ((float)$usec + (float)$sec);
    $full_time = func_microtime() - $xst;
    __add_mark('','PHP', TRUE);
    $sum_points = 0;
    foreach ($bench_counts as $tname => $t) {
        if (empty($t['points'])) {
            $bench_counts[$tname]['points'] = 0;
        } else {
            $sum_points += $t['points'];
        }
    }

    $last_m = constant('XCART_START_MEM');
    $max = defined('BENCH_TIME_LIMIT') ? constant('BENCH_TIME_LIMIT') : 0.05;
    $mem_max = defined('BENCH_MEM_LIMIT') ? constant('BENCH_MEM_LIMIT') : 0.1;
    if ($config['Logging']['log_bench_reports'] != 'N') {
        // Logging benchamrk reports
        if (defined('BENCH_LOG_SUMMARY') && constant('BENCH_LOG_SUMMARY')) {
            $msg = '';
            foreach ($bench_counts as $tname => $t) {
                // Add to log bencmark counters by type
                $msg .= sprintf("$tname:\ttime: %.4f", $t['time']);
                $msg .= " (".round($t['time']/$full_time*100, 2)."%);\treference points: ".$t['points']." (".round($t['points']/$sum_points*100, 2)."%)\n";
            }

            // Add to log summary counters
            $msg .= sprintf("Full time: %.4f\n", $full_time);
            if (constant('XCART_START_MEM') > 0) {
                $msg .= sprintf("Used memory: %.3f\n", round((memory_get_usage()-constant('XCART_START_MEM'))/1048576, 3));
                $msg .= sprintf("Max. used memory: %.3f\n", round(($bench_max_memory-constant('XCART_START_MEM'))/1048576, 3));
            }

            $msg .= sprintf("Max. saved session data: %.3f\n", round($bench_max_session/1024, 3));
            x_log_flag('log_bench_reports', 'BENCH', $msg);
        }

        if (defined('BENCH_LOG_ONLY') && constant('BENCH_LOG_ONLY')) {
            return TRUE;
        }
    }

    if ($REQUEST_METHOD != 'GET') {
        return TRUE;
    }

    $last_t = $xst;

    if( defined('BENCH_REPORT_FIXED_POSITION') && constant('BENCH_REPORT_FIXED_POSITION')) {
        echo '<div style="position: fixed; border: 1px solid grey; bottom: 0px; right: 0px; width: auto; z-index: 9999">';
    }
?>

<table cellpadding="3" cellspacing="0">
<tr>
<td bgcolor="white" colspan="5"><h1 style="margin: 0px">Benchmark report</h1></td>
</tr>
<tr>
<td bgcolor="#eeeeee" colspan="4"><b>Start time:</b>
<?php echo date("m/d/Y H:i:s", $xst); ?></td>
</tr>

<?php
    foreach ($bench_counts as $tname => $t) {
?>
<tr>
<td colspan="4" bgcolor="#cccccc"><b>[<?php echo $tname?>]</b></td>
</tr>
<tr bgcolor="#eeeeee">
<td><b>Time:</b></td>
<td><?php echo sprintf("%.4f", $t['time'])." (".round($t['time']/$full_time*100, 2)."%)"; ?>
<td><b>Reference points:</b></td>
<td><?php echo "$t[points] (".round($t['points']/$sum_points*100, 2)."%)"; ?></td>
</tr>

<?php
    }
?>
<!--
<BENCH_SQL value="<?php echo $bench_counts['SQL']['time']; ?>" />
<BENCH_PHP value="<?php echo $bench_counts['PHP']['time']; ?>" />
<BENCH_SMARTY value="<?php echo $bench_counts['SMARTY']['time']; ?>" />
-->

<tr>
<td colspan="4" bgcolor="#cccccc"><b>[SUMMARY]</b></td>
</tr>
<tr bgcolor="#eeeeee">
<td colspan="4"><b>FULL TIME:</b>
<?php echo sprintf("%.4f", $full_time);?></td>
</tr>

<?php
    if (constant('XCART_START_MEM') > 0) {
?>
<tr bgcolor="#eeeeee">
<td colspan="4"><b>USED MEMORY:</b>
<?php echo sprintf("%.3f", round((memory_get_usage()-constant('XCART_START_MEM'))/1048576, 3)); ?>Mb</td>
</tr>
<tr bgcolor="#eeeeee">
<td colspan="4"><b>MAX. USED MEMORY:</b>
<?php echo sprintf("%.3f", round(($bench_max_memory-constant('XCART_START_MEM'))/1048576, 3)); ?>Mb</td>
</tr>
<!--
<BENCH_MAX_MEM value="<?php echo $bench_max_memory; ?>" />
-->
<?php } ?>
<tr bgcolor="#eeeeee">
<td colspan="4"><b>MAX. SAVED SESSION DATA:</b>
<?php echo sprintf("%.3f", round($bench_max_session/1024, 3)); ?>Kb</td>
</tr>
</table>
<?php
    if( defined('BENCH_REPORT_FIXED_POSITION') && constant('BENCH_REPORT_FIXED_POSITION')) {
        echo '</div>';
    }

    $white = 'white';
    $grey = "#eeeeee";
    $red = 'red';
    $green = 'lightgreen';

    if (!is_array($bench_profilier) || empty($bench_profilier) || !defined('BENCH_DISPLAY_TYPE') || constant('BENCH_DISPLAY_TYPE') == 'T')
        return TRUE;

    $last_m = constant('XCART_START_MEM');

    $max = defined('BENCH_TIME_LIMIT') ? constant('BENCH_TIME_LIMIT') : 0.05;
    $mem_max = defined('BENCH_MEM_LIMIT') ? constant('BENCH_MEM_LIMIT') : 0.1;

?>

<h2>*** Detailed report</h2>

<table bgcolor="black" cellpadding="2" cellspacing="1" width="100%">
<tr bgcolor="#dddddd">
    <th>#</th>
    <th>Time</th>
    <th nowrap="nowrap">Memory, Mb</th>
    <th align="left">Label</th>
</tr>
<?php
    foreach ($bench_profilier as $k => $v) {
        $tr = ((($k+1) % 2 == 0) ? $grey : $white);
        $alt = '';
        if (!empty($v['trace']))
            $alt = ' title="'.implode(" - ", $v['trace']).'"';
        $delta_t = ($v['mark']-$last_t);
        $delta_m = round(($v['mem']-$last_m)/1048576, 3);
        $color_m = $color_t = '';
        if ($delta_t > $max) {
            $color_t = $red;
        }
        if ($delta_m > $mem_max) {
            $color_m = $red;
        } elseif ($delta_m < 0) {
            $color_m = $green;
        }
        if (!empty($color_t))
            $color_t = " bgcolor=\"$color_t\"";
        if (!empty($color_m))
            $color_m = " bgcolor=\"$color_m\"";
?>
<tr bgcolor="<?php echo $tr; ?>">
    <td<?php echo $alt; ?>><?php echo ($k+1); ?></td>
    <td<?php echo $color_t; ?>><?php echo sprintf("%.4f", $delta_t); ?></td>
    <td<?php echo $color_m; ?>><?php echo sprintf("%.3f", $delta_m); ?></td>
    <td><?php echo $v['type'];

        if ($v['type'] == 'SQL') {
            echo ": ".$v['data']['query'];
            if (constant('BENCH_DISPLAY_TYPE') == 'A' && !empty($v['data']['explain'])) {
?><br />Explain:<br /><?php
                if (is_array($v['data']['explain'])) {
?>
    <table cellpadding="3" cellspacing="1" bgcolor="black">
    <tr bgcolor="#cccccc">
        <td><?php echo implode("</td>\n\t\t<td>", array_keys($v['data']['explain'][0])); ?></td>
    </tr>
<?php
                foreach ($v['data']['explain'] as $e) {
?>
    <tr>
<?php
                    foreach ($e as $ei) {
?>
        <td bgcolor="white">
<?php
                        if (is_null($ei)) {
                            echo 'NULL';
                        } elseif (is_bool($ei)) {
                            echo ($ei) ? 'TRUE' : 'FALSE';
                        } else {
                            echo $ei;
                        }
?>
        </td>
<?php
                    }
?>
    </tr>
<?php
                }
?>
    </table>
<?php
                } else {
echo $v['data']['explain'];
                }
            }
        } elseif (!empty($v['data'])) {
            echo ": ";
            if (is_array($v['data'])) {
                print_r($v['data']);
            } else {
                echo $v['data'];
            }
        }
?>
    </td>
</tr>
<?php
        $last_t = $v['mark'];
        $last_m = $v['mem'];
    }
?>
</table>
<?php
} // }}}

function _get_smarty_size($tpl_output, &$smarty) { // {{{
    global $__smarty_size;
    $__smarty_size += strlen($tpl_output);
    return $tpl_output;
} // }}}

register_shutdown_function('__debug');
if (isset($smarty) && defined('BENCH') && constant('BENCH')) {
    $smarty->register_outputfilter('_get_smarty_size');
}

?>
