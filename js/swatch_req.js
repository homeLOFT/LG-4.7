$(document).ready(function () {

    $(".swatchreq-link").click(function (event) {
        event.preventDefault();
        $("#sr-modal").dialog({
            autoOpen: false,
            closeOnEscape: true,
            width: 'auto',
            height: 'auto',
            maxWidth: 2000,
            modal: true,
            fluid: true, //new option
            resizable: false,
            draggable: false,
            dialogClass: 'srmodal',
            buttons: {
                "Close Window": function () {
                    $("#sr-modal").dialog("close");
                }
            }
        });

        $(".ui-dialog-titlebar").hide();

        $.get("/common/modal/pages/request-swatches.html", function (html) {

            $("#sr-modal").html(html).dialog('open', {
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

function swatchTitle(title){
		document.getElementById("swatchTitle").innerHTML = title;
}