{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, meta.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
  <meta http-equiv="Content-Type" content="text/html; charset={$default_charset|default:"utf-8"}" />
  <meta http-equiv="X-UA-Compatible" content="{$smarty.config.XUACompatible}" />
  <meta http-equiv="Content-Script-Type" content="text/javascript" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta http-equiv="Content-Language" content="{$shop_language}" />
{if $main eq "product"}
  {assign var="prod_descr" value=$product.descr|default:$product.fulldescr}
  <meta property="og:type" content="product" />	
  <meta property="og:title" content="{$product.product|escape}" />
  <meta property="og:description" content="{$prod_descr}" />
  <meta property="og:image" content="{$product.image_url}" />
  <meta property="product:price:amount" content="{$product.price}" />
  <meta property="product:price:currency" content="USD" />
{elseif $main eq "catalog" and $cat ne "0"}
  {assign var="cat_descr" value=$current_category.meta_description}
  <meta property="og:type" content="website" />
  <meta property="og:description" content="{$cat_descr}" />
  <meta property="og:title" content="{$meta_title}" />
  <meta property="og:image" content="https://{$smarty.server.HTTP_HOST}/{$current_category.image_path}" />
{/if}
{if $printable}
  <meta name="ROBOTS" content="NOINDEX,NOFOLLOW" />
{else}
  {meta type='description' page_type=$meta_page_type page_id=$meta_page_id}
  {meta type='keywords' page_type=$meta_page_type page_id=$meta_page_id}
{/if}
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
