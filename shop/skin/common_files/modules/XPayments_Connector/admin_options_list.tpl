{*
f7f946996b3f74495f07af7fc9812865f7e93875, v4 (xcart_4_6_5), 2014-08-04 14:12:36, admin_options_list.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<ul class="xpc-options">
{foreach from=$options_list item=option}
<li>
  {assign var="opt_comment" value="opt_`$option.name`"}
  {assign var="opt_label_id" value="opt_`$option.name`"}
  {assign var="opt_descr" value="opt_descr_`$option.name`"}
  <div class="xpc-option-name">
    {$lng.$opt_comment}
    {if $options_errors}
      <br />
      {foreach from=$options_errors item=e key=error_option}
        {if $error_option eq $option.name}
          <span class="xpc-option-error">{$e}</span>
        {/if}
      {/foreach}
    {/if}

  </div>
  <div class="xpc-option-value-container">
    <span class="xpc-option-value">
      {if $option.type eq "numeric"}
        <input id="{$option.name}" type="text" size="10" name="{$option.name}" value="{$option.value|formatnumeric}" />
      {elseif $option.type eq "text" or $option.type eq "trimmed_text"}
        <input type="text" size="30" name="{$option.name}" value="{$option.value|escape:htmlall}" />
      {elseif $option.type eq "password"}
        <input type="password" size="30" name="{$option.name}" id="{$opt_label_id}" value="{$option.value|escape:htmlall}" />
      {elseif $option.type eq "checkbox"}
        {if $option.disabled}
          <input type="hidden" name="{$option.name}" value="{$option.value|escape}" />
        {/if}
        <input type="checkbox" id="{$opt_label_id}" name="{$option.name}"{if $option.value eq "Y"} checked="checked"{/if}{if $option.disabled} disabled="disabled"{/if} />
      {elseif $option.type eq "textarea"}
        <textarea name="{$option.name}" cols="30" rows="5">{$option.value|escape:html}</textarea>
      {elseif $option.type eq "selector" and $option.variants ne ''}
        <select name="{$option.name}">
          {foreach from=$option.variants item=vitem key=vkey}
            <option value="{$vkey}"{if $vitem.selected} selected="selected"{/if}>{$vitem.name}</option>
          {/foreach}
        </select>
      {/if}
    </span>
    <span class="xpc-option-help">
      {if $lng.$opt_descr and not $hidden}
        {include file="main/tooltip_js.tpl" title=$lng.$opt_comment|default:$option.comment text=$lng.$opt_descr id="help_`$option.name`" type="img" sticky=true}
      {/if}
    </span>
  </div>
</li>
{/foreach}
</ul>
