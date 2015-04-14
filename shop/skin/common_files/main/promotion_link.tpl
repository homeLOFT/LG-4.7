{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, promotion_link.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<div class="promotion-cell">
  <div class="promotion-link">
  	<a title="{$title|escape:"html"}" href="{$href|amp}">{$title}</a>
  </div>
  {if $promo_note ne ""}
    <div class="promo-note">
      {$promo_note}
    </div>
  {/if}
</div>
