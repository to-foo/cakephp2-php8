<script type="text/javascript">
$(function(){
	$("#<?php echo $FormId;?>").bind("submit", function() {

		$("#AjaxSvgLoader").show();

		var data = $(this).serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "show_result", value: 1});
		data.push({name: "resort", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: this.getAttribute("action"),
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
