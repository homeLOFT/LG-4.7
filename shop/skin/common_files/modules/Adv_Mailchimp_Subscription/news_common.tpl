{*
$Id$

vim: set ts=2 sw=2 sts=2 et:
*}
{include file="page_title.tpl" title=$lng.lbl_mailchimp_news_management}
<a href="http://www.mailchimp.com/signup/?pid=xcart&source=website"><img src="http://xmail.x-shops.com/mailchimp/img/mailchimp.jpg" alt=""></a>
<br>
<br>

{$lng.txt_mailchimp_news_management_top_text}

<br /><br />


{if $mode eq ""}

<br>
{include file="modules/Adv_Mailchimp_Subscription/mailchimp_import.tpl"}
<br>


{include file="modules/Adv_Mailchimp_Subscription/news_lists_select.tpl"}

{elseif $mode eq "create" or $mode eq "modify" or ($mode eq "messages" and ($action eq "add" or $action eq "modify"))}

{$lng.txt_fields_are_mandatory}
<br />
<br />
{/if}

{capture name=dialog}

{if $mode eq "modify"}
{assign var="dialog_title" value=$lng.lbl_news_list_details}
{include file="modules/Adv_Mailchimp_Subscription/news_details.tpl"}
{/if}

{/capture}

{include file="dialog.tpl" title=$dialog_title content=$smarty.capture.dialog extra='width="100%"'}
