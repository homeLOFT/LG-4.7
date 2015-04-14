{*
797238b7266d446ddf5fa673410e7442472ee634, v2 (xcart_4_6_4), 2014-04-21 09:40:53, bread_crumbs.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
{if $location}
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
  <td valign="top" align="left">
  <div id="location">
      {foreach from=$location item=l name=location}
        {if $l.1 and not $smarty.foreach.location.last}
          <a href="{$l.1|amp}" class="bread-crumb{if $smarty.foreach.location.last} last-bread-crumb{/if}{if $smarty.foreach.location.first} first-bread-crumb{/if}">{if $smarty.foreach.location.first}&nbsp;{else}{if $webmaster_mode eq "editor"}{$l.0}{else}{$l.0|escape}{/if}{/if}</a>
        {else}
          <span class="bread-crumb{if $smarty.foreach.location.last} last-bread-crumb{/if}">{if $webmaster_mode eq "editor"}{$l.0}{else}{$l.0|escape}{/if}</span>
        {/if}
        {if not $smarty.foreach.location.last && $config.Appearance.breadcrumbs_separator ne ''}
          <span>{*$config.Appearance.breadcrumbs_separator|escape*}</span>
        {/if}
      {/foreach}
  </div>
  </td>
  <td width="130" valign="top" align="right">
  {include file="customer/printable_link.tpl"}
  </td>
</tr>
</table>
{/if}
