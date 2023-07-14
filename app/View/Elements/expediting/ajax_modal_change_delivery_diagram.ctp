<script type="text/javascript">
$(document).ready(function(){
	$("select#change_diagramm_view").change(function() {

		url = $("#ThisRequstUrl").val();

		$("#AjaxSvgLoader").show();

		var data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});
		data.push({name: "case", value: $(this).val()});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
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
