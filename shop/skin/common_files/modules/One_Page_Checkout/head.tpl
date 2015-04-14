{*
4f550a0b753878e34fc3d4947ade1e38ff1cb35d, v4 (xcart_4_6_0), 2013-03-27 13:55:55, head.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $login ne '' and $main eq 'cart'}
  <div class="checkout-top-login">

    <form action="{$authform_url}" method="post" name="toploginform">
      <input type="hidden" name="mode" value="logout" />
      <input type="hidden" name="redirect" value="{$redirect|amp}" />
      <input type="hidden" name="usertype" value="{$auth_usertype|escape}" />


    </form>

  </div>
{/if}
