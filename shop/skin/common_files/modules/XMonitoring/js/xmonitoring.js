/*
vim: set ts=2 sw=2 sts=2 et:
 */

/**
 * X-Monitoring scripts
 * 
 * @category   Modules
 * @package    X-Monitoring
 * @subpackage JS Library
 * @author     Michael Bugrov <mixon@x-cart.com> 
 * @version    0a663280c0dd87505274160aac2ed21e25c768c8, v5 (xcart_4_6_2), 2013-10-18 14:33:17, xmonitoring.js, aim
 * @link       http://www.x-cart.com/
 * @see        ____file_see____
 */
var xmonitoring = {
                
    fload_indicator: '',

    pload_indicator: '',
    
    confirm_action3: function(action_name, action_params, action_message) {
        var xmonitoring_dialog_buttons = {};        
        var xmonitoring_message = action_message + txt_xmonitoring_confirm_msg;
        
        xmonitoring_dialog_buttons[lbl_xmonitoring_yes] = function () {
            xmonitoring.perform_action(action_name, action_params);
            $(this).dialog("close");
        };
        xmonitoring_dialog_buttons[lbl_xmonitoring_no] = function () {
            $(this).dialog("close");
        };
        
        $('<div class="xm-dialog"></div>').appendTo('body')
        .html('<div><h3>' + xmonitoring_message + '</h3></div>')
        .dialog({
            title: lbl_xmonitoring_confirm_title,
            autoOpen: true,
            modal: true,
            width: 'auto',
            resizable: false,
            buttons: xmonitoring_dialog_buttons,
            close: function (event, ui) {
                $(this).remove();
            }
        });
    },
    
    confirm_action: function(action_name, action_params) {
        xmonitoring.confirm_action3(action_name, action_params, '');
    },
                
    perform_action: function(action_name, action_params) {
        document.xmonitoring_actions.xm_action.value = action_name;
        document.xmonitoring_actions.xm_params.value = action_params;
        document.xmonitoring_actions.submit();
    },

    diff: function(file) {
        document.xmonitoring_actions.reset();
        /* update current form params */
        document.xmonitoring_actions.method = 'get';
        document.xmonitoring_actions.target = '_blank';
        xmonitoring.perform_action('diff', file);
        /* restore default form params */
        document.xmonitoring_actions.method = 'post';
        document.xmonitoring_actions.target = '_self';
    },

    page_diff: function(file) {
        document.xmonitoring_actions.reset();
        /* update current form params */
        document.xmonitoring_actions.method = 'get';
        document.xmonitoring_actions.target = '_blank';
        xmonitoring.perform_action('page_diff', file);
        /* restore default form params */
        document.xmonitoring_actions.method = 'post';
        document.xmonitoring_actions.target = '_self';
    },
                
    remove: function(file) {
        xmonitoring.confirm_action('remove', file);
    },
    
    remove2: function(file, title) {
        xmonitoring.confirm_action3('remove', file, title);
    },
    
    remove_selected: function() {
        xmonitoring.confirm_action('remove_selected', null);
    },
                
    restore: function(file) {
        xmonitoring.confirm_action('restore', file);
    },
    
    restore2: function(file, title) {
        xmonitoring.confirm_action3('restore', file, title);
    },
    
    restore_selected: function() {
        xmonitoring.confirm_action('restore_selected', null);
    },
                
    accept: function(file) {
        xmonitoring.confirm_action('accept', file);
    },
    
    accept2: function(file, title) {
        xmonitoring.confirm_action3('accept', file, title);
    },
    
    accept_selected: function() {
        xmonitoring.confirm_action('accept_selected', null);
    },

    generate: function() {
        xmonitoring.confirm_action('generate', null);
    },
                
    freload: function() {
        $('#xmonitoring_files').html(xmonitoring.fload_indicator);
        xmonitoring.loadXmTab($('#xmonitoring_files'), 'xmonitoring_files');
    },

    preload: function() {
        $('#xmonitoring_pages').html(xmonitoring.pload_indicator);
        xmonitoring.loadXmTab($('#xmonitoring_pages'), 'xmonitoring_pages');
    },

    page_diff: function(file) {
        document.xmonitoring_actions.reset();
        /* update current form params */
        document.xmonitoring_actions.method = 'get';
        document.xmonitoring_actions.target = '_blank';
        xmonitoring.perform_action('page_diff', file);
        /* restore default form params */
        document.xmonitoring_actions.method = 'post';
        document.xmonitoring_actions.target = '_self';
    },

    page_accept: function(file) {
        xmonitoring.confirm_action('page_accept', file);
    },

    page_report: function(file) {
        xmonitoring.confirm_action('page_report', file);
    },

    page_accept_selected: function() {
        xmonitoring.confirm_action('page_accept_selected', null);
    },

    report_selected_files: function() {
        xmonitoring.confirm_action('report_selected_files', null);
    },

    report_selected_pages: function() {
        xmonitoring.confirm_action('report_selected_pages', null);
    },
    
    saveFIndicator: function() {
        $(function () {
            xmonitoring.fload_indicator = $('#xmonitoring_files').html();
            xmonitoring.freload();
        });
    },

    savePIndicator: function() {
        $(function () {
            xmonitoring.pload_indicator = $('#xmonitoring_pages').html();
            xmonitoring.preload();
        });
    },
    
    loadTabsUI: function() {
        $(function() {
            $('#xmonitoring-tabs-container').tabs(tabsOptions);
        });
    },
    
    drawEventsChart: function() {
        var eventsData = google.visualization.arrayToDataTable(xm_eventsData);
        var eventsOptions = {
            title: lbl_xmonitoring_monitoring_summary
        };
        var eventsChart = new google.visualization.PieChart(document.getElementById('events_chart_div'));
        
        eventsChart.draw(eventsData, eventsOptions);
    },
    
    drawReasonsChart: function() {
        var reasonsData = google.visualization.arrayToDataTable(xm_reasonsData);
        var reasonsOptions = {
            title: lbl_xmonitoring_availability_problems,
            colors: ['AF2FBA', '973A57', 'DD59D8', '798893', '2195BD',
            '86A65A', '4389A6', 'AA73F2',
            '3D2257', 'C46B99', 'B93E47', 'C952EE', '61B82F']
        };
        var reasonsChart = new google.visualization.PieChart(document.getElementById('reasons_chart_div'));
        
        reasonsChart.draw(reasonsData, reasonsOptions);
    },
    
    drawCharts: function() {
        xmonitoring.drawEventsChart();
        xmonitoring.drawReasonsChart();
    },
    
    reDrawCharts: function(tabsUI) {
        if (typeof $ != 'undefined') {
            if (
                tabsUI.newTab
                && tabsUI.newTab.find('a').attr('href') == '#xmonitoring-tabs-availibility'
            ) {
                $('#xmonitoring-tabs-availibility').show();
                /* redraw charts */
                xmonitoring.drawCharts();
            }
        }
    },

    loadXmTab: function(elm, name, params) {
        if (!ajax.core.isReady())
            return false;

        params = params || {};

        var d = new Date();
        params.t = d.getTime()

        var xhr = false;
        try {
            xhr = $.ajax(
            {
                url: xmonitoring_admin_dir + '/get_block.php?block=' + name + '&language=' + store_language,
                type: 'GET',
                data: params,
                dataType: 'html',
                complete: function(res, status) {
                    if (status == "success" || status == "notmodified") {
                        var xmonitoring = res.responseText.match(/(<!-- xmonitoring tab -->)([\s\S]*)(<!-- \/xmonitoring tab -->)/i);
                        if (xmonitoring && xmonitoring[2]) {
                            elm.html(res.responseText);
                        } else {
                            var error = res.responseText.match(/(<!-- central space -->)([\s\S]*)(<!-- \/central space -->)/i);
                            if (error && error[2]) {
                                elm.html(error[2]);
                            } else {
                                elm.html(res.responseText);
                            }
                        }
                    }
                }
            });
            return xhr;
        } catch(e) {
            return false;
        }
        return false;
    }
};
