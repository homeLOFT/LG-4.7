{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, affiliate_categories.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}

{if $categories}

  <ul class="xaff-categories xaff-cat-{$level}"{if $level gt 0} style="display: none;"{/if}>
    {foreach from=$categories item=c}

      <li>
        <span>{strip}
          {if $c.childs}
            [<a href="partner_banners.php?bannerid={$banner.bannerid}&amp;get=1&amp;categoryid={$c.categoryid}" onclick="javascript: return xaffCExpand(this);" class="expand">+</a>]
          {else}
            &nbsp;&nbsp;&nbsp;
          {/if}
        {/strip}</span>
        <a href="partner_banners.php?bannerid={$banner.bannerid}&amp;get=1&amp;categoryid={$c.categoryid}">{$c.category}</a>

        {if $c.childs}
          {include file="main/affiliate_categories.tpl" categories=$c.childs level=$level+1}
        {/if}

      </li>

    {/foreach}
  </ul>

{/if}
