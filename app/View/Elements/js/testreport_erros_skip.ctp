<script type="text/javascript">
$(document).ready(function() {

	$("a.error_fields").click(function() {

		var data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: $(this).attr("name"), value: true});
		data.push({name: "data[rel_menue]", value: $(this).attr("rel")});
		data.push({name: "data[field_id]", value: $(this).attr("rev")});

 		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				$("#dialog").dialog('close');
			}
		});

		return false;
	});
});
</script>
