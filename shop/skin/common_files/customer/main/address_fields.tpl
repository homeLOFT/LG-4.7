{*
6f2153b66bce77b318eb5c04a7da8de70a3dd500, v19 (xcart_4_7_0), 2015-01-05 23:06:12, address_fields.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
  {assign var=use_default_addr value=$config.General.apply_default_country|replace:"N":""}
  {assign var=autofocus value='autofocus'}
  {if $default_fields.title.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}title">{$lng.lbl_title}</label></td>
      <td{if $default_fields.title.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        {include file="main/title_selector.tpl" val=$address.titleid id="`$id_prefix`title" name="`$name_prefix`[title]"}
      </td>
    </tr>
  {/if}

  {if $default_fields.firstname.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}firstname">{$lng.lbl_first_name}</label></td>
      <td{if $default_fields.firstname.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        <input type="text" id="{$id_prefix}firstname" name="{$name_prefix}[firstname]" size="32" maxlength="128" value="{$address.firstname|default:$personal_firstname|escape}" {$autofocus} />
        {assign var=autofocus value=''}
      </td>
    </tr>
  {/if}

  {if $default_fields.lastname.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}lastname">{$lng.lbl_last_name}</label></td>
      <td{if $default_fields.lastname.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        <input type="text" id="{$id_prefix}lastname" name="{$name_prefix}[lastname]" size="32" maxlength="128" value="{$address.lastname|default:$personal_lastname|escape}" {$autofocus} />
        {assign var=autofocus value=''}
      </td>
    </tr>
  {/if}

  {if $default_fields.address.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}address">{$lng.lbl_address}</label></td>
      <td{if $default_fields.address.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        <input type="text" id="{$id_prefix}address" name="{$name_prefix}[address]" size="32" maxlength="255" value="{$address.address|escape}" {$autofocus} />
        {assign var=autofocus value=''}
      </td>
    </tr>
  {/if}

  {if $default_fields.address_2.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}address_2">{$lng.lbl_address_2}</label></td>
      <td{if $default_fields.address_2.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        <input type="text" id="{$id_prefix}address_2" name="{$name_prefix}[address_2]" size="32" maxlength="128" value="{$address.address_2|escape}" />
      </td>
    </tr>
  {/if}

  {if $default_fields.city.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}city">{$lng.lbl_city}</label></td>
      <td{if $default_fields.city.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        <input type="text" id="{$id_prefix}city" name="{$name_prefix}[city]" size="32" maxlength="64" value="{$address.city|default_cond:$config.General.default_city:$use_default_addr|escape}" {$autofocus} />
        {assign var=autofocus value=''}
      </td>
    </tr>
  {/if}

  {if $default_fields.country.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}country">{$lng.lbl_country}</label></td>
      <td{if $default_fields.country.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        <select name="{$name_prefix}[country]" id="{$id_prefix}country" onchange="check_zip_code_field(this, $('#{$id_prefix}zipcode').get(0))">
          {foreach from=$countries item=c}
            <option value="{$c.country_code}"{if $address.country eq $c.country_code or ($c.country_code eq $config.General.default_country and $address.country eq "")} selected="selected"{/if}>{$c.country|amp}</option>
          {/foreach}
        </select>
      </td>
    </tr>
  {/if}

  {if $default_fields.state.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}state">{$lng.lbl_state}</label></td>
      <td{if $default_fields.state.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        {include file="main/states.tpl" states=$states name="`$name_prefix`[state]" default=$address.state|default:$config.General.default_state default_country=$address.country|default:$config.General.default_country id="`$id_prefix`state" country_name="`$id_prefix`country"}
      </td>
    </tr>
  {/if}

  {if $default_fields.county.avail eq 'Y' and $config.General.use_counties eq "Y"}
    <tr>
      <td class="data-name"><label for="{$id_prefix}county">{$lng.lbl_county}</label></td>
      <td{if $default_fields.county.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        {include file="main/counties.tpl" counties=$counties name="`$name_prefix`[county]" id="`$id_prefix`county" default=$address.county country_name="`$id_prefix`country"}
      </td>
    </tr>
  {/if}

  {if $default_fields.state.avail eq 'Y' and $default_fields.country.avail eq 'Y'}
    <tr style="display: none;">
      <td{if $default_fields.state.required eq 'Y'} class="data-required"{/if}>
        {include file="main/register_states.tpl" state_name="`$name_prefix`[state]" country_name="`$id_prefix`country" county_name="`$name_prefix`[county]" state_value=$address.state|default:$config.General.default_state county_value=$address.county}
      </td>
    </tr>
  {/if}

  {if $default_fields.zipcode.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}zipcode">{$lng.lbl_zip_code}</label></td>
      <td{if $default_fields.zipcode.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        {include file="main/zipcode.tpl" zip_section=$zip_section name="`$name_prefix`[zipcode]" id="`$id_prefix`zipcode" val=$address.zipcode|default_cond:$config.General.default_zipcode:$use_default_addr zip4=$address.zip4}
      </td>
    </tr>
  {/if}

  {if $default_fields.phone.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}phone">{$lng.lbl_phone}</label></td>
      <td{if $default_fields.phone.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        <input type="text" id="{$id_prefix}phone" name="{$name_prefix}[phone]" size="32" maxlength="32" value="{$address.phone|escape}" />
      </td>
    </tr>
  {/if}

  {if $default_fields.fax.avail eq 'Y'}
    <tr>
      <td class="data-name"><label for="{$id_prefix}fax">{$lng.lbl_fax}</label></td>
      <td{if $default_fields.fax.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
      <td>
        <input type="text" id="{$id_prefix}fax" name="{$name_prefix}[fax]" size="32" maxlength="32" value="{$address.fax|escape}" />
      </td>
    </tr>
  {/if}

  {if $additional_fields ne ''}

    {foreach from=$additional_fields item=v}

      {if $v.section eq 'B' and $v.avail eq 'Y'}

        <tr>
          <td class="data-name"><label for="additional_values{if $address_type ne ''}_{$address_type}{/if}_{$v.fieldid}">{$v.title}</label></td>
          <td{if $v.required eq 'Y'} class="data-required">*{else}>&nbsp;{/if}</td>
          <td>
            {if $v.type eq 'T'}
              <input type="text" name="additional_values[{$v.fieldid}]{if $address_type ne ''}[{$address_type}]{/if}" id="additional_values{if $address_type ne ''}_{$address_type}{/if}_{$v.fieldid}" size="32" value="{if $address_type ne ''}{$v.value.$address_type|escape}{else}{$v.value|escape}{/if}" />

            {elseif $v.type eq 'C'}
              <input type="checkbox" name="additional_values[{$v.fieldid}]{if $address_type ne ''}[{$address_type}]{/if}" id="additional_values{if $address_type ne ''}_{$address_type}{/if}_{$v.fieldid}" value="Y"{if ($address_type ne '' and $v.value.$address_type eq 'Y') or ($v.value eq 'Y')} checked="checked"{/if} />

            {elseif $v.type eq 'S'}
              <select name="additional_values[{$v.fieldid}]{if $address_type ne ''}[{$address_type}]{/if}" id="additional_values{if $address_type ne ''}_{$address_type}{/if}_{$v.fieldid}">
                {foreach from=$v.variants item=o}
                  <option value='{$o|escape}'{if ($address_type ne '' and $v.value.$address_type eq $o) or ($v.value eq $o)} selected="selected"{/if}>{$o|escape}</option>
                {/foreach}
              </select>

            {/if}
          </td>
        </tr>

      {/if}

    {/foreach}

  {/if}


  {if $update_address_book eq 'Y' and $login ne ''}
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
      <td colspan='2'>
        {include file="modules/One_Page_Checkout/profile/address_book_link.tpl" type=$address_type change_mode='Y' addressid="`$address.id`" hide_address_book_link='Y'}
        <input type="hidden" id="{$id_prefix}id" name="{$name_prefix}[id]" size="32" maxlength="32" value="{$address.id|escape}" />
      </td>
    </tr>
  {/if}
