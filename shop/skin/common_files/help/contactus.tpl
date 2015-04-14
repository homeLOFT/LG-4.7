{*
f18d906cd98df9d6c0f0305473781ab742ce5efa, v7 (xcart_4_6_5), 2014-07-30 18:13:36, contactus.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_contact_us}</h1>

{include file="check_required_fields_js.tpl" fillerror=$fillerror}
{include file="check_email_script.tpl"}
{include file="check_zipcode_js.tpl"}
{include file="change_states_js.tpl"}

{if $smarty.get.mode eq "update" or $smarty.get.mode eq ""}
  <p class="text-block">{$lng.txt_contact_us_header}</p>
{/if}

{capture name=dialog}

  {if $smarty.get.mode eq "sent"}

    {$lng.txt_contact_us_sent}

  {elseif $smarty.get.mode eq "update" or $smarty.get.mode eq ""}

    <form action="help.php?section=contactus&amp;mode=update&amp;action=contactus" method="post" name="registerform" onsubmit="javascript: return check_zip_code(this);">
      <input type="hidden" name="usertype" value="{$usertype|escape}" />

      <table cellspacing="0" class="data-table" summary="{$lng.lbl_contact_us|escape}">

        {if $default_fields.username.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="username">{$lng.lbl_username}</label></td>
            <td{if $default_fields.username.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td><input type="text" id="username" name="username" size="32" maxlength="128" value="{if $userinfo.username ne ''}{$userinfo.username|escape}{else}{$userinfo.login|escape}{/if}" /></td>
          </tr>
        {/if}

        {if $default_fields.title.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="title">{$lng.lbl_title}</label></td>
            <td{if $default_fields.title.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              {include file="main/title_selector.tpl" val=$userinfo.titleid}
            </td>
          </tr>
        {/if}

        {if $default_fields.firstname.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="firstname">{$lng.lbl_first_name}</label></td>
            <td{if $default_fields.firstname.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              <input type="text" id="firstname" name="firstname" size="32" maxlength="128" value="{$userinfo.firstname|escape}" />
            </td>
          </tr>
        {/if}
 
        {if $default_fields.lastname.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="lastname">{$lng.lbl_last_name}</label></td>
            <td{if $default_fields.lastname.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              <input type="text" id="lastname" name="lastname" size="32" maxlength="128" value="{$userinfo.lastname|escape}" />
            </td>
          </tr>
        {/if}

        {if $default_fields.company.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="company">{$lng.lbl_company}</label></td>
            <td{if $default_fields.company.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              <input type="text" id="company" name="company" size="32" value="{$userinfo.company|escape}" />
            </td>
          </tr>
        {/if}
 
        {if $default_fields.address.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="address">{$lng.lbl_address}</label></td>
            <td{if $default_fields.address.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              <input type="text" id="address" name="address" size="32" maxlength="255" value="{$userinfo.address|escape}" />
            </td>
          </tr>
        {/if}
 
        {if $default_fields.address_2.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="address_2">{$lng.lbl_address_2}</label></td>
            <td{if $default_fields.address_2.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              <input type="text" id="address_2" name="address_2" size="32" maxlength="255" value="{$userinfo.address_2|escape}" />
            </td>
          </tr>
        {/if}
 
        {if $default_fields.city.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="city">{$lng.lbl_city}</label></td>
            <td{if $default_fields.city.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              <input type="text" id="city" name="city" size="32" maxlength="64" value="{$userinfo.city|escape}" />
            </td>
          </tr>
        {/if}
 
        {if $default_fields.county.avail eq 'Y' and $config.General.use_counties eq "Y"}
          <tr>
            <td class="data-name"><label for="county">{$lng.lbl_county}</label></td>
            <td{if $default_fields.county.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              {include file="main/counties.tpl" counties=$counties name="county" default=$userinfo.county stateid=$userinfo.stateid country_name="country"}
            </td>
          </tr>
        {/if}

        {if $default_fields.state.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="state">{$lng.lbl_state}</label></td>
            <td{if $default_fields.state.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              {include file="main/states.tpl" states=$states name="state" default=$userinfo.state|default:$config.General.default_state default_country=$userinfo.country|default:$config.General.default_country country_name="country"}
            </td>
          </tr>
        {/if}
 
        {if $default_fields.country.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="country">{$lng.lbl_country}</label></td>
            <td{if $default_fields.country.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              <select id="country" name="country" onchange="javascript: check_zip_code_field(this, $('#zipcode'));">
                {foreach from=$countries item=c}
                  <option value="{$c.country_code}"{if ($userinfo.country eq $c.country_code) or ($c.country_code eq $config.General.default_country and $userinfo.country eq "")} selected="selected"{/if}>{$c.country}</option>
                {/foreach}
              </select>
            </td>
          </tr>
        {/if}

        {if $default_fields.zipcode.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="zipcode">{$lng.lbl_zip_code}</label></td>
            <td{if $default_fields.zipcode.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              {include file="main/zipcode.tpl" val=$userinfo.zipcode zip4=$userinfo.zip4 id="zipcode" name="zipcode"}
            </td>
          </tr>
        {/if}

        {if $default_fields.state.avail eq 'Y' and $default_fields.country.avail eq 'Y'}
          <tr style="display: none;">
            <td>
              {include file="main/register_states.tpl" state_name="state" country_name="country" county_name="county" state_value=$userinfo.state|default:$config.General.default_state county_value=$userinfo.county}
            </td>
          </tr>
        {/if}

        {if $default_fields.phone.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="phone">{$lng.lbl_phone}</label></td>
            <td{if $default_fields.phone.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              <input type="text" id="phone" name="phone" size="32" maxlength="32" value="{$userinfo.phone|escape}" />
            </td>
          </tr>
        {/if}
 
        {if $default_fields.email.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="email">{$lng.lbl_email}</label></td>
            <td{if $default_fields.email.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              <input type="text" id="email" name="email" class="input-email" size="32" maxlength="128" value="{$userinfo.email|escape}" onchange="javascript: checkEmailAddress(this);" />
            </td>
          </tr>
        {/if}

        {if $default_fields.fax.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="fax">{$lng.lbl_fax}</label></td>
            <td{if $default_fields.fax.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td><input type="text" id="fax" name="fax" size="32" maxlength="128" value="{$userinfo.fax|escape}" /></td>
          </tr>
        {/if}
 
        {if $default_fields.url.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="url">{$lng.lbl_web_site}</label></td>
            <td{if $default_fields.url.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>
              <input type="text" id="url" name="url" size="32" maxlength="128" value="{if $userinfo.url eq ""}http://{else}{$userinfo.url|escape}{/if}" />
            </td>
          </tr>
        {/if}

        {foreach from=$additional_fields item=v key=k}
          {if $v.avail eq "Y"}
            <tr>
              <td class="data-name"><label for="additional_values_{$k}">{$v.title|default:$v.field}</label></td>
              <td{if $v.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
              <td>

                {if $v.type eq 'T'}
                  <input type="text" id="additional_values_{$k}" name="additional_values[{$k}]" size="32" value="{$userinfo.additional_values[$k]|escape}" />

                {elseif $v.type eq 'C'}
                  <input type="checkbox" id="additional_values_{$k}" name="additional_values[{$k}]" value="Y"{if $userinfo.additional_values[$k] eq 'Y'} checked="checked"{/if} />

                {elseif $v.type eq 'S'}

                  <select id="additional_values_{$k}" name="additional_values[{$k}]">
                    {foreach from=$v.variants item=o}
                      <option value='{$o|escape}'{if $userinfo.additional_values[$k] eq $o} selected="selected"{/if}>{$o|escape}</option>
                    {/foreach}
                  </select>
                {/if}

              </td>
            </tr>
          {/if}
        {/foreach}

        {if $default_fields.department.avail eq 'Y'}
          <tr>
            <td class="data-name"><label for="department">{$lng.lbl_department}</label></td>
            <td{if $default_fields.department.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
            <td>

              <select id="department" name="department">
                <option value="All" {if $userinfo.department eq "All" or $userinfo.department eq ""}selected="selected"{/if}>{$lng.lbl_all}</option>
                <option value="Partners" {if $userinfo.department eq "Partners"}selected="selected"{/if}>{$lng.lbl_partners}</option>
                <option value="Marketing / publicity" {if $userinfo.department eq "Marketing / publicity"}selected="selected"{/if}>{$lng.lbl_marketing_publicity}</option>
                <option value="Webdesign" {if $userinfo.department eq "Webdesign"}selected="selected"{/if}>{$lng.lbl_web_design}</option>
                <option value="Sales" {if $userinfo.department eq "Sales"}selected="selected"{/if}>{$lng.lbl_sales_department}</option>
              </select>

            </td>
          </tr>
        {/if}

        <tr>
          <td class="data-name"><label for="subject">{$lng.lbl_subject}</label></td>
          <td class="data-required">*</td>
          <td>
            <input type="text" id="subject" name="subject" size="32" maxlength="128" value="{$userinfo.subject|escape}" />
          </td>
        </tr>

        <tr>
          <td class="data-name"><label for="message_body">{$lng.lbl_message}</label></td>
          <td class="data-required">*</td>
          <td>
            <textarea cols="48" id="message_body" rows="12" name="body">{$userinfo.body}</textarea>
          </td>
        </tr>

        {include file="customer/buttons/submit.tpl" type="input" additional_button_class="main-button" assign="submit_button"}

        {if $active_modules.Image_Verification and $show_antibot.on_contact_us eq 'Y'}
          {include file="modules/Image_Verification/spambot_arrest.tpl" mode="data-table" id=$antibot_sections.on_contact_us antibot_err=$antibot_contactus_err button_code=$submit_button}
        {else}      
        <tr>
          <td colspan="2">&nbsp;</td>
          <td class="button-row">
              {$submit_button}
          </td>
        </tr>
        {/if}

      </table>

    </form>

  {/if}

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_contact_us content=$smarty.capture.dialog noborder=true}
