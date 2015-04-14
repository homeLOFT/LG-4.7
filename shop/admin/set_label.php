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
 * Process label edit action (webmaster mode)
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Admin interface
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    3968cba5ecdb78320d43cbe05a25fe35597bc800, v47 (xcart_4_7_0), 2015-02-17 13:29:01, set_label.php, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

define('USE_TRUSTED_POST_VARIABLES',1);
define('USE_TRUSTED_SCRIPT_VARS',1);

$trusted_post_variables = array('val');

define('FORM_ID_DISABLED', true);

require __DIR__.'/auth.php';
require $xcart_dir.'/include/security.php';

unset($editor_mode);
x_session_register('editor_mode');

if ($REQUEST_METHOD == 'POST' && $editor_mode == 'editor' && !XCSecurity::$admin_safe_mode) {
    if (func_query_first_cell("SELECT COUNT(*) FROM $sql_tbl[languages] WHERE name='$name' AND code='$lang'") > 0) {
        func_array2update('languages', array('value' => $val), "code='$lang' AND name='$name'");
    } else {
        $data = func_query_first("SELECT topic, name FROM $sql_tbl[languages] WHERE name='$name'");
        if (!empty($data) && is_string($lang)) {
            $data['code'] = $lang;
            $data['value'] = $val;
            func_array2insert('languages', $data, true);
        }
    }
    func_data_cache_clear('get_language_vars');
    func_data_cache_clear('get_default_fields');
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<body onload="javascript: wndClose();">
<script type="text/javascript">
//<![CDATA[
function wndClose()
{
    if (window.opener) {
        if (window.screenLeft && window.screenTop) {
            window.opener.defaultLabelWindowX = window.screenLeft;
            window.opener.defaultLabelWindowY = window.screenTop;
        } else {
            window.opener.defaultLabelWindowX = window.screenX;
            window.opener.defaultLabelWindowY = window.screenY;
        }
    }
    window.close();
}
//]]>
</script>
</body>
</html>
