<script type="text/javascript">
$(document).ready(function() {


  $("a.close_after_printing").click(function() {

    data = new Array();

    data.push({name: "ajax_true", value: 1});

    $.ajax({
  		type	: "POST",
  		cache	: false,
  		url		: $("#CurrentUrl").val(),
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

	});
});
</script>
