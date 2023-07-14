<script type="text/javascript">
$(document).ready(function(){

$("a.ajax_show_examiner").on( "click", function() {

	$("#AjaxSvgLoader").show();

	var data = new Array();

	data.push({name: "ajax_true", value: 1});

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
