{*
0a663280c0dd87505274160aac2ed21e25c768c8, v6 (xcart_4_6_2), 2013-10-18 14:33:17, login_link.tpl, aim 
vim: set ts=2 sw=2 sts=2 et:
*}
<a href="{$authform_url}" title="{$lng.lbl_sign_in|escape}" {if not (($smarty.cookies.robot eq 'X-Cart Catalog Generator' and $smarty.cookies.is_robot eq 'Y') or ($config.Security.use_https_login eq 'Y' and not $is_https_zone))} onclick="javascript: return !popupOpen('login.php','');"{/if}{if $classname} class="{$classname|escape}"{/if} id="href_Sign_in">{$lng.lbl_sign_in}</a>
