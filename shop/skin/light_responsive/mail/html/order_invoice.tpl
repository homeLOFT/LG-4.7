{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, order_invoice.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $customer ne ''}
  {assign var="_userinfo" value=$customer}{else}{assign var="_userinfo" value=$userinfo}
{/if}

{config_load file="$skin_config"}

<table class="block-grid three-up" summary="{$lng.lbl_summary|escape}">
  <tr>
      <td id="html_order_info">
      <strong style="font-size: 28px; text-transform: uppercase;">
        {if $order.status eq 'A' or $order.status eq 'P' or $order.status eq 'C'}{$lng.lbl_receipt}{else}{$lng.lbl_invoice}{/if}
      </strong>
      <br />
      <br />
      <strong>{$lng.lbl_date}:</strong> {$order.date|date_format:$config.Appearance.datetime_format}<br />
      <strong>{$lng.lbl_order_id}:</strong> #{$order.orderid}<br />
      <strong>{$lng.lbl_order_status}:</strong> {include file="main/order_status.tpl" status=$order.status mode="static"}<br />
      <strong>{$lng.lbl_payment_method}:</strong><br />
      {$order.payment_method}<br />
      {if $order.extra.xpc_saved_card_num and $order.extra.xpc_saved_card_type}
        {$order.extra.xpc_saved_card_type} {$order.extra.xpc_saved_card_num}<br />
      {/if}                  
      {if not $active_modules.POS_System or not $pos_hide_shipping_info}
        <strong>{$lng.lbl_delivery}:</strong><br />
        {$order.shipping|trademark:'use_alt'|default:$lng.txt_not_available}
      {/if}
      {if $order.tracking}
      <br /><strong>{$lng.lbl_tracking_number}:</strong> {$order.tracking|escape}
      {/if}
      </td><td style="padding-top: 30px !important;">
        &nbsp;
      </td><td class="invoice-right-info">
       <img src="https://www.leathergroups.com/common/images/logo.png" alt="" /><br />
       {$config.Company.location_address}, {$config.Company.location_city}<br />
      {$config.Company.location_zipcode}{if $config.Company.location_country_has_states}, {$config.Company.location_state_name}{/if}<br />
      {$config.Company.location_country_name}<br />
      {if $config.Company.company_phone}
        {$lng.lbl_phone_1_title}: {$config.Company.company_phone}<br />
      {/if}
      {if $config.Company.company_phone_2}
        {$lng.lbl_phone_2_title}: {$config.Company.company_phone_2}<br />
      {/if}
      {if $config.Company.company_fax}
        {$lng.lbl_fax}: {$config.Company.company_fax}<br />
      {/if}
      {if $config.Company.orders_department}
        {$lng.lbl_email}: {$config.Company.orders_department}<br />
      {/if}
      {if $order.applied_taxes}
        <br />
        {foreach from=$order.applied_taxes key=tax_name item=tax}
          {$tax.regnumber}<br />
        {/foreach}
      {/if}
    </td>
  </tr>
</table>

{include file="mail/html/responsive_row.tpl" content='<hr style="border: 0px none; border-bottom: 2px solid #58595b; margin: 2px 0px 17px 0px; padding: 0px; height: 0px;" />'}

<table class="block-grid data-table invoice-table" summary="{$lng.lbl_address|escape}">
  <tr>
    <td class="name"><strong>{$lng.lbl_email}:</strong></td>
    <td class="value">{$order.email}</td>
  </tr>

  {if $_userinfo.default_fields.title}
    <tr>
      <td class="name"><strong>{$lng.lbl_title}:</strong></td>
      <td class="value">{$order.title}</td>
    </tr>
  {/if}

  {if $_userinfo.default_fields.firstname}
    <tr>
      <td class="name"><strong>{$lng.lbl_first_name}:</strong></td>
      <td class="value">{$order.firstname}</td>
    </tr>
  {/if}

  {if $_userinfo.default_fields.lastname}
    <tr>
      <td class="name"><strong>{$lng.lbl_last_name}:</strong></td>
      <td class="value">{$order.lastname}</td>
    </tr>
  {/if}

  {if $_userinfo.default_fields.company}
    <tr>
      <td class="name"><strong>{$lng.lbl_company}:</strong></td>
      <td class="value">{$order.company}</td>
    </tr>
  {/if}

  {if $_userinfo.default_fields.tax_number}
    <tr>
      <td class="name"><strong>{$lng.lbl_tax_number}:</strong></td>
      <td class="value">{$order.tax_number}</td>
    </tr>
  {/if}

  {if $_userinfo.default_fields.url}
    <tr>
      <td class="name"><strong>{$lng.lbl_url}:</strong></td>
      <td class="value">{$order.url}</td>
    </tr>
  {/if}

  {foreach from=$_userinfo.additional_fields item=v}
    {if $v.section eq 'P' and $v.value ne ''}
      <tr>
        <td class="name"><strong>{$v.title}:</strong></td>
        <td class="value">{$v.value}</td>
      </tr>
    {/if}
  {/foreach}
