{*
f7f946996b3f74495f07af7fc9812865f7e93875, v3 (xcart_4_6_5), 2014-08-04 14:12:36, saved_cards_add_new.tpl, aim 
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
var save_cc_paymentid = '{$xpc_save_cc_paymentid}';

{literal}

function showXPCFrame(caller) {
  $('#xpc_iframe').attr('src', 'payment/cc_xpc_iframe.php?paymentid=' + save_cc_paymentid + '&save_cc=Y');
  $(caller).hide(100);
  $('#xpc_iframe_container').show(200);
  $('#xpc_iframe_section').block();
}

function submitXPCFrame() {
  $('#xpc_iframe_section').block();
  if (window.postMessage && window.JSON) {
    var message = {
      message: 'submitPaymentForm',
      params: {}
    };

    if (window.frames['xpc_iframe'])
      window.frames['xpc_iframe'].postMessage(JSON.stringify(message), '*');
  }
  return false;
}

function messageListener(event) {
  if (event.source === window.frames['xpc_iframe'] && window.JSON) {
    var msg = JSON.parse(event.data);
    if (msg) {
      if ('paymentFormSubmitError' === msg.message) {
        $('#xpc_iframe_section').unblock();
        if (msg.params.type !== undefined && msg.params.type != 0) {
          var errorMsg = (msg.params.error === undefined) ? '' : msg.params.error;
          popupOpen(
            'payment/cc_xpc_iframe.php?xpc_action=xpc_popup'
            + '&message=' + encodeURIComponent(errorMsg)
            + '&type=' + parseInt(msg.params.type)
            + '&paymentid=' + save_cc_paymentid
            + '&payment_method=' + lbl_error
            + '&save_cc=Y'
          );
        }
      }

      if ('ready' === msg.message) {
        msg.params.height >= 0 && $('#xpc_iframe').height(msg.params.height);
        $('#xpc_iframe_section').unblock();
      }
    }
  }
}

if (window.addEventListener)
  addEventListener('message', messageListener, false);
else
  attachEvent('onmessage', messageListener);

{/literal}
//]]>
</script>

<div id="xpc_iframe_container" style="display: none;">
 
  <form name="checkout_form" onsubmit="javascript: return submitXPCFrame();">
    {capture name=xpc_save_cc_amount}{currency value=$config.XPayments_Connector.xpc_save_cc_amount}{/capture}
    {$lng.txt_xpc_saved_cards_add_new|substitute:amount:$smarty.capture.xpc_save_cc_amount}
    <br /><br />
    <div id="xpc_iframe_section" style="display: inline-block;">
    {include file="modules/XPayments_Connector/xpc_iframe.tpl" save_cc=true}
    <div id="xpc_submit" class="button-row">
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_submit type="input" additional_button_class="main-button"}
    </div>
    </div>
  </form>

</div>
