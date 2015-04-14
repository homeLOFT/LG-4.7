{*
c4876f0dc3c1be77d432d7277a473c36b8ae058c, v27 (xcart_4_6_1), 2013-09-07 11:40:24, ps_paypal_group.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>PayPal</h1>

{if $countries}
<form action="cc_processing.php" method="post" name="countryform">
<input type="hidden" name="cc_processor" value="{$smarty.get.cc_processor|escape:"url"}" />
<input type="hidden" name="mode" value="update_country" />

<table width="100%">
  <tr>
    <td nowrap="nowrap">{$lng.lbl_business_location}:&nbsp;</td>
    <td width="100%">
      <select name="paypal_country" id="paypal_country" onchange="javascript: document.countryform.submit();">
        <option value="none"{if $config.paypal_country eq $c.country_code or ($c.country_code eq $config.Company.location_country and $config.paypal_country eq "")} selected="selected"{/if}>{$lng.lbl_none}</option>
        {foreach from=$countries item=c}
        <option value="{$c.country_code}"{if $config.paypal_country eq $c.country_code or ($c.country_code eq $config.Company.location_country and $config.paypal_country eq "")} selected="selected"{/if}>{$c.country|amp}</option>
        {/foreach}
      </select>
    </td>
  </tr>
</table>
</form>
{/if}

<script type="text/javascript">
//<![CDATA[
var pp_promo = {ldelim}
  uk: '{$lng.lbl_paypal_api_promo_uk|wm_remove|escape:javascript}',
  pro: '{$lng.lbl_paypal_api_promo_pro|wm_remove|escape:javascript}',
  ipn: '{$lng.lbl_paypal_api_promo_ipn|wm_remove|escape:javascript}',
  express: '{$lng.lbl_paypal_api_promo_express|wm_remove|escape:javascript}',
  advanced: '',
  payflowlink: '',
  pro_hosted: '',
{rdelim};
var paypal_solution = '{$config.paypal_solution|default:'ipn'|escape:javascript}';
//]]>
</script>
<script type="text/javascript" src="{$SkinDir}/js/ps_paypal_group.js"></script>

<br />

{capture name=dialog}

  <table width="100%" cellpadding="5">
    <tr>
      <td width="100%">
  
        {$lng.txt_paypal_solution_title}

        <form action="cc_processing.php" method="post">
          <input type="hidden" name="cc_processor" value="{$smarty.get.cc_processor|escape:"url"}" />

          <table cellpadding="5" cellspacing="5" width="100%" id="paypal-settings">

            {if $show_paypal_methods.ipn eq "Y" || $show_paypal_methods.ipn eq ""}
            <tr valign="top">
              <td width="20">
                <input id="r_sol_ipn" type="radio" name="paypal_solution" onclick="view_solution('ipn');" value="ipn"{if $config.paypal_solution eq "ipn"} checked="checked"{/if} />
              </td>
              <td width="100%">
                <h3><label for="r_sol_ipn">{if $config.paypal_country eq 'US'}{$lng.lbl_paypal_sol_std_us}{else}{$lng.lbl_paypal_sol_std}{/if}</label></h3>
                {$lng.txt_paypal_sol_std_note}<br />
                <a href="{if $config.paypal_country eq 'US'}https://www.paypal.com/webapps/mpp/referral/paypal-payments-standard?partner_id=RDGQCFJTT6Y6A{else}https://www.paypal.com/webapps/mpp/referral/website-payments-standard?partner_id=RDGQCFJTT6Y6A{/if}" target="_blank">{$lng.lbl_paypal_signup}</a>
              </td>
            </tr>
            {/if}

            {if $show_paypal_methods.advanced eq 'Y' || $show_paypal_methods.advanced eq ''}
            <tr valign="top">
              <td>
                <input id="r_sol_advanced" type="radio" name="paypal_solution" onclick="view_solution('advanced');" value="advanced"{if $config.paypal_solution eq "advanced"} checked="checked"{/if} />
              </td>
              <td>
                <h3><label for="r_sol_advanced">{$lng.lbl_paypal_sol_advanced}</label></h3>
                {$lng.txt_paypal_sol_advanced_note}<br />
                <a href="https://www.paypal.com/webapps/mpp/referral/paypal-payments-advanced?partner_id=RDGQCFJTT6Y6A" target="_blank">{$lng.lbl_paypal_signup}</a>
              </td>
            </tr>
            {/if}

            {if $show_paypal_methods.pro_hosted eq 'Y' || $show_paypal_methods.pro_hosted eq ''}
            <tr valign="top">
              <td>
                <input id="r_sol_pro_hosted" type="radio" name="paypal_solution" onclick="view_solution('pro_hosted');" value="pro_hosted"{if $config.paypal_solution eq 'pro_hosted'} checked="checked"{/if} />  
              </td>
              <td>
                <h3><label for="r_sol_pro_hosted">{$lng.lbl_paypal_sol_pro_hosted}</label></h3>
                {$lng.txt_paypal_sol_pro_hosted_note}<br />
               </td>
            </tr>
            {/if}

            {if $show_paypal_methods.pro eq "Y" || $show_paypal_methods.pro eq ""}
            <tr valign="top">
              <td>
                <input id="r_sol_pro" type="radio" name="paypal_solution" onclick="view_solution('pro');" value="pro"{if $config.paypal_solution eq "pro"} checked="checked"{/if} />  
              </td>
              <td>
                <h3><label for="r_sol_pro">{if $config.paypal_country eq 'US'}{$lng.lbl_paypal_sol_pro_us}{else}{$lng.lbl_paypal_sol_pro}{/if}{include file="main/tooltip_js.tpl" text=$lng.txt_paypal_sol_pro_tooltip id="r_sol_pro_tip" type='img' sticky=true width=400}</label></h3>
                {$lng.txt_paypal_sol_pro_note}<br />
                <a href="{if $config.paypal_country eq 'US'}https://www.paypal.com/webapps/mpp/referral/paypal-payments-pro?partner_id=RDGQCFJTT6Y6A{else}https://www.paypal.com/webapps/mpp/referral/website-payments-pro?partner_id=RDGQCFJTT6Y6A{/if}" target="_blank">{$lng.lbl_paypal_signup}</a>
                </td>
            </tr>
            {/if}

            {if $show_paypal_methods.uk eq "Y" || $show_paypal_methods.uk eq ""}
            <tr valign="top">
              <td>
                <input id="r_sol_uk" type="radio" name="paypal_solution" onclick="view_solution('uk');" value="uk"{if $config.paypal_solution eq "uk"} checked="checked"{/if} />
              </td>
              <td>
                <h3><label for="r_sol_uk">{$lng.lbl_paypal_sol_uk}{include file="main/tooltip_js.tpl" text=$lng.txt_paypal_sol_uk_tooltip id="r_sol_uk_tip" type='img' sticky=true width=400}</label></h3>
                {$lng.txt_paypal_sol_pro_note_uk}<br />
                <a href="{if $config.paypal_country eq 'US'}https://www.paypal.com/webapps/mpp/referral/paypal-payments-pro?partner_id=RDGQCFJTT6Y6A{else}https://www.paypal.com/webapps/mpp/referral/website-payments-pro?partner_id=RDGQCFJTT6Y6A{/if}" target="_blank">{$lng.lbl_paypal_signup}</a>
              </td>
            </tr>
            {/if}

            {if $show_paypal_methods.payflowlink eq "Y" || $show_paypal_methods.payflowlink eq ""}
            <tr valign="top">
              <td>
                <input id="r_sol_payflowlink" type="radio" name="paypal_solution" onclick="view_solution('payflowlink');" value="payflowlink"{if $config.paypal_solution eq "payflowlink"} checked="checked"{/if} />
              </td>
              <td>
                <h3><label for="r_sol_payflowlink">{$lng.lbl_paypal_sol_payflowlink}</label></h3>
                {$lng.txt_paypal_sol_payflowlink_note}<br />
                <a href="https://www.paypal.com/webapps/mpp/referral/paypal-payflow-link?partner_id=RDGQCFJTT6Y6A" target="_blank">{$lng.lbl_paypal_signup}</a>
              </td>
            </tr>
            {/if}

            {if $show_paypal_methods.express eq 'Y' || $show_paypal_methods.express eq ''}
            <tr valign="top">
              <td>
                <input id="r_sol_express" type="radio" name="paypal_solution" onclick="view_solution('express');" value="express"{if $config.paypal_solution eq "express"} checked="checked"{/if} />
              </td>
              <td>
                <h3><label for="r_sol_express">{$lng.lbl_paypal_sol_express}</label></h3>
                {$lng.txt_paypal_sol_express_note}<br />
                <a href="https://www.paypal.com/webapps/mpp/referral/paypal-express-checkout?partner_id=RDGQCFJTT6Y6A" target="_blank">{$lng.lbl_paypal_signup}</a>
              </td>
            </tr>
            {/if}

            <tr>
              <td colspan="2">
                <hr size="1" noshade="noshade" />
                <div id="pp_promo"></div>
              </td>
            </tr>

            {* configuration boxes *}
            <tr id="sol_pro"{if $config.paypal_solution ne "pro"} style="display: none;"{/if}>
              <td>&nbsp;</td>
              <td>
                {if $xpc_dp_processors and $xpc_dp_processors.pro}
                  {include file="payments/ps_paypal_pro.tpl" conf_prefix="conf_data[pro]" module_data=$conf_data.pro}
                {else}
                  {$lng.txt_paypal_pro_pg_tooltip}
                {/if}
              </td>
            </tr>

            <tr id="sol_pro_hosted"{if $config.paypal_solution ne 'pro_hosted'} style="display: none;"{/if}>
              <td>&nbsp;</td>
              <td>
                {include file="payments/ps_paypal_pro_hosted.tpl" conf_prefix="conf_data[pro_hosted]" module_data=$conf_data.pro_hosted}
              </td>
            </tr>

            <tr id="sol_express"{if $config.paypal_solution ne "express"} style="display: none;"{/if}>
              <td>&nbsp;</td>
              <td>
                {include file="payments/ps_paypal_express.tpl" conf_prefix="conf_data[express]" module_data=$conf_data.pro}
              </td>
            </tr>

            <tr id="sol_uk"{if $config.paypal_solution ne "uk"} style="display: none;"{/if}>
              <td>&nbsp;</td>
              <td>
                {if $xpc_dp_processors and $xpc_dp_processors.uk}
                  {include file="payments/ps_paypal_uk.tpl" conf_prefix="conf_data[uk]" module_data=$conf_data.uk}
                {else}
                  {$lng.txt_paypal_pro_pg_tooltip}
                {/if}
              </td>
            </tr>

            <tr id="sol_ipn"{if $config.paypal_solution ne "ipn"} style="display: none;"{/if}>
              <td>&nbsp;</td>
              <td>
                {include file="payments/ps_paypal.tpl" conf_prefix="conf_data[ipn]" module_data=$conf_data.ipn}
              </td>
            </tr>

            <tr id="sol_advanced"{if $config.paypal_solution ne "advanced"} style="display: none;"{/if}>
              <td>&nbsp;</td>
              <td>
                {include file="payments/ps_paypal_advanced.tpl" conf_prefix="conf_data[advanced]" module_data=$conf_data.advanced}
              </td>
            </tr>

            <tr id="sol_payflowlink"{if $config.paypal_solution ne "payflowlink"} style="display: none;"{/if}>
              <td>&nbsp;</td>
              <td>
                {include file="payments/ps_paypal_advanced.tpl" conf_prefix="conf_data[payflowlink]" module_data=$conf_data.payflowlink payflowlink="Y"}
              </td>
            </tr>

            <tr>
              <td>&nbsp;</td>
              <td>
                <div id="paypal_bml_status">{if $active_modules.Bill_Me_Later}{$lng.lbl_paypal_bml_status_on}{else}{$lng.lbl_paypal_bml_status_off}{/if}</div>
              </td>
            </tr>

            <tr>
              <td colspan="2"><hr size="1" noshade="noshade" /></td>
            </tr>

            <tr>
              <td>&nbsp;</td>
              <td>
                <table width="100%">
                  <tr valign="top">
                    <td colspan="2"><p id="paypal_help_number">{$lng.txt_paypal_help_number}</p></td>
                  </tr>
                </table>
              </td>
            </tr>

            <tr>
              <td colspan="2" align="center">
                <input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" />
              </td>
            </tr>

          </table>
        </form>
      </td>
      <td valign="top">{include file="payments/ps_paypal_logo.tpl"}</td>
    </tr>
  </table>

{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
