{*
48c08c2b83f10d525a143bc234762a25cccf7351, v5 (xcart_4_5_5), 2012-11-15 15:39:47, payment_form.tpl, aim 
vim: set ts=2 sw=2 sts=2 et:
*}
<form method="{$method}" action="{$request_url|amp}" name="process">
{foreach from=$fields key=fn item=fv}
<input type="hidden" name="{$fn}" value="{$fv|escape:"html"}" />
{/foreach}

{capture name="button"}
  {$lng.txt_noscript_payment_note|substitute:"payment":$payment:"button":$lng.lbl_submit}
  <br />
  <input type="submit" value="{$lng.lbl_submit}" />
{/capture}

{if not $autosubmit}
  {$smarty.capture.button}
{else}

<div id="text_box">
  <noscript>
    {$smarty.capture.button}
  </noscript>
</div>

<script type="text/javascript">
//<![CDATA[
if (document.getElementById('text_box'))
    document.getElementById('text_box').innerHTML = '{$lng.txt_script_payment_note|substitute:"payment":$payment|escape:"javascript"}';

document.process.submit();
//]]>
</script>

{/if}

</form>
