{*
850e5138e855497e58a9e99e00c2e8e04e3f7234, v1 (xcart_4_4_0_beta_2), 2010-05-21 08:31:50, ups.tpl, joy
vim: set ts=2 sw=2 sts=2 et:
*}
<form name="noname" method="post" action="http://wwwapps.ups.com/tracking/tracking.cgi" target="_blank">
<input name="accept_UPS_license_agreement" type="hidden" value="yes"  />
<input name="nonUPS_title" type="hidden" value="" />
<input name="nonUPS_header" type="hidden" value="" />
<input name="nonUPS_body" type="hidden" value="" />
<input name="nonUPS_footer" type="hidden" value="" />
<input name="tracknum" type="hidden" value="{$order.tracking|escape}" />
<input type="submit" value="{$lng.lbl_track_it|strip_tags:false|escape}" />
<br />
{$lng.txt_ups_redirection}
</form>
