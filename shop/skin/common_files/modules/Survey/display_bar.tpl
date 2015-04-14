{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, display_bar.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<table cellspacing="0" cellpadding="0" width="{$length}">
  <tr>
    <td valign="middle">

      <table cellspacing="0" cellpadding="0">
        <tr>
          <td width="{$bar_width}">
            <img src="{$ImagesDir}/bar{if $highlighted}_hl{/if}.gif" class="StatisticsBar{if $highlighted}HL{/if}" height="11" width="{$bar_width}" alt="" />
          </td>
          <td width="{$width_invert}" class="SurveyStatLabel">{$percent}%</td>
        </tr>
      </table>

    </td>
  </tr>
</table>
