<script type="text/javascript">
$(document).ready(function(){
	$("a.showtestingareas").click(function() {

	$("#AjaxSvgLoader").show(); 

	$(".ui-dialog").show();
	$("#dialog").dialog().dialog("close");
	$("#maximizethismodal").hide();

	var modalheight = Math.ceil(($(window).height() * 90) / 100);
	var modalwidth = Math.ceil(($(window).width() * 90) / 100);

	var dialogOpts = {
		modal: false,
		width: modalwidth,
		height: modalheight,
		autoOpen: false,
		draggable: true,
		resizeable: true
	};

	$("#dialog").dialog(dialogOpts);

	var data = $(this).serializeArray();
	data.push({name: "ajax_true", value: 1});
	data.push({name: "dialog", value: 1});
	data.push({name: "layout", value: 2});
	data.push({name: "result_url", value: "<?php echo $url;?>"});

	$("#dialog").empty();

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: $(this).attr("href"),
		data	: data,
		success: function(data) {
			$("#dialog").html(data);
			$("#dialog").dialog("open");
			$("#dialog").show();
			$("#AjaxSvgLoader").hide(); 
			}
		});
	
		return false;
	});
});
</script>
