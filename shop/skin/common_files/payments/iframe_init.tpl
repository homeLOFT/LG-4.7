{*
13cd7f2a4eeeb071125e384d732532375d012673, v3 (xcart_4_4_0_beta_2), 2010-06-08 06:17:37, iframe_init.tpl, igoryan 
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="payment-details-title" style="display: none;">
  <h1>{$lng.lbl_payment_details}</h1>
  {$lng.txt_payment_details_iframe|substitute:"payment_method":$payment_method}
</div>
{literal}
<script type="text/javascript">
//<![CDATA[
function frameLoaded() {
	$('.payment-wait-title').hide();
	$('.payment-details-title').show();
}
//]]>
</script>
{/literal}
