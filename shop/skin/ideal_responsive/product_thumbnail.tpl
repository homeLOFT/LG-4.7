{*
8ec8b858917c921a726e2197071ce74d020e5ead, v1 (xcart_4_6_0), 2013-04-02 16:43:59, product_thumbnail.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{strip}
<img
{if $id ne ''} id="{$id}"{/if} src="
{if $tmbn_url}
{$tmbn_url|amp}
{else}
{if $full_url}
{$current_location}
{else}
{$xcart_web_dir}
{/if}
/image.php?type={$type|default:"T"}&amp;id={$productid}
{/if}
"
{if $image_x ne 0} width="{$image_x}"{/if}
{if $image_y ne 0} height="{$image_y}"{/if} alt="{$product|escape}" title="{$product|escape}" />
{/strip}
