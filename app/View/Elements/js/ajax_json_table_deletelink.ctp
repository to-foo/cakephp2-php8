<script type="text/javascript">
$(document).ready(function(){


	function DeleteTableRow(data){

		if(data.StatusClass.length == 0) return false;
		if(data.StatusText.length == 0) return false;
		if(data.RemoveTableRow.length == 0) return false;

		if(data.StatusClass == "success"){
			statustext = "<span class='"+ data.StatusClass +"'>" + data.StatusText + "</span>";
			$("tr#" + data.RemoveTableRow + " td.value").html(statustext);
			setTimeout(function() {
				$("tr#" + data.RemoveTableRow).hide('slow', function(){ $("tr#" + data.RemoveTableRow).remove(); });
			}, 3000);
		}

		if(data.StatusClass == "error"){
			statustext  = $("tr#" + data.RemoveTableRow + " td.value").text() + " ";
			statustext += "<span class='"+ data.StatusClass +"'>" + data.StatusText + "</span>";
			$("tr#" + data.RemoveTableRow + " td.value").html(statustext);
		}
	}

	$("a.json_delete").click(function() {

	if($(this).hasClass("confirm")){

		if($(this).attr("title").length == 0) return false;

		confirmtext = $(this).attr("title");
		var check = confirm(confirmtext);
		if (check == false) return false;
		if (check == null) return false;

	}

	data = $(this).serializeArray();

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
				DeleteTableRow(data);
			}
		});
		return false;
	});
});
</script>
