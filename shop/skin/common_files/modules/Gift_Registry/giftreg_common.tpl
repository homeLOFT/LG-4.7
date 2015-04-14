{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, giftreg_common.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $message ne ""}
{include file="modules/Gift_Registry/giftreg_message.tpl"}
{/if}

{if $main_mode eq "manager"}

{include file="modules/Gift_Registry/events_list.tpl"}

{if $mode eq "maillist"}
{include file="modules/Gift_Registry/maillist.tpl"}

{elseif $mode eq "products"}
{include file="modules/Gift_Registry/products.tpl"}

{elseif $mode eq "send"}
{include file="modules/Gift_Registry/event_send.tpl"}

{elseif $mode eq "gb"}
{include file="modules/Gift_Registry/event_guestbook.tpl"}

{elseif $mode eq "modify"}
{include file="modules/Gift_Registry/event_modify.tpl"}

{/if}

{elseif $mode eq "event_details"}
{include file="modules/Gift_Registry/event_details_customer.tpl"}

{else}
{include file="modules/Gift_Registry/giftreg_search.tpl"}

{/if}
