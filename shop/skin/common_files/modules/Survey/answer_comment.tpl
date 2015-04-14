{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, answer_comment.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<td colspan="2"{if $is_column} valign="top"{/if} class="SurveyAnswerComment{$survey_style_suffix}">
<textarea {if $is_column or $is_menu}rows="2" cols="20"{else}rows="4" cols="40"{/if} class="Survey" name="data[{$qid}][comment][{$aid}]"{if $readonly} readonly="readonly"{/if} onblur="javascript: if (!this.oldTxt) this.oldTxt = ''; var changed = this.oldTxt not = this.value; this.oldTxt = this.value; if (changed &amp;&amp; document.getElementById('ans_{$aid}')) document.getElementById('ans_{$aid}').checked = true;">{$a.comment}</textarea>
</td>

