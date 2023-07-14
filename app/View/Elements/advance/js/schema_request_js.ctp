<?php
if(!isset($JSONName['parameter']['cascade_id'])) return;
echo $this->Form->input('SchemeStart',array('type' => 'hidden','value' => $JSONName['parameter']['cascade_id']));
?>
<script type="text/javascript">

$(document).ready(function() {

		$("#AjaxSvgLoader").show();

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
    data.push({name: "scheme_start", value: $("#SchemeStart").val()});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#SchemeUrl").val(),
			data	: data,
			success: function(data) {
				$("#advances").html(data);
				$("#AjaxSvgLoader").hide();
			}
		});

});

</script>
