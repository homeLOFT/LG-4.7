{*
f9e8d26ef6e73fd8048f2466be61b96fcff2cb7e, v2 (xcart_4_5_2), 2012-07-10 05:59:34, xcart_paid_modules.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

{if $paid_modules ne ''}
<div>
<ul>
{foreach from=$paid_modules item=module}
<li>
<h2><a href='{$module.page|default:$config.rss_xcart_paid_default_url}' target='_blank'><img src='{$module.image}' height="100" alt="{$module.name|escape}" /></a></h2>
<a href='{$module.page|default:$config.rss_xcart_paid_default_url}' target='_blank'>{$module.name|escape}</a>
<p style="text-align:left">{$module.desc}</p>
</li>
{/foreach}
</ul>
</div>
{/if}
