{*
9b350dfa72bbc9db0b28842bc254175b2e873d3d, v23 (xcart_4_7_0), 2014-12-20 18:48:34, main.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $active_modules.Cloud_Search}
  {include file="modules/Cloud_Search/reminder.tpl"}
{/if}

{if $current_passwords_security or $default_passwords_security or $blowfish_key_expired or $db_backup_expired or $dashboard_notifications or $not_secure_config_values or $dashboard_warnings}
{capture name=dialog}
{if $current_passwords_security}
<div class="SecurityWarning">
{$lng.txt_your_password_insecured}
<br /><br />
{if $active_modules.Simple_Mode}
<div align="left"><a class="simple-button" title="{$lng.lbl_chpass}" href="{$catalogs.provider}/change_password.php">{$lng.lbl_chpass}</a></div>
{else}
<div align="left"><a class="simple-button" title="{$lng.lbl_chpass}" href="change_password.php">{$lng.lbl_chpass}</a></div>
{/if}
</div>
{/if}

{if $default_passwords_security}
<div class="SecurityWarning">
{capture name=accounts}
{section name=acc loop=$default_passwords_security}
{if $default_passwords_security[acc] ne $current_passwords_security.0}
{assign var="display_default_passwords_security" value="1"}
&nbsp;&nbsp;&nbsp;{$default_passwords_security[acc]}<br />
{/if}
{/section}
{/capture}
{if $display_default_passwords_security}
{$lng.txt_default_passwords_insecured|substitute:"accounts":$smarty.capture.accounts}
<br /><br />
<div align="left"><a class="simple-button" title="{$lng.lbl_users_management|escape}" href="users.php">{$lng.lbl_users_management}</a></div>
{/if}
</div>
{/if}

{if $blowfish_key_expired or $not_secure_config_values.security_keys}
<div class="SecurityWarning">
{$lng.txt_blowfish_key_expired}
<br /><br />
<div align="left"><a class="simple-button" title="{$lng.lbl_regenerating_blowfish_key|escape}" href="tools.php#regenbk">{$lng.lbl_regenerating_blowfish_key}</a></div>
</div>
{/if}

{if $not_secure_config_values.auth_code}
<div class="SecurityWarning">
{$lng.txt_auth_code_not_secure}
<br /><br />
</div>
{/if}

{if $db_backup_expired}
<div class="SecurityWarning">
{$lng.txt_db_backup_expired}
<br /><br />
<div align="left"><a class="simple-button" title="{$lng.lbl_backup_database|escape}" href="db_backup.php">{$lng.lbl_backup_database}</a></div>
</div>
{/if}

{if $dashboard_news.security_news}
<div>
<font class="ErrorMessage">{$lng.txt_new_sec_patches_available}</font>
<ul>
{foreach item=news from=$dashboard_news.security_news}
<li>{$news.description|default:$news.title}</li>
{/foreach}
</ul>
</div>
{/if}

{if $dashboard_news.usual_news}
<div>
{$lng.lbl_other_updates}
<ul>
{foreach item=news from=$dashboard_news.usual_news}
<li>{$news.description|default:$news.title}</li>
{/foreach}
</ul>
</div>
{/if}

{if $dashboard_warnings}
<div>
<font class="ErrorMessage">{$lng.lbl_warnings}:</font>
<ul class="admin-dashboard-notications">
  {foreach item=_warning from=$dashboard_warnings}
    <li>{$_warning}</li>
  {/foreach}
</ul>
</div>
{/if}

{if $dashboard_notifications}
<div>
<strong>{$lng.lbl_notifications}:</strong>
<ul class="admin-dashboard-notications">
  {foreach item=_notification from=$dashboard_notifications}
    <li>{$_notification}</li>
  {/foreach}
</ul>
</div>
{/if}

{/capture}

{include file="location.tpl" location="" alt_content=$smarty.capture.dialog extra='width="100%"' newid="password_security" alt_type="W"}
{/if}
<br />

{if $smarty.cookies.hide_dialog_xcart_news eq '' and $smarty.cookies.skip_remote_feeds eq ''}
<script type="text/javascript">
//<![CDATA[
{literal}
$(document).ready(function () {
  ajax.core.loadBlock($('#xcart_news_items'), 'latest_xcart_news', { _request_prefix : '/admin', _request_type : 'GET'})
  $('#dialog_xcart_news a.close-link').click(function(){
    var date_time = new Date().getTime() + 3600*6*1000;
    $.cookie('hide_dialog_xcart_news', '1', { expires: new Date(date_time)});
  });
});
{/literal}
//]]>
</script>
{capture name=dialog}
<h2>{$lng.lbl_xcart_news}</h2>
<div id="xcart_news_items"></div>
<div align="right">
<a href='{$config.rss_xcart_news_url}' target='_blank'>{$lng.lbl_view_more}</a>
</div>
{/capture}
{include file="location.tpl" location="" alt_content=$smarty.capture.dialog extra='width="100%"' newid="dialog_xcart_news" alt_type="I" image_none="Y"}
{/if}

