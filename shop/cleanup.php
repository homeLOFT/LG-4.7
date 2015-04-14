<?php
/* vim: set ts=4 sw=4 sts=4 et: */
require_once __DIR__.'/top.inc.php';
require __DIR__.'/init.php';

if (!defined('DEVELOPMENT_MODE')) {
    if (empty($auth_code) || $auth_code != $installation_auth_code) {
        func_403();
    }
}

require $xcart_dir . '/include/safe_mode.php';
x_load('backoffice');

$result = func_remove_xcart_caches();

?>
The compiled templates cache ('var/templates_c' directory) has been cleaned up.<br />
The X-Cart cache ('var/cache' directory) has been cleaned up.
<?php if (!empty($result['is_large'])) { ?>
<br /><b>Note:</b> some files were not removed. Please, delete them manually.
<?php }
