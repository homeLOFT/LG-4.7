{*
aad85f2be3e7abf71f38a7de369cbd6188fe4f3b, v3 (xcart_4_7_0), 2015-02-11 09:38:53, hosted_form.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript" src="https://www.simplify.com/commerce/simplify.pay.js"></script>

<button id="simplify-payment-button"
    {foreach from=$fields key=fn item=fv}data-{$fn}="{$fv|escape:"html"}"{/foreach}
    style="display: none;">
</button>

<div id="payment-info-box"></div>

<script type="text/javascript">
//<![CDATA[
if (document.getElementById('payment-info-box'))
    document.getElementById('payment-info-box').innerHTML = '{$lng.txt_script_payment_note|substitute:"payment":$payment|escape:"javascript"}';

window.addEventListener('load', function() {ldelim}
    document.getElementById('simplify-payment-button').click();
{rdelim});
//]]>
</script>
