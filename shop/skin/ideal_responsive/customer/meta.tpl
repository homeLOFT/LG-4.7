{*
4eb1aaf1d647943990792fe0ae233b77c6b06383, v2 (xcart_4_6_1), 2013-06-27 17:47:34, meta.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
  <meta http-equiv="Content-Type" content="text/html; charset={$default_charset|default:"utf-8"}" />
  <meta http-equiv="X-UA-Compatible" content="{$smarty.config.XUACompatible}" />
  <meta http-equiv="Content-Script-Type" content="text/javascript" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta http-equiv="Content-Language" content="{$shop_language}" />
{if $printable}
  <meta name="ROBOTS" content="NOINDEX,NOFOLLOW" />
{else}
  {meta type='description' page_type=$meta_page_type page_id=$meta_page_id}
  {meta type='keywords' page_type=$meta_page_type page_id=$meta_page_id}
{/if}
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
