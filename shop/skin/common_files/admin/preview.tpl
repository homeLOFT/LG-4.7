{*
10d4766a297b130dea8de7e1d6cd01925e213749, v2 (xcart_4_6_0), 2013-04-09 11:07:38, preview.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $template}
  {config_load file="$skin_config"}
  {if $use_default_css}
    {include file="service_css.tpl"}
  {/if}
  {include file=$template}
{/if}
