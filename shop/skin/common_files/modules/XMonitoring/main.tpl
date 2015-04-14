{*
d8577151af4a3cd3fd00a46cf74a79d2eeba58d1, v8 (xcart_4_6_2), 2013-10-16 19:18:44, main.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

{include file="page_title.tpl" title=$lng.lbl_xmonitoring_title}

{if $xmonitoring_expired eq 'Y'}
    {$lng.txt_xmonitoring_expired}
{else}

    {* Load XMonitoring JS *}
    <script type="text/javascript" src="{$SkinDir}/modules/XMonitoring/js/xmonitoring.js"></script>
    
    {* Define common options *}
    <script type="text/javascript">
        //<![CDATA[        
        var lbl_xmonitoring_confirm_title = '{$lng.lbl_xmonitoring_confirm_title}';
        var lbl_xmonitoring_yes = '{$lng.lbl_xmonitoring_yes}';
        var lbl_xmonitoring_no = '{$lng.lbl_xmonitoring_no}';
        var txt_xmonitoring_confirm_msg = '{$lng.txt_xmonitoring_confirm_msg}';
        
        var lbl_xmonitoring_monitoring_summary = '{$lng.lbl_xmonitoring_monitoring_summary}';
        var lbl_xmonitoring_availability_problems = '{$lng.lbl_xmonitoring_availability_problems}';
        
        var tabsOptions = {ldelim}
            beforeActivate: function( event, ui ) {ldelim}
                xmonitoring.reDrawCharts(ui);
            {rdelim}
        {rdelim};

        var xmonitoring_admin_dir = '{$xmonitoring_admin_dir}';
        
        /* load tabs UI */
        xmonitoring.loadTabsUI();
        //]]>
    </script>

    {* Load graphics API *}
    <script type="text/javascript" src="https://www.google.com/jsapi"></script>

    <div id="xmonitoring-tabs-container" class="ui-tabs ui-widget ui-widget-content ui-corner-all">
        <ul class="ui-tabs-nav ui-helper-reset ui-helper-clearfix ui-widget-header ui-corner-all">
            <li class="ui-state-default ui-corner-top ui-tabs-active ui-state-active">
                <a href="#xmonitoring-tabs-availibility" rel="noreferrer">{$lng.lbl_xmonitoring_availibility}</a>
            </li>
            <li class="ui-state-default ui-corner-top">
                <a href="#xmonitoring-tabs-details" rel="noreferrer">{$lng.lbl_xmonitoring_details}</a>
            </li>
            <li class="ui-state-default ui-corner-top">
                <a href="#xmonitoring-tabs-files" rel="noreferrer">{$lng.lbl_xmonitoring_files}</a>
            </li>
            <li class="ui-state-default ui-corner-top">
                <a href="#xmonitoring-tabs-pages" rel="noreferrer">{$lng.lbl_xmonitoring_webpages}</a>
            </li>
        </ul>

        <div id="xmonitoring-tabs-availibility" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
            <div style="text-align: center;"><h2>{$lng.lbl_xmonitoring_period}: {$xmonitoring_begin_date|date_format:$config.Appearance.date_format} - {$xmonitoring_end_date|date_format:$config.Appearance.date_format}</h2></div>
            
            <script type="text/javascript">
                var xm_eventsData = [
                    ['Event', '  Duration'],
                {if $xmonitoring_has_items eq 'Y'}
                    ['{$lng.lbl_xmonitoring_ok}', {$xmonitoring_duration_ms}],
                {/if}
                {foreach from=$xmonitoring_events_groups item=v}
                    ['{$v.type} ({$v.value})', {$v.duration_ms}],
                {/foreach}
                ];
            </script>
            <div id="events_chart_div" style="width: 100%; height: 400px;"></div>

            <script type="text/javascript">
                var xm_reasonsData = [
                    ['Event', 'Duration'],
                {foreach from=$xmonitoring_reasons_groups item=v}
                    ['{$v.reason}', {$v.duration_ms}],
                {/foreach}
                ];
            </script>
            <div id="reasons_chart_div" style="width: 100%; height: 400px;"></div>
            
            <script type="text/javascript">
                google.load("visualization", "1", {ldelim}packages:["corechart"]{rdelim});
                google.setOnLoadCallback(xmonitoring.drawCharts);
            </script>
        </div>

        <div id="xmonitoring-tabs-details" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
            {capture name=xdetails}
                <table cellpadding="2" cellspacing="1" width="100%">
                    <tr class="TableHead">
                        <th>{$lng.lbl_xmonitoring_time}</th>
                        <th>{$lng.lbl_xmonitoring_event}</th>
                        <th>{$lng.lbl_value}</th>
                        <th>{$lng.lbl_reason}</th>
                        <th>{$lng.lbl_xmonitoring_duration}</th>
                    </tr>
                    {foreach from=$xmonitoring_events item=v}
                        <tr{cycle values=", class='TableSubHead'"}>
                            <td>{$v.time|date_format:$config.Appearance.datetime_format}</td>
                            <td>{$v.type}</td>
                            {if $v.value eq 'OK'}
                                <td><span style="color: green;">{$v.value}</span></td>
                            {else}
                                <td><span style="color: red;">{$v.value}</span></td>
                            {/if}
                            <td>{$v.reason}</td>
                            <td>{$v.duration_hr}</td>
                        </tr>
                    {/foreach}
                </table>
            {/capture}
            {include file="dialog.tpl" content=$smarty.capture.xdetails extra='width="100%"'}
        </div>

        <form name="xmonitoring_actions" action="xmonitoring.php" method="post">
            <input type="hidden" name="xm_action" value="" />
            <input type="hidden" name="xm_params" value="" />

        <div id="xmonitoring-tabs-files" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
            <div id="xmonitoring_files">
                <div id="xmonitoring_filesSearch" style="text-align: center;">
                    <img src="{$SkinDir}/images/quick_search_searching.gif" alt=""><br />
                    <span>{$lng.lbl_searching}...</span>
                </div>
            </div>
            <script type="text/javascript">
                {* save load indicator and reload *}
                xmonitoring.saveFIndicator();
            </script>
        </div>

        <div id="xmonitoring-tabs-pages" class="ui-tabs-panel ui-widget-content ui-corner-bottom">
            <div id="xmonitoring_pages">
                <div id="xmonitoring_pagesSearch" style="text-align: center;">
                    <img src="{$SkinDir}/images/quick_search_searching.gif" alt=""><br />
                    <span>{$lng.lbl_searching}...</span>
                </div>
            </div>
            <script type="text/javascript">
                {* save load indicator and reload *}
                xmonitoring.savePIndicator();
            </script>
        </div>

        </form>

    </div>
{/if}
