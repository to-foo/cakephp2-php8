<script type="text/javascript">
$(document).ready(function(){

	function CssContainerWait(id) {
		$("#" + id).empty();
		$("#" + id).css("min-height","40px");
		$("#" + id).css("background-image","url(img/indicator.gif)");
		$("#" + id).css("background-repeat","no-repeat");
		$("#" + id).css("background-position","center center");
		$("#" + id).css("background-size","auto 15px");
	}


	var data = new Array();
	data.push({name: "ajax_true", value: 1});

	if($("#SupplierSupplier").length){data.push({name: "data[Statistic][supplier]", value: $("#SupplierSupplier").val()});}
	if($("#SupplierStatus").length){data.push({name: "data[Statistic][status]", value: $("#SupplierStatus").val()});}
	if($("#SupplierOrdered").length){data.push({name: "data[Statistic][ordered]", value: $("#SupplierOrdered").val()});}
	if($("#SupplierPlanner").length){data.push({name: "data[Statistic][planner]", value: $("#SupplierPlanner").val()});}
	if($("#SupplierAreaOfResponsibility").length){data.push({name: "data[Statistic][area_of_responsibility]", value: $("#SupplierAreaOfResponsibility").val()});}


	$("a.expediting_print").on( "click", function() {

		url = "<?php echo Router::url(array_merge(array('action'=>'savepostdata'), $this->request->projectvars['VarsArray']));?>";
		pdf = "<?php echo Router::url(array_merge(array('action'=>'pdf'), $this->request->projectvars['VarsArray']));?>";

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: url,
			data	: data,
			success: function(data) {
//				window.open(pdf, '_blank');
				document.location.href = pdf;
			}
		});

		return false;

	});

	$("a.expediting_navi").on( "click", function() {

			CssContainerWait("container");

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: $(this).attr("href"),
				data	: data,
				success: function(data) {
					$("#container").html(data);
					$("#container").show();
				}
			});

		return false;
	});

	$("a.mail_status").on( "click", function() {

			data.push({name: "control", value: "mailstatus"});

			$.ajax({
				type	: "POST",
				cache	: false,
				url		: $(this).attr("href"),
				data	: data,
				dataType: "json",
				success: function(data) {
					if(data.Supplier.save == 1){
						console.log(data.Supplier)
						$("#mail_status_" + data.Supplier.id).removeClass(data.Supplier.remove_class).addClass(data.Supplier.add_class);
						$("#mail_status_" + data.Supplier.id).attr("title",data.Supplier.title);
					}
				}
			});

		return false;
	});
/*
	$("select#childcascade").change(function() {

			CssContainerWait("container");

			var project_id = "<?php echo $this->request->projectvars['VarsArray'][0];?>";
			var controller = "<?php echo $ExpeditingSelect['controller'];?>";
			var action = "<?php echo $ExpeditingSelect['action'];?>";
			var url = controller+"/"+action+"/"+project_id+"/"+$(this).val();

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: url,
				data	: data,
				success: function(data) {
					$("#container").html(data);
					$("#container").show();
				}
			});
		return false;
	});

	$("select.filter").change(function() {

			CssContainerWait("suppliere_container");

			var project_id = "<?php echo $this->request->projectvars['VarsArray'][0];?>";
			var cascade_id = "<?php echo $this->request->projectvars['VarsArray'][1];?>";
			var controller = "<?php echo $ExpeditingSelect['controller'];?>";
			var action = "<?php echo $ExpeditingSelect['action'];?>";
			var data = new Array();
			var url = controller+"/"+action+"/"+project_id+"/"+cascade_id;

			data.push({name: "ajax_true", value: 1});
			data.push({name: "refresh_supplier_index_filter", value: 1});

			if($("#supplier").val().length > 0) data.push({name: "data[Statistic][supplier]", value: $("select#supplier :selected").text()});
			if($("#status").val().length > 0) data.push({name: "data[Statistic][status]", value: $("select#status").val()});
			if($("#ordered").val().length > 0) data.push({name: "data[Statistic][ordered]", value: $("select#ordered").val()});
			if($("#planner").val().length > 0) data.push({name: "data[Statistic][planner]", value: $("select#planner :selected").text()});
			if($("#area_of_responsibility").val().length > 0) data.push({name: "data[Statistic][area_of_responsibility]", value: $("select#area_of_responsibility :selected").text()});

			$.ajax({
				type	: "POST",
				cache	: true,
				url		: url,
				data	: data,
				success: function(data) {
					$("#container").html(data);
					$("#container").show();
				}
			});
		return false;
	});
*/

	$("a#time_statistik").on( "click", function() {

		$("#AjaxSvgLoader").show();

		$(".ui-dialog").show();
		$("#dialog").dialog().dialog("close");
		$("#maximizethismodal").hide();

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

		var data = $(this).serializeArray();

		if($("#supplier").val().length > 0) data.push({name: "data[Statistic][supplier]", value: $("select#supplier :selected").text()});
		if($("#status").val().length > 0) data.push({name: "data[Statistic][status]", value: $("select#status").val()});
		if($("#ordered").val().length > 0) data.push({name: "data[Statistic][ordered]", value: $("select#ordered").val()});
		if($("#planner").val().length > 0) data.push({name: "data[Statistic][planner]", value: $("select#planner :selected").text()});
		if($("#area_of_responsibility").val().length > 0) data.push({name: "data[Statistic][area_of_responsibility]", value: $("select#area_of_responsibility :selected").text()});

		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});
		data.push({name: "case", value: 2});

		$("#dialog").empty();

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("href"),
			data	: data,
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").dialog("open");
				$("#dialog").show();
				$("#AjaxSvgLoader").hide();
				}
			});

		return false;
	});

});
</script>
