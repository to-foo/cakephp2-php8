<script type="text/javascript">
$(document).ready(function(){

	function SetMessage(data){

		if(data.StatusClass.length == 0) return false;
		if(data.StatusText.length == 0) return false;
		if(data.RemoveTableRow.length == 0) return false;

		if(data.StatusClass == "success"){
			statustext = "<span class='"+ data.StatusClass +"'>" + data.StatusText + "</span>";
			$("tr#" + data.RemoveTableRow + " td.value").html(statustext);
			$("tr#" + data.RemoveTableRow + " td a.json_edit").attr("json-data",$("tr#" + data.RemoveTableRow + " td.value").text());

			setTimeout(function() {
				$("tr#" + data.RemoveTableRow + " td.value span").removeClass(data.StatusClass);
			}, 3000);
		}
		if(data.StatusClass == "hint" || data.StatusClass == "error"){
			statustext = "<span class='"+ data.StatusClass +"'>" + data.StatusText + "</span>";
			value = $("tr#" + data.RemoveTableRow + " td.value").text();
			$("tr#" + data.RemoveTableRow + " td.value").html(value + " " + statustext);

			setTimeout(function() {
				$("tr#" + data.RemoveTableRow + " td.value span").remove();
			}, 3000);
		}
	}

	$("a.json_edit").click(function() {

		if($(this).attr("title").length == 0) return false;

		check = prompt($(this).attr("title"), $(this).attr("json-data"));

		if(check == $(this).attr("json-data")) return false;
		if(check == null) return false;
		if(check.length == 0) return false;

		data = $(this).serializeArray();

		data.push({name: "json_value_old", value: $(this).attr("json-data")});
		data.push({name: "json_value", value: check});
		data.push({name: "ajax_true", value: 1});
		data.push({name: "json_true", value: 1});
		data.push({name: "dialog", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			dataType: "json",
			success: function(data) {
				SetMessage(data);
			}
		});

		return false;
	});
});
</script>
