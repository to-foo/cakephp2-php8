<?php
		$urls = array('search'=>array('action'=>'search'), 'statistics'=>array('controller'=>'statistics', 'action'=>'index'));
		$titles = array('search'=>__('Search reports'), 'statistics'=>__('Statistics'), 'assign'=>__('Assign report as %s', isset($reportnumber['Testingmethod']['allow_children']) && $reportnumber['Testingmethod']['allow_children']==0 ? __('parent report') : __('child report')));
?><div class="modalarea detail">
	<h2><?php
		echo array_search($this->request->data['type'], array_keys($titles)) === false ? $titles['search'] : $titles[$this->request->data['type']];
	?></h2>
	<?php
		if($this->request->projectID != 0) {
			//echo $this->Html->tag('div', __('Project %s', $SearchFields['dropdowns']['topproject_id']['Result'][$this->request->projectID]), array('class'=>'hint'));
		}
	?>
	<?php
	if(is_array($projectID)) $projectID = 0;
//	if($reportID > 0){
//		echo $this->Form->create('Reportnumber', array('class' => 'Searchform', 'url' => array('action' => 'show', $projectID, 0, $orderID, $reportID)));
//	}
//	else{
		echo $this->Form->create('Reportnumber', array('class' => 'GlobalSearchform', 'url' => array_merge(
			array_search($this->request->data['type'], array_keys($urls)) === false ? $urls['search'] : $urls[$this->request->data['type']], $VarsArray)));
//	}

	?>
	<fieldset>
	<?php

	if($this->request->data['type'] == 'search') { 
		echo $this->Navigation->showSearchHeader();
	}

	foreach($SearchFields['dropdowns'] as $_key => $_SearchFields){

		if(isset($_SearchFields['Hidden']) && $_SearchFields['Hidden']) continue;
		if(!empty($_SearchFields['Break'])) echo '</fieldset><fieldset>';		

		$options = array();
		$options['type'] = 'select';
		if(isset($_SearchFields['Locked']) && $_SearchFields['Locked'] == 1) $options['disabled'] = 'disabled';
		$options['options'] = $_SearchFields['Result'];

		if(!isset($_SearchFields['Result']) || empty($_SearchFields['Result'])) {
			$options['disabled'] = true;
			$options['options'] = array(''=>__('No data to display'));
		} else {
			if($reportID > 0 && trim($_key) != 'report_id'){
				$options['empty'] = ' ';
			}
			elseif($reportID == 0){
				$options['empty'] = ' ';
			}

		//	if(trim($_key) == 'topproject_id' && $VarsArray[0] != 0) $options['empty'] = false;
			
			if(trim($_key) == 'topproject_id') {
				if(!isset($this->request->data['Reportnumber']['topproject_id'])) {
					if(!empty($Deliverynumber['term'][0])) $options['value'] = $Deliverynumber['term'][0];
					else $options['value']=$this->request->projectID;
				} else {
					$options['value']=$this->request->data['Reportnumber']['topproject_id'];
				}
			}
		//	elseif(trim($_key) == 'equipment_type_id' && isset($Deliverynumber['term'][1])) $options['value'] = $Deliverynumber['term'][1];
		//	elseif(trim($_key) == 'equipment_id' && isset($Deliverynumber['term'][2])) $options['value'] = $Deliverynumber['term'][2];
		//	elseif(trim($_key) == 'order_id' && isset($Deliverynumber['term'][3])) $options['value'] = $Deliverynumber['term'][3];
			elseif(trim($_key) == 'report_id') {
				$options['empty'] = ' ';
				if(isset($this->request->data['Reportnumber']['report_id'])) $options['value']=$this->request->data['Reportnumber']['report_id'];
				else $options['value'] = (isset($this->request->projectvars['reportID'])) ? $this->request->projectvars['reportID'] : 0 ;
			}
			elseif(isset($this->request->data['Reportnumber'][$_key]) && !empty($this->request->data['Reportnumber'][$_key]))
				$options['value'] = $this->request->data['Reportnumber'][$_key];
			// Herausgenommen, weil man sonst Dropdowns unter Umständen nicht umstellen kann
			//elseif(count($_SearchFields['Result']) == 1)
			//	$options['value'] = @reset(array_keys($_SearchFields['Result']));
			elseif(isset($_SearchFields['Value']) && $_SearchFields['Value'] != '' && (array_search(trim($_SearchFields['Value']), $_SearchFields['Result']) !== false))
				$options['value'] = $_SearchFields['Value'];
			else
				$options['value'] = null;
		}

		$options['label'] = $_SearchFields['Value'];

		if(empty($_SearchFields['Area']) || $_SearchFields['Area'] == 'no') {
			echo $this->Form->input('Reportnumber.'.trim($_key),$options);
		} else {

			$_option_date['from'] = $options;
			$_option_date['to'] = $options;

			foreach($_option_date['from']['options'] as $_date_key => $__option_date){
				$_option_date['from']['options'][$_date_key] = '01. ' . $_option_date['from']['options'][$_date_key];
				$_value_from = explode('-',$_date_key);
 				$_option_date['to']['options'][$_date_key] = cal_days_in_month(CAL_GREGORIAN, $_value_from[1], $_value_from[0]) . '. ' . $_option_date['to']['options'][$_date_key];
			}

			echo $this->Form->input('Reportnumber.'.trim($_key).'_from', array_merge($_option_date['from'], array('label'=>$_SearchFields['Value'].' '.__('from') . ' (' . __('created',true) . ')')));
			echo $this->Form->input('Reportnumber.'.trim($_key).'_to', array_merge($_option_date['to'], array('label'=>$_SearchFields['Value'].' '.__('to') . ' (' . __('created',true) . ')')));
		}
	}
	
	echo '</fieldset><fieldset class="autocomplete">';


	foreach($SearchFields['autocompletes'] as $_SearchFields) {

		if($_SearchFields['Hidden']) continue;
		if(!empty($_SearchFields['Break'])) echo '</fieldset><fieldset class="autocomplete">';

		$options = array('type' => 'text', 'required' => false, 'class'=>$_SearchFields['Type']);
		
		if(!empty($_SearchFields['Caption'])) $options['label'] = $_SearchFields['Caption'];
		if(empty($_SearchFields['Area']) || $_SearchFields['Area'] == 'no') {
			echo $this->Form->input('Reports.'.$_SearchFields['Model'].'.'.$_SearchFields['Key'], $options);
		} else {
			$options['class'] = 'searchdate';	
			echo $this->Form->input('Reports.'.$_SearchFields['Model'].'.'.$_SearchFields['Key'].'_from', array_merge($options, array('label'=>$options['label'].' '.__('from'))));
			echo $this->Form->input('Reports.'.$_SearchFields['Model'].'.'.$_SearchFields['Key'].'_to', array_merge($options, array('label'=>$options['label'].' '.__('to'))));
		}
	}
	?>
	</fieldset>
