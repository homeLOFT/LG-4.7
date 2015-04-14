{*
5307525d7b7d0f425aef4f633889acf5cf835714, v21 (xcart_4_7_0), 2015-01-13 13:19:29, address_fields.tpl, mixon

vim: set ts=2 sw=2 sts=2 et:
*}
{if $type eq 'S'}
  <div id="ship2diff_box">
{else}
  {assign var=autofocus value='autofocus'}
{/if}

{if $login ne ''}
  {include file="modules/One_Page_Checkout/profile/address_book_link.tpl" type=$type change_mode='Y' addressid="`$address.id`"}
  <input type="hidden" id="{$id_prefix}id" name="{$name_prefix}[id]" size="32" value="{$address.id|escape}" />
{/if}

<div class="address-book-content">

    <ul{if $first} class="first"{/if}>
    {assign var=use_default_addr value=$config.General.apply_default_country|replace:"N":""}

    {foreach from=$default_fields item=f key=fname}

      {if $f.avail eq 'Y' and $fname ne 'zip4'}
        {assign var=label_for value="`$id_prefix``$fname`"}
        {getvar var=liclass func=func_tpl_get_user_field_cssclass current_field=$fname default_fields=$default_fields}
        <li class="{$liclass}">

          {capture name=regfield}

            {if $fname eq 'title'}

              {include file="main/title_selector.tpl" val=$address.titleid name="`$name_prefix`[title]" id="`$id_prefix`title"}

            {elseif $fname eq 'zipcode'}

              {include file="main/zipcode.tpl" val=$address.zipcode|default_cond:$config.General.default_zipcode:$use_default_addr zip4=$address.zip4 name="`$name_prefix`[zipcode]" id="`$id_prefix`zipcode"}

            {elseif $fname eq 'state'}

              {assign var=label_for value=$name_prefix|replace:"[":"_"|replace:"]":""|cat:"_state"}
              {include file="main/states.tpl" states=$states name="`$name_prefix`[state]" default=$address.state|default:$config.General.default_state default_country=$address.country|default:$config.General.default_country country_name="`$id_prefix`country" style='class="input-style" style="width: 250px;"'}

            {elseif $fname eq 'country'}

              <select name="{$name_prefix}[country]" id="{$id_prefix}country" onchange="check_zip_code_field(this, $('#{$id_prefix}zipcode'))" class="input-style" style="width: 250px;">
                {foreach from=$countries item=c}
                  <option value="{$c.country_code}"{if $address.country eq $c.country_code or ($c.country_code eq $config.General.default_country and $address.country eq "")} selected="selected"{/if}>{$c.country|amp}</option>
                {/foreach}
              </select>

            {elseif $fname eq 'county' and $config.General.use_counties eq 'Y'}

              {include file="main/counties.tpl" counties=$counties name="`$name_prefix`[county]" default=$address.county country_name="country" id="`$id_prefix`county"}

            {elseif $fname eq 'city' and $address.$fname eq '' and $use_default_addr}

              <input type="text" id="{$id_prefix}{$fname}" name="{$name_prefix}[{$fname}]" size="32" maxlength="128" value="{$config.General.default_city|escape}" {$autofocus} />
              {assign var=autofocus value=''}

            {else}
              {* use personal firstname/lastname as default values *}
              {assign var=default_value value=''}
              {if $address.$fname eq ''}
                {if $fname eq 'firstname'}
                  {assign var=default_value value=$personal_firstname}
                {elseif $fname eq 'lastname'}
                  {assign var=default_value value=$personal_lastname}
                {/if}
              {/if}

              <input type="text" id="{$id_prefix}{$fname}" name="{$name_prefix}[{$fname}]" size="32" maxlength="255" value="{$address.$fname|default:$default_value|escape}" {$autofocus} />
              {assign var=autofocus value=''}

            {/if}
          {/capture}

          {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield required=$f.required name=$f.title field=$label_for}

        </li>

        {if $liclass eq 'fields-group last'}
          <li class="clearing"></li>
        {/if}

      {/if}
    {/foreach}

    {if $default_fields.country.avail eq 'Y' and $default_fields.state.avail eq 'Y'}
      <li style="display:none">
        {include file="main/register_states.tpl" state_name="`$name_prefix`[state]" country_name="`$id_prefix`country" county_name="`$name_prefix`[county]" state_value=$address.state|default:$config.General.default_state county_value=$address.county}
      </li>
    {/if}

    {if $additional_fields ne ''}

      {foreach from=$additional_fields item=v}

        {if $v.section eq 'B' and $v.avail eq 'Y'}

          <li class="single-field">
            {assign var=oneline value=false}
            {capture name=regfield}
              {if $v.type eq 'T'}
                <input type="text" name="additional_values[{$v.fieldid}][{$type}]" id="additional_values_{$v.fieldid}_{$type}" size="32" value="{$v.value.$type|escape}" />

              {elseif $v.type eq 'C'}
                <input type="checkbox" name="additional_values[{$v.fieldid}][{$type}]" id="additional_values_{$v.fieldid}_{$type}" value="Y"{if $v.value.$type eq 'Y'} checked="checked"{/if} />
                {assign var=oneline value=true}

              {elseif $v.type eq 'S'}
                <select name="additional_values[{$v.fieldid}][{$type}]" id="additional_values_{$v.fieldid}_{$type}">
                  {foreach from=$v.variants item=o}
                    <option value='{$o|escape}'{if $v.value.$type eq $o} selected="selected"{/if}>{$o|escape}</option>
                  {/foreach}
                </select>
                {assign var=oneline value=true}
              {/if}
            {/capture}
            {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield required=$v.required name=$v.title oneline=$oneline field="additional_values_`$v.fieldid`_`$type`"}

          </li>

        {/if}

      {/foreach}

    {/if}

    </ul>

    {if $userinfo.login eq ''}
      {include file="modules/One_Page_Checkout/address_buttons.tpl" change_mode='Y'}
    {/if}

</div>

<input id="{$id_prefix}no_address" type="hidden" name="{$name_prefix}[no_address]" value="" />

{if $type eq 'S'}
  </div>
{/if}
