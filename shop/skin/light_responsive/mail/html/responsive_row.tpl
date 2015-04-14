{*
a74fe6885509c651f2ee37e8b41267a193293cc7, v1 (xcart_4_7_0), 2015-02-27 17:35:59, responsive_row.tpl, aim

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
