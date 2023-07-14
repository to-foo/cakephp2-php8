<script>
$(document).ready(function () {

		$("div.images ul").sortable({
			start: function(event,ui){
			},
			stop: function(event,ui){

				var data = new Array();

				$("div.images ul li").each(function(key,value){

					data.push({name: "data[Reportimage][sorting][" + $(this).attr("data-sort") + "]", value: key});

				});

				data.push({name: "image_sorting", value: 1});
				data.push({name: "ajax_true", value: 1});
				data.push({name: "json_true", value: 1});

				$.ajax({
					type	: "POST",
					cache	: false,
					url		: "<?php echo $url;?>",
					data	: data,
					dataType: "json",
					success: function(data) {
						}
					});

			}
		});

});
</script>
