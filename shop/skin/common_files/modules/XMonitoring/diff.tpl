{*
e1db5cf03524aef7b3d94390d4b4baa6311fd42b, v2 (xcart_4_5_5), 2013-02-07 17:35:38, diff.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<html>
    <head>
        <link rel="stylesheet" type="text/css" href="{$SkinDir}/modules/XMonitoring/css/prettify.css" />
        <link rel="stylesheet" type="text/css" href="{$SkinDir}/modules/XMonitoring/css/textdiff.css" />
        <script type="text/javascript" src="{$SkinDir}/modules/XMonitoring/js/prettify.js"></script>
        <script type="text/javascript" src="{$SkinDir}/modules/XMonitoring/js/lang-diff.js"></script>
    </head>
    <body onload="prettyPrint()">
        {if $xmonitoring_snapshot_error ne 'Y'}
        <div class="diff-help">
            <a href="http://help.x-cart.com/index.php?title=X-Cart:To_apply_a_patch_manually#What_is_diff_.28patch.29_file.3F" target="_blank">What is a diff file?</a>
        </div>
        <div class="diff-index">
            <h1>Index: {$xmonitoring_diff.fileinfo_to.filename}</h1>
            <h2>--- a/{$xmonitoring_diff.fileinfo_to.filename}&nbsp;&nbsp;&nbsp;{$xmonitoring_diff.fileinfo_from.fmtime|date_format:"%a %b %d %H:%M:%S %Y"}</h2>
            <h2>+++ b/{$xmonitoring_diff.fileinfo_to.filename}&nbsp;&nbsp;&nbsp;{$xmonitoring_diff.fileinfo_to.fmtime|date_format:"%a %b %d %H:%M:%S %Y"}</h2>
        </div>
        {/if}
        <div class="diff-content">{$xmonitoring_diff.file_diff}</div>
    </body>
</html>
