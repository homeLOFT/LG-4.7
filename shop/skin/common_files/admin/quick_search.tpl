{*
ff29a71cdf48354b4babee44be8b67e407c3e642, v3 (xcart_4_6_5), 2014-08-29 16:56:37, quick_search.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $need_quick_search eq "Y"}
    <li class="menu-item quick-search-menu">
        <table>
            <tr>
                <td class="quick-search-form">
                    <form name="qsform" action="" onsubmit="javascript: quick_search($('#quick_search_query').val()); return false;">
                        <input type="text" class="default-value" id="quick_search_query" onkeypress="javascript:$('#quick_search_panel').hide();" onclick="javascript:$('#quick_search_panel').hide();" value="{$lng.lbl_keywords|escape}" />
                    </form>
                </td>
                <td class="main-button">
                    <button onclick="javascript:quick_search($('#quick_search_query').val());return false;">
                        {$lng.lbl_search}
                    </button>
                </td>
                <td>
                    {include file="main/tooltip_js.tpl" text=$lng.txt_how_quick_search_works id="qs_help" type="img" alt_image="question_gray.png" wrapper_tag="div"}
                </td>
            </tr>
        </table>
    </li>
{/if}
