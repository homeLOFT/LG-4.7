{*
e1db5cf03524aef7b3d94390d4b4baa6311fd42b, v4 (xcart_4_5_5), 2013-02-07 17:35:38, item_categories.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
      {foreach from=$items item="item" key="num"}
	    {include file="modules/Sitemap/item_categories_recurs.tpl" item=$item}
      {/foreach}
