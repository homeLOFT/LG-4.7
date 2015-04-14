{*
5d60b84b8ae181c9bbff34c0d4da94e3cb55901d, v4 (xcart_4_5_5), 2012-11-27 11:59:43, printable.tpl, aim
vim: set ts=2 sw=2 sts=2 et:
*}
<table cellspacing="0" cellpadding="0">
<tr>
  <td align="right" valign="middle"><a href="{$php_url.url|escape}?printable=Y{if $php_url.query_string ne ''}&amp;{$php_url.query_string|escape}{/if}" style="TEXT-DECORATION: underline;" rel="nofollow">{$lng.lbl_printable_version}&nbsp;</a></td>
  <td width="16" valign="middle"><a href="{$php_url.url|escape}?printable=Y{if $php_url.query_string ne ''}&amp;{$php_url.query_string|escape}{/if}" rel="nofollow"><img src="{$ImagesDir}/printer.gif" alt="" /></a></td>
</tr>
</table>
