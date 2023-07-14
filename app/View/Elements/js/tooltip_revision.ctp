<script type="text/javascript">
$(document).ready(function() {
	$("a.tooltip_ajax_revision").tooltip({
		content: function(callback,ui) {
			var element = $(this);
			var data = $(this).serializeArray();
			var modelfield = $(this).attr('id');
			var modelfield = modelfield.split("/");

			data.push({name: "model", value: modelfield[0]});
			data.push({name: "field", value: modelfield[1]});
			data.push({name: "ajax_true", value: 1});
			data.push({name: "dialog", value: 1});
			data.push({name: "layout", value: 1});
			data.push({name: "view", value: 1});
			data.push({name: "tooltip", value: 1});

			$.ajax({
					type	: "POST",
					cache	: false,
					url		: element.attr("href"),
					data	: data,
					beforeSend: function(data) {
						$(".modalarea").css("cursor","wait");
					},
					success: function(data) {
						$(".modalarea").css("cursor","inherit");
						callback(data);
					}
			});
		}
	});
});
</script>
