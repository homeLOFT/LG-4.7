{*
a166bad21114870eed6978591b0bd8e013fa70ea, v2 (xcart_4_6_4), 2014-05-15 21:21:01, xpc_iframe_content.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head></head>
<body>

{* Draws a place order form and submits it to payment_cc.php *}

<form name="xpc_form" id="xpc_form" action="{$action}" method="post">
    {foreach from=$fields key="name" item="value"}
        <input type="hidden" name="{$name}" value="{$value}" />
    {/foreach}
</form>

{* Ok, we're ready to place order now *}

<script type="text/javascript">
//<![CDATA[
  document.getElementById('xpc_form').submit();
//]]>
</script>

</body>
</html>
