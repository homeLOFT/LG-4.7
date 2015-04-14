{*
4f550a0b753878e34fc3d4947ade1e38ff1cb35d, v3 (xcart_4_6_0), 2013-03-27 13:55:55, head.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $login ne ""}
  <div class="checkout-top-login">

    <form action="{$authform_url}" method="post" name="toploginform">
      <input type="hidden" name="mode" value="logout" />
      <input type="hidden" name="redirect" value="{$redirect|amp}" />
      <input type="hidden" name="usertype" value="{$auth_usertype|escape}" />

      <span class="checkout-top-login-text">
        <strong><a href="register.php?mode=update" title="{$lng.lbl_my_account|escape}">{$fullname|default:$login}</a></strong>
      </span>
      {include file="customer/buttons/logout_menu.tpl" style="link"}

    </form>

  </div>
{/if}
