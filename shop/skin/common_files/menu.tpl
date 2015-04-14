{*
797238b7266d446ddf5fa673410e7442472ee634, v2 (xcart_4_6_4), 2014-04-21 09:40:53, menu.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
<table cellspacing="0" cellpadding="0" width="100%" class="VertMenuBorder">
  <tr>
    <td class="VertMenuTitleBox">
      <table cellspacing="0" cellpadding="0" width="100%">
        <tr>
          <td>{$link_begin}<img src="{$ImagesDir}/{if $dingbats ne ''}{$dingbats}{else}spacer.gif{/if}" class="VertMenuTitleIcon" alt="{$menu_title|escape}" />{$link_end}</td>
          <td width="100%"><span class="VertMenuTitle">{$menu_title}</span></td>
          {if $link_href}
            <td style="padding-right: 7px;"><a href="{$link_href}"><img src="{$ImagesDir}/menu_arrow.gif" alt="" /></a></td>
          {/if}
        </tr>
      </table>
    </td>
  </tr>
  <tr> 
    <td class="VertMenuBox">
      <table cellpadding="{$cellpadding|default:"5"}" cellspacing="0" width="100%">
        <tr>
          <td>{$menu_content}<br /></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
