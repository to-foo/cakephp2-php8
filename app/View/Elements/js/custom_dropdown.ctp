<script type="text/javascript">
$(document).ready(function(){
	/*
	<?php 
	/*
	echo join(PHP_EOL, array_map(function($e) {
			return '$("form.editreport select#'.$e.':not(:has(option[rel=\'custom\']))").append(\'$(<option value="&nbsp;" rel="custom">'.__('Custom value').'</option>)\');';
		}, $reportnumber['CustomDropdownFields']));
		*/
	?>

	$("select").on("change", function() {
		if($(this).find("option:selected").attr("rel")=="custom") {
			style = {
				"border": 0,
				"width": $(this).width() - 1,
				"height": $(this).height(),
				"position": "absolute",
				"top": $(this).position().top + 1,
				"left": $(this).position().left + 1
			};

			id = $(this).attr("id");
			name = $(this).attr("name");

			$(this).parent().append($('<input id="'+id+'" name="'+name+'" type="text" class="customValue" />').css(style).attr("rel", id)).find(".customValue").focusout(function() {
				rel = "#"+$(this).attr("rel");
				val = $(this).val().replace('"',"'");

				if(val != "") {
					$(this).parent().find(rel+' option:last-child').before('<option value="'+val+'">'+val+'</option>').parent().val(val);
				}
				else {
					$(rel).attr("disabled",false);
					$(rel).closest("div").css("background-image","none");
				}

				$(this).remove()
			}).focus();

		}
	});	
	*/
});
</script>