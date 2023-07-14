<script type="text/javascript">
$(function(){
	$("#<?php echo $FormId;?>").bind("submit", function() {

		$("#AjaxSvgLoader").show();

		var data = $(this).serializeArray();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "landig_page_large", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: this.getAttribute("action"),
			data	: data,
			success: function(data) {
				$("#large_window").html(data);
				$("#large_window").show();
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
