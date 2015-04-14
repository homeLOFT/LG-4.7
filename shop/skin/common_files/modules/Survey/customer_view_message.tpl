{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, customer_view_message.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$survey.survey}</h1>

{capture name=dialog}

  {$survey.complete|default:$lng.txt_survey_default_complete_message}

  <br />

  {if $section ne "preview"}

    <br />

    {if $survey.publish_results eq 'Y'}
      {include file="customer/buttons/button.tpl" href="survey.php?surveyid=`$survey.surveyid`&amp;mode=view" button_title=$lng.lbl_survey_view_results}
      <br />
    {/if}

    {include file="customer/buttons/button.tpl" href="survey.php" button_title=$lng.lbl_survey_go2surveys}

  {/if}

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_survey content=$smarty.capture.dialog noborder=true}

