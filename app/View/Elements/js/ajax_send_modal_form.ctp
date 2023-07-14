<script type="text/javascript">
$(document).ready(function(){

	$("form.dialogform").bind("submit", function() {

		$("#AjaxSvgLoader").show();

		var data = $(this).serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("action"),
			data	: data,
			success : function(data) {
		    	$("#dialog").html(data);
		    	$("#dialog").show();
					$("#AjaxSvgLoader").hide();
			}
		});

		return false;
	});
});
</script>
