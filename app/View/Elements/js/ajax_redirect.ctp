<?php
if(!isset($url)) return;

echo $this->Form->input('RedirectAjaxUrl',array('type' => 'hidden','value' => $url));
?>
<script type="text/javascript">
$(document).ready(function(){

	$("#AjaxSvgLoader").show();

	var data = new Array();
	data.push({name: "ajax_true", value: 1});

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: $("#RedirectAjaxUrl").val(),
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
</script>
