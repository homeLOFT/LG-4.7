{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, dhl_ext_countries.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $config.Shipping.realtime_shipping eq "Y" and $config.Shipping.use_intershipper ne "Y" and (not $active_modules.UPS_OnLine_Tools or $show_carriers_selector ne 'Y' or $current_carrier ne 'UPS') and $dhl_ext_countries and $has_active_arb_smethods}

  <label>
    {$lng.txt_dhl_ext_countries_note}:
    <select name="dhl_ext_country" {if $onchange} onchange="javascript: self.location = 'cart.php?mode=checkout&amp;action=update&amp;dhl_ext_country=' + this.options[this.selectedIndex].value;"{/if}>
      {if not $dhl_ext_country}
        <option value="">{$lng.lbl_please_select_one}</option>
      {/if}
      {foreach from=$dhl_ext_countries item=c}
        <option value="{$c}"{if $c eq $dhl_ext_country} selected="selected"{/if}>{$c}</option>
      {/foreach}
    </select>
  </label>

{/if}
