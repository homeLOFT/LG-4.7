{*
9801da6f17425f50e8047475e08712045e9818fd, v2 (xcart_4_6_1), 2013-08-29 12:26:36, news_messages.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $action eq "add" or $action eq "modify"}
  {include file="modules/News_Management/news_messages_modify.tpl"}
{else}
  {include file="modules/News_Management/news_messages_list.tpl"}
{/if}
