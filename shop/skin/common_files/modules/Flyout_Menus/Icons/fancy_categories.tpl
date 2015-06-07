{*
7b79378dac39e23466f777ebd246768ed6458635, v6 (xcart_4_7_2), 2015-04-16 16:16:33, fancy_categories.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $fc_skin_path}

  {load_defer file="`$fc_skin_path`/func.js" type="js"}
  <div id="{$fancy_cat_prefix}rootmenu" class="fancycat-icons-scheme {if $config.Flyout_Menus.icons_mode eq 'C'}fancycat-icons-c{else}fancycat-icons-e{/if}">
    {if $fancy_use_cache}
      {if $fancy_use_ajax}
        <div id="{$fancy_cat_prefix}sub_rootmenu"></div>
        <script type="text/javascript">
        //<![CDATA[
          $.get("{fancycat_get_cache}", function(data) {
            $("#{$fancy_cat_prefix}sub_rootmenu").html(data);
          });
        //]]>
        </script>
      {else}
        {fancycat_get_cache}
      {/if}

    {elseif $config.Flyout_Menus.icons_mode eq 'C'}
      {include file="`$fc_skin_path`/fancy_subcategories_exp.tpl" level=0}

    {else}
      {include file="`$fc_skin_path`/fancy_subcategories.tpl" level=0}
    {/if}
    {if $catexp}
<script type="text/javascript">
//<![CDATA[
var catexp = {$catexp|default:0};
//]]>
</script>
    {/if}
    <div class="clearing"></div>
  </div>
{/if}
