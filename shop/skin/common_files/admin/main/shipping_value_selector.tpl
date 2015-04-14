{*

5263249d04f77b4d573a65c42c24fff675c60fd1, v1 (xcart_4_6_5), 2014-09-11 13:11:04, shipping_value_selector.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}

{if $toggle_selector and $toggle_value}
<script type="text/javascript">
//<![CDATA[
$(document).ready( function() {ldelim}
  // initial ui update
  $('{$toggle_selector}').toggle($('#{$param_prefix}_{$param_name}').val() == '{$toggle_value}');
  // bind event related ui update
  $('#{$param_prefix}_{$param_name}').bind('change', function(event){ldelim}
    $('{$toggle_selector}').toggle(this.value == '{$toggle_value}');
  {rdelim});
{rdelim});
//]]>
</script>
{/if}

<tr>
  <td width="50%"><b>{$lng_label}:</b></td>
  <td>
    <select name="{$param_name}" id="{$param_prefix}_{$param_name}">
      {foreach from=$options key=item_value item=item_name}
          <option value="{$item_value}" {if $shipping_options.$param_prefix.$param_name eq $item_value}selected="selected"{/if}>{$item_name}</option>
      {/foreach}
    </select>
  </td>
</tr>
