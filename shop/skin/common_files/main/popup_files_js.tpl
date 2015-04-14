{*
13cd7f2a4eeeb071125e384d732532375d012673, v3 (xcart_4_4_0_beta_2), 2010-06-08 06:17:37, popup_files_js.tpl, igoryan
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript" language="JavaScript 1.2">
//<![CDATA[
function popup_files (filename, path) {ldelim}
  window.open ("popup_files.php?{if $usertype eq "A"}product_provider={$product.provider}&{/if}field_filename="+filename+"&field_path="+path, "selectfile", "width=600,height=550,toolbar=no,status=no,scrollbars=yes,resizable=no,menubar=no,location=no,direction=no");
{rdelim}

function popup_images (filename, path) {ldelim}
  window.open ("popup_files.php?tp=images&field_filename="+filename+"&field_path="+path, "selectfile", "width=600,height=450,toolbar=no,status=no,scrollbars=yes,resizable=no,menubar=no,location=no,direction=no");
{rdelim}
//]]>
</script>
