

<script type="text/javascript">
$(document).ready(function() {

	$("input.error_data_field").css("width","90%");
	$("input.error_data_field").css("margin-top","0.25em");

	$(".multi_errorselect_report").change(function() {

		if($(this).parent().find("input").val() == null){

		} else {
			$(this).parent().find("input").val($(this).val().join(","));
		}

		if($(this).parent().find("textarea").val() == null){

		} else {
			$(this).parent().find("textarea").val($(this).val().join("\n"));
		}

	});

	$("input.error_data_field").change(function() {

		$("input.error_data_field").parent().find("select.multi_errorselect_report option").attr('selected', false);

		this_string = $(this).val();

		if(this_string.length == 0){

			$(this_id).multiSelect("refresh");
			return false;

		}

		this_string = this_string.replace(" ", ",");
		this_string = this_string.replace(",,", ",");
		this_string = this_string.replace(" ", "");
		this_string = this_string.split(',');

		$(this).val(this_string);

		this_id = "#" + $(this).attr("id");
		$.each(this_string, function( index, value ) {

			if($("input.error_data_field").parent().find("select.multi_errorselect_report option[value="+value+"]").attr("selected") == "selected"){

			} else {
				$("input.error_data_field").parent().find("select.multi_errorselect_report option[value="+value+"]").attr("selected","selected");
			}
		});

		$(this_id).multiSelect("refresh");

		$("select" + this_id).parent().find("input").val($("select" + this_id).val().join(","));

	});
});
</script>
