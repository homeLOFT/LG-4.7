{*
999c65bcf65df2b8fb166cbb4805b69c5b5bfbca, v3 (xcart_4_4_4), 2011-08-01 13:32:01, customer_answer_comment.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<textarea{if $question.col gt 1} style="width: 90%;"{/if}{if $is_column or $is_menu} rows="2" cols="20"{else} rows="4" cols="40"{/if} name="data[{$qid|escape}][comment][{$aid|escape}]" id="ansc_{$aid|escape}"{if $readonly} readonly="readonly"{elseif $a.selected eq ""} disabled="disabled"{/if}>{$a.comment|escape}</textarea>
