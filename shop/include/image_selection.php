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
 * Image selection library
 *
 * @category   X-Cart
 * @package    X-Cart
 * @subpackage Lib
 * @author     Ruslan R. Fazlyev <rrf@x-cart.com>
 * @copyright  Copyright (c) 2001-2015 Qualiteam software Ltd <info@x-cart.com>
 * @license    http://www.x-cart.com/license.php X-Cart license agreement
 * @version    2b39e63712da5477e1aaf5cfa80d1370f583bce9, v93 (xcart_4_7_0), 2015-02-17 23:56:28, image_selection.php, Yuriy
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */

if ( !defined('XCART_SESSION_START') ) { header("Location: ../"); die("Access denied"); }

x_load('backoffice','files', 'image');

x_session_register('file_upload_data');

$service_fields = array('file_path', 'source', 'image_x', 'image_y', 'image_size', 'image_type', 'dir_upload', 'id', 'type', 'date', 'filename');

$config_data = $config['setup_images'][$type];
$userfiles_dir = func_get_files_location() . XC_DS;

/**
 * Check post_max_size exceeding
 */
$_max_filesize = func_max_upload_image_size($config_data, false, $type);
func_check_uploaded_files_sizes('userfile', 1016, $_max_filesize);

if (!isset($config['available_images'][$type]) || empty($type)) {
    if (func_is_ajax_request()) {
        func_register_ajax_message(
            'popupDialogCall',
            array(
                'action' => 'close'
            )
        );
        func_header_location($HTTP_REFERER);
    } else {
        func_close_window();
    }
}

/**
 * POST method
 */
if ($REQUEST_METHOD == 'POST') {

    $max_filesize = 0;
    $data = array();
    $data['is_copied'] = false; // file is not a copy and should not deleted

    $error = '';

    switch($source) {
    case 'S': // server path (user's files)
        $max_filesize = func_max_upload_image_size($config_data, true, $type);
        $newpath = trim(urldecode($newpath));
        if (!zerolen($newpath)) {
            $data['file_path'] = $userfiles_dir.$newpath;
        } else {
            // The file is not specified
            $error = 'empty_file';
        }
        break;
    case 'U': // URL
        $max_filesize = func_max_upload_image_size($config_data, true, $type);
        $fileurl = trim($fileurl);
        if (!zerolen($fileurl) && func_url_is_exists($fileurl)) {
            if (strpos($fileurl, '/') === 0) {
                $fileurl = $http_location.$fileurl;
            } elseif (!is_url($fileurl)) {
                $fileurl = "http://".$fileurl;
                if (!is_url($fileurl))
                    break;
            }

            $tmp = @parse_url(urldecode($fileurl));
            if (empty($tmp['path']))
                break;

            $data['file_path'] = $fileurl;
            $tmp = explode('/', $tmp['path']);
            $data['filename'] = array_pop($tmp);
        } elseif(!zerolen($fileurl)) {
            // The url cannot be loaded or http/https module is not worked
            $error = 'url_not_loadable';
        } else {
            // The url is not specified
            $error = 'empty_file';
        }
        break;

    case 'L': // uploaded file
        $max_filesize = func_max_upload_image_size($config_data, false, $type);
        if (zerolen($userfile)) {
            // The file is not specified
            $error = 'empty_file';
            break;
        }
        $name_limit = min(strlen($_FILES['userfile']['name']), 200);
        $_FILES['userfile']['name'] = substr($_FILES['userfile']['name'], -$name_limit);

        if (func_is_image_userfile($userfile, $userfile_size, $userfile_type)) {
            $data['is_copied'] = true; // can be deleted
            $data['filename'] = basename(stripslashes($_FILES['userfile']['name']));
            $userfile = func_move_uploaded_file('userfile');
            $data['file_path'] = $userfile;
        } else {
            // The file is not image
            $error = 'not_image';
        }
    }

    if (isset($data['file_path']) && !func_is_allowed_file($data['file_path'])) {
        // cannot accept this file
        if ($data['is_copied'])
            unlink($data['file_path']);

        unset($data['file_path']);
        #The type of file is disabled by admin
        $error = 'not_allowed';
    }

    if (!empty($error)) {
        $top_message['content'] = func_get_langvar_by_name("err_upload_" . $error);
        $top_message['type'] = 'W';
        func_header_location($HTTP_REFERER);
    }

    list(
        $data['file_size'],
        $data['image_x'],
        $data['image_y'],
        $data['image_type']) = func_get_image_size($data['file_path']);

    if (!$data['file_size'] && $source == 'U') {
        $top_message['content'] = func_get_langvar_by_name("txt_upload_url_warning");
        $top_message['type'] = 'W';
        func_header_location($HTTP_REFERER);
    }

    if ($data['file_size'] == 0) {
        // Ignore non readable or zero-sized
        if ($data['is_copied'])
            unlink($data['file_path']);

        $data['file_path'] = '';
        $data['is_copied'] = false;
    }

    if (!isset($data['filename'])) {
        $data['filename'] = basename($data['file_path']);
    }

    if ($max_filesize && $data['file_size'] > $max_filesize) {
        @unlink($data['file_path']);
        func_header_location($HTTP_REFERER);
    }

    $data['source'] = $source;
    $data['id'] = $id;
    $data['type'] = $type;
    $data['date'] = XC_TIME;

    $file_upload_data[$type] = $data;

    func_define('XC_DISABLE_SESSION_SAVE', true);
    x_session_save();

    $image_data = array(
        'image_x' => $data['image_x'],
        'image_y' => $data['image_y'],
        'image_type' => $data['image_type'],
        'image_size' => $data['file_size']
    );
    $smarty->assign('image', $image_data);
    $alt = func_display('main/image_property.tpl', $smarty, false);

    $add_descr = $add_dimensions = '';
    if ($type == 'P' || $type == 'T') {
        $max_x = $config['images_dimensions'][$type]['width'];
        $max_y = $config['images_dimensions'][$type]['height'];

        if ($data['image_x'] > $max_x || $data['image_y'] > $max_y) {
            list($max_x, $max_y) = func_get_proper_dimensions ($data['image_x'], $data['image_y'], $max_x, $max_y);
            $add_dimensions .= "parent.document.getElementById('".$imgid."').height='$max_y';
                parent.document.getElementById('".$imgid."').width='$max_x';";
        } else {
            $add_dimensions .= "parent.document.getElementById('".$imgid."').height='$data[image_y]';
                parent.document.getElementById('".$imgid."').width='$data[image_x]';";
        }

        $smarty->assign('show_modified', 1);
        $descr = str_replace(array("\n","\r",'"'), array("\\n","",'\"'), func_display("main/image_property2.tpl", $smarty, false));

        $add_descr = "if (parent.document.getElementById('original_image_descr_$type')) {
            parent.document.getElementById('original_image_descr_$type').style.display = \"none\";
        }
        if (parent.document.getElementById('modified_image_descr_$type')) {
            parent.document.getElementById('modified_image_descr_$type').innerHTML = \"$descr\";
            parent.document.getElementById('modified_image_descr_$type').style.display = \"\";
        }";

        $add_descr .= "if (parent.document.getElementById('".$type."image_reset')) {
            parent.document.getElementById('".$type."image_reset').disabled = \"\";
        }";

        $add_descr .= "if (parent.document.getElementById('a_".$imgid."')) {
            parent.document.getElementById('a_".$imgid."').href = '".$xcart_web_dir."/image.php?type=".$type."&id=".$id."&tmp=".XC_TIME."';
        }";

        $add_descr .= "if (parent.document.getElementById('image_save_msg')) {
            parent.document.getElementById('image_save_msg').style.display = '';
        }";

        $add_descr .= "if (parent.document.getElementById('".$imgid."_reset')) {
            parent.document.getElementById('".$imgid."_reset').style.display = \"\";
        }";

        if ($type == 'P') {
            $add_descr .= "if (parent.document.getElementById('tr_generate_thumbnail')) {
                parent.document.getElementById('tr_generate_thumbnail').style.display = \"\";
            }";
        }
    }

    echo "<script type=\"text/javascript\">
