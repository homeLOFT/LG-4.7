{*
d672e73d94f39ed7c84f3122ca0af7a5654f9ecb, v4 (xcart_4_4_2), 2010-12-09 14:00:53, menu_survey.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
{if $menu_survey and (not $survey or $menu_survey.surveyid ne $survey.surveyid)}

  {include file="modules/Survey/survey_js.tpl" survey=$menu_survey is_menu=true}

  {capture name=menu}
    <form name="surveyfillmenuform{$menu_survey.surveyid}" method="post" action="survey.php" onsubmit="javascript: return savePeriod(this);">
      <input type="hidden" name="surveyid" value="{$menu_survey.surveyid}" />
      <input type="hidden" name="mode" value="fill" />
      <input type="hidden" name="key" value="{$key}" />

      {include file="modules/Survey/survey.tpl" survey=$menu_survey is_menu=true}

      <hr />

      <div class="center">
        <div class="halign-center">
          {include file="customer/buttons/submit.tpl" type="input" additional_button_class="menu-button"}
        </div>
      </div>

    </form>

  {/capture}
  {include file="customer/menu_dialog.tpl" title=$menu_survey.survey content=$smarty.capture.menu additional_class="menu-survey"}

{/if}