<?php
if(Configure::check('StatisticsMaxSearchResult')){
	$StatisticsMaxSearchResult = Configure::read('StatisticsMaxSearchResult');
} else {
	$StatisticsMaxSearchResult = 500;
}
if($testingreportsCount > 0){
//	if($this->request->data['type'] == 'statistics' && $testingreportsCount > $StatisticsMaxSearchResult)
	if($testingreportsCount > $StatisticsMaxSearchResult)
		echo $this->Html->tag('p', __('The search result is too large. This system can evaluate a maximum of %s reports, please limit your search result.', $StatisticsMaxSearchResult));

	// Für Zuweisen von Prüfberichten dürfen nicht alle möglichen Berichte angezeigt werden, wenn kein Passender existiert.

	if($empty_result && $this->request->data['type']=='assign') {
		$options = array(
	 	   'label' => __('There are no reports matching your search', true),
	 	   'disabled' => 'disabled',
		);
	} else {
		$options = $testingreportsCount . ' ' .  __('Show matching reports', true);
	}
}
else {
	$options = array(
 	   'label' => __('There are no reports matching your search', true),
 	   'disabled' => 'disabled',
	);
}

if($testingreportsCount > $StatisticsMaxSearchResult){
	$options = array(
 	   'label' => __('To match results', true),
 	   'disabled' => 'disabled',
	);
}

if($this->request->data['type'] == 'assign') echo $this->Form->input('create', array('value'=> __('Create report', true), 'label'=>false, 'type'=>'reset', 'class'=>'create'));
echo $this->Form->input('type', array('name'=>'data[type]', 'type'=>'hidden', 'value'=>$this->request->data['type']));

//echo $this->Html->link('test');
if(!isset($this->request->data['Reportnumber'])){	
	echo $this->Html->link(__('Zurücksetzen'),array('action'=>'search'),array('class' => 'resetsearch round'));
	echo $this->Form->end(); 
} else {
	echo $this->Html->link(__('Zurücksetzen'),array('action'=>'search'),array('class' => 'resetsearch round'));
	echo  $this->Form->end($options);
}

