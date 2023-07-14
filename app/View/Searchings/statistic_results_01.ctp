<?php
//return;
/*
$Methots = array();
pr($Testingmethods);
foreach($List as $_key => $_data){
	foreach($_data as $__key => $__data){ 
		foreach($__data['result'] as $___key => $___data){
			$Methots[$___key] = $Testingmethods[$___key];
		}
	}
}
*/
?>
<?php echo $this->Form->create('Generally',array('id' => 'GenerallySearchForm','target' => '_blank','class'=>'search_form')); ?>
<?php
echo $this->Form->input('ReportnumberTestingmethod',array(
			'class' => 'dropdown statistic_filter',
			'id' => 'ReportnumberTestingmethod',
			'name' => 'data[Reportnumber][testingmethod]',
			'options' => $Testingmethods, 
			'selected' => $this->request->data['Reportnumber']['testingmethod'], 
			'label' => false,
			'div' => false
		)
);
echo $this->Form->input('ReportnumberYears',array(
			'class' => 'dropdown statistic_filter',
			'id' => 'ReportnumberYears',
			'name' => 'data[Reportnumber][years]',
			'options' => $Years, 
			'label' => false,
			'div' => false,
			'empty' => count($Years) > 1 ? ' ' : false
		)
);
echo $this->Form->input('ReportnumberMonths',array(
			'class' => 'dropdown statistic_filter',
			'id' => 'ReportnumberMonths',
			'name' => 'data[Reportnumber][months]',
			'options' => $Months, 
			'label' => false,
			'div' => false,
			'empty' => ' '
		)
);

echo $this->Form->button(__('Overview',true),array(
			'id' => 'WeldingOverview',
			'type' => 'button', 
			'class' => 'change_graph submit_graph inactive', 
			'disabled' => 'disabled'
		)
);
echo $this->Form->button(__('Welding mistakes',true),array(
			'id' => 'WeldingMistake',
			'type' => 'button',
			'class' => 'change_graph submit_graph'
		)
);
if(isset($this->request->data['Generally']['welding_company']) && $this->request->data['Generally']['welding_company'] > 0){
	echo $this->Form->button(__('Welder overview',true),array(
			'id' => 'WelderOverview',
			'type' => 'button',
			'class' => 'change_graph submit_graph'
		)
	);
}
if(isset($this->request->data['history']) && $this->request->data['history'] > 0){
	echo $this->Form->input('history',array(
			'id' => 'history', 
			'name' => 'history', 
			'type' => 'hidden', 
			'value' => $this->request->data['history']
		)
	);
}
?>
<?php echo $this->Form->end(); ?>
<?php
echo $this->Html->link('Print',array_merge(array('action' => 'pdf'),$this->request->projectvars['VarsArray']),
				array(
					'id' => 'ExportPDF',
					'class' => 'icon icon_print', 
					'title' => __('Download statistic as pdf file',true), 
					'target' => '_blank'
					)
			);
echo $this->Html->link('Export',array_merge(array('action' => 'csv'),$this->request->projectvars['VarsArray']),
				array(
					'id' => 'ExportCSV',
					'class' => 'icon icon_export', 
					'title' => __('Download statistic as csv file',true), 
					'target' => '_blank'
					)
			);

?>
<script type="text/javascript">
$(document).ready(function(){

	function CssContainerShow(id) {
		$("#" + id).css("min-height","inherit");
		$("#" + id).css("background","inherit");
	}

	function CssContainerWait(id) {
		heigth = $("#" + id).height();
		$("#" + id).empty();
		$("#" + id).css("min-height",heigth + "px");
		$("#" + id).css("background-image","url(img/indicator.gif)");
		$("#" + id).css("background-repeat","no-repeat");
		$("#" + id).css("background-position","center center");
		$("#" + id).css("background-size","auto 15px");
	}
	
	$("button#ShowSearchForm").click(function() {

		var data = new Array();
		data.push({name: "ajax_true", value: 1});
		
//		CssContainerWait("container");

		$.ajax({
			type	: "POST",
			data	: data,
			cache	: false,
			url		: "<?php echo Router::url(array_merge(array('action'=>'statistic'), $this->request->projectvars['VarsArray']));?>",
			dataType: "html",
			success: function(data) {
				$("#container").html(data);
				$("#container").show();
			},
		});
	
	});
	
	$("button.submit_graph").click(function() {

		CssContainerWait("diagramm_1");

		$("button.submit_graph").prop("disabled", false);
		$("button.submit_graph").removeClass("inactive");

		var data = $(this).parents("form").serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "search_typ", value: 4});

		if($(this).attr("id") == "WeldingOverview") data.push({name: "statistic_typ", value: 2});
		if($(this).attr("id") == "WeldingMistake") data.push({name: "statistic_typ", value: 3});
		if($(this).attr("id") == "WelderOverview") data.push({name: "statistic_typ", value: 4});

		$.ajax({
			type	: "POST",
			data	: data,
			cache	: false,
			url		: "<?php echo Router::url(array_merge(array('action'=>'results'), $this->request->projectvars['VarsArray']));?>",
			dataType: "html",
			success: function(data) {
				$("#diagramm_1").html(data);
				$("#diagramm_1").show();
				CssContainerShow("diagramm_1");
				$("html, body").animate({ scrollTop: $(document).height() }, "slow");				
			},
		});

		$(this).prop("disabled", true);
		$(this).addClass("inactive");
	});
	
	$("select.statistic_filter").change(function() {
		
		if($(this).attr("id") == "ReportnumberMonths" && $("#ReportnumberYears").val().length == 0){
			alert("choose a year");
			return false;
		}

		CssContainerWait("diagramm_1");

		var data = $(this).parents("form").serializeArray();

		data.push({name: "ajax_true", value: 1});
		data.push({name: "search_typ", value: 4});

		if($(this).parents("form").find("button#WeldingOverview").hasClass("inactive") === true) data.push({name: "statistic_typ", value: 2});
		if($(this).parents("form").find("button#WeldingMistake").hasClass("inactive") === true) data.push({name: "statistic_typ", value: 3});
		if($(this).parents("form").find("button#WelderOverview").hasClass("inactive") === true) data.push({name: "statistic_typ", value: 4});

		$.ajax({
			type	: "POST",
			data	: data,
			cache	: false,
			url		: "<?php echo Router::url(array_merge(array('action'=>'results'), $this->request->projectvars['VarsArray']));?>",
			dataType: "html",
			success: function(data) {
				$("#diagramm_1").html(data);
				$("#diagramm_1").show();
				CssContainerShow("diagramm_1");
				$("html, body").animate({ scrollTop: $(document).height() }, "slow");				
			},
		});
	});	
});
</script>
