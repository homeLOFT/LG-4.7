{*
c1b4b906f1c2e01484f55ea4426fd37f6239852c, v5 (xcart_4_6_0), 2013-05-31 16:14:19, np_products.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $next_product or $prev_product}
{strip}
<div class="np-products">
<ul>

{if $prev_product}
	<li>
    <a href="product.php?productid={$prev_product.productid}&amp;cat={$cat}" class="prev"><span class="arrow">&larr;</span>&nbsp;{$lng.lbl_previous_product}</a>{if $next_product}<span class="sep"></span>{/if}
    <div class="popup" id="np-popup-prev"><img src="{$ImagesDir}/loading.gif" alt="" /></div>
  </li>
{/if}

{if $next_product}
  <li class="last">
    <a href="product.php?productid={$next_product.productid}&amp;cat={$cat}" class="next">{$lng.lbl_next_product}&nbsp;<span class="arrow">&rarr;</span></a>
    <div class="popup" id="np-popup-next"><img src="{$ImagesDir}/loading.gif" alt="" /></div>
</li>
{/if}

</ul>
</div>
{/strip}
{/if}
<script type="text/javascript">
//<![CDATA[
  var npProducts = [];
  {if $prev_product}
    npProducts['prev'] = [];
    npProducts['prev']['id'] = {$prev_product.productid};
    npProducts['prev']['loaded'] = false;
  {/if}
  {if $next_product}
    npProducts['next'] = [];
    npProducts['next']['id'] = {$next_product.productid};
    npProducts['next']['loaded'] = false;
  {/if}
//]]>
</script>
{load_defer file="js/np_products.js" type="js"}
