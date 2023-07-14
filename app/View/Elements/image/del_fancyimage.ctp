<?php
echo $this->Form->input('ThisImageDeleteUrl',array('type' => 'hidden','value' => Router::url(array('action'=>'delimage'))));
echo $this->Form->input('ThisImageDeleteConfirm',array('type' => 'hidden','value' => __('Do you want to delete this picture?',true)));
echo $this->Form->input('ThisImageDeleteErrorText',array('type' => 'hidden','value' => __('Failed to delete the image',true)));
?>
<script type="text/javascript">

$("li.image span a span.imagebox").click(function() {

	var r = confirm($("#ThisImageDeleteConfirm").val());
	var data = new Array();
	data.push({name: "ajax_true", value: 1});

	var parm = $(this).parent().attr("rev");
	url	= $("#ThisImageDeleteUrl").val();

	if(r == true) {
		$.ajax({
			type	: "POST",
			data	: data,
			cache	: false,
			url		: url + "/" + parm,
			dataType: "json",
			success: function(data) {
				if(data.image == 0) alert($("ThisImageDeleteErrorText").val());
				if(data.image != 0) $(data.image).remove();
			},
		});
	} else {

	}

	return false;
});
</script>
