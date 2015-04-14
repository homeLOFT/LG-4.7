{*
d46af178036e5a4f3ca2bf3eaaaacef0b4db60fe, v10 (xcart_4_6_2), 2013-12-26 12:49:08, ui_tabs.tpl, aim 
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript">
//<![CDATA[
$(function() {ldelim}
  var default_tab = '{$default_tab|default:0}';
  var _storage_key_base = '{$prefix|default:"ui-tabs-"}';
  var _storage_key = _storage_key_base + xcart_web_dir;
  {literal}

  if (
    isLocalStorageSupported()
    && default_tab == '-1last_used_tab'
  ) {
    // Take into account EU cookie law
    var _used_storage = ('function' != typeof window.func_is_allowed_cookie || func_is_allowed_cookie(_storage_key_base)) ? localStorage : sessionStorage;
    var tOpts = {
      activate : function( event, ui ) {
          _used_storage[_storage_key] = ui.newTab.index();
      }
    };
    default_tab = parseInt(_used_storage[_storage_key]) || 0;
  } else {
    var tOpts = {};
    default_tab = parseInt(default_tab) || 0;
  }

  // Allow choose active tab by adding hash in URL, do not set 'active' in this way
  if (window.location.hash == '') {
    tOpts.active = default_tab;
  }

  {/literal}

  $('#{$prefix}container').tabs(tOpts);
{rdelim});
//]]>
</script>

<div id="{$prefix}container">

  <ul>
  {foreach from=$tabs item=tab key=ind}
    {inc value=$ind assign="ti"}
    <li><a href="{if $tab.url}{$tab.url|amp}{else}#{$prefix}{$tab.anchor|default:$ti}{/if}">{$tab.title|wm_remove|escape}</a></li>
  {/foreach}
  </ul>

  {foreach from=$tabs item=tab key=ind}
    {if $tab.tpl}
      {inc value=$ind assign="ti"}
      <div id="{$prefix}{$tab.anchor|default:$ti}">
        {include file=$tab.tpl nodialog=true}
      </div>
    {/if}
  {/foreach}

</div>