<!-- QUICK MENU -->
{include file="main/quick_menu.tpl"}
<!-- QUICK MENU -->

{if $active_modules.Kayako_Connector ne "" and $kayakoEnabled ne ""}
{include file="modules/Kayako_Connector/admin/tickets_summary.tpl"}
{/if}

<a name="orders"></a>
{capture name=dialog}
{$lng.txt_top_info_orders}
<br /><br />
<div align="center">
<table cellpadding="0" cellspacing="0" width="90%">
<tr>
<td class="TableHead">

<table cellpadding="3" cellspacing="1" width="100%">
<tr class="TableHead">
<td>{$lng.lbl_status}</td>
<td nowrap="nowrap" align="center">{$lng.lbl_since_last_log_in}</td>
<td align="center">{$lng.lbl_today}</td>
<td nowrap="nowrap" align="center">{$lng.lbl_this_week}</td>
<td nowrap="nowrap" align="center">{$lng.lbl_this_month}</td>
</tr>

{foreach key=key item=item from=$orders}
<tr class="{cycle values='SectionBox,TableSubHead'}">
<td nowrap="nowrap" align="left">{if $key eq "C"}{$lng.lbl_complete}{elseif $key eq "P"}{$lng.lbl_processed}{elseif $key eq "Q"}{$lng.lbl_queued}{elseif $key eq "F" or $key eq "D"}{$lng.lbl_failed}/{$lng.lbl_declined}{elseif $key eq "I"}{$lng.lbl_not_finished}{/if}:</td>
{section name=period loop=$item}
<td align="center">{$item[period]}</td>
{/section}
</tr>
{/foreach}

<tr class="{cycle values='SectionBox,TableSubHead'}">
<td align="right"><b>{$lng.lbl_gross_total}:</b></td>
{section name=period loop=$gross_total}
<td align="center">{currency value=$gross_total[period]}</td>
{/section} 
</tr>

<tr class="{cycle values='SectionBox,TableSubHead'}">
<td align="right"><b>{$lng.lbl_total_paid}:</b></td>
{section name=period loop=$total_paid}
<td align="center">{currency value=$total_paid[period]}</td>
{/section}
</tr>
</table>

</td>
</tr>
</table>
</div>

<br /><br />

<div align="right">{include file="buttons/button.tpl" button_title=$lng.lbl_search_orders href="orders.php" title=$lng.lbl_search_orders}</div>

{if $last_order}
<br /><br />

{include file="main/subheader.tpl" title=$lng.lbl_last_order}

<table cellpadding="3" cellspacing="1" width="100%">

<tr>
<td>&nbsp;&nbsp;</td>
<td>
<table cellpadding="3" cellspacing="1">

<tr>
<td class="FormButton">{$lng.lbl_order_id}:</td>
<td>#{$last_order.orderid}</td>
</tr>

<tr>
<td class="FormButton">{$lng.lbl_order_date}:</td>
<td>{$last_order.date|date_format:$config.Appearance.datetime_format}</td>
</tr>

<tr>
<td class="FormButton">{$lng.lbl_order_status}:</td>
<td>{include file="main/order_status.tpl" status=$last_order.status mode="static"}</td>
</tr>

<tr>
<td class="FormButton">{$lng.lbl_customer}:</td>
<td>{$last_order.title} {$last_order.firstname|default:$last_order.b_firstname} {$last_order.lastname|default:$last_order.b_lastname}</td>
</tr>

<tr>
<td class="FormButton" valign="top">{$lng.lbl_ordered}:</td>
<td>
{if $last_order.products}
{section name=product loop=$last_order.products}
<b>{$last_order.products[product].product|truncate:"30":"..."}</b>
[{$lng.lbl_price}: {currency value=$last_order.products[product].price}, {$lng.lbl_quantity}: {$last_order.products[product].amount}]
{if $last_order.products[product].product_options}
<br />
{$lng.lbl_options}: {$last_order.products[product].product_options|replace:"\n":"; "}
{/if}
<br />
{/section}
{/if}
{if $last_order.giftcerts}
{section name=gc loop=$last_order.giftcerts}
<b>{$lng.lbl_gift_certificate} #{$last_order.giftcerts[gc].gcid}</b>
[{$lng.lbl_price}: {currency value=$last_order.giftcerts[gc].amount}]
<br />
{/section}
{/if}
</td>
</tr>

