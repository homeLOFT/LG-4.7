{*
b5099a5f214ad527ead5c73e1366174b4a1a9c51, v9 (xcart_4_5_2), 2012-07-13 13:45:51, head.tpl, tito
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="line0">

  <div class="logo">
    <a href="{$catalogs.customer}/home.php"><img src="{$ImagesDir}/xlogo.gif" alt="" /></a>
  </div>

  <div class="line1">

  {if ($main ne 'cart' or $cart_empty) and $main ne 'checkout'}

    {include file="customer/language_selector.tpl"}

    <div class="auth-row">
    {if $login eq ''}
      {include file="customer/main/login_link.tpl"}
      |
      <a href="register.php">{$lng.lbl_register}</a>
    {else}
      <span>{$fullname|default:$login|escape}</span>
      <a href="{$xcart_web_dir}/login.php?mode=logout">{$lng.lbl_logoff}</a>
      |
      <a href="register.php?mode=update">{$lng.lbl_my_account}</a>
    {/if}
      {if $active_modules.Quick_Reorder}
        {include file="modules/Quick_Reorder/quick_reorder_link.tpl"}
      {/if}
      |
      <a href="help.php" class="last">{$lng.lbl_need_help}</a>
    </div>

  {/if}

  </div>

  <div class="line2">

    {if ($main ne 'cart' or $cart_empty) and $main ne 'checkout'}
      {include file="customer/search.tpl"}
    {/if}
    {include file="customer/phones.tpl"}

  </div>

  <div class="line3">
    {include file="customer/tabs.tpl"}
  </div>

</div>

{include file="customer/noscript.tpl"}
