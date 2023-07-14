<script type="text/javascript">
$(document).ready(function(){
	$("div.paging a").click(function() {

		var data = new Array();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "show_search_result", value: 1});

		$("#fast_searchresult_container").empty();
		$(".large_window").css("background-image","url(img/indicator.gif)");

		$.ajax({
			type: "POST",
			url: $(this).attr("href"),
			data: data,
			success:
				function(data) {

					$("#fast_searchresult_container").html(data);
					$("#fast_searchresult_container").show();
					$(".large_window").css("background-image","none");

				},
			});

		return false;
	});
});
</script>
