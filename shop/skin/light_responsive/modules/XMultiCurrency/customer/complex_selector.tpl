{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, complex_selector.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

<script type="text/javascript">
//<![CDATA[

var lng_mc_selector_title = '{$lng.mc_lbl_selector_title}';
var lng_thumbnails = [
{foreach from=$all_languages item=l}
  ['{$l.code}', '{if not $l.is_url}{$current_location}{/if}{$l.tmbn_url}'],
{/foreach}
  ['empty', '']
];

var mc_countries = [
{foreach from=$mc_all_countries item=cnt}
{if not $cnt.excluded}
['{$cnt.country_code}', '{$cnt.currency_code}', '{$cnt.language_code}'],
{/if}
{/foreach}
];
{literal}

function toggleSelectorDlg(base, linesCount)
{
  $('#mc-selector-language-block-menu').hide();
  x = $(base).offset().left;
  y = $(base).offset().top;
  var at = 'left+' +x + ' top+'+y;
  _minHeight = 55 + linesCount * 52;
  var is_mobile = ($(window).width() < 768);
  $('#mc_selector').dialog({ title: lng_mc_selector_title, position: {my : 'left top', at : at}, width: (is_mobile) ? 310 : 550, maxWidth: 550, minWidth: (is_mobile) ? 290 : 530, minHeight: _minHeight, draggable: false, modal: true});
}

function getLngThumbnail(code)
{
  for (var i = 0; i < lng_thumbnails.length; i++) {
    if (lng_thumbnails[i][0] == code) {
      return lng_thumbnails[i][1];
    }
  }
  return '';
}

function setCurrencyByCountry(country_code)
{
  var currency_code = '';

  for (var i = 0; i < mc_countries.length; i++) {
    if (mc_countries[i][0] == country_code) {
      currency_code = mc_countries[i][1];
      break;
    }
  }

  if (currency_code != '') {
    $('#mc_currency option[value="' + currency_code + '"]').attr('selected', 'selected');
  }
}

function setLanguageByCountry(country_code)
{
  var language_code = '';

  for (var i = 0; i < mc_countries.length; i++) {
    if (mc_countries[i][0] == country_code) {
      language_code = mc_countries[i][2];
      break;
    }
  }

  if (language_code != '') {
    $('#mc-selected-language').val(language_code);
    $('#mc-selector-language-current').css('backgroundImage', $('#mc-selector-language-block-menu-item-' + language_code).css('backgroundImage'));
    $('#mc-selector-language-current').html($('#mc-selector-language-block-menu-item-' + language_code).html());
  }
}


{/literal}

//]]>
</script>

{foreach from=$all_languages item=l name=languages}
  {if $store_language eq $l.code}
    {if $config.Appearance.line_language_selector eq 'Y'}
      {assign var="cur_lng_dspl" value=$l.code3}
    {elseif $config.Appearance.line_language_selector eq 'A'}
      {assign var="cur_lng_dspl" value=$l.code}
    {elseif $config.Appearance.line_language_selector eq 'L'}
      {assign var="cur_lng_dspl" value=$l.language}
    {/if}
    {assign var="curlng" value=$l}
  {/if}
{/foreach}

{assign var="mc_selector_lines" value=0}
{if $smarty.foreach.languages.total gt 1}
  {math assign="mc_selector_lines" equation="x+1" x=$mc_selector_lines}
{/if}
{if $mc_allow_currency_selection}
  {math assign="mc_selector_lines" equation="x+1" x=$mc_selector_lines}
{/if}
{if $config.mc_allow_select_country eq "Y"}
  {math assign="mc_selector_lines" equation="x+1" x=$mc_selector_lines}
{/if}

<div class="languages mc-selector-menu-block" id="mc-selector-menu" {if $smarty.foreach.languages.total gt 1 or $mc_allow_currency_selection}onclick="javascript: toggleSelectorDlg(this, {$mc_selector_lines})"{else}style="cursor: auto;" {/if}>

  <div class="mc-selector-menu-item">

  {if $config.Appearance.line_language_selector eq 'F'}
    <img src="{if not $curlng.is_url}{$current_location}{/if}{$curlng.tmbn_url|amp}" alt="{$curlng.language|escape}" width="{$curlng.image_x}" height="{$curlng.image_y}" title="{$lng.lbl_language|escape}: {$curlng.language|escape}" />

  {else}
    <strong class="language-code lng-{$store_language}" title="{$lng.lbl_language|escape}: {$curlng.language|escape}">'{$cur_lng_dspl}'</strong>
  {/if}

  </div>

  <div class="mc-selector-menu-item" title="{$lng.mc_lbl_currency|escape}: {$store_currency_data.name|escape}">{$store_currency}{if $store_currency_data.symbol ne "" and $store_currency ne $store_currency_data.symbol} ({$store_currency_data.symbol}){/if}</div>

  {if $config.mc_allow_select_country eq "Y"}
  {assign var="store_country_name" value="country_`$store_country`"}
  <div class="mc-selector-menu-item" title="{$lng.lbl_country|escape}: {$lng.$store_country_name|escape}">{$lng.$store_country_name}</div>
  {/if}

</div>

<div class="mc-selector-popup-block" id="mc_selector">

  <form action="home.php" method="get" id="mcselectorform">

  <ul class="mc-selector-block">

  {* Language selector *}

  {if $smarty.foreach.languages.total gt 1}

  <li class="mc-selector-row mc-selector-language-row">
    <input type="hidden" name="sl" value="{$store_language}" id="mc-selected-language" />
    <div class="mc-selector-label">{$lng.lbl_select_language}:</div>

    {capture name="curlangdlg"}
    {foreach from=$all_languages item=l}
    {if $store_language eq $l.code}{assign var="mc_current_language" value=$l.language}{/if}
    <div class="mc-selector-language-block-menu-item" id="mc-selector-language-block-menu-item-{$l.code}" style="background-image: url({if not $l.is_url}{$current_location}{/if}{$l.tmbn_url|amp});" onclick="javascript: $('#mc-selected-language').val('{$l.code}'); $('#mc-selector-language-current').css('backgroundImage', $(this).css('backgroundImage')); $('#mc-selector-language-current').html($(this).html()); $('#mc-selector-language-block-menu').hide();">{$l.language}</div>
    {/foreach}
    {/capture}

    <span class="mc-selector-language-current" id="mc-selector-language-current" style="background-image: url({if not $curlng.is_url}{$current_location}{/if}{$curlng.tmbn_url|amp});" onclick="javascript: $('#mc-selector-language-block-menu').css('left', $(this).position().left); $('#mc-selector-language-block-menu').toggle();">{$mc_current_language}</span>

    <div class="mc-selector-language-block-menu" id="mc-selector-language-block-menu">
      {$smarty.capture.curlangdlg}
    </div>

  </li>

  {/if}

  {* Currency selector *}

  {if $mc_allow_currency_selection}

  <li class="mc-selector-row">

    {if $smarty.foreach.languages.total gt 1}
    <div class="mc-selector-row-separator"></div>
    {/if}

    <div class="mc-selector-label">{$lng.mc_lbl_select_surrency}:</div>
    <select name="mc_currency" id="mc_currency">
      {foreach from=$mc_all_currencies item=cur}
      <option value="{$cur.code}"{if $store_currency eq $cur.code} selected="selected"{/if}>{$cur.code} - {$cur.name}</option>
      {/foreach}
    </select>

  </li>

  {/if}

  {* Country selector *}

  {if $config.mc_allow_select_country eq "Y"}

  <li class="mc-selector-row">

    <div class="mc-selector-row-separator"></div>

    <div class="mc-selector-label">{$lng.mc_lbl_select_coountry}:</div>
    <select name="mc_country" id="mc_country" onchange="javascript: setCurrencyByCountry($('#mc_country option:selected').val()); setLanguageByCountry($('#mc_country option:selected').val())">
      {foreach from=$mc_all_countries item=cnt}
      {if not $cnt.excluded}
      <option value="{$cnt.country_code}"{if $store_country eq $cnt.country_code} selected="selected"{/if}>{$cnt.country}</option>
      {/if}
      {/foreach}
    </select>
  </li>

  {/if}

</ul>

  <div class="mc-selector-button">
    <input type="submit" value="{$lng.lbl_apply}" />
  </div>

  </form>

</div>
