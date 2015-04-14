{*
0d0465915bddae9202730b9dcd479c6724c60147, v1 (xcart_4_5_5), 2013-01-11 13:17:36, currency.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{strip}

  {if $plain_text_message eq ""}
    <span class="currency">
    {/if}

    {if $display_sign}

      {if $value gte 0}
        +
      {else}
        -
      {/if}

    {/if}

    {assign var="cf_value" value=$value|abs_value|formatprice}

    {if $config.version lt 4.2}
      {assign var="cf_value" value=$config.General.currency_format|replace:"x":$cf_value|replace:"$":$config.General.currency_symbol}
    {/if}

    {if $tag_id ne "" and $plain_text_message eq ""}
      {assign var="cf_value" value="<span id=\"`$tag_id`\">`$cf_value`</span>"}
    {/if}

    {if $config.version lt 4.2}
      {$cf_value}
    {else}
      {$config.General.currency_format|replace:"x":$cf_value|replace:"$":$config.General.currency_symbol}
    {/if}


    {if $plain_text_message eq ""}
    </span>
  {/if}

{/strip}
