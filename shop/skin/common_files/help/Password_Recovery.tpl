{*
e4583dbab8d4535ab7cb41dc7825363010b100cd, v6 (xcart_4_5_5), 2012-11-20 14:08:19, Password_Recovery.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $smarty.get.section eq 'Password_Recovery_error' and $smarty.get.err_type eq 'antibot'}
  {assign var='antibot_err' value=true}
{/if}
<table width="100%">
<tr>
  <td align="center" width="100%">

    <form action="help.php" method="post" name="processform">
    <input type="hidden" name="action" value="recover_password" />

      <table class="login-table">
      <tr>
        <td colspan="3" class="login-title">{$lng.lbl_forgot_password}</td>
      </tr>

      <tr> 
        <td>{$recover_field_name}</td>
        <td><font class="CustomerMessage">*</font></td>
        <td><input type="text" name="username" size="30" value="{$username|escape:"html"}" /></td>
      </tr>

      {if $smarty.get.section eq "Password_Recovery_error" and not $antibot_err}
        <tr>
          <td colspan="3" class="ErrorMessage">{$lng.txt_email_not_match|substitute:"login_field":$recover_field_name}</td>
        </tr>
      {/if}

      <tr>
        <td colspan="3">&nbsp;</td>
      </tr>

      {capture name='submit_button'}
        <div class="main-button">
          <button class="big-main-button" type="submit">{$lng.lbl_submit}</button>
        </div>
      {/capture}

      {if $active_modules.Image_Verification and $show_antibot.on_pwd_recovery eq 'Y'}
        {include file="modules/Image_Verification/spambot_arrest.tpl" mode="data-table" id=$antibot_sections.on_pwd_recovery antibot_err=$antibot_err button_code=$smarty.capture.submit_button}
      {else}
        <tr> 
          <td colspan="2">&nbsp;</td>
          <td class="main-button">{$smarty.capture.submit_button}</td>
        </tr>
      {/if}

      </table>

    </form>

  </td>
</tr>
</table>
