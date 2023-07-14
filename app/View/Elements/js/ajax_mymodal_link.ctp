<script type="text/javascript">
$(document).ready(function(e) {
    $("#dialog a.mymodal, #dialog .settingslink a").click(function() {

        var data = $(this).serializeArray();
        data.push({
            name: "ajax_true",
            value: 1
        });
        data.push({
            name: "dialog",
            value: 1
        });

        if ($(this).attr("href") !== "javascript:") {
            $.ajax({
                type: "POST",
                cache: false,
                url: $(this).attr("href"),
                data: data,
                success: function(data) {
                    $("#dialog").html(data);
                    $("#AjaxSvgLoader").hide();
                },
                statusCode: {
                    404: function() {
                        alert("page not found");
                        location.reload();
                    }
                },
                statusCode: {
                    403: function() {
                        alert("page blocked");
                        location.reload();
                    }
                }
            });
        }
        return false;
    });
});
</script>
