<script type="text/javascript">
$(document).ready(function() {
    $("a.ajax").click(function() {

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
});
</script>