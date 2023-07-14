
<?php echo $this->Form->input('AjaxLinkLargeWindowClassIdName',array('type' => 'hidden','value' => $name));?>

<script type="text/javascript">
$(document).ready(function(){

	function AjaxLinkLargeWindow(name){

		$(name).click(function() {

			$('div.module div.item').removeClass("active");
			$(this).parent('div.item').addClass("active");

			$("#large_window").empty();
			$("#large_window").css("background-image","url(img/indicator.gif)");

			var data = new Array();

			data.push({name: "ajax_true", value: 1});
			data.push({name: "landig_page_large", value: 1});

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: $(this).attr("href"),
				data	: data,
				success: function(data) {
					$("#large_window").html(data);
					$("#large_window").show();
				},
				complete: function(data) {
					$("#large_window").css("background-image","none");
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

	}

	AjaxLinkLargeWindow($("#AjaxLinkLargeWindowClassIdName").val());

});
</script>
