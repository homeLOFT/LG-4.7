{*
1b517203fe38a899ebc8fdc5ad481983b7e0a607, v7 (xcart_4_6_2), 2014-02-02 09:49:59, checkout_0_enter.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_personal_details}</h1>

{if $active_modules.Image_Verification and $show_antibot.on_login eq 'Y' and $login_antibot_on}
  {assign var="is_antibot" value="Y"}
{/if}

{if $active_modules.Image_Verification and $show_antibot.on_login eq 'Y' and $login_antibot_on and $main ne 'disabled'}
  {assign var="left_ext_additional_class" value=" flc-ext-left-dialog"}
  {assign var="right_ext_additional_class" value=" flc-ext-right-dialog"}
{/if}

{capture name=dialog}

  <div class="text-block">{$lng.txt_login_incorrect}</div>

  {include file="customer/main/login_form.tpl" is_flc=true}

{/capture}
{if $active_modules.XAuth ne ""}
{include file="customer/dialog.tpl" title=$lng.lbl_returning_customer content=$smarty.capture.dialog additional_class="flc-left-dialog`$left_ext_additional_class` flc-no-fixed-height" }
{else}
{include file="customer/dialog.tpl" title=$lng.lbl_returning_customer content=$smarty.capture.dialog additional_class="flc-left-dialog`$left_ext_additional_class`"}
{/if}

<script type="text/javascript">
//<![CDATA[
{literal}
function show_regdlg() {
  $('#content-container').css("height", "auto");

  document.getElementById('flc-register-dialog').style.display = (document.getElementById('flc-register-dialog').style.display == '') ? 'none' : '';
  setTimeout(
    function() {
      self.location.hash = 'regdlg';
    },
    200
  );

  if (typeof(window.IEFix) != 'undefined') window.IEFix();

  $('#content-container').css("height", "auto");

  return false;
}
{/literal}
//]]>
</script>

{capture name=dialog}

  <span class="flc-login-text">
    {$lng.lbl_flc_new_customer_text}&nbsp;
    <a href="cart.php?mode=checkout&amp;no_script=Y#regdlg" onclick="javascript: return show_regdlg();">{$lng.lbl_flc_new_customer_link}</a>
  </span>

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_new_customer content=$smarty.capture.dialog additional_class="flc-right-dialog`$right_ext_additional_class`"}

<div class="clearing"></div>

{if $paypal_express_active}
  {include file="payments/ps_paypal_pro_express_checkout.tpl"}
{/if}

{if $active_modules.Abandoned_Cart_Reminder && $login eq '' && $config.Abandoned_Cart_Reminder.abcr_ajax_save eq 'Y'}
  {load_defer file="modules/Abandoned_Cart_Reminder/checkout.js" type="js"}
{/if}

<div id="flc-register-dialog"{if $av_error ne 1 and $reg_error eq '' and $smarty.get.no_script ne 'Y'} style="display: none;"{/if}>

  <a name="regdlg"></a>

  {include file="customer/main/register.tpl"}

</div>

{if $top_message.reg_error ne '' or $av_error eq 1}
<script type="text/javascript">
//<![CDATA[
self.location.hash = 'regdlg';
//]]>
</script>
{/if}
