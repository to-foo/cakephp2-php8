<script type="text/javascript">
$(document).ready(function() {

  $("select#ChangeModulChangeModul").change(function() {

		$("#AjaxSvgLoader").show();

    url = $(this).val();
		data = new Array();

		data.push({name: "ajax_true", value: 1});
    data.push({name: "modul", value: $("#ChangeModulModul").val()});
    data.push({name: "data_area", value: $("#ChangeModulDataArea").val()});
    data.push({name: "scheme_start", value: $("#ChangeModulSchemeStart").val()});

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
