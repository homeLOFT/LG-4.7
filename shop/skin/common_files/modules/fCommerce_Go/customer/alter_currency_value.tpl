{*
0d0465915bddae9202730b9dcd479c6724c60147, v1 (xcart_4_5_5), 2013-01-11 13:17:36, alter_currency_value.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{strip}

  {if $alter_currency_value eq ""}
    {assign var="alter_currency_value" value="0"}
  {/if}

  {if $config.General.alter_currency_symbol ne ""}

    {if $config.version gte 4.2}
      (
    {/if}

    {if $plain_text_message eq ""}
      <span class="nowrap">
      {/if}

      {math equation="altercurrencyvalue*rate" altercurrencyvalue=$alter_currency_value rate=$config.General.alter_currency_rate assign="cf_value"}

      {if $display_sign}

        {if $cf_value gte 0}
          +
        {else}
          -
        {/if}

      {/if}

      {assign var="cf_value" value=$cf_value|abs_value|formatprice}

      {if $config.version lt 4.2}
        {assign var="cf_value" value=$config.General.alter_currency_format|replace:"x":$cf_value|replace:"$":$config.General.alter_currency_symbol}

        {assign var="cf_value" value="(`$cf_value`)"}
      {/if}

      {if $tag_id ne "" and $plain_text_message eq ""}
        {assign var="cf_value" value="<span id=\"`$tag_id`\">`$cf_value`</span>"}
      {/if}

      {if $config.version lt 4.2}
        {$cf_value}
      {else}
        {$config.General.alter_currency_format|replace:"x":$cf_value|replace:"$":$config.General.alter_currency_symbol}
      {/if}

      {if $plain_text_message eq ""}
      </span>
    {/if}

    {if $config.version gte 4.2}
      )
    {/if}

  {/if}

{/strip}
