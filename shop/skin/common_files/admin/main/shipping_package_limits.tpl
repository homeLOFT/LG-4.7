{*

5263249d04f77b4d573a65c42c24fff675c60fd1, v3 (xcart_4_6_5), 2014-09-11 13:11:04, shipping_package_limits.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
<tr>
    <td colspan="2"><br />{include file="main/subheader.tpl" title=$lng.lbl_package_limits class="grey"}</td>
</tr>

<tr>
  <td><label for="param11"><b>{$lng.lbl_carrier_pkg_no_use}:</b></label></td>
  <td><input type="checkbox" name="param11" id="param11" value="Y"{if $shipper_options.param11 eq "Y" or !$shipper_options.param11} checked="checked"{/if} /></td>
</tr>

<tr>
  <td><b>{$lng.lbl_maximum_package_weight} ({$_weight_symbol|default:$config.General.weight_symbol})*:</b></td>
  <td>
    <input type="text" name="max_weight" size="6" value="{$shipper_options.param08|doubleval}"/>
   </td>
</tr>

<tr class="use-package-dimensions">
  <td><b>{$lng.lbl_maximum_package_dimensions} ({$_dimensions_symbol|default:$config.General.dimensions_symbol})*:</b></td>
  <td nowrap="nowrap">
    <table cellpadding="0" cellspacing="1" border="0">
    <tr>
      <td>{$lng.lbl_length}</td>
      <td></td>
      <td>{$lng.lbl_width}</td>
      <td></td>
      <td>{$lng.lbl_height}</td>
    </tr>
    <tr>
      <td><input type="text" name="dim_length" size="6" value="{$shipper_options.dim_length|doubleval}" {if $disable_dimensions eq "Y"} disabled="disabled"{/if} /></td>
      <td>&nbsp;x&nbsp;</td>
      <td><input type="text" name="dim_width" size="6" value="{$shipper_options.dim_width|doubleval}" {if $disable_dimensions eq "Y"} disabled="disabled"{/if} /></td>
      <td>&nbsp;x&nbsp;</td>
      <td><input type="text" name="dim_height" size="6" value="{$shipper_options.dim_height|doubleval}" {if $disable_dimensions eq "Y"} disabled="disabled"{/if} /></td>
    </tr>
    </table>
  </td>
</tr>

<tr class="use-package-dimensions">
  <td><label for="use_maximum_dimensions"><b>{$lng.lbl_use_maximum_dimensions}:</b></label></td>
  <td><input type="checkbox" name="use_maximum_dimensions" id="use_maximum_dimensions" value="Y"{if $shipper_options.param09 eq "Y"} checked="checked"{/if} {if $disable_dimensions eq "Y"} disabled="disabled"{/if} /></td>
</tr>

<tr class="use-package-dimensions">
  <td colspan="2"><b>*</b> {$lng.txt_shipper_limits_note|substitute:"shipper":$shipper}</td>
</tr>
