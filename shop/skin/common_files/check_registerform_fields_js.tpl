{*
28e84bab20d5308fa0800cbab39263ed27580ded, v23 (xcart_4_7_0), 2015-01-05 15:43:40, check_registerform_fields_js.tpl, aim 

vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
var is_run = false;
var unique_key = '{unique_key}';
var is_js_opc = {if $is_opc}true{else}false{/if};

function checkRegFormFields(form) {ldelim}

  if (is_run) {ldelim}
    return false;
  {rdelim}

  is_run = {if $is_opc}false{else}true{/if};
  if (
      check_zip_code(form)
      {if $config.Security.use_complex_pwd eq 'Y'} && checkPasswordStrength(form.passwd1, form.passwd2){/if}
  ) {ldelim}
    return true;
  {rdelim}

  is_run = false;

  return false;
{rdelim}

{if $usertype eq 'C'}

var anonymousFlag = {if $anonymous and $config.General.enable_anonymous_checkout eq 'Y'}true{else}false{/if};

{literal}

$(function() {
  $(document).on({
      'blur submit' : function(){$('#email_note').hide(); },
      'focus'       : function(){ showNote('email_note', this, is_js_opc) }
  }, '#email');

  $('#passwd1, #passwd2')
    .bind('change', function() {
      $('#password_is_modified').val('Y');
    })
    .bind('keydown', function() {
    })
    .bind('blur', function() {
      $('#passwd_note').hide();
    })
    .bind('focus', function() {
      showNote('passwd_note', this, is_js_opc)
    });

  $('#passwd1, #passwd2')
    .bind('change', function() {

      var pwd1 = $('#passwd1').val();
      var pwd2 = $('#passwd2').val();
      var vm   = $('#passwd2').parent().find('span.validate-mark');

      if (vm === undefined) {
        return true;
      }

      if (pwd1 == '' || pwd2 == '') {
        vm.removeClass('validate-matched validate-non-matched');
      } else if (pwd1 != pwd2) {
        vm.removeClass('validate-matched').addClass('validate-non-matched');
      } else {
        vm.removeClass('validate-non-matched').addClass('validate-matched');
      }
    });


  $('#create_account, #ship2diff')
    .bind('click', function(){
      if ($(this).is(':checked')) {
        $('#' + $(this).attr('id') + '_box').show();
        $(this).parents('.register-exp-section').removeClass('register-sec-minimized'); 
      }
      else {
        $('#' + $(this).attr('id') + '_box').hide();
        $(this).parents('.register-exp-section').addClass('register-sec-minimized'); 
      }
      {/literal}
      {if $checkout_module eq 'Fast_Lane_Checkout'}
      $('#content-container').css('height', 'auto');
      $('#page-container2').css('height', 'auto');
      {/if}
      {literal}
    });

  // Remove passwords if create_account is unchecked  
  if (current_area == 'C') {
    $('#create_account')
      .bind('click', function(){
        if (!$(this).is(':checked')) {
          $('#uname').val('');
          $('#passwd1').val('');
          $('#passwd2').val('');
        }
      });

    // Do not submit existing_s/new_s checkboxes for hided S address section
    $('#ship2diff')
      .bind('click', function(){
        if (!$(this).is(':checked')) {
          $('#existing_S').prop('checked', false);
          $('#new_S').prop('checked', false);
        }
      });
  }    

{/literal}

{if not $ship2diff}
$('#ship2diff_box').hide();
{/if}
{if not ($reg_error and $userinfo.create_account) and $config.General.enable_anonymous_checkout eq 'Y'}
$('#passwd1').val('');
$('#passwd2').val('');
$('#create_account_box').hide();
{/if}

{literal}
});
{/literal}

{/if}
//]]>
</script>
