<script type="text/javascript">
$(document).ready(function(){

	$("a.ajax_add_report").click(function() {

	$("#AjaxSvgLoader").show();

	var data = new Array();
	data.push({name: "ajax_true", value: 1});

	if($(this).hasClass("assignlink")) {

		data.push({name: "linked", value: 1});
		data.push({name: "setGenerally", value: $('.modalarea .hint input:checked').val()});

	} else if($(this).hasClass("repairreport")) {

		data.push({name: "data[Reportnumber][Generally]", value: 1});
		data.push({name: "data[Reportnumber][Specific]", value: 1});
		data.push({name: "data[Reportnumber][Evaluation]", value: 1});
		data.push({name: "data[repair]", value: 1});
		data.push({name: "data[repair_status]", value: 1});

	}

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: $(this).attr("href"),
		data	: data,
		success: function(data) {
			$("#container").html(data);
			$("#container").show();
			$("#AjaxSvgLoader").hide();
		},
		statusCode: {
	    404: function() {
	      alert( "page not found" );
				location.reload();
	    }
	  },
		statusCode: {
	    403: function() {
	      alert( "page blocked" );
				location.reload();
	    }
	  }
		});

		return false;
	});
});
</script>
