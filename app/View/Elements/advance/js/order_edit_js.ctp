<script type="text/javascript">
$(document).ready(function() {

	function DeleteAdvance(data){

		if(data.Status == undefined) return;

		if(data.Status.update == "success") $(data.Status.row).hide();

		if(data.Status.update == "error") {
			alert(data.Status.message);
		}
	}

	function ReloadContainer(){

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
		data.push({name: "scheme_start", value: $("#StartId").val()});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $("#SchemeUrl").val(),
			data	: data,
			success: function(data) {
				$("#advances").html(data);
				$("#AjaxSvgLoader").hide();
			}
		});

	}

	function UpdateTable(data){

		if(data.Status == undefined) return;

		status = data.Status.update;
		message = data.Status.message;
		tr_row_id = "#advance_detail_" + data.AdvancesDataDependency.id;
		value = data.AdvancesDataDependency.value;
		status_value_class = tr_row_id + " td span.status_" + data.AdvancesDataDependency.value;

		if(status == "error"){
			alert(message);
			return;
		}

		if(value == 0) current_status = "deaktiv";
		if(value == 1) current_status = "okay";
		if(value == 2) current_status = "error";

		$(tr_row_id).removeAttr("class");
		$(tr_row_id + " td span").removeClass("deaktiv");
		$(tr_row_id + " td span").removeClass("okay");
		$(tr_row_id + " td span").removeClass("error");
		$(tr_row_id + " td span").addClass("empty");
		$(status_value_class).removeClass("empty");
		$(status_value_class).addClass(current_status);
	}

	$('.editable').editable(function(value, settings) {

			AdvanceId = $(this).attr("data-id");
			AdvanceField = $(this).attr("data-field");
			Field = "data[AdvancesDataDependency][" + AdvanceField + "]";

			data = new Array();

			data.push({name: "ajax_true", value: 1});
			data.push({name: "scheme_start", value: $("#StartId").val()});
			data.push({name: "json_false", value: 1});
			data.push({name: "data_area", value: "advance_area"});
			data.push({name: Field, value: value});
			data.push({name: "data[AdvancesDataDependency][id]", value: AdvanceId});

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: $("#EditUrl").val(),
				data	: data,
				dataType: "json",
				success: function(data) {
					var output;
					output = data.result
					return output;
				}
			});

	    return(value);
	}, {
	    type   : "textarea",
			onblur : "submit",
			placeholder : "Edit",
	});


	$("a.icon_delete").on('click', function (e) {

		bgcolor = $(this).closest("tr td").css("background-color");
		$(this).closest("tr td").css("background-color","#ff9b9b");

		check = confirm("<?php echo __('Should this value be deleted?',true);?>");

		if (check == false) {
			$(this).closest("tr td").css("background-color",bgcolor);
			return false;
		}

		$("#AjaxSvgLoader").show();

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "json_true", value: 1});
		data.push({name: "data_area", value: "advance_area"});
		data.push({name: "data[AdvancesData][id]", value: $(this).attr("rel")});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			dataType: "json",
			success: function(data) {
				DeleteAdvance(data);
				ReloadContainer();
				$("#AjaxSvgLoader").hide();
			}
		});

		$(this).closest("tr td").css("background-color",bgcolor);
		return false;

	});

	$("a.add_advance_point").on('click', function (e) {

		$("#AjaxSvgLoader").show();

		data = new Array();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "data_area", value: $("ul.editmenue li.active a").attr("rel")});
		data.push({name: "data[AdvancesDataDependency][scheme_start]", value: $("#StartId").val()});
		data.push({name: "data[AdvancesDataDependency][order_id]", value: $(this).attr("rel")});
		data.push({name: "data[AdvancesDataDependency][cascade_id]", value: $("#StartId").val()});

		$("#dialog").empty();

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
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
