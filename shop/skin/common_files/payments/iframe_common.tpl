{*
4e86cd41227dbf833fe62a5fdca912a2e5e9c05f, v5 (xcart_4_6_2), 2013-11-15 09:02:22, iframe_common.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<!-- MAIN -->
<script type="text/javascript">
//<![CDATA[

  var paymentCancelUrl = '{$cancel_url}';

  {if $active_modules.One_Page_Checkout and not $smarty.post.disable_js_iframe}
  function frameLoaded() {ldelim}
    $('#payment_content').unblock();
  {rdelim}
  {/if}

//]]>
</script>

<div id="payment_content">
  <iframe src="{$iframe_src}" height="{$height}" width="{$width}" frameborder="0" scrolling="no" onload="return frameLoaded();" id="payment_content_iframe"></iframe>
</div>

{if $active_modules.One_Page_Checkout and not $smarty.post.disable_js_iframe}
<script type="text/javascript">
//<![CDATA[
  $('#payment_content').block();
//]]>
</script>
{else $active_modules.Fast_Lane_Checkout}
<script type="text/javascript">
//<![CDATA[
  var msg_confirmation = '{$lng.msg_payment_cancel_confirmation_js|wm_remove|escape:"javascript"}';
//]]>
</script>

<div align="center">
  {include file="customer/buttons/button.tpl" button_title=$lng.lbl_cancel href="javascript:if(confirm(msg_confirmation))window.location=paymentCancelUrl" additional_button_class="main-button" js_to_href="Y"}
</div>
{/if}
<!-- /MAIN -->
