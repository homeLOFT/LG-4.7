{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, language_selector.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
{if $all_languages_cnt gt 1}
  <div class="languages {if $config.Appearance.line_language_selector eq 'Y'}languages-row{elseif $config.Appearance.line_language_selector eq 'F'}languages-flags{else}languages-select{/if}">

    {if $config.Appearance.line_language_selector eq 'Y' or $config.Appearance.line_language_selector eq 'A' or $config.Appearance.line_language_selector eq 'L'}

      {foreach from=$all_languages item=l name=languages}
        {if $config.Appearance.line_language_selector eq 'Y'}
          {assign var="lng_dspl" value=$l.code3}
        {elseif $config.Appearance.line_language_selector eq 'A'}
          {assign var="lng_dspl" value=$l.code}
        {elseif $config.Appearance.line_language_selector eq 'L'}
          {assign var="lng_dspl" value=$l.language}
        {/if} 
        {if $store_language eq $l.code}
          <strong class="language-code lng-{$l.code}">{$lng_dspl|default:$l.language}</strong>
        {else}
          <a href="home.php?sl={$l.code}" class="language-code lng-{$l.code}">{$lng_dspl|default:$l.language}</a>
        {/if}
        {if not $smarty.foreach.languages.last}|{/if}
      {/foreach}

    {elseif $config.Appearance.line_language_selector eq 'F'}

      <ul>
      {foreach from=$all_languages item=l name=languages}
        {if $store_language eq $l.code}
          <li class="language-code lng-{$l.code} current"><span class="lng"><img src="{if not $l.is_url}{$current_location}{/if}{$l.tmbn_url|amp}" alt="" width="{$l.image_x}" height="{$l.image_y}" /> <span class="arrow-down">{$l.code}</span></span></li>
        {/if}
      {/foreach}
      {foreach from=$all_languages item=l name=languages}
        {if $store_language neq $l.code}
          <li class="language-code lng-{$l.code} not-current"><a href="home.php?sl={$l.code}"><img src="{if not $l.is_url}{$current_location}{/if}{$l.tmbn_url|amp}" alt="" width="{$l.image_x}" height="{$l.image_y}" /> <span>{$l.code}</span></a></li>
        {/if}
      {/foreach}
      </ul>

    {else}

      <form action="home.php" method="get" name="sl_form">
        <input type="hidden" name="redirect" value="{$smarty.server.PHP_SELF|escape}{if $php_url.query_string}?{$php_url.query_string|escape}{/if}" />

        {strip}
          <label>{$lng.lbl_select_language}:
          <select name="sl" onchange="javascript: this.form.submit();">
            {foreach from=$all_languages item=l}
              <option value="{$l.code}"{if $store_language eq $l.code} selected="selected"{/if}>{$l.language}</option>
            {/foreach}
          </select>
          </label>
        {/strip}

      </form>

    {/if}

  </div>
{/if}