</table>

<table class="block-grid two-up address-table-container" summary="{$lng.lbl_addresses|escape}">
  <tr>
    <td>
      {if not $active_modules.POS_System or not $pos_hide_shipping_info}
      <div class="address-header">{$lng.lbl_billing_address}</div>
      <hr style="border: 0px none; border-bottom: 2px solid #58595b; margin: 2px 0px; padding: 0px; height: 0px;" />
      {/if}

      <table class="block-grid address-table" summary="{$lng.lbl_billing_address|escape}">

        {if $_userinfo.default_address_fields.title}
          <tr>
            <td class="name"><strong>{$lng.lbl_title}:</strong></td>
            <td class="value">{$order.b_title|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.firstname}
          <tr>
            <td class="name"><strong>{$lng.lbl_first_name}:</strong> </td>
            <td class="value">{$order.b_firstname|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.lastname}
          <tr>
            <td class="name"><strong>{$lng.lbl_last_name}:</strong> </td>
            <td class="value">{$order.b_lastname|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.address}
          <tr>
            <td class="name"><strong>{$lng.lbl_address}:</strong> </td>
            <td class="value">{$order.b_address|escape}<br />{$order.b_address_2|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.city}
          <tr>
            <td class="name"><strong>{$lng.lbl_city}:</strong> </td>
            <td class="value">{$order.b_city|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.county and $config.General.use_counties eq 'Y'}
          <tr>
            <td class="name"><strong>{$lng.lbl_county}:</strong> </td>
            <td class="value">{$order.b_countyname|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.state}
          <tr>
            <td class="name"><strong>{$lng.lbl_state}:</strong> </td>
            <td class="value">{$order.b_statename|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.country}
          <tr>
            <td class="name"><strong>{$lng.lbl_country}:</strong> </td>
            <td class="value">{$order.b_countryname|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.zipcode}
          <tr>
            <td class="name"><strong>{$lng.lbl_zip_code}:</strong> </td>
            <td class="value">{include file="main/zipcode.tpl" val=$order.b_zipcode zip4=$order.b_zip4 static=true}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.phone}
          <tr>
            <td class="name"><strong>{$lng.lbl_phone}:</strong> </td>
            <td class="value">{$order.b_phone|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.fax}
          <tr>
            <td class="name"><strong>{$lng.lbl_fax}:</strong> </td>
            <td class="value">{$order.b_fax|escape}</td>
          </tr>
        {/if}

        {foreach from=$_userinfo.additional_fields item=v}
          {if $v.section eq 'B' and $v.value.B ne ''}
            <tr>
              <td class="name"><strong>{$v.title}:</strong></td>
              <td class="value">{$v.value.B}</td>
            </tr>
          {/if}
        {/foreach}

      </table>

    </td>{if not $active_modules.POS_System or not $pos_hide_shipping_info}<td>
      <div class="address-header">{$lng.lbl_shipping_address}</div>
      <hr style="border: 0px none; border-bottom: 2px solid #58595b; margin: 2px 0px; padding: 0px; height: 0px;" />

      <table class="block-grid address-table" summary="{$lng.lbl_shipping_address|escape}">

        {if $_userinfo.default_address_fields.title}
          <tr>
            <td class="name"><strong>{$lng.lbl_title}:</strong></td>
            <td class="value">{$order.s_title|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.firstname}
          <tr>
            <td class="name"><strong>{$lng.lbl_first_name}:</strong> </td>
            <td class="value">{$order.s_firstname|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.lastname}
          <tr>
            <td class="name"><strong>{$lng.lbl_last_name}:</strong> </td>
            <td class="value">{$order.s_lastname|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.address}
          <tr>
            <td class="name"><strong>{$lng.lbl_address}:</strong> </td>
            <td class="value">{$order.s_address|escape}<br />{$order.s_address_2|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.city}
          <tr>
            <td class="name"><strong>{$lng.lbl_city}:</strong> </td>
            <td class="value">{$order.s_city|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.county and $config.General.use_counties eq 'Y'}
          <tr>
            <td class="name"><strong>{$lng.lbl_county}:</strong> </td>
            <td class="value">{$order.s_countyname|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.state}
          <tr>
            <td class="name"><strong>{$lng.lbl_state}:</strong> </td>
            <td class="value">{$order.s_statename|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.country}
          <tr>
            <td class="name"><strong>{$lng.lbl_country}:</strong> </td>
            <td class="value">{$order.s_countryname|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.zipcode}
          <tr>
            <td class="name"><strong>{$lng.lbl_zip_code}:</strong> </td>
            <td class="value">{include file="main/zipcode.tpl" val=$order.s_zipcode zip4=$order.s_zip4 static=true}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.phone}
          <tr>
            <td class="name"><strong>{$lng.lbl_phone}:</strong> </td>
            <td class="value">{$order.s_phone|escape}</td>
          </tr>
        {/if}

        {if $_userinfo.default_address_fields.fax}
          <tr>
            <td class="name"><strong>{$lng.lbl_fax}:</strong> </td>
            <td class="value">{$order.s_fax|escape}</td>
          </tr>
        {/if}

        {foreach from=$_userinfo.additional_fields item=v}
          {if $v.section eq 'B' and $v.value.S ne ''}
            <tr>
              <td class="name"><strong>{$v.title}:</strong></td>
              <td class="value">{$v.value.S}</td>
            </tr>
           {/if}
        {/foreach}

      </table>
    </td>
    {else}{* POS_System *}
      <td>&nbsp;</td>
    {/if}
  </tr>
  {capture name="additional_fields"}{foreach from=$_userinfo.additional_fields item=v}{strip}
    {if $v.section eq 'A' and $v.value ne ''}
      <tr>
        <td class="name"><strong>{$v.title}:</strong></td>
        <td class="value">{$v.value}</td>
      </tr>
    {/if}
  {/strip}{/foreach}{/capture}
  {if $smarty.capture.additional_fields ne ''}
    <tr>
      <td>
        <div class="address-header">{$lng.lbl_additional_information}</div>
        <hr style="border: 0px none; border-bottom: 2px solid #58595b; margin: 2px 0px; padding: 0px; height: 0px;" />
      </td>
    </tr>

  {/if}
