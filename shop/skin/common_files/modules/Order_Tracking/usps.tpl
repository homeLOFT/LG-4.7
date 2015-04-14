{*
21c5f1bfd4929f147efc293bb4d7c092d4871a89, v2 (xcart_4_6_2), 2014-01-22 20:17:56, usps.tpl, mixon
vim: set ts=2 sw=2 sts=2 et:
*}
<form action="http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do" method="get" name="getTrackNum" id="getTrackNum" target="_blank">
<input type="hidden" id="strOrigTrackNum" name="strOrigTrackNum" value="{$order.tracking|escape}" />
<input type="submit" value="{$lng.lbl_track_it|strip_tags:false|escape}" />
<br />
{$lng.txt_usps_redirection}
</form>