</table>
</td>
</tr>

</table>

<br />

<div align="right">{include file="buttons/button.tpl" button_title=$lng.lbl_order_details_label href="order.php?orderid=`$last_order.orderid`" title=$lng.lbl_order_details_label}</div>

{/if}

{/capture}
{include file="dialog.tpl" title=$lng.lbl_orders_info content=$smarty.capture.dialog extra='width="100%"'}

<br /><br />

<a name="topsellers"></a>
{capture name=dialog}

{$lng.txt_top_info_top_sellers}

<br /><br />

<div class="TopLabel" align="center">{$lng.lbl_top_N_products|substitute:"N":$max_top_sellers}</div>

<br />

<table cellpadding="0" cellspacing="0" width="100%">
<tr>
<td class="TableHead">
<table cellpadding="3" cellspacing="1" width="100%">

<tr class="TableHead">
  <td width="25%" nowrap="nowrap" align="center">{$lng.lbl_since_last_log_in}</td>
  <td width="25%" nowrap="nowrap" align="center">{$lng.lbl_today}</td>
  <td width="25%" nowrap="nowrap" align="center">{$lng.lbl_this_week}</td>
  <td width="25%" nowrap="nowrap" align="center">{$lng.lbl_this_month}</td>
</tr>

{capture name=top_products}
<tr class="SectionBox">
{foreach key=key item=item from=$top_sellers}
<td align="center"{if $item} valign="top"{/if}>
{if $item}
{assign var="is_top_products" value="1"}
<table cellpadding="2" cellspacing="1" width="100%">
{section name=period loop=$item}
<tr{cycle name="col`$smarty.section.period.index`" values=', class="TableSubHead"'}>
  <td>{inc value=$smarty.section.period.index}.</td>
  <td align="left"><a href="product_modify.php?productid={$item[period].productid}">{$item[period].product|truncate:"20":"..."}</a></td>
  <td>{$item[period].count}</td>
</tr>
{/section}
</table>
{else}
{$lng.txt_no_top_products_statistics}
{/if}
</td>
{/foreach}
</tr>
{/capture}

{if $is_top_products}

{$smarty.capture.top_products}

</table>
</td>
</tr>
</table>

<br />

<div class="TopLabel" align="center">{$lng.lbl_top_N_categories|substitute:"N":$max_top_sellers}</div>

<br />

<table cellpadding="0" cellspacing="0" width="100%">
<tr>
<td class="TableHead">
<table cellpadding="3" cellspacing="1" width="100%">

<tr class="TableHead">
  <td width="25%" nowrap="nowrap" align="center">{$lng.lbl_since_last_log_in}</td>
  <td width="25%" nowrap="nowrap" align="center">{$lng.lbl_today}</td>
  <td width="25%" nowrap="nowrap" align="center">{$lng.lbl_this_week}</td>
  <td width="25%" nowrap="nowrap" align="center">{$lng.lbl_this_month}</td>
</tr>

<tr class="SectionBox">
{foreach key=key item=item from=$top_categories}
<td align="center"{if $item} valign="top"{/if}>
{if $item}
<table cellpadding="2" cellspacing="1" width="100%">
{section name=period loop=$item}
<tr{cycle name="col`$smarty.section.period.index`" values=", class='TableSubHead'"}>
  <td>{inc value=$smarty.section.period.index}.</td>
  <td align="left"><a href="category_modify.php?cat={$item[period].categoryid}">{$item[period].category}</a></td>
  <td>{$item[period].count}</td>
</tr>
{/section}
</table>
{else}
{$lng.txt_no_top_categories_statistics}
{/if}
</td>
{/foreach}
</tr>

{else}

<tr class="SectionBox">
  <td colspan="4" align="center">{$lng.txt_no_statistics}</td>
</tr>

{/if}

</table>
</td>
</tr>
</table>

<br /><br />

<div align="right">{include file="buttons/button.tpl" button_title=$lng.lbl_search_orders href="orders.php" title=$lng.lbl_search_orders}</div>{$lng.txt_how_setup_store_bottom}

{/capture}
{include file="dialog.tpl" title=$lng.lbl_top_sellers content=$smarty.capture.dialog extra='width="100%"'}
