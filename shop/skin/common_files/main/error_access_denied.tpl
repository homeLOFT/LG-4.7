{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, error_access_denied.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<h1>{$lng.err_access_denied}</h1>
{$message}
{if $id ne ''}
<br /><br />
<b>{$lng.lbl_error_id}:</b> {$id}
{/if}
