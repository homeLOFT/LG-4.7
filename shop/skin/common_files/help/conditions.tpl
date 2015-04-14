{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, conditions.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<br />
{capture name=dialog}
{* Place terms and conditions here *}

{if $usertype eq "B"}
{include file="help/conditions_affiliates.tpl"}
{else}
{include file="help/conditions_customers.tpl"}
{/if}

{/capture}
{include file="dialog.tpl" title=$lng.lbl_terms_n_conditions content=$smarty.capture.dialog extra='width="100%"'}
