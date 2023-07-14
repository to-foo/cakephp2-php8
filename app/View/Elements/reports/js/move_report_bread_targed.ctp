<script type="text/javascript">
$(document).ready(function(){

	$('#ReportnumberMoveForm input[type="submit"]').hide();

	function changeCascadeTargedBreadcrump(data){

		$("div.targed_breadcrumb span.warning").empty().append("<?php echo __('To',true);?> " + data.TargedCascadeTree.bread);

		$("a.move_navigation").one("click", function() {

			$("div.flash_error").remove();
			$('#ReportnumberMoveForm input[type="submit"]').hide();

			var data = new Array();

			$("#ReportnumberMoveForm select#ReportnumberOrderId").removeClass('activate_animation');
			$("#ReportnumberMoveForm select#ReportnumberReportId").removeClass('activate_animation');
			$("#ReportnumberMoveForm select#ReportnumberOrderId").prop("disabled",true);
			$("#ReportnumberMoveForm select#ReportnumberReportId").prop("disabled",true);

			data.push({name: "data[Reportnumber][cascade_id]", value: $(this).attr("data-cascade")});
			data.push({name: "data[Reportnumber][order_id]", value: 0});
			data.push({name: "data[Reportnumber][report_id]", value: 0});
			data.push({name: "ajax_true", value: 1});
			data.push({name: "dialog", value: 1});
			data.push({name: "current", value: 1});
			data.push({name: "json_true", value: 1});

			$("#ReportnumberMoveForm select#ReportnumberOrderId option").removeAttr('selected');
			$("#ReportnumberMoveForm select#ReportnumberReportId option").removeAttr('selected');

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: $("#ReportnumberMoveForm").attr('action'),
				data	: data,
				dataType: "json",
				success: function(data) {
					changeCascadeMoveInput(data);
					changeCascadeTargedBreadcrump(data);
				}
			});

			return false;

		});

	}

	function changeCascadeMoveInput(data) {

		if(!data.CascadeList) return false;

		$("#ReportnumberMoveForm select#ReportnumberCascadeId").empty().append('<option value=""></option>');

		$("#ReportnumberOrderId").parent("div").show();

		$.each(data.CascadeList, function(index,value){

			$("#ReportnumberMoveForm select#ReportnumberCascadeId").append('<option value="' + value.key + '">' + value.value + '</option>');

		});

		$("#ReportnumberMoveForm select#ReportnumberCascadeId option[value='" + data.Cascade.id + "']").attr('selected',true);

		$("#ReportnumberMoveForm select#ReportnumberCascadeId").addClass('activate_animation');

		if(data.Orders && data.Orders.length > 0) $("#ReportnumberMoveForm select#ReportnumberCascadeId").removeClass('activate_animation');
		if(data.order_deactive && data.order_deactive == 1) $("#ReportnumberMoveForm select#ReportnumberCascadeId").removeClass('activate_animation');

	}

	function changeOrderMoveInput(data) {

		if(!data.Orders) return false;
		if(data.Orders.length == 0) return false;

		$("#ReportnumberOrderId").parent("div").show();

		$("#ReportnumberMoveForm select#ReportnumberCascadeId option[value='" + data.Cascade.id + "']").attr('selected',true);

		$("#ReportnumberMoveForm select#ReportnumberOrderId").removeClass('activate_animation');
		$("#ReportnumberMoveForm select#ReportnumberOrderId").prop("disabled",true);
		$("#ReportnumberMoveForm select#ReportnumberOrderId").empty().append('<option value=""></option>');

		$.each(data.Orders, function(index,value){

			$("#ReportnumberMoveForm select#ReportnumberOrderId").append('<option value="' + value.key + '">' + value.value + '</option>');

		});

		$("#ReportnumberMoveForm select#ReportnumberOrderId").addClass('activate_animation');
		$("#ReportnumberMoveForm select#ReportnumberOrderId").prop("disabled",false);

		if(!data.Order) return false;

		$("#ReportnumberMoveForm select#ReportnumberOrderId option[value='" + data.Order.id + "']").attr('selected',true);
		$("#ReportnumberMoveForm select#ReportnumberOrderId").removeClass('activate_animation');

	}

	function changeReportMoveInput(data) {

		if(!data.Reports) return false;

		$("#ReportnumberMoveForm select#ReportnumberCascadeId option[value='" + data.Cascade.id + "']").attr('selected',true);
		$("#ReportnumberMoveForm select#ReportnumberOrderId option[value='" + data.Order.id + "']").attr('selected',true);

		$("#ReportnumberMoveForm select#ReportnumberReportId").removeClass('activate_animation');
		$("#ReportnumberMoveForm select#ReportnumberReportId").prop("disabled",true);
		$("#ReportnumberMoveForm select#ReportnumberReportId").empty().append('<option value=""></option>');

		$.each(data.Reports, function(index,value){

			$("#ReportnumberMoveForm select#ReportnumberReportId").append('<option value="' + value.key + '">' + value.value + '</option>');

		});

		$("#ReportnumberMoveForm select#ReportnumberReportId").addClass('activate_animation');
		$("#ReportnumberMoveForm select#ReportnumberReportId").prop("disabled",false);

		if(!data.Report) return false;

		$("#ReportnumberMoveForm select#ReportnumberReportId option[value='" + data.Report.id + "']").attr('selected',true);
		$("#ReportnumberMoveForm select#ReportnumberReportId").removeClass('activate_animation');

		$('#ReportnumberMoveForm input[type="submit"]').fadeIn("slow", function(){});

	}

	function changeOrderActiveDeactive(data){

		if(!data.order_deactive) return false;
		if(data.order_deactive != 1) return false;

		$("#ReportnumberOrderId").parent("div").hide();

	}

	function printMessages(data){

		if(!data.Messages) return false;

		$.each(data.Messages, function(index,value){

			if(value.type == "error"){

				Mess = '<div id="flash_error" class="flash_error message_info"><span class="error">' + value.message + '</span></div>';
				$('div.message_info:last').after(Mess);

			}

		});

	}

	function loadMovedReport(data) {

		if(!data.FormName.url) return false;

		var Url = data.FormName.url;

		$("#AjaxSvgLoader").show();

		var data = new Array();

		data.push({name: "ajax_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: Url,
			data	: data,
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
				$("#AjaxSvgLoader").hide();
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

		$("#dialog").dialog().dialog("close");
		$("#maximizethismodal").hide();

	}

	$("#ReportnumberMoveForm select").change(function() {

		$("div.flash_error").remove();

		var data = $("#ReportnumberMoveForm").serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});
		data.push({name: "current", value: 1});
		data.push({name: "json_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: $("#ReportnumberMoveForm").attr('action'),
			data	: data,
			dataType: "json",
			success: function(data) {
				changeCascadeMoveInput(data);
				changeOrderMoveInput(data);
				changeReportMoveInput(data);
				changeCascadeTargedBreadcrump(data);
				changeOrderActiveDeactive(data);
				printMessages(data);
			}
		});

		return false;
	});

	$(".submit").click(function() {

		var data = $("#ReportnumberMoveForm").serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});
		data.push({name: "current", value: 1});
		data.push({name: "save", value: 1});
		data.push({name: "json_true", value: 1});

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: $("#ReportnumberMoveForm").attr('action'),
			data	: data,
			dataType: "json",
			success: function(data) {
				loadMovedReport(data);
			}
		});
		return false;
	});
});
</script>
