{*
1e1924d979329a127f421a68658195f4e14e6c8e, v2 (xcart_4_6_0), 2013-04-09 12:52:17, quick_search.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<script type="text/javascript" src="{$SkinDir}/js/quick_search.js"></script>
<div id="quick_search_panel" style="display:none;">
  <div class="quick-search-panel-main">

    <div class="quick-search-body" id="quick_search_body1">
      <span id="quick_search_results">{$lng.lbl_keywords}</span>
      <span id="quick_search_no_results" style="display:none;">{$lng.lbl_no_results_found}</span>
      <span id="quick_search_no_pattern" style="display:none;">{$lng.lbl_quick_search_nopattern}</span><br />
    </div>

    <div class="quick-search-body" id="quick_search_body2" style="display:none;">
      {$lng.lbl_searching}...<br /><br />
      <img src="{$ImagesDir}/quick_search_searching.gif" alt="" />
    </div>

    <div class="quick-search-close" onclick="javascript:close_quick_search();"></div>

  </div>
</div>
