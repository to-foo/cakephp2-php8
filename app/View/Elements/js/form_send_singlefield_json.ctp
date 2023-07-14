<script type="text/javascript">
$(function(){

	$(".send_json_single").change(function() {

		var form = $(this).parents('form:first');
		var data = form.serializeArray();
		var url = form.attr("action");

		data.push({name: "ajax_true", value: 1});
		data.push({name: "json_true", value: 1});

		console.log(url);

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			dataType: 'json',
			success: function(data) {
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
