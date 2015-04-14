{*
5ad5b7e78e6713001699fe36faa92655bf7c02f9, v2 (xcart_4_5_2), 2012-07-05 10:16:13, service_head.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{if $is_lexity_enabled}

<script type="text/javascript">
//<![CDATA[
(function(d, w) {ldelim}
var x = d.getElementsByTagName('SCRIPT')[0];
var f = function () {ldelim}
var s = d.createElement('SCRIPT');
s.type = 'text/javascript';
s.async = true;
s.src = '//np.lexity.com/embed/{$lexity_partner_code}/{$lexity_embed_hash}?id={$lexity_merchant_id}';
x.parentNode.insertBefore(s, x);
{rdelim};
w.attachEvent ? w.attachEvent('onload', f) : w.addEventListener('load', f, false);
{rdelim}(document, window));
//]]>
</script>

{/if}
