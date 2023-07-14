<script type="text/javascript">
$(document).ready(function() {

    $("#dialog button#CloseDialogForm").click(function() {

        if ($(".ui-dialog").is(":visible")) {
            $("#dialog").dialog().dialog("close");
        }

        return false;

    });

    $("#dialog button#CancelResetForm").click(function() {
        $(this).closest("form")[0].reset();
    });

    $("#dialog button#CancelLoadLastPage").click(function() {

        if ($("#UrlLoadLastPage").length == 0) return false;

        var data = new Array;
        data.push({
            name: "ajax_true",
            value: 1
        });
        data.push({
            name: "dialog",
            value: 1
        });

        $.ajax({
            type: "POST",
            cache: false,
            url: $("#UrlLoadLastPage").val(),
            data: data,
            success: function(data) {
                $("#dialog").html(data);
                $("#dialog").dialog("open");
                $("#dialog").show();
            }
        });

        return false;
    });
});
</script>