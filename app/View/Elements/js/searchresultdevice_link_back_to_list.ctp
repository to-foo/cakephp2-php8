<script type="text/javascript">
$(document).ready(function(){
	$("a.searchresultlinkdevices").click(function() {
	$("#AjaxSvgLoader").show();

	var data = new Array();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "show_result", value: 1});
	data.push({name: "historyback", value: 1});

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: $(this).attr("href"),
		data	: data,
		success: function(data) {
			$("#container").html(data);
			$("#container").show();
			$("#AjaxSvgLoader").hide();

			$("#searchresultlinkdevices").hide();
			$("#searchresultlinkdevices").css("visibility","hidden");
			}
		});

		return false;
	});
});
</script>
