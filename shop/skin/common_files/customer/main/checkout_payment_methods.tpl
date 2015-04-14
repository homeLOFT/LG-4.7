{*
3a2e337192fbff1bc0df2af9e750784e5a684986, v6 (xcart_4_5_3), 2012-09-24 08:49:31, checkout_payment_methods.tpl, random 
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="checkout-payments">
  {include file="modules/`$checkout_module`/payment_methods.tpl"}
</div>

<script type="text/javascript">
//<![CDATA[

var txt_payments_are_unavailable = '{$lng.txt_payments_are_unavailable|wm_remove|escape:javascript}';
var shippingsCOD = [{strip}
{if $shipping}
  {foreach from=$shipping item=s}
    {if $s.is_cod eq 'Y'}
      '{$s.shippingid}',
    {/if}
  {/foreach}
{/if}
{/strip}];

func_display_cod();

{literal}
function func_is_current_shipping_cod() {
  // Get current selected method
  var f = $('div.checkout-shippings');
  var current_shippingid = $('input:radio[name=shippingid]:checked', f).eq(0).val();
  return $.inArray(current_shippingid, shippingsCOD) != -1; 
}

function func_display_cod() {

  if (shippingsCOD.length > 0)
    var show_cod_payments = func_is_current_shipping_cod();
  else
    var show_cod_payments = false;
  
  var has_cod_payments = false;
  var f = $('div.checkout-payments');
  $('tr[id^="cod_tr"]', f). // Get all COD payments
    each(function() {
      has_cod_payments = true;

      $(this).toggle(show_cod_payments);// Change visibility for the COD payments according show_cod_payments var

      var pmid = ($(this).attr('id').replace("cod_tr",""));
      $('#pmbox_'+pmid, f).toggle(
        show_cod_payments 
        && $('#pm'+pmid, f).is(':checked')
      );
    }
   ) 
  
  $('#cod_payments_are_unavailable').detach();
  if (show_cod_payments || !has_cod_payments) {
    var res = parseInt($('input:radio[name=paymentid]:checked', f).eq(0).val(), 10);

  } else {
    var res = parseInt(unselect_hidden_payment_method(), 10);

    // Show message if all payments are hidden
    if (!$('input:radio[name=paymentid]', f).filter(":visible").length) 
      $(f).after("<div id='cod_payments_are_unavailable'>"+txt_payments_are_unavailable+"</div>");

  } 

  return isNaN(res) ? 0 : res;
}


/**
 * Change selected payment method if it is hidden
 */
function unselect_hidden_payment_method() {

  var f = $('div.checkout-payments');
  var current_checked = $('input:radio[name=paymentid]:checked', f).eq(0); // Get selected payment

  if (current_checked.is(':hidden')) {
    // If it is hidden change it to nearest visible payment
    $('input:radio[name=paymentid]', f).filter(":visible").eq(0).prop('checked', true);
  } 

  // Return null if all payments are hidden or return selected
  return $('input:radio[name=paymentid]:checked', f).filter(":visible").eq(0).val();
}
{/literal}
//]]>
</script>

