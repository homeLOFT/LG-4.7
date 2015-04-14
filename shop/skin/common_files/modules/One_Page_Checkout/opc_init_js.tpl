{*
f2c96ddb72c96605401a2025154fc219a84e9e75, v21 (xcart_4_6_1), 2013-08-19 12:16:49, opc_init_js.tpl, random 
vim: set ts=2 sw=2 sts=2 et:
*}
{include file="check_email_script.tpl"}
{include file="check_password_script.tpl"}
{include file="check_zipcode_js.tpl"}
{include file="change_states_js.tpl"}

<script type="text/javascript">
//<![CDATA[

var txt_accept_terms_err = '{$lng.txt_accept_terms_err|wm_remove|escape:"javascript"}';
var msg_being_placed     = '{$lng.msg_order_is_being_placed|wm_remove|escape:"javascript"}';

var txt_opc_incomplete_profile    = '{$lng.txt_opc_incomplete_profile|wm_remove|escape:"javascript"}';
var txt_opc_payment_not_selected  = '{$lng.txt_opc_payment_not_selected|wm_remove|escape:"javascript"}';
var txt_opc_shipping_not_selected = '{$lng.txt_opc_shipping_not_selected|wm_remove|escape:"javascript"}';

var shippingid    = {$cart.shippingid|default:0};
var paymentid     = {$cart.paymentid|default:0};
var unique_key    = '{unique_key}';
var av_error      = {if $av_error}true{else}false{/if};
var need_shipping = {if $need_shipping}true{else}false{/if};

var paypal_express_selected = {if $paypal_express_selected}true{else}false{/if};

var payments = [];
{foreach from=$payment_methods item=p name=pt}
payments[{$p.paymentid}] = {ldelim}url: '{$p.payment_script_url}', name: '{$p.payment_method|wm_remove|escape:"javascript"}', surcharge: '{$p.surcharge}{$p.surcharge_type}', iframe: {if $p.background eq 'I'}true{else}false{/if}{if $p.processor_file eq 'ps_paypal_pro.php' and $p.payment_template eq 'customer/main/payment_offline.tpl' and not $paypal_express_selected}, message: '{$lng.lbl_paypal_redirecting}'{/if}{rdelim};
{/foreach}

var xpc_iframe_methods = {if $active_modules.XPayments_Connector and $config.XPayments_Connector.xpc_use_iframe eq 'Y'}true{else}false{/if};  
var xpc_paymentids = [];

{if $active_modules.Klarna_Payments and $config.Klarna_Payments.user_country eq 'de'}
var txt_de_policy_err = '{$lng.txt_de_policy_err|wm_remove|escape:"javascript"}';
{/if}

{literal}

function checkCheckoutForm() {

  // Check if profile filled in: registerform should not exist on the page
  if ($('form[name=registerform]').length > 0) {
    xAlert(txt_opc_incomplete_profile, '', 'E');
    return false;
  }

  if (need_shipping && ($('input[name=shippingid]').val() <= 0 || (undefined === shippingid || shippingid <= 0))) {
    xAlert(txt_opc_shipping_not_selected, '', 'E');
    return false;
  }
  
  if (!paymentid && (undefined === paymentid || paymentid <= 0)) {
    xAlert(txt_opc_shipping_not_selected, '', 'E');
    return false;
  }

  {/literal}
    {if $active_modules.Klarna_Payments}
      if (klarna_user_country == 'de') {ldelim}
      var de_termsObj = $('input[name=de_terms]:visible');
      if (de_termsObj && $(de_termsObj).attr('checked') != 'checked') {ldelim}
        xAlert(txt_de_policy_err, '', 'W');
        return false;
      {rdelim}
      {rdelim}
    {/if}
  {literal}
  // Check terms accepting
  var termsObj = $('#accept_terms')[0];
  if (termsObj && !termsObj.checked) {
    xAlert(txt_accept_terms_err, '', 'W');
    return false;
  }

  return true;
}
{/literal}

//]]>
</script>
