<script type="text/javascript">
$(document).ready(function(){

	function RemoveTableRow(data){

		if(data.after_this_func){
			$("#" + data.after_this_func).hide('slow', function(){ $("#" + data.after_this_func).remove(); });
		}

	}

	$("a.json").click(function() {

		json_stop_animation();

		if($(this).hasClass("confirm_delete")){

			confirmtext = $(this).attr("data-confirm");
			var check = confirm(confirmtext);
			if (check == false) return false;
			if (check == null) return false;

		}


    data = new Array();

		ThisId = $(this).attr("data-id");
		ThisModel = $(this).attr("data-model");
    ThisField = $(this).attr("data-field");
		ThisValue = $(this).attr("data-value");
		Field = "data[" + ThisModel + "][" + ThisField + "]";

		data.push({name: "json_true", value: 1});
    data.push({name: "ajax_true", value: 1});
    data.push({name: Field, value: ThisValue});
    data.push({name: "data[" + ThisModel + "][id]", value: ThisId});

		if($(this).attr("data-value-function-after")) data.push({name: "data[" + ThisModel + "][after_this_func]", value: $(this).attr("data-value-function-after")});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			dataType: "json",
			success: function(data) {
				RemoveTableRow(data.data);
				json_request_animation(data.value);
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
