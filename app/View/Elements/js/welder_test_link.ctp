<?php
$welder_url =  $this->Html->url(array_merge(array('controller'=>'welders','action'=>'overview'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('welder_url',array('type' => 'hidden','value' => $welder_url));
echo $this->Form->input('welder_id',array('type' => 'hidden','value' => $WelderId));
?>
<script type="text/javascript">
$(document).ready(function(){
	$("a.create_welder_test").click(function() {

		$("#weldermanagment").show();
		$("#weldermanagment").css("visibility","visible");

		$("#weldermanagment").attr("href",$("input#welder_url").val());

		var data = new Array();
		
		data.push({name: "ajax_true", value: 1});
		data.push({name: "welder_id", value: $("input#welder_id").val()});
	
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").show();
				$("#AjaxSvgLoader").hide(); 
			}
		});

		return false;
	});
});
</script>