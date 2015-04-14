{if $mode eq 'plain_list'}

  <ul>
    {if $login eq ''}
      <li>{include file="customer/main/login_link.tpl"}</li>
      <li><a href="register.php">{$lng.lbl_register}</a></li>
    {else}
      <li><a href="register.php?mode=update">{$lng.lbl_my_account}</a></li>
      <li><a href="{$xcart_web_dir}/login.php?mode=logout">{$lng.lbl_logoff}</a></li>
    {/if}

    {if $login}

      {if $active_modules.Quick_Reorder}
        {if $show_quick_reorder_link eq "Y"}
	      <li><a href="quick_reorder.php">{$lng.lbl_quick_reorder_customer}</a></li>
        {/if}
      {/if}

    {/if}
  </ul>

{else}

  <div class="header-links">
    {if $login eq ''}
      {include file="customer/main/login_link.tpl"}
      <a href="register.php">{$lng.lbl_register}</a>
    {else}
      <a href="register.php?mode=update">{$lng.lbl_my_account}</a>
      <span class="name">({$fullname|default:$login|escape})</span>
      <a href="{$xcart_web_dir}/login.php?mode=logout">{$lng.lbl_logoff}</a>
    {/if}

    {if $login}

      {if $active_modules.Quick_Reorder}
        {if $show_quick_reorder_link eq "Y"}
	      <a href="quick_reorder.php">{$lng.lbl_quick_reorder_customer}</a>
        {/if}
      {/if}

    {/if}
  </div>

{/if}
