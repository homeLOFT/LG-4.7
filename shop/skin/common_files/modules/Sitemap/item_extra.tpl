{*
e97937aa02e4d5945b9fac4e9554385dcfa96672, v5 (xcart_4_4_2), 2010-11-15 07:00:42, item_extra.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
      {foreach from=$items item="item" key="num"}
	    <li><a href="{$item.url}" title="{$item.name|escape}">{$item.name|amp}</a></li>
      {/foreach}
