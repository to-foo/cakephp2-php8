<span id="sessionspan"></span>
<script type="text/javascript">
$(document).ready(function(){

	$("#content .reportnumbers .edit .specialchars")
		.attr("rel", "")
		.removeData("timeout")
		.off("click")
		.unbind("click")
		.on("click", function() {

			var modalheight = Math.ceil(($(window).height() * 90) / 100);
			var modalwidth = Math.ceil(($(window).width() * 90) / 100);

			var dialogOpts = {
				modal: false,
				width: modalwidth,
				height: modalheight,
				autoOpen: false,
				draggable: true,
				resizeable: true
			};

			$("#dialog").dialog(dialogOpts);

			$("#dialog").load($(this).attr("href"), {
				"ajax_true": 1,
				"thisID": $(this).attr("rel"),
			});
			$("#dialog").dialog("open");
			return false;
		});

	$(".reportnumbers input[type=\'text\'], .reportnumbers textarea").on({
		"focus": function() {
			$("#content .reportnumbers .edit .specialchars").attr("rel", $(this).attr("id"));
		}
	});
});
</script>
