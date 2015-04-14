{*
13cd7f2a4eeeb071125e384d732532375d012673, v3 (xcart_4_4_0_beta_2), 2010-06-08 06:17:37, check_email_script.tpl, igoryan
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
var txt_email_invalid = "{$lng.txt_email_invalid|wm_remove|escape:javascript|replace:"\n":" "|replace:"\r":" "}";
var email_validation_regexp = new RegExp("{$email_validation_regexp|wm_remove|escape:javascript}", "gi");
//]]>
</script>
<script type="text/javascript" src="{$SkinDir}/js/check_email_script.js"></script>
