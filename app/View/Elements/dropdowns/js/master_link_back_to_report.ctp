<?php
if (Configure::check('DropdownsManager') == false) {
    return;
}
if (Configure::read('DropdownsManager') == false) {
    return;
}
?>
<script type="text/javascript">
    $(document).ready(function() {

        $("#masterdropdownmanagment").css("visibility", "hidden");

        $("a#edit_master_dropdown").click(function() {

            $("#masterdropdownmanagment").show();
            $("#masterdropdownmanagment").css("visibility", "visible");
            $("#masterdropdownmanagment").attr("href", $("div.breadcrumbs a").last().attr("href"));

            $("#AjaxSvgLoader").show();

            var data = $(this).serializeArray();

            data.push({
                name: "ajax_true",
                value: 1
            });

            const uri = $(this).attr("href");

            $.ajax({
                type: "POST",
                cache: false,
                url: uri,
                data: data,
                success: function(data) {

                    $("#dialog").dialog().dialog("close");
			        $("#dialog").empty();
			        $("#dialog").css("overflow","inherit");
			        $("#dialog").dialog("destroy");

                    $('#container').html(data);
                    $("#container").show();
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


            return false;

        });

        $("a#masterdropdownmanagment").click(function() {

            $("#masterdropdownmanagment").css("visibility", "hidden");

        });

        $("a.dropdown_back_report").click(function() {

            $("#masterdropdownmanagment").show();
            $("#masterdropdownmanagment").css("visibility", "visible");
            //   $("#masterdropdownmanagment").addClass("ajax");
            $("#masterdropdownmanagment").attr("href", $(this).next("input").val());

        });
    });
</script>