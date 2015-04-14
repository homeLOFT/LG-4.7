{*
33d10397c41945af154b13fd90d436ffe6d7bc00, v3 (xcart_4_6_4), 2014-04-25 11:13:26, content.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
var txt_accept_terms_err = '{$lng.txt_accept_terms_err|wm_remove|escape:"javascript"}';
var msg_being_placed     = '{$lng.msg_order_is_being_placed|wm_remove|escape:"javascript"}';
var amazon_pa_orefid = '{$smarty.get.amz_pa_ref|escape:"javascript"}';
var amazon_pa_place_order_enabled = false;
var amazon_pa_address_selected = false;
var amazon_pa_payment_selected = false;

$(function() {ldelim}
  func_amazon_pa_init_checkout();
{rdelim});
//]]>
</script>

<h1>{$lng.lbl_amazon_pa_checkout}</h1>
<br />

<table cellspacing="0" cellpadding="0" width="85%">
<tr>
  <td valign="top">
    {* amazon widgets *}
    <div id="addressBookWidgetDiv"></div>
    <br /><br />
    <div id="walletWidgetDiv"></div>
    <br />
  </td>
  <td width="5%">&nbsp;</td>
  <td width="40%" valign="top">
    <div class="checkout-container">
    {* shipping method selector *}
    {include file="modules/One_Page_Checkout/opc_shipping.tpl" checkout_module="One_Page_Checkout"}
    <br />
    {* cart details *}
    {include file="modules/One_Page_Checkout/opc_summary.tpl" button_href="javascript: return func_amazon_pa_place_order();" payment_script_url="amazon_checkout.php" checkout_module="One_Page_Checkout"}
    </div>
    <br />
  </td>
</tr>
</table>
