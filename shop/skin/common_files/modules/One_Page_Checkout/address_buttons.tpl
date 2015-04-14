{*
aad85f2be3e7abf71f38a7de369cbd6188fe4f3b, v4 (xcart_4_7_0), 2015-02-11 09:38:53, address_buttons.tpl, aim

vim: set ts=2 sw=2 sts=2 et:
*}
<div class="buttons-box">
    <a href="javascript:void(0);" class="update-profile" title="{$lng.lbl_save}" style="display: none;" tabindex="-1">{$lng.lbl_save}</a>
    {* <a href="javascript:void(0);" class="restore-value" title="{$lng.lbl_restore}" style="display: none;" tabindex="-1"></a> *}
    {if $change_mode ne 'Y'}
        <a href="javascript:void(0);" class="edit-profile" title="{$lng.lbl_change}" tabindex="-1"></a>
    {else}
        <a href="javascript:void(0);" class="cancel-edit" title="{$lng.lbl_cancel}" tabindex="-1"></a>
    {/if}
</div>

