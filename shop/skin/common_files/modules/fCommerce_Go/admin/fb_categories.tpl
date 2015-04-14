{* 0d0465915bddae9202730b9dcd479c6724c60147, v1 (xcart_4_5_5), 2013-01-11 13:17:36, fb_categories.tpl, random *}
<ul class="fb-shop-categories-list level-{$level}">
  {foreach from=$categories item=c}
    <li class="fb-shop-category-item{if $level gt 0} {cycle values="TableSubHead,white-bg"}{/if}{if $c.childs && $fb_shop_config.expanded_category[$c.categoryid] eq 'Y'} opened{/if}"{if $c.avail neq 'Y'} title="{$lng.txt_category_disabled}"{/if}>
      {if $c.childs}
        <span class="expand-collapse{if $fb_shop_config.expanded_category[$c.categoryid] neq 'Y'} closed{/if}"></span>
        <input type="hidden" class="expanded-category" name="fb_shop_config[expanded_category][{$c.categoryid}]" value="{$fb_shop_config.expanded_category[$c.categoryid]}" />
      {/if}
      <label><input type="checkbox" class="level-{$level}" name="fb_shop_config[categories_menu][]" value="{$c.categoryid}"{if ($fb_shop_config.categories_menu && in_array($c.categoryid, $fb_shop_config.categories_menu)) || !$fb_shop_config.categories_menu} checked="checked"{/if} {if $c.avail neq 'Y'} disabled="disabled"{/if}/><span class="{if $c.avail neq 'Y'} disabled{/if}{if $level eq 0} root-category-title{/if}">{$c.category}</span></label>
      {if $c.childs}
        {include file="modules/fCommerce_Go/admin/fb_categories.tpl" level=$level+1 categories=$c.childs roots="`$roots`,`$c.categoryid`"}
      {/if}
    </li>
  {/foreach}
</ul>
