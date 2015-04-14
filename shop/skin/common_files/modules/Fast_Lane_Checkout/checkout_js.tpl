{*
00f6fa8f47972ca7e487d5b6f6916ba35694e9b6, v13 (xcart_4_6_4), 2014-06-09 14:18:46, checkout_js.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
var txt_accept_terms_err = '{$lng.txt_accept_terms_err|wm_remove|escape:"javascript"}';
var lbl_warning = '{$lng.lbl_warning|wm_remove|escape:"javascript"}';

{literal}
function checkCheckoutForm() {
  var result = true;
{/literal}
  var unique_key = "{unique_key}";

{literal}

  if (!result) {
    return false;
  }

  var termsObj = $('#accept_terms')[0];
  if (termsObj && !termsObj.checked) {
    xAlert(txt_accept_terms_err, lbl_warning, 'W');
    return false;
  }

  if (result && checkDBClick()) {
    if (document.getElementById('msg'))
       document.getElementById('msg').style.display = '';

    if (document.getElementById('btn_box'))
       document.getElementById('btn_box').style.display = 'none';
  }

  return result;
}

var checkDBClick = function() {
  var clicked = false;
  return function() {
    if (clicked)
      return false;

    clicked = true;
    return true;
  }
}();

function checkCheckoutFormXP() {
  if (checkCheckoutForm()) {
    var isXPCAllowSaveCard = ($('input[type=checkbox][name=allow_save_cards]').is(':checked')) ? 'Y' : '';
    var partnerId = ($('input[name=partner_id]').length) ? $('input[name=partner_id]').val() : '';
    $.post(
      'payment/cc_xpc_iframe.php',
      {
        xpc_action: 'xpc_before_place_order',
        allow_save_cards: isXPCAllowSaveCard,
        partner_id: partnerId,
        Customer_Notes: $('textarea[name=Customer_Notes]').val()
      },
      function() {
        if (window.postMessage && window.JSON) {
          var message = {
            message: 'submitPaymentForm',
            params: {}
          };

          if (window.frames['xpc_iframe'])
            window.frames['xpc_iframe'].postMessage(JSON.stringify(message), '*');
        }
      }
    );

  }

  return false;
}

function messageListener(event) {
  if (event.source === window.frames['xpc_iframe'] && window.JSON) {
    var msg = JSON.parse(event.data);
    if (msg) {
      if ('paymentFormSubmitError' === msg.message) {
        $('#msg').hide();
        $('#btn_box').show();
        if (msg.params.type !== undefined && msg.params.type != 0) {
          $('.xpc_iframe_container').unblock();
          var errorMsg = (msg.params.error === undefined) ? '' : msg.params.error;
          popupOpen(
            'payment/cc_xpc_iframe.php?xpc_action=xpc_popup'
            + '&message=' + encodeURIComponent(errorMsg)
            + '&type=' + parseInt(msg.params.type)
            + '&paymentid=' + this.paymentid
            + '&payment_method=' + $('input[name=payment_method]').val()
          );
        }
      }

      if ('ready' === msg.message) {
        msg.params.height >= 0 && $('#xpc_iframe').height(msg.params.height);

        $('.xpc_iframe_container').unblock();
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

{if $active_modules.TaxCloud}
  {include file="modules/TaxCloud/exemption_js.tpl"}
{/if}

