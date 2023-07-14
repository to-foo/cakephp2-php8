<?php echo $this->Html->link(' ','javascript:',array('class' => 'memory_link advancemanager','id' => 'advancemanager', 'title' => __('Back to advance manager')));?>
<script type="text/javascript">
$(document).ready(function(){
	$("a.advancemanager").click(function() {

	$("#AjaxSvgLoader").show();

	var SchemeID = $(this).attr("rel");
	var SchemeUrl = $(this).attr("rev");
	var data = new Array();

	data.push({name: "ajax_true", value: 1});
	data.push({name: "search_typ", value: $(this).attr("rel")});
	data.push({name: "history", value: $(this).attr("rev")});

	$.ajax({
		type	: "POST",
		cache	: false,
		url		: $(this).attr("href"),
		data	: data,
		success: function(data) {

			$("#container").html(data);
			$("#container").show();
			$("#AjaxSvgLoader").hide();

			data = new Array();
			data.push({name: "ajax_true", value: 1});
			data.push({name: "data_area", value: "advance_area"});
	    data.push({name: "scheme_start", value: SchemeID});

			$("#AjaxSvgLoader").show();

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: SchemeUrl,
				data	: data,
				success: function(data) {
					$("#advances").html(data);
					$("#AjaxSvgLoader").hide();
					}
				});
			}
		});

		return false;
	});
});
</script>
