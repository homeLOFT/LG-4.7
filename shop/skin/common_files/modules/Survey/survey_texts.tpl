{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, survey_texts.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
{if $texts}
{foreach from=$texts item=t}
<div class="{cycle values="SurveyAnswerTextLine_1,SurveyAnswerTextLine_2"}">
<a href="survey.php?surveyid={$t.surveyid}&amp;section=instances&amp;sresultid={$t.sresultid}" target="survey_instance" class="SurveyAnswerTextLine">[{$t.date|date_format:$config.Appearance.datetime_format}]</a> {$t.comment|replace:"\n":"<br />\n"}
</div>

{/foreach}
{/if}
