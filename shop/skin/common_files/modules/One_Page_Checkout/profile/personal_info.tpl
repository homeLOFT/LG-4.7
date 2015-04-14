{*
b78bdf1df5a3ef546258fb1be768abfcd7ed12b3, v8 (xcart_4_6_6), 2014-11-24 18:46:39, personal_info.tpl, mixon 
vim: set ts=2 sw=2 sts=2 et:
*}
{if $is_areas.P eq 'Y'}

  {if not $hide_header}
    <h3>{$lng.lbl_personal_information}</h3>
  {/if}

  <ul{if $first} class="first"{/if}>
  {foreach from=$default_fields item=f key=fname}

    {if $f.avail eq 'Y'}
      {getvar var=liclass func=func_tpl_get_user_field_cssclass current_field=$fname default_fields=$default_fields}
      <li class="{$liclass}">

        {capture name=regfield}
          {if $fname eq "tax_number" and $userinfo.tax_exempt eq "Y" and $config.Taxes.allow_user_modify_tax_number ne "Y"}
              {assign var="fstate" value="disabled"}
          {else}
              {assign var="fstate" value=""}
          {/if}
          {if $fname eq "title"}
            {include file="main/title_selector.tpl" val=$f.titleid}
          {else}
            <input type="text" name="{$fname}" id="{$fname}" value="{$userinfo[$fname]|escape}" {if $fstate eq "disabled"}disabled="disabled"{/if} />
          {/if}
        {/capture}
        {include file="modules/One_Page_Checkout/opc_form_field.tpl" content=$smarty.capture.regfield required=$f.required name=$f.title field=$fname fstate=$fstate}

      </li>

    {if $liclass eq 'fields-group last'}
      <li class="clearing"></li>
    {/if}

    {/if}
  {/foreach}
  </ul>

  {include file="modules/One_Page_Checkout/profile/additional_info.tpl" section="P"}
{/if}
