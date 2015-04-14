{*
16fd72c950254a8d4861815dac0d6147af93e43f, v2 (xcart_4_7_0), 2014-12-23 10:13:26, customer_view_results.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$survey.survey}</h1>

{capture name=dialog}

  {foreach from=$survey.questions item=q key=qid}

    {counter assign="question_counter"}
    <p class="survey-question">{$question_counter}. {$q.question}</p>

    <div class="survey-result-row">
      <table cellspacing="1" class="width-100" summary="{$q.question|escape}">

        {if $q.answers_type ne 'N'}

          {foreach from=$q.answers item=a key=aid name=answers}

            <tr{interline name=answers foreach_iteration="`$smarty.foreach.answers.iteration`" foreach_total="`$smarty.foreach.answers.total`"}>
              <td class="survey-result-answer{if $a.highlighted} survey-answer-hl{/if}">{$a.answer}</td>
              <td{if $a.highlighted} class="survey-answer-hl"{/if}>
                {include file="modules/Survey/customer_display_bar.tpl" width=$a.bar_width percent=$a.percent}
              </td>
            </tr>

          {/foreach}

        {else}

            <tr>
              <td class="survey-result-answer">{$lng.lbl_survey_comment_filled}</td>
              <td>
                {include file="modules/Survey/customer_display_bar.tpl" width=$q.bar_width percent=$q.percent}
              </td>
            </tr>

        {/if}

      </table>

    </div>

  {/foreach}

  <div class="buttons-row">
    {if $avail_unfilled_surveys}
      {include file="customer/buttons/button.tpl" button_title=$lng.lbl_survey_return_to_list href="survey.php"}
      <div class="button-separator"></div>
    {/if}
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_continue_shopping href="home.php"}
    <div class="clearing"></div>
  </div>

{/capture}
{include file="customer/dialog.tpl" title=$survey.survey content=$smarty.capture.dialog noborder=true}
