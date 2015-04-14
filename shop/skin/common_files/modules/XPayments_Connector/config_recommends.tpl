{*
dd04ee9e9d58e0a79554ac3b281aad57aa26814e, v7 (xcart_4_6_4), 2014-07-01 09:14:21, config_recommends.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="xpc-recommends">

{if $system_requirements_errors}

  {include file="main/subheader.tpl" title=$lng.txt_xpc_requirements_failed}

  {foreach from=$system_requirements_errors item=e}
    <table cellpadding="7" cellspacing="1">
        <tr>
          <td valign="top">
            <img src="{$ImagesDir}/icon_error_small.gif" alt="" />
          </td>
          <td>
            {$e}
          </td>
        </tr>
    </table>
  {/foreach}
{/if}

{if $xpc_recommends}

  {include file="main/subheader.tpl" title=$lng.lbl_xpc_recommendations}

  <table cellpadding="7" cellspacing="1">

    {foreach from=$xpc_recommends key=type item=recommends}

      {foreach from=$recommends key=key item=recommendation}

        <tr{cycle values=', class="TableSubHead"'}>
          <td>
            <img src="{$ImagesDir}/{if $type eq 'E'}icon_error_small.gif{else}icon_warning_small.gif{/if}" alt="" />
          </td>
          <td>
            {if $key eq "payment_methods"}

              {$lng.lbl_xpc_recommend_payment_methods}<br />
              <ul>
                {foreach from=$recommendation item=payment_module}
                  <li>{$payment_module}</li>
                {/foreach}
              </ul>

            {else}

              {$recommendation}

            {/if}
          </td>
        </tr>

      {/foreach}

    {/foreach}

  </table>

{/if}

</div>
