{*
9244a952cd048dfaea0c677c21a2ecf6686e43e6, v2 (xcart_4_6_2), 2013-12-25 09:44:00, html_message_template.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width" />

    {include file="lib/ink/ink_css.tpl"}
    {include file="mail/html/html_message_template_css.tpl"}
  </head>
  <body{$reading_direction_tag}>
    <table class="body">
      <tr>
        <td class="center" align="center" valign="top">
          <center>
            <table class="container">
              <tr>
                <td>
{if $mail_body_template}
{include file=$mail_body_template}
{/if}
                </td>
              </tr>
            </table>
          </center>
        </td>
      </tr>
    </table>
  </body>
</html>