<!--

if (parent.document.getElementById('$imgid')) {
    $add_dimensions
    parent.document.getElementById('$imgid').src = '$xcart_web_dir/image.php?type=$type&id=$id&tmp=".XC_TIME."';
    parent.document.getElementById('$imgid').alt = \"".str_replace(array("\n","\r",'"'), array("\\n","",'\"'), $alt)."\";
    var i = parent.document.getElementById('$imgid');
    $add_descr

} else if (parent.document.getElementById('".$imgid."_0')) {
    var cnt = 0;
    while (parent.document.getElementById('".$imgid."_'+cnt)) {
        parent.document.getElementById('".$imgid."_'+cnt).src = '$xcart_web_dir/image.php?type=$type&id=$id&tmp=".XC_TIME."';
        var i = parent.document.getElementById('".$imgid."_'+cnt);
        cnt++;
    }
}

if (parent.document.getElementById('".$imgid."_text')) {
    parent.document.getElementById('".$imgid."_text').style.display = '';
    var cnt = 1;
    while (parent.document.getElementById('".$imgid."_text' + cnt)) {
        parent.document.getElementById('".$imgid."_text' + cnt).style.display = '';
        cnt++;
    }
}

if (parent.document.getElementById('skip_image_$type')) {
    parent.document.getElementById('skip_image_$type').value = '';

} else if (parent.document.getElementById('skip_image_".$type."_".$id."')) {
    parent.document.getElementById('skip_image_".$type."_".$id."').value = '';
}

if (parent.document.getElementById('".$imgid."_reset'))
    parent.document.getElementById('".$imgid."_reset').style.display = '';

if (parent.document.getElementById('".$imgid."_onunload'))
    parent.document.getElementById('".$imgid."_onunload').value = 'Y';

parent.jQuery('.popup-dialog').dialog('close');
-->
</script>";
    exit;
}

$_table = $sql_tbl['images_'.$type];
$_field = $config['available_images'][$type] == 'U' ? "id" : "imageid";

$smarty->assign('type', $type);
$smarty->assign('imgid', $imgid);
$smarty->assign('id', $id);
$smarty->assign('config_data', $config_data);

if (func_is_ajax_request()) {

    func_display('main/popup_image_selection_iframe.tpl', $smarty);

} else {

    $max_filesize = func_convert_to_megabyte(func_max_upload_image_size($config_data, FALSE, $type));
    $upload_warning = func_get_langvar_by_name('txt_max_file_size_warning', array('size' => $max_filesize), FALSE, TRUE);

    $smarty->assign('upload_warning', $upload_warning);

    func_display('main/popup_image_selection.tpl', $smarty);

}

?>
