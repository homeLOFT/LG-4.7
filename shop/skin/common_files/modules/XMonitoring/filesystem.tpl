{*
fb6c3380ee54b2582ac3726654777711577fe132, v3 (xcart_4_6_0), 2013-03-19 11:46:40, filesystem.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<!-- xmonitoring tab -->
{include file="main/check_all_row.tpl" style="line-height: 170%;" form="xmonitoring_actions" prefix="xm_files"}
<table cellpadding="2" cellspacing="1" width="100%">
    {capture name=xfiles}
        <table cellpadding="2" cellspacing="1" width="100%">
            <tr class="TableHead">
                <th width="5">&nbsp;</th>
                <th>{$lng.lbl_xmonitoring_file_name}</th>
                <th>{$lng.lbl_xmonitoring_owner}</th>
                <th>{$lng.lbl_xmonitoring_perms}</th>
                <th>{$lng.lbl_xmonitoring_modified}</th>
                <th>{$lng.lbl_xmonitoring_action}</th>
            </tr>
            {foreach from=$xmonitoring_files key=xm_section item=xm_item}
                {if count($xm_item) gt 0}
                    {foreach from=$xm_item key=k item=v}
                        {if $v.info.filename}
                            <tr{cycle values=", class='TableSubHead'"}>

                                {* options block begin *}
                                {assign var=xm_warning_info value=$lng.wrn_xmonitoring_changes_detected}
                                {assign var=xm_warning_icon value=$ImagesDir|cat:"/icon_warning_small.gif"}

                                {assign var=xm_diff_action value="javascript: xmonitoring.diff('"|cat:$v.info.filename|cat:"');"}
                                {assign var=xm_restore_action value="javascript: xmonitoring.restore('"|cat:$v.info.filename|cat:"');"}
                                {assign var=xm_accept_action value="javascript: xmonitoring.accept('"|cat:$v.info.filename|cat:"');"}

                                {assign var=xm_remove_action value="javascript: xmonitoring.remove('"|cat:$v.info.filename|cat:"');"}

                                {assign var=xm_hide_diff_button value='N'}
                                {assign var=xm_hide_restore_button value='N'}
                                {* options block end *}

                                {* logic block begin *}
                                {if $v.snapshot ne ''}
                                    {if $v.snapshot.signature_check_record eq ''}
                                        {assign var=xm_accept_action value="javascript: xmonitoring.accept2('"|cat:$v.info.filename|cat:"', '"|cat:$lng.err_xmonitoring_invalid_snapshot|substitute:"filename":$v.info.filename|cat:"<br /><br />');"}
                                        {assign var=xm_warning_info value=$lng.wrn_xmonitoring_invalid_snapshot}
                                        {assign var=xm_hide_restore_button value='Y'}
                                    {/if}
                                {else}
                                    {assign var=xm_accept_action value="javascript: xmonitoring.accept2('"|cat:$v.info.filename|cat:"', '"|cat:$lng.err_xmonitoring_no_file_snapshot|substitute:"filename":$v.info.filename|cat:"<br /><br />');"}
                                    {assign var=xm_warning_info value=$lng.wrn_xmonitoring_no_file_snapshot}
                                    {assign var=xm_warning_icon value=$ImagesDir|cat:"/help_sign.gif"}
                                    {assign var=xm_hide_diff_button value='Y'}
                                    {assign var=xm_hide_restore_button value='Y'}
                                {/if}

                                {if $xm_section eq 'system_check'}
                                    {assign var=lbl_file_type value=$lng.lbl_xmonitoring_system_file}
                                {elseif $xm_section eq 'secure_check'}
                                    {assign var=lbl_file_type value=$lng.lbl_xmonitoring_secure_file}
                                {else}
                                    {assign var=lbl_file_type value=$lng.lbl_xmonitoring_potent_hack}
                                    {assign var=xm_warning_icon value=$ImagesDir|cat:"/icon_warning_small_red.png"}
                                    {assign var=xm_warning_info value=$lng.wrn_xmonitoring_potencial_hack}
                                {/if}
                                {* logic block end *}

                                <td width="5">
                                    {if $xm_section eq 'system_check' or $xm_section eq 'secure_check'}
                                        <input type="checkbox" name="xm_files[{$v.info.filename}]">
                                        {assign var=xm_show_accept_restore_buttons value='Y'}
                                    {/if}
                                </td>
                                <td class="xm_{$xm_section}">{$v.info.filename}{if $xm_warning_info}<img src="{$xm_warning_icon}" title="{$lbl_file_type}: {$xm_warning_info}" style="float: right; width: 15px;" />{/if}</td>
                                <td{if $v.snapshot.signature_check_record and $v.error.fowner} class="error-field" title="{$lng.lbl_xmonitoring_original}: {$v.error.fowner}"{/if}>{$v.info.fowner}</td>
                                <td{if $v.snapshot.signature_check_record and $v.error.fperms} class="error-field" title="{$lng.lbl_xmonitoring_original}: {$v.error.fperms|substr:-3}"{/if}>{$v.info.fperms|substr:-3}</td>
                                <td{if $v.snapshot.signature_check_record and $v.error.fmtime} class="error-field" title="{$lng.lbl_xmonitoring_original}: {$v.error.fmtime|date_format:$config.Appearance.datetime_format}"{/if}>{$v.info.fmtime|date_format:$config.Appearance.datetime_format}</td>
                                <td>
                                    {if $xm_section eq 'system_check' or $xm_section eq 'secure_check'}
                                        {if $xm_hide_diff_button ne 'Y'}
                                            <input type="button" value="{$lng.lbl_xmonitoring_diff|strip_tags:false|escape}" onclick="{$xm_diff_action}" />
                                        {/if}
                                        {if $xm_hide_restore_button ne 'Y'}
                                            <input type="button" value="{$lng.lbl_xmonitoring_restore|strip_tags:false|escape}" onclick="{$xm_restore_action}" />
                                        {/if}
                                        <input type="button" value="{$lng.lbl_xmonitoring_accept|strip_tags:false|escape}" onclick="{$xm_accept_action}" />
                                    {else}
                                        <input type="button" value="{$lng.lbl_xmonitoring_remove|strip_tags:false|escape}" onclick="{$xm_remove_action}" />
                                    {/if}
                                </td>
                            </tr>
                        {/if}
                    {/foreach}
                {else}
                    <tr{cycle values=", class='TableSubHead'"}>
                        <td colspan="5" class="xm_{$xm_section}"></td>
                    </tr>
                {/if}
            {/foreach}
        </table>
    {/capture}
    {include file="dialog.tpl" content=$smarty.capture.xfiles extra='width="100%"'}
    <table cellpadding="2" cellspacing="1" width="100%">
        <tr>
            <td style="text-align: left;">
                {if $xmonitoring_has_snapshots eq "N"}
                    <input type="button" value="{$lng.lbl_xmonitoring_create|strip_tags:false|escape}" onclick="javascript: xmonitoring.generate();" />
                {/if}
                {if $xm_show_accept_restore_buttons eq 'Y'}
                    <input type="button" value="{$lng.lbl_xmonitoring_accept_selected|strip_tags:false|escape}" onclick="xmonitoring.accept_selected();" />
                    <input type="button" value="{$lng.lbl_xmonitoring_restore_selected|strip_tags:false|escape}" onclick="xmonitoring.restore_selected();" />
                    <input type="button" value="{$lng.lbl_xmonitoring_report_selected|strip_tags:false|escape}" onclick="javascript: xmonitoring.report_selected_files();" title="{$lng.txt_xmonitoring_report_help}" />
                {/if}
            </td>
            <td style="text-align: right;">
                {if $xmonitoring_has_snapshots eq "Y"}
                    <input type="button" value="{$lng.lbl_xmonitoring_check_files|strip_tags:false|escape}" onclick="javascript: xmonitoring.freload();" />
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
