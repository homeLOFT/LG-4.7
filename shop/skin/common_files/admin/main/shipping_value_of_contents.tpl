{*

0c6c8dfc71cbe1dd453a242345dbcd117dae3963, v2 (xcart_4_6_0), 2013-02-28 12:27:40, shipping_value_of_contents.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

<script type="text/javascript">
//<![CDATA[
$(document).ready( function() {ldelim}
  $('#{$name_prefix}_type').bind("change", function(event){ldelim}
    $('#{$name_prefix}_fixed').toggle(this.value == 'fixed_value');
  {rdelim})
{rdelim});

//]]>
</script>

<tr>
  <td width="50%"><b>{$lng_label}:</b></td>
  <td>
  <select name="{$name_prefix}_type" id="{$name_prefix}_type">
    <option value="150%"{if $shipper_options.$param_name eq "150%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"150%"}</option>
    <option value="140%"{if $shipper_options.$param_name eq "140%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"140%"}</option>
    <option value="130%"{if $shipper_options.$param_name eq "130%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"130%"}</option>
    <option value="120%"{if $shipper_options.$param_name eq "120%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"120%"}</option>
    <option value="110%"{if $shipper_options.$param_name eq "110%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"110%"}</option>
    <option value="100%"{if $shipper_options.$param_name eq "100%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"100%"}</option>
    <option value="90%"{if $shipper_options.$param_name eq "90%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"90%"}</option>
    <option value="80%"{if $shipper_options.$param_name eq "80%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"80%"}</option>
    <option value="70%"{if $shipper_options.$param_name eq "70%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"70%"}</option>
    <option value="60%"{if $shipper_options.$param_name eq "60%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"60%"}</option>
    <option value="50%"{if $shipper_options.$param_name eq "50%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"50%"}</option>
    <option value="40%"{if $shipper_options.$param_name eq "40%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"40%"}</option>
    <option value="30%"{if $shipper_options.$param_name eq "30%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"30%"}</option>
    <option value="20%"{if $shipper_options.$param_name eq "20%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"20%"}</option>
    <option value="10%"{if $shipper_options.$param_name eq "10%"} selected="selected"{/if}>{$lng.lbl_N_of_order_total|substitute:"percent":"10%"}</option>
    <option value="disabled"{if $shipper_options.$param_name eq "disabled" or !$shipper_options.$param_name} selected="selected"{/if}>{$lng.lbl_disabled}</option>
    <option value="fixed_value"{if $shipper_options.$fixed_value_name eq "Y"} selected="selected"{/if}>{$lng.lbl_fixed_value}</option>
  </select>
    <input type="text" name="{$name_prefix}_fixed" id="{$name_prefix}_fixed" size="10" {if $shipper_options.$fixed_value_name ne "Y"} value="0" style="display: none;"{else}value="{$shipper_options.$param_name|default:'0'}"{/if}/>
  </td>
</tr>
