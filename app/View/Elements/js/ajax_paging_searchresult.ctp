<script type="text/javascript">
$(document).ready(function(){
	$("div.paging a").click(function() {

	$("#AjaxSvgLoader").show();

	var data = $(this).serializeArray();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "search_typ", value: $("form#ReportsOrdersCase input#search_type").val()});
	data.push({name: "history", value: $("form#ReportsOrdersCase input#history").val()});
	
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