$type = 'search';
if(isset($this->request->data['type'])) $type = $this->request->data['type'];
?>
<div class="clear" id="testdiv"></div>
</div>
<div class="clear" id="testdiv"></div>
<script type="text/javascript">
	$(document).ready(function(){

	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,"");
	}


	$(".searchdate").datetimepicker({ 
			format: "Y-m-d",
			minDate: new Date(<?php echo $reportrange['first'][0];?>, <?php echo $reportrange['first'][1];?> - 1, <?php echo $reportrange['first'][2];?>),
			timepicker:false, 
			lang:"de", 
			scrollInput: false
	});
<?php

echo '
	// ursprüngliche Formulardaten werden zum späteren Vergleichen gespeichert
	lastdata = $("#ReportnumberSearchForm").serializeArray();

	$("a.resetsearch").click(function() {

		$("#dialog").empty();
		$("#dialog").css("background-image","url(img/indicator.gif)"); 
		$("#dialog").css("background-repeat","no-repeat"); 
		$("#dialog").css("background-position","center center"); 
		$("#dialog").css("min-height","4em"); 
		
		$("#dialog").load($(this).attr("href"),{"ajax_true": 1,"type": "' . $type . '"})
		return false;
	});
	
	$("#ReportnumberSearchForm input, #ReportnumberSearchForm select").change(function() {

		if($("#waiting").length) {
			return false;
		}

		var data = $("#ReportnumberSearchForm").serializeArray();

		// Bei leeren Formularfelder wird submit abgebrochen
		// Wenn Ursprungsdaten und aktuelle Daten gleich sind, wird submit abgebrochen
		searchcounter = 0;
		$.each(data, function( index, field ) {

			if(field.name != "_method" && field.name != "data[type]"){

				// unnötige Leerzeichen entfernen
				data[index].value = data[index].value.trim();
				field.value = field.value.trim();
				lastdata[index]["value"] = lastdata[index]["value"].trim();
				thislength = field.value.length;
				if(lastdata[index]["value"] == field.value){
					thislength = 0;
				}
				if(thislength == 0 && lastdata[index]["value"].length > 0){
					searchcounter += 1;
				}
				searchcounter += thislength;
			}
		});

		if(searchcounter == 0){
//			console.log(lastdata);
//			console.log(data);
			return;
		}
		
		$(".modalarea form").prepend("<div id=\"waiting\"><span>'.__('Please wait').'</span></div>");
		$("div.submit input").val("'.__('The database is searched',true).'");
		
		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});
		data.push({name: "type", value: "'.$this->request->data['type'].'"});

		$(".modalarea form").find("input, select").prop("disabled", true);

		$("#dialog").empty();
		$("#dialog").css("background-image","url(img/indicator.gif)"); 
		$("#dialog").css("background-repeat","no-repeat"); 
		$("#dialog").css("background-position","center center"); 
		$("#dialog").css("min-height","4em"); 

		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "'.Router::url(array_merge(array('action'=>'search'), $VarsArray)).'",
			data	: data,
			success: function(data) {
				$("#dialog").html(data);
				$("#dialog").show();
			},
			done: function() {
				$(".modalarea form #waiting").remove();
			}
		});
		return false;
	});

	$(".create").click(function() {
		$.ajax({
			url: "'.$this->Html->url(array_merge(array('controller'=>'testingmethods', 'action'=>'listing'), $VarsArray)).'",
			type: "POST",
			data: {ajax_true: 1, linked: 1},
			success: function(data) {
				$("#dialog").html(data).dialog();
			}
		});

		return false;
	});

	$(".date").prop({"readOnly": true}).datetimepicker({timepicker:false, format:"Y-m-d", lang:"de"});
	$("<a>leeren</a>")
		.attr(\'href\',\'\')
		.attr(\'class\',\'cleardate\')
		.click(function(e) {
			input = $(this).siblings(\'textarea, input[type="text"]\');
			if(input.val() != "") input.val("").trigger("change");
			
			e.stopImmediatePropagation();
			e.stopPropagation();
			e.preventDefault();
		})
		.insertAfter("#dialog .date");

	$(".datetime").prop({"readOnly": true}).datetimepicker({format: "Y-m-d H:i", lang:"de"});
';
?>
	});
</script>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>
