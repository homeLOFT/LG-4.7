{*
vim: set ts=2 sw=2 sts=2 et:
*}
<!-- xmonitoring tab -->
{include file="main/check_all_row.tpl" style="line-height: 170%;" form="xmonitoring_actions" prefix="xm_pages"}
<table cellpadding="2" cellspacing="1" width="100%">
    {capture name=xpages}
        <table cellpadding="2" cellspacing="1" width="100%">
            <tr class="TableHead">
                <th width="5">&nbsp;</th>
                <th>{$lng.lbl_xmonitoring_page}</th>
                <th>{$lng.lbl_xmonitoring_status}</th>
                <th>{$lng.lbl_xmonitoring_modified}</th>
                <th>{$lng.lbl_xmonitoring_action}</th>
            </tr>
            {foreach from=$xmonitoring_webpages key=n item=p}
                
                {* options block begin *}
                {assign var=xm_diff_action value="javascript: xmonitoring.page_diff('"|cat:$p.page_name|cat:"');"}
                {assign var=xm_accept_action value="javascript: xmonitoring.page_accept('"|cat:$p.page_name|cat:"');"}
                {assign var=xm_report_action value="javascript: xmonitoring.page_report('"|cat:$p.page_name|cat:"');"}
                {if $p.is_modified ne ''}
                  {assign var=xm_has_modifications value="Y"}
                {/if}
                {* options block end *}
                
                <tr {if $p.is_modified}class="xm_modified"{/if}>
                <td width="5">
                    {if $p.is_modified}
                    <input type="checkbox" name="xm_pages[{$p.page_name}]">
                    {/if}
                </td>
                <td>{$p.page_name}</td>
                <td>{if $p.is_modified}
                        <span style="color: red;">{$lng.lbl_xmonitoring_modified}</span>
                    {else}
                        <span style="color: green;">OK</span>
                    {/if}
                </td>
                <td>{$p.last_updated|date_format:$config.Appearance.datetime_format}</td>
                <td>
                    {if $p.is_modified}
                    <input type="button" value="{$lng.lbl_xmonitoring_diff|strip_tags:false|escape}" onclick="{$xm_diff_action}" />
                    <input type="button" value="{$lng.lbl_xmonitoring_accept|strip_tags:false|escape}" onclick="{$xm_accept_action}" />
                    {/if}
                </td>
                </tr>
            {/foreach}
        </table>
    {/capture}
    {include file="dialog.tpl" content=$smarty.capture.xpages extra='width="100%"'}
    <table cellpadding="2" cellspacing="1" width="100%">
        <tr>
            <td style="text-align: left;">
                {if $xmonitoring_has_webpages && $xm_has_modifications}
                    <input type="button" value="{$lng.lbl_xmonitoring_accept_selected|strip_tags:false|escape}" onclick="javascript: xmonitoring.page_accept_selected();" />
                    <input type="button" value="{$lng.lbl_xmonitoring_report_selected|strip_tags:false|escape}" onclick="javascript: xmonitoring.report_selected_pages();" title="{$lng.txt_xmonitoring_report_help}" />
                {/if}
            </td>
            <td style="text-align: right;">
                {if $xmonitoring_has_webpages}
                    <input type="button" value="{$lng.lbl_xmonitoring_check_pages|strip_tags:false|escape}" onclick="javascript: xmonitoring.preload();" />
                {/if}
            </td>
        </tr>
    </table>
</table>
<script type="text/javascript">
    $(function() {ldelim}
    $('input:submit, input:button, button, a.simple-button').button();
    {rdelim});
</script>
<!-- /xmonitoring tab -->
