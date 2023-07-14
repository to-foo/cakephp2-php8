<?php
echo $this->Form->input('TemplateUrl',array(
	'type' => 'hidden',
	'value' => $TemplateUrl
	)
);

echo $this->Form->create('Template', array('class' => 'login'));

foreach($this->request->data['Reportnumber'] as $key => $value){

	echo $this->Form->input($key,array(
		'type' => 'hidden',
		'value' => $value
		)
	);
}

echo $this->Form->end();
?>
<script type="text/javascript">
$(document).ready(function(){

	$("#AjaxSvgLoader").show();

	var data = $("#TemplateMassActionsForm").serializeArray();
	
	data.push({name: "ajax_true",value: 1});

	$.ajax({

		type	: "POST",
		cache	: false,
		url		: $("#TemplateUrl").val(),
		data	: data,
		success: function(data) {
			$("#dialog").html(data);
			$("#dialog").dialog("open");
			$("#dialog").show();
			$("#dialog").css('overflow','scroll');
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
