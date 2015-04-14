{*
2e4457b15a0a26fb318a798b150f6fe2045ad0c0, v1 (xcart_4_4_5), 2011-12-23 12:52:28, cc_bean_interaco.tpl, ferz
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>Beanstream INTERAC<sup>&reg;</sup> Online</h1>

{$lng.txt_cc_configure_top_text}

<br /><br />

{$lng.lbl_cc_beani_config_note1|substitute:"URL":"https://payments.beanstream.com/partners/application.asp?cPartner=xcart"}

<br /><br />

{capture name=dialog}
<form action="cc_processing.php?cc_processor={$smarty.get.cc_processor|escape:"url"}" method="post">

{$lng.lbl_cc_beani_config_note2}

<ul>
  <li style="padding-bottom: 4px;">{$lng.lbl_cc_beani_referrer_url}: <span style="color: #3333ff">{$https_location}/payment/payment_cc.php</span></li>
  <li style="padding-bottom: 4px;">{$lng.lbl_cc_beani_funded_url}: <span style="color: #3333ff">{$https_location}/payment/cc_bean_interaco.php</span></li>
  <li style="padding-bottom: 4px;">{$lng.lbl_cc_beani_notfunded_url}: <span style="color: #3333ff">{$https_location}/payment/cc_bean_interaco.php</span></li>
</ul>

<table cellspacing="0" cellpadding="0" border="0">
<tr>
<td>

  <table cellspacing="10">

    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>

    <tr>
      <td>{$lng.lbl_cc_beani_mid}:</td>
      <td>
        <input type="text" name="param01" size="32" value="{$module_data.param01|escape}" />
        {include file="main/tooltip_js.tpl" text=$lng.lbl_cc_beani_mid_note type="img" id="mid_note" width='500'}
      </td>
    </tr>

    <tr>
      <td>{$lng.lbl_cc_beani_use_login}:</td>
      <td>
        <input type="hidden" name="param02" value="N" />
        <input type="checkbox" name="param02" size="32" value="Y" {if $module_data.param02 eq "Y"}checked="checked"{/if} onchange="javascript: $('#validate-by-username').toggle();" />
        {include file="main/tooltip_js.tpl" text=$lng.lbl_cc_beani_use_login_note type="img" id="passwd_note" wrapper_tag='div' sticky=true width='500'}
      </td>
    </tr>

    <tr style="display: {if $module_data.param02 eq "Y"}block{else}none{/if};" id="validate-by-username">
      <td style="padding-left: 50px; padding-bottom: 10px;" colspan="2">
          <table>
            <tr>
              <td>{$lng.lbl_username}:</td>
              <td><input type="text" name="param03" size="16" value="{$module_data.param03|escape}" /></td>
            </tr>
            <tr>
              <td>{$lng.lbl_password}:</td>
              <td><input type="text" name="param05" size="16" value="{$module_data.param05|escape}" /></td>
            </tr>
          </table>
      </td>
    </tr>

    <tr>
      <td>{$lng.lbl_cc_order_prefix}:</td>
      <td><input type="text" name="param04" size="32" value="{$module_data.param04|escape}" /></td>
    </tr>

    <tr>
      <td colspan="2">&nbsp;</td>
    </tr>

    <tr>
      <td colspan="2"><input type="submit" value="{$lng.lbl_update|strip_tags:false|escape}" /></td>
    </tr>

  </table>

</td>
<td style="vertical-align: top; padding-left: 125px; padding-top: 35px;" align="right">
<a href="http://www.beanstream.com/public/interac.asp" title="INTERAC Online service" target="_new"><img src="https://beanstreamsupport.pbworks.com/f/interac_logo.jpg" alt="INTERAC Online service" /></a>
</td>
</tr>
</table>

</form>

<br /><br /><br />

<sup>&reg;</sup> {$lng.lbl_beani_trademark}

{/capture}
{include file="dialog.tpl" title=$lng.lbl_cc_settings content=$smarty.capture.dialog extra='width="100%"'}
