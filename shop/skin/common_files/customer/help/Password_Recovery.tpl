{*
aad85f2be3e7abf71f38a7de369cbd6188fe4f3b, v7 (xcart_4_7_0), 2015-02-11 09:38:53, Password_Recovery.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_forgot_password}</h1>

<p class="text-block">
{$lng.txt_password_recover|replace:"#login_type#":$recover_field_name}
</p>

{capture name=dialog}
  
  {if $smarty.get.section eq 'Password_Recovery_error' and $smarty.get.err_type eq 'antibot'}
    {assign var='antibot_err' value=true}
  {/if}

  <form action="help.php" method="post" name="processform">
    <input type="hidden" name="action" value="recover_password" />

    <table cellspacing="0" class="data-table">

      <tr> 
        <td class="data-name"><label for="username">{$recover_field_name}</label></td>
        <td class="data-required">*</td>
        <td>
          <input type="text" name="username" id="username" size="30" value="{$username|escape:"html"}" />
          {if $smarty.get.section eq 'Password_Recovery_error' and not $antibot_err}
            <div class="error-message">{$lng.txt_email_not_match|substitute:"login_field":$recover_field_name}</div>
          {/if}
        </td>
      </tr>

      {include file="customer/buttons/submit.tpl" type="input" assign="submit_button"}

      {if $active_modules.Image_Verification and $show_antibot.on_pwd_recovery eq 'Y'}
        {include file="modules/Image_Verification/spambot_arrest.tpl" mode="data-table" id=$antibot_sections.on_pwd_recovery antibot_err=$antibot_err button_code=$submit_button}
      {else}
        <tr> 
          <td colspan="2">&nbsp;</td>
          <td class="button-row">{$submit_button}</td>
        </tr>
      {/if}

    </table>

  </form>
{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_forgot_password content=$smarty.capture.dialog noborder=true}
