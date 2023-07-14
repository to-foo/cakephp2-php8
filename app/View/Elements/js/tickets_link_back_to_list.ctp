<script type="text/javascript">
$(document).ready(function(){
	$("a.resultlinktickets").click(function() {

	$("#AjaxSvgLoader").show();

	var data = new Array();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "search_typ", value: $(this).attr("rel")});
	data.push({name: "history", value: $(this).attr("rev")});

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: $(this).attr("href"),
		data	: data,
		success: function(data) {
			$("#container").html(data);
			$("#container").show();
			$("#AjaxSvgLoader").hide();
			}
		});

		return false;
	});
});
</script>
