<script type="text/javascript">
$(document).ready(function() {

  $("select#ChangeModul").change(function() {

		$("#AjaxSvgLoader").show();

    url = $(this).val();
		data = new Array();

		data.push({name: "ajax_true", value: 1});

    $.ajax({
  		type	: "POST",
  		cache	: false,
  		url		: url,
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
