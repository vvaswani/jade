$( document ).ready(function() {

    $("#confirm-modal").on("show.bs.modal", function(e) {
        var link = $(e.relatedTarget);
        $(this).load(link.attr("href"));
    });

});
