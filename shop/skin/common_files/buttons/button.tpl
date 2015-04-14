{*
f5ff53b784699933c71810346b3f020bf1a0558d, v3 (xcart_4_7_0), 2015-02-04 17:53:27, button.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.Adaptives.platform eq 'MacPPC' and $config.Adaptives.browser eq 'NN'}
  {assign var="js_to_href" value="Y"}
{/if}
{if $type eq 'input'}
  {assign var="img_type" value='input type="image"'}
{else}
  {assign var="img_type" value='img'}
{/if}
{assign var="js_link" value=$href|regex_replace:"/^\s*javascript\s*:/Si":""}
{if $js_link eq $href}
  {assign var="js_link" value="javascript: self.location='"|cat:$href|amp|cat:"';"}
{else}
  {assign var="js_link" value=$href}
  {if $js_to_href ne 'Y'}
    {assign var="onclick" value=$href}
  {if $style neq "button" and $submit eq "Y"}
  {assign var="href" value="#"}
  {else}
  {assign var="href" value="javascript:void(0);"}
  {/if}
  {/if}
{/if}

{if $style eq 'button' and ($config.Adaptives.platform ne 'MacPPC' or $config.Adaptives.browser ne 'NN')}
<table cellspacing="0" cellpadding="0" onclick="{$js_link}" class="ButtonTable"{if $title ne ''} title="{$title|strip_tags:false|escape}"{/if}>
{strip}
<tr>
  <td>
    <{$img_type} src="{$ImagesDir}/but1.gif" class="ButtonSide" alt="{$title|strip_tags:false|escape}" />
  </td>
  <td class="Button"{$reading_direction_tag}>
    <font class="Button">{$button_title}</font>
  </td>
  <td>
    <img src="{$ImagesDir}/but2.gif" class="ButtonSide" alt="{$title|strip_tags:false|escape}" />
  </td>
</tr>
{/strip}
</table>
{elseif $image_menu}
{strip}
<table cellspacing="0" class="SimpleButton">
<tr>
{if $button_title ne ''}
  <td>
<a class="VertMenuItems" href="{$href|amp}"
  {if $onclick ne ''} onclick="{$onclick}"{/if}
  {if $title ne ''} title="{$title|strip_tags:false|escape}"{/if}
  {if $target ne ''} target="{$target}"{/if}>
  <font class="VertMenuItems">{$button_title}</font>
</a>&nbsp;
  </td>
{/if}
  <td>
{if $img_type eq 'img'}
<a class="VertMenuItems" href="{$href|amp}"
  {if $onclick ne ''} onclick="{$onclick}"{/if}
  {if $title ne ''} title="{$title|strip_tags:false|escape}"{/if}
  {if $target ne ''} target="{$target}"{/if}>
{/if}
  <{$img_type} src="{$ImagesDir}/go_menu.gif" class="GoImage" alt="" />
{if $img_type eq 'img'}
</a>
{/if}
</td>
</tr>
</table>
{/strip}
{else}{*if $style eq 'button' and ($config.Adaptives.platform ne 'MacPPC' or $config.Adaptives.browser ne 'NN')*}
{strip}
<table cellspacing="0" class="SimpleButton">
<tr>
{if $button_title ne ''}
<td>
<a class="{if $img_type eq 'img'}simple-button simple-{$substyle|default:"arrow"}-button{else}Button{/if}" href="{$href|amp}"
  {if $onclick ne ''} onclick="{$onclick}"{/if}
  {if $title ne ''} title="{$title|strip_tags:false|escape}"{/if}
  {if $target ne ''} target="{$target}"{/if}>
  {$button_title}
</a>  
</td>
{/if}
{if $img_type neq 'img'}
<td>
  &nbsp;<{$img_type} src="{$ImagesDir}/go.gif" class="GoImage" alt="" />
</td>
{/if}
</tr>
</table>
{/strip}
{/if}
