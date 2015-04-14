{*
0d0465915bddae9202730b9dcd479c6724c60147, v1 (xcart_4_5_5), 2013-01-11 13:17:36, navigation.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}

{if $total_pages gt 2}
  <div class="nav-pages">
    {strip}

      {if $data.navigation_arrow_left}
        <a class="nav-arrows left-arrow right-delimiter" href="javascript:void(0);" onclick="ajax_process('cat={$cat}&sort={$data.sort}&fb_mode={$data.mode}&sort_direction={$data.sort_direction}&per_page={$data.objects_per_page}&substring={$data.substring}&page={$data.navigation_arrow_left}');"><img src="tpls/tab/images/spacer.gif" alt="" /></a>
        {/if}

      {if $start_page gt 1}
        <a class="nav-page right-delimiter" href="javascript:void(0);" onclick="ajax_process('cat={$cat}&sort={$data.sort}&fb_mode={$data.mode}&sort_direction={$data.sort_direction}&per_page={$data.objects_per_page}&substring={$data.substring}&page=1');" title="page #1">1</a>

        {if $start_page gt 2}
          <span class="nav-dots right-delimiter">...</span>
        {/if}

      {/if}

      {section name=page loop=$total_pages start=$start_page}

        {if $smarty.section.page.index eq $navigation_page}
          <span class="current-page{if not $smarty.section.page.last or ($total_pages lte $total_super_pages or $data.navigation_arrow_right)} right-delimiter{/if}" title="current page: #{$smarty.section.page.index}">{$smarty.section.page.index}</span>
        {else}
          <a class="nav-page{if not $smarty.section.page.last or ($total_pages lte $total_super_pages or $data.navigation_arrow_right)} right-delimiter{/if}" href="javascript:void(0);" onclick="ajax_process('cat={$cat}&sort={$data.sort}&fb_mode={$data.mode}&sort_direction={$data.sort_direction}&per_page={$data.objects_per_page}&substring={$data.substring}&page={$smarty.section.page.index}');" title="page #{$smarty.section.page.index}">{$smarty.section.page.index}</a>
        {/if}

      {/section}
      
      {if $total_pages lte $total_super_pages}

        {if $total_pages lt $total_super_pages}
          <span class="nav-dots right-delimiter">...</span>
        {/if}
        <a class="nav-page{if $data.navigation_arrow_right} right-delimiter{/if}" href="javascript:void(0);" onclick="ajax_process('cat={$cat}&sort={$data.sort}&fb_mode={$data.mode}&sort_direction={$data.sort_direction}&per_page={$data.objects_per_page}&substring={$data.substring}&page={$total_super_pages}');" title="page #{$total_super_pages}">{$total_super_pages}</a>
      {/if}

      {if $data.navigation_arrow_right}
        <a class="nav-arrows right-arrow" href="javascript:void(0);" onclick="ajax_process('cat={$cat}&sort={$data.sort}&fb_mode={$data.mode}&sort_direction={$data.sort_direction}&per_page={$data.objects_per_page}&substring={$data.substring}&page={$data.navigation_arrow_right}');"><span>{$lng.lbl_next}</span></a>
        {/if}

    {/strip}

  </div>

  <div class="clearing"></div>
{/if}
