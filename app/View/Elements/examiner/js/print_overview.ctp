<script type="text/javascript">
$(document).ready(function() {

	$("#<?php echo $FormId;?>").bind("submit", function() {


		$("#AjaxSvgLoader").show();

		var data = $(this).serializeArray();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "json_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: this.getAttribute("action"),
			data	: data,
			dataType: "json",
			success: function(data) {
					$("#AjaxSvgLoader").hide();
					window.location.href = data.url;
				}
			});

		return false;

	});

	$("a.printlink").click(function() {
		window.open(
		  $(this).attr("href"),
		  "_blank"
		);
		return false;
	});
});
</script>
