{********************************************************************************
| Dynamic Product Tabs
| Copyright Smack Digital Inc.
| All rights reserved.
| License: http://www.smackdigital.com/downloads/license-agreement.pdf
********************************************************************************
| Tab display
********************************************************************************}
<div id="wcmgroup1" class="wcmtabcontainer" style="text-align:left;">

	{* Alignment *}
    {if $config.WCM_Dynamic_Product_Tabs.tab_heading_align eq 'L'}
    {assign var="headingalign" value="padding-left:4px; padding-right: 11px;"}
    {/if}

    {php}
    $active_modules = $template->get_template_vars('active_modules');
    $tabs = $template->get_template_vars('WCMtabs');
    $config = $template->get_template_vars('config');

    foreach ($tabs as $wcmTabCheck => $tab)
    {
        $tabs[$wcmTabCheck]['content'] = trim($tab['content']);
        $tabs[$wcmTabCheck]['name'] = trim($tab['name']);
        $wcmTabModuleName = $tab['module_name'];

        // Empty Content
        if ($tabs[$wcmTabCheck]['content'] == '')
        {
          $tabs[$wcmTabCheck]['content'] = '';
          $tabs[$wcmTabCheck]['name'] = '';
        }
        // Send to Friend
        elseif ($wcmTabModuleName == 'custom_send_to_friend' AND $config['Appearance']['send_to_friend_enabled'] != 'Y')
        {
            $tabs[$wcmTabCheck]['content'] = '';
            $tabs[$wcmTabCheck]['name'] = '';
        }
        // Detailed Images in a Popup
        elseif ($wcmTabModuleName == 'Detailed_Product_Images' AND $active_modules[$wcmTabModuleName] == '' )
        {
          $tabs[$wcmTabCheck]['content'] = '';
          $tabs[$wcmTabCheck]['name'] = '';
        }
        // Module Disabled
        elseif ($wcmTabModuleName != '' AND $wcmTabModuleName != 'custom_send_to_friend' AND $active_modules[$wcmTabModuleName] == '')
        {
          $tabs[$wcmTabCheck]['content'] = '';
          $tabs[$wcmTabCheck]['name'] = '';
        }

        $template->assign('WCMtabs',$tabs);
    }
    {/php}

    {********************************************************************************
    | Tab Headings
    ********************************************************************************}
	{if $smarty.get.printable ne "Y"}
	<ul class="tabs-nav">
        {if $config.WCM_Dynamic_Product_Tabs.tab_product_details eq 'Y'}
        <li class="tabs-selected"><a class="tab-link" data-tabid="#tab0" href="{$smarty.server.REQUEST_URI|escape}#tab0"><span class="size{$config.WCM_Dynamic_Product_Tabs.tab_size}"{if $headingalign ne ""} style="{$headingalign}"{/if}>{$lng.lbl_product_details}</span></a>
		<div id="tab0" class="tabs-container">
		{$content}
		</div>
         <div class="clearing"></div>
        </li>
        {/if}
        {section name=tabtitles loop=$WCMtabs}
            {eval var=$WCMtabs[tabtitles].content assign=tabContent}
            {if $tabContent ne ""}
                <li{if $config.WCM_Dynamic_Product_Tabs.tab_product_details ne 'Y' AND $smarty.section.tabtitles.first} class="tabs-selected"{/if}><a class="tab-link" data-tabid="#tab{$WCMtabs[tabtitles].tabid}" href="{$smarty.server.REQUEST_URI|escape}#tab{$WCMtabs[tabtitles].tabid}"><span class="size{$config.WCM_Dynamic_Product_Tabs.tab_size}"{if $headingalign ne ""} style="{$headingalign}"{/if}>{eval var=$WCMtabs[tabtitles].name}</span></a>
                <div id="tab{$WCMtabs[tabtitles].tabid}" class="tabs-container tabs-hide">
                {$tabContent}
                </div>
                <div class="clearing"></div>
                </li>
            {/if}
        {/section}
	</ul>
	{/if}
</div>
<div class="clearing"></div>
<br />
{capture name=wcm_javascript_code}
{literal}
if (window.jQuery) {
    // Global variables
    var wcmTabsWidth = 0;
    var $wcmTab = $('.tabs-nav > li');
    var $wcmTabLink = $('.tabs-nav > li > a.tab-link');

    // Get total tab width
    $wcmTab.each(function () {
        wcmTabsWidth += wcmGetTabWidth($(this));
    });

    // Set tab width for responsiveness
    if (wcmTabsWidth > 0)
        wcmSetTabWidth(wcmTabsWidth);

    $(function () {
        // Load content when accessed directly
        var currentHash = '#tab0';
        if (window.location.hash)
        {
            currentHash = window.location.hash;
            if (currentHash != '#tab0' && currentHash != '' && $(currentHash).length > 0 && $(currentHash).hasClass('tabs-container'))
            {
                if (!$('.tabs-container').hasClass('tabs-hide'))
                    $('.tabs-container:not(currentHash)').addClass('tabs-hide');
                $wcmTab.removeClass('tabs-selected');
                $(currentHash).removeClass('tabs-hide').parent('li').addClass('tabs-selected');
            }
            else
            {
                currentHash = '#tab0';
            }
        }

        // Set initial content height
        $('.wcmtabcontainer').css({height : parseInt($(currentHash).outerHeight(true)) + 20});

        // Adjust content height when tab is clicked
        $wcmTabLink.click(function () {
            wcmSetContentHeight($(this).data('tabid'));
        });

        // Recheck tab width and content height on rezize
        $(window).resize(function () {
            wcmSetTabWidth(wcmTabsWidth);
            $('.wcmtabcontainer').css({height : parseInt($('li.tabs-selected').find('.tabs-container').outerHeight(true)) + 20});
        });
    });

    // Function: Add mobile class if tabs are wider than the container
    function wcmSetTabWidth(wcmTabsWidth)
    {
        if (wcmTabsWidth > parseInt($('.wcmtabcontainer').outerWidth(true)))
            $wcmTab.addClass('tabs-mobile');
        else
            $wcmTab.removeClass('tabs-mobile');
    }

    // Function: Get the width of a single tab
    function wcmGetTabWidth(tab)
    {
        return $(tab).outerWidth(true);
    }

    // Function: Set the content height when a tab is clicked
    function wcmSetContentHeight(content)
    {
        if ($(content).hasClass('tabs-hide'))
        {
            var $currentTab = $(content).siblings('a.tab-link');
            if ($(content).parent('li').hasClass('tabs-mobile'))
            {
                $('.wcmtabcontainer').animate({height : parseInt($(content).outerHeight(true)) + 20}, 200, "linear", function () {
                    wcmScrollTop($currentTab);
                });
            }
            else
            {
                $('.wcmtabcontainer').css({height : parseInt($(content).outerHeight(true)) + 20});
            }
        }
    }

    // Function: Scroll to current tab on mobile
    function wcmScrollTop($tab)
    {
        if ($tab.parent('li').hasClass('tabs-mobile'))
            $("body, html").animate({scrollTop: $tab.position().top + "px"});
    }
}
{/literal}
{/capture}
{load_defer file="wcm_javascript_code" direct_info=$smarty.capture.wcm_javascript_code type="js"}