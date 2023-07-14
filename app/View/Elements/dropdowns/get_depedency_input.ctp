<script type="text/javascript">
$(document).ready(function() {

	$("div.dependency_table").hide();

	if($("select.skip_to option").length == 2){
		$("select.skip_to option").each(function(key,val){
			if(key == 1){
				key = val.value;
				if(key.length > 0) $("div." + key).show();
			}
		});
	}

	function SaveDependencyInput(data){

		index = 0;
		key = Object.keys(data.DependencyFields)[index];
		value = data.DependencyFields[key];

		if(value.length == 0){
			$("div.append_from_json").remove();
			return false;
		}

		$("p.status_text").empty();
		$("p.status_text").html(data.StatusText);

		$("div.append_from_json").remove();

		inputfield  = '<div class="input text required append_from_json">';
		inputfield += '<label for="DropdownsMastersDataValue">' + value + ' (Add)</label>';
		inputfield += '<input name="data[DropdownsMastersDependency][value]" maxlength="255" type="text" value="" id="DropdownsMastersDependencyValue" required="required">';
		inputfield += '</div>';
		inputfield += '<input name="data[DropdownsMastersDependency][field]" maxlength="255" type="hidden" value="' + key + '" id="DropdownsMastersDependencyField">';

		$("fieldset.for_edit_dependencies").append($(inputfield));

		$("div.dependency_table").hide();
		$("div." + key).show();

	}

  $("select.skip_to").change(function() {

//		$("#AjaxSvgLoader").show();
		url = $(this).parents('form:first').attr("action");

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "json_true", value: 1});
		data.push({name: "DependencyFields", value: $(this).val()});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			dataType: "json",
			success: function(data) {
				SaveDependencyInput(data);
			}
			});

		return false;

	});

	$("select.skip_to_next").change(function() {

		$("#AjaxSvgLoader").show();

		url = $(this).val();

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		$.ajax({
			type	: "POST",
			cache	: false,
			url		: url,
			data	: data,
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").dialog("open");
				$("#dialog").show();
				$("#AjaxSvgLoader").hide();
			}
		});
	});
});
</script>
