{if $login eq ''}
  {include file="customer/main/login_link.tpl"}
  <a href="register.php">{$lng.lbl_register}</a>
{else}
  <span>{$fullname|default:$login|escape}</span>
  <a href="{$xcart_web_dir}/login.php?mode=logout">{$lng.lbl_logoff}</a>
  <a href="register.php?mode=update">{$lng.lbl_my_account}</a>
{/if}

{if $active_modules.Wishlist}
	<a href="cart.php?mode=wishlist">{$lng.lbl_wish_list}</a>
{/if}

{if $login}
<a href="orders.php">{$lng.lbl_orders_history}</a>

{if $active_modules.Quick_Reorder}
	{include file="modules/Quick_Reorder/quick_reorder_link.tpl" current_skin="ideal_responsive"}
{/if}

{/if}
