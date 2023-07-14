<?php
echo $this->Form->input('TemplateEvaluationUrl',array('type' => 'hidden','value' => Router::url(array('controller' => 'templates','action'=>'get'))));
?>

<script type="text/javascript">
$(document).ready(function() {

	function formatOption (option) {
    var $option = $(
      '<div><strong>' + option.text + '</strong></div><div>' + option.title + '</div>'
    );
    return $option;
  };


	$("select#TemplateDropdownSelect").select2({
		placeholder: {
			id: '0', // the value of the option
			text: 'Select an template'
		},
		allowClear: true,
    templateResult: formatOption
  });

	$("select#TemplateDropdownSelect").on("change", function() {

		json_request_load_animation();

	  var data = new Array();

	  data.push({name: "ajax_true", value: 1});
		data.push({name: "reportnumber_id", value: $("#ReportnumberId").val()});

	  $.ajax({
	    type	: "POST",
	    cache	: false,
	    url		: "templates/view/" + $(this).val(),
	    data	: data,
	    dataType: "json",
	    success: function(data) {

	    },
	    complete: function(data) {
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
