{*
e94f191a2cc0cb8dbefd384fabaa6f48af5f3c11, v1 (xcart_4_6_2), 2013-12-25 09:14:58, responsive_row.tpl, random
vim: set ts=2 sw=2 sts=2 et:
*}
<table class="block-grid{if $block3 ne ''} three-up{elseif $block2 ne ''} two-up{else} one-up{/if} {$class}">
  <tr>
    <td>
      {$block1|default:$content}
    </td>{if $block2 ne ''}<td>
      {$block2}
    </td>{/if}{if $block3 ne ''}<td>
      {$block3}
    </td>{/if}
  </tr>
</table>
