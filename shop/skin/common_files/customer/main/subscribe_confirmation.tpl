{*
57b8549e51508b5fa15646fc6527ce5c40d8117e, v2 (xcart_4_4_0_beta_2), 2010-07-01 06:54:08, subscribe_confirmation.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}

<h1>{$lng.txt_thankyou_for_subscription}</h1>

{capture name=dialog}

  <p class="text-block">
    {$lng.txt_newsletter_subscription_msg}:<br />
    <strong>{$smarty.get.email|replace:"\\":""}</strong>
  </p>
  {$lng.txt_unsubscribe_information} <a href="{$http_location}/mail/unsubscribe.php?email={$smarty.get.email|escape|replace:"\\":""}">{$lng.lbl_this_url}</a>.

{/capture}
{include file="customer/dialog.tpl" title=$lng.txt_thankyou_for_subscription content=$smarty.capture.dialog noborder=true}
