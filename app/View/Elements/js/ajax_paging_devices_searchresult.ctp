<script type = "text/javascript" >
$(document).ready(function() {
	$("div.paging a").click(function() {

		$("#AjaxSvgLoader").show();

		var devicesData = <?php echo json_encode($DeviceData); ?>;

		var data = $(this).serializeArray();
		data.push({
			name: "_method",
			value: "POST"
		});
		data.push({
			name: "ajax_true",
			value: 1
		});
		data.push({
			name: "show_result",
			value: 1
		});

		for (const [key, value] of Object.entries(devicesData["<?php echo $CurrentModel; ?>"])) {
			if(typeof(value) == 'object') {
				for (const [childkey, childvalue] of Object.entries(value)) {
					data.push({
						name: "data[<?php echo $CurrentModel; ?>][" + key + "][" + childkey + "]",
						value: childvalue
					});
				};

			}else{
				data.push({
					name: "data[<?php echo $CurrentModel; ?>][" + key + "]",
					value: value
				});
			}
		};
		$.ajax({
			type: "POST",
			cache: false,
			url: $(this).attr("href"),
			data: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				$("#AjaxSvgLoader").hide();
			},
			statusCode: {
				404: function() {
					alert("page not found");
					location.reload();
				}
			},
			statusCode: {
				403: function() {
					alert("page blocked");
					location.reload();
				}
			}
		});

		return false;
	});
});
</script>
