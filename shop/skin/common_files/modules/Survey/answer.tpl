{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, answer.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<td class="survey-answer-mark{$survey_style_suffix}"{if $is_column} valign="top"{if $a.textbox_type ne 'Y'} rowspan="2"{/if}{/if}>
  <input {if $question.answers_type eq 'C'}type="checkbox" name="data[{$qid}][answers][]"{else}type="radio" name="data[{$qid}][answers]"{/if} id="ans_{$aid}" value="{$aid}"{if $a.selected} checked="checked"{/if}{if $readonly} readonly="readonly"{/if} />
</td>
<td class="survey-answer{$survey_style_suffix}"{if $is_column} valign="top"{if $a.textbox_type ne 'Y'} rowspan="2"{/if}{/if}>
  <label for="ans_{$aid}">{$a.answer}</label>
</td>
