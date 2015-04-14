{*
5e0fb6f5a2de2fe0eca4eec19009d7c396e29c2a, v9 (xcart_4_7_0), 2015-03-03 08:32:30, customer_options.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $product_options ne '' or $product_wholesale ne ''}
{if $include_table_tag ne ''}<table>{/if}
  {if $nojs ne 'Y'}
    <tr style="display: none;">
      <td colspan="3">

<script type="text/javascript">
//<![CDATA[
var alert_msg = '{$alert_msg|wm_remove|escape:javascript}';
//]]>
</script>
        {include file="modules/Product_Options/check_options.tpl"}
      </td>
    </tr>
  {/if}
  {if $is_popup_poptions}
    {assign var=autofocus value='autofocus'}
  {else}
    {assign var=autofocus value=''}
  {/if}

  {foreach from=$product_options key=k item=v}
    {if $v.options ne '' or $v.is_modifier eq 'T' or $v.is_modifier eq 'A'}
      <tr>
        <td class="property-name product-input">
          {if $usertype eq 'A'}
            {$v.class}
          {else}
            {$v.classtext|escape|default:$v.class}
          {/if}
        </td>
        <td class="property-value" colspan="2">

          {if $cname ne ''}
            {assign var="poname" value="$cname[`$v.classid`]"}
          {else}
            {assign var="poname" value="product_options[`$v.classid`]"}
          {/if}

          {if $v.is_modifier eq 'T'}

            <input id="po{$v.classid}" type="text" name="{$poname}" value="{$v.default|escape}"{if $aom_edit_mode eq 'Y'} data-aom-orig-value="{$v.default|escape}"{/if} {$autofocus} />

          {elseif $v.is_modifier eq 'A'}

            <textarea id="po{$v.classid}" name="{$poname}"{if $aom_edit_mode eq 'Y'} data-aom-orig-value="{$v.default|escape}"{/if} {$autofocus}>{$v.default|escape}</textarea>

          {else}

            <select id="po{$v.classid}" name="{$poname}"{if $disable} disabled="disabled"{/if}{if $nojs ne 'Y'} onchange="javascript: check_options();"{/if}{if $aom_edit_mode eq 'Y'} data-aom-orig-value="{$aom_orig_options.{$v.classid}.optionid}"{/if} {$autofocus}>

              {foreach from=$v.options item=o}

                <option value="{$o.optionid}"{if $o.selected eq 'Y'} selected="selected"{/if}>
                {strip}
                  {$o.option_name|escape}
                  {if $v.is_modifier eq 'Y' and $o.price_modifier ne 0}
                    &nbsp;(
                    {if $o.modifier_type ne '%'}
                      {currency value=$o.price_modifier display_sign=1 plain_text_message=1}
                    {else}
                      {$o.price_modifier}%
                    {/if}
                    )
                  {/if}
                {/strip}
                </option>

              {/foreach}
            </select>
          {/if}
          {assign var=autofocus value=''}

        </td>
      </tr>
    {/if}

  {/foreach}
{if $include_table_tag ne ''}</table>{/if}
{/if}

{if $product_options_ex ne ''}
{if $include_table_tag ne ''}<table>{/if}
  <tr>
      <td class="warning-message" colspan="3" id="exception_msg" style="display: none;"></td>
  </tr>

  {if $err ne ''}

    <tr>
      <td colspan="3" class="customer-message">{$lng.txt_product_options_combinations_warn}:</td>
    </tr>

    {foreach from=$product_options_ex item=v}
      <tr>
        <td colspan="3" class="poptions-exceptions-list">

          {foreach from=$v item=o}

            {strip}
            <div>
              {if $usertype eq 'A'}
                {$o.class}
              {else}
                {$o.classtext|escape}
              {/if}
              : {$o.option_name|escape}
            </div>
            {/strip}

          {/foreach}

        </td>
      </tr>
    {/foreach}

  {/if}
{if $include_table_tag ne ''}</table>{/if}
{/if}
