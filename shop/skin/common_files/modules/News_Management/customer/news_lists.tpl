{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, news_lists.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.lbl_news_subscribe_to_newslists}</h1>

{capture name=dialog}

<form action="news.php" method="post">
  <input type="hidden" name="mode" value="subscribe" />
  <input type="hidden" name="newsemail" value="{$newsemail|escape}" />

{foreach from=$lists item=list key=k}
  <label class="news-item">
    <input type="checkbox" name="s_lists[]" value="{$list.listid}" checked="checked" />
    {$list.name}
  </label>
  <div class="news-item-descr">{$list.descr}</div>
{/foreach}

  <div class="button-row">
    {include file="customer/buttons/button.tpl" button_title=$lng.lbl_subscribe additional_button_class="main-button" type="input"}
  </div>

</form>

{/capture}
{include file="customer/dialog.tpl" title=$lng.lbl_news_subscribe_to_newslists content=$smarty.capture.dialog noborder=true}
