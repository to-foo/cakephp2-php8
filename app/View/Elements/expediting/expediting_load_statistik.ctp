<div id="suppliere_container">
<div class="statistic_container" id="statistic_1"></div>
<div class="statistic_container" id="diagramm_3"></div>
<div class="statistic_container" id="statistic_2"></div>
<div class="statistic_container" id="statistic_3"></div>
</div>
<script type="text/javascript">

$(document).ready(function(){

	function CssContainerShow(id) {
		$("#" + id).css("min-height","inherit");
		$("#" + id).css("background","inherit");
	}

	function CssContainerWait(id) {
		$("#" + id).css("min-height","40px");
		$("#" + id).css("background-image","url(img/indicator.gif)");
		$("#" + id).css("background-repeat","no-repeat");
		$("#" + id).css("background-position","center center");
		$("#" + id).css("background-size","auto 15px");
	}

	CssContainerWait("diagramm_1");
	CssContainerWait("diagramm_2");
	CssContainerWait("diagramm_3");
	CssContainerWait("statistic_1");
	CssContainerWait("statistic_2");
	CssContainerWait("statistic_3");

	var data = new Array();

	data.push({name: "ajax_true", value: 1});
	data.push({name: "case", value: 1});

	if($("#SupplierSupplier").length){data.push({name: "data[Statistic][supplier]", value: $("#SupplierSupplier").val()});}
	if($("#SupplierStatus").length){data.push({name: "data[Statistic][status]", value: $("#SupplierStatus").val()});}
	if($("#SupplierPlanner").length){data.push({name: "data[Statistic][planner]", value: $("#SupplierPlanner").val()});}
	if($("#SupplierAreaOfResponsibility").length){data.push({name: "data[Statistic][area_of_responsibility]", value: $("#SupplierAreaOfResponsibility").val()});}

	data.push({name: "diagramm", value: "Projekt"});

 	$.ajax({
		type	: "POST",
		data	: data,
		cache	: false,
		url		: "<?php echo Router::url(array_merge(array('action'=>'diagramm'), $this->request->projectvars['VarsArray']));?>",
		dataType: "html",
		success: function(data) {
			$("#diagramm_3").html(data);
			$("#diagramm_3").show();
			CssContainerShow("diagramm_3");
		},
	});

	$.ajax({
		type	: "POST",
		data	: data,
		cache	: false,
		url		: "<?php echo Router::url(array_merge(array('action'=>'statisticdetail'), $this->request->projectvars['VarsArray']));?>",
		dataType: "html",
		success: function(data) {
			$("#statistic_1").html(data);
			$("#statistic_1").show();
			CssContainerShow("statistic_1");
		},
	});

	data.push({name: "case", value: 2});

	$.ajax({
		type	: "POST",
		data	: data,
		cache	: false,
		url		: "<?php echo Router::url(array_merge(array('action'=>'statisticdetail'), $this->request->projectvars['VarsArray']));?>",
		dataType: "html",
		success: function(data) {
			$("#statistic_2").html(data);
			$("#statistic_2").show();
			CssContainerShow("statistic_2");
		},
	});

	data.push({name: "case", value: 3});

	$.ajax({
		type	: "POST",
		data	: data,
		cache	: false,
		url		: "<?php echo Router::url(array_merge(array('action'=>'statisticdetail'), $this->request->projectvars['VarsArray']));?>",
		dataType: "html",
		success: function(data) {
			$("#statistic_3").html(data);
			$("#statistic_3").show();
			CssContainerShow("statistic_3");
		},
	});

	$(".tooltip").tooltip({});
});

</script>
