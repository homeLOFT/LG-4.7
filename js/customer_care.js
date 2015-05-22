$(document).ready(function () {

    $(".custcare-link").click(function (event) {
        event.preventDefault();
        $("#cc-modal").dialog({
            autoOpen: false,
            closeOnEscape: true,
            width: 'auto',
            height: 'auto',
            maxWidth: 2000,
            modal: true,
            fluid: true, //new option
            resizable: false,
            draggable: false,
            dialogClass: 'ccmodal',
            buttons: {
                "Close Window": function () {
                    $("#cc-modal").dialog("close");
                }
            }
        });

        $(".ui-dialog-titlebar").hide();

        $.get("/common/modal/pages/customer-care.html", function (html) {

            $("#cc-modal").html(html).dialog('open', {
                show: {
                    effect: 'slide',
                    duration: 1500
                },
                hide: {
                    effect: 'slide',
                    duration: 1500
                }
            });
        });
    });
});