/* vim: set ts=2 sw=2 sts=2 et: */

$(function () {

  $(ajax.messages).bind(
    'productAdded',
    function(e, data) {

      $('.ui-dialog-content').dialog('close').dialog('destroy').remove();

      var dialog = $(data.content).not('script');
      var dialogScripts = $(data.content).filter('script');

      dialog.dialog({

        autoOpen: false,
        dialogClass: "product-added",
        modal: true,
        title: data.title,
        width: 575,
        draggable: false,
        resizable: false,
        position:  {my : 'center center', at : 'center center'},
        closeOnEscape: true,

        close: function() {
          dialogScripts.remove();
        },

        open: function () {
          $(".product-added .view-cart").button();
          $(".product-added .continue-shopping").button().click(function () {
            dialog.dialog('close');
            return false;
          });
          $(".product-added .proceed-to-checkout").button().click(function () {
            dialog.dialog('close');
          });
          $('.ui-widget-overlay').click(function () {
            dialog.dialog('close');
          });
        }

      });

      dialogScripts.appendTo('body');
      dialog.dialog('open');

      ajax.widgets.products();
    }
  );

});
