<script type="text/javascript">
$(document).ready(function() {

  $("a.sendmail").click(function() {

    data = $(this).parents('form:first').serializeArray();
    data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: $("ul.editmenue li.active a").attr("rel")});

    element = '<div id="flash_warning" class="message_info"><span class="progress">Email sending in progress</span></div>';

    $("#SendReportMailResponse").empty();
    $("#SendReportMailResponse").html(element);

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				  $("#SendReportMailResponse").html(data);
				}
			});

		return false;

	});
});
</script>