</table>

{if $smarty.capture.additional_fields ne ''}
  <table class="block-grid data-table invoice-table" summary="{$lng.lbl_additional_information|escape}">
    {$smarty.capture.additional_fields}
  </table>
{/if}

{if $order.PO_Number}
  <table class="block-grid two-up address-table-container">
    <tr>
      <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
      <td><strong>{$lng.lbl_po_number}</strong>: {$order.PO_Number}</td>
      <td colspan="2" width="55%">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3">&nbsp;</td>
    </tr>
  </table>
{/if}

{if $config.Email.show_cc_info eq "Y" 
  and $show_order_details eq "Y" 
  and ($order.details ne "" or $order.extra.advinfo ne "")
}

  <table class="block-grid two-up address-table-container"> 
    <tr>
      <td>
        <div class="address-header">{$lng.lbl_order_payment_details}</div>
        <hr style="border: 0px none; border-bottom: 2px solid #58595b; margin: 2px 0px; padding: 0px; height: 0px;" />
      </td>
    </tr>
  </table>

  {if $order.details ne ""}
    {capture name="row"}
      {$order.details|order_details_translate|escape|nl2br}
    {/capture}
    {include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}
  {/if}
  
  {if $order.extra.advinfo ne ""}
    {capture name="row"}
      {$order.extra.advinfo|escape|nl2br}
    {/capture}
    {include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}
  {/if}
{/if}

{if $order.netbanx_reference}
  {capture name="row"}
    NetBanx Reference: {$order.netbanx_reference}
  {/capture}
  {include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}
{/if}

{include file="mail/html/order_data.tpl"}

{if $order.need_giftwrap eq "Y"}
  {capture name="row"}
    {include file="modules/Gift_Registry/gift_wrapping_invoice.tpl" show=message}
  {/capture}
  {include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}
{/if}

{if $order.customer_notes ne ""}
  {capture name="row"}
    <p style="font-size: 14px; font-weight: bold; text-align: center;">{$lng.lbl_customer_notes}</p>
    <div style="border: 1px solid #cecfce; padding: 5px;">{$order.customer_notes|nl2br}</div>
  {/capture}
  {include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}
{/if}

{capture name="row"}
  {$lng.txt_thank_you_for_purchase}
{/capture}
{include file="mail/html/responsive_row.tpl" content=$smarty.capture.row}
