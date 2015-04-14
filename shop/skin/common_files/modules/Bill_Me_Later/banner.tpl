{*
68007a143c54027751c407c427631d654255b1a6, v4 (xcart_4_6_2), 2013-10-30 09:09:54, banner.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
{if $bml_page eq 'home'}
  {assign var='bml_location' value=$config.Bill_Me_Later.bml_banner_on_home}
{elseif $bml_page eq 'category'}
  {assign var='bml_location' value=$config.Bill_Me_Later.bml_banner_on_category}
{elseif $bml_page eq 'product'}
  {assign var='bml_location' value=$config.Bill_Me_Later.bml_banner_on_product}
{elseif $bml_page eq 'cart'}
  {assign var='bml_location' value=$config.Bill_Me_Later.bml_banner_on_cart}
{/if}
{if $bml_location and $bml_location ne 'disabled'}
{include file="modules/Bill_Me_Later/placement_types.tpl"}
<div class="paypal-bml-banner {$bml_page} {$bml_location}">
<script type="text/javascript" data-pp-pubid="{$config.paypal_bml_publisherid}" data-pp-placementtype="{$smarty.capture.bml_placementtype}">
//<![CDATA[
{literal}
(function (d, t) {
"use strict";
var s = d.getElementsByTagName(t)[0], n = d.createElement(t);
n.src = "//paypal.adtag.where.com/merchant.js";
s.parentNode.insertBefore(n, s);
}(document, "script"));
{/literal}
//]]>
</script>
</div>
{/if}
