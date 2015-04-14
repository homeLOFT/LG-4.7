{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, wishlists_pcounts.tpl, joy 
vim: set ts=2 sw=2 sts=2 et:
*}
<br /><br />
<strong>{$lng.lbl_gift_registry}</strong>:
<ul class="wishlists-events">
{foreach from=$pcounts item=count key=eventid}
{if $eventid gt 0}
<li>{$events[$eventid].title}: <a href="wishlists.php?mode=wishlist&amp;customer={$v.userid}&amp;eventid={$eventid}">{$lng.lbl_n_items|substitute:"items":$count}</a></li>
{/if}
{/foreach}
</ul>
