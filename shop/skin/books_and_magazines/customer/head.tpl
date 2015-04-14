{*
2aca87f302048436ed08b4e6738089849840409f, v7 (xcart_4_5_3), 2012-08-07 09:50:06, head.tpl, tito
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="head-main">

<div class="line2">
	<table cellspacing="0" cellpadding="0" width="100%">
	<tr>
		<td width="7"><img src="{$AltImagesDir}/custom/tab1.gif" alt="" /></td>
		<td class="headbg2">
			{include file="customer/tabs.tpl"}
		</td>
		<td>
      {include file="customer/language_selector.tpl"}
		</td>
		<td width="7"><img src="{$AltImagesDir}/custom/tab2.gif" alt="" /></td>
	</tr>
	</table>

  {if ($main ne 'cart' or $cart_empty) and $main ne 'checkout'}



  {else}

    {include file="modules/`$checkout_module`/head.tpl"}

  {/if}
</div>

<div class="line1">
  <div class="line1_container">
  <div class="logo">
    <a href="{$catalogs.customer}/home.php"><img src="{$AltImagesDir}/custom/logo.gif" alt="" /></a>
  </div>

	<div class="line1-right">
		{include file="customer/search.tpl"}

		<table cellspacing="0" cellpadding="0" width="100%">
		<tr>
		<td align="right">
		<table cellspacing="0" cellpadding="0">
		<tr>
			<td><img src="{$AltImagesDir}/custom/head1.gif" alt="" /></td>
			<td class="headbg">
				<table cellspacing="0" cellpadding="0" class="head-links">
				<tr>
				{if $login ne ''}
					<td style="padding-right: 4px; padding-left: 0px;"><div class="top-username">{$login}</div></td>
					<td style="padding: 13px 0px 0px 0px;" valign="top"><img src="{$AltImagesDir}/custom/head5.gif" alt="" /></td>
					<td><a href="register.php?mode=update">{$lng.lbl_my_account}</a></td>
						<td>
							<form action="login.php?mode=logout" method="post" name="loginform">
					<input type="hidden" name="mode" value="logout" />
					<a href="javascript:void(0);" onclick="javascript: setTimeout(function() {ldelim}document.loginform.submit();{rdelim}, 100);">{$lng.lbl_logoff}</a>
							</form>
						</td>
          {if $active_modules.Quick_Reorder}
            {include file="modules/Quick_Reorder/quick_reorder_link.tpl" current_skin="books_and_magazines"}
          {/if}
				{else}
					<td>{include file="customer/main/login_link.tpl"}</td>
					<td><a href="register.php" title="{$lng.lbl_register|escape}">{$lng.lbl_register}</a></td>
				{/if}

				</tr>
				</table>
			</td>
			<td><div style="position: relative;width: 67px;height: 77px;">{include file="customer/menu_cart.tpl"}</div></td>
			<td><img src="{$AltImagesDir}/custom/head2.gif" alt="" /></td>
		</tr>
			</table>
		</td>
		</tr>
		</table>
	</div>
   </div>
</div>

{include file="customer/noscript.tpl"}

</div>
