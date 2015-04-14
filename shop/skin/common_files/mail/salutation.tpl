{* 850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, salutation.tpl, joy *}
{strip}
{if $salutation ne ""}
   {$lng.eml_dear|substitute:"customer":$salutation}
{else}
   {if $firstname eq "" and $lastname eq ""}
       {$lng.eml_dear_customer}
   {else}
       {$lng.eml_dear|substitute:"customer":"`$title` `$firstname` `$lastname`"}
   {/if}
{/if}
{/strip},
