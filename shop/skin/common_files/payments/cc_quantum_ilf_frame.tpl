{*
a6180bf9db8e09a234098b9025d9862a11668382, v2 (xcart_4_5_3), 2012-09-27 13:25:37, cc_quantum_ilf_frame.tpl, random 
vim: set ts=2 sw=2 sts=2 et:
*}
{include file='payments/iframe_common.tpl' iframe_src=$ilf_src cancel_url=$cancel_url height='500' width='800'}

<script type="text/javascript">
//<![CDATA[
{literal}
function refreshSession(k, ip) {
	if (!k || !ip)
		return false;

  var post_url = 'cc_quantum_ilf.php?frame_refresh=' + Math.random();

  var data = {
    ip: ip,
    k: k
  };

	var request = {
    type: 'POST',
    url: post_url,
    data: data
  };

	return ajax.query.add(request)
}
{/literal}

setInterval("refreshSession('{$ilf_key}', '{$ilf_ip}')", 20000);
//]]>
</script>
