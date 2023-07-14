<script type="text/javascript">

$(document).ready(function(){

	$("#<?php echo $name;?>").bind("submit", function() {

		var data = $(this).serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "save_result", value: 1});

		$.ajax({
	    type	: "POST",
	    cache	: false,
			url		: $(this).attr("action"),
	    data	: data,
	    dataType: "json",
	    success: function(data) {
				json_request_animation(data.message);
	    },
	    complete: function(data) {
	      json_request_stop_animation();
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
