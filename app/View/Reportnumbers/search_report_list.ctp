<div class="modalarea detail">
	<h2><?php echo __('Search reports').': '.$reportName; ?></h2>
	<?php 
		echo $this->Form->create('Reportnumber', array('class' => 'Searchform', 'url' => array('action' => 'searchReportList',$reportID, $projectID, $orderID)));
	?>
	<fieldset>
	<?php  
	foreach($SearchFields['dropdowns'] as $_key => $_SearchFields){
		if(!empty($_SearchFields['Break'])) echo '</fieldset><fieldset>';

		$options = array('empty' => ' ');

		/*
		if(isset($_SearchFields['Value']) && $_SearchFields['Value'] != ''){
			$options['value'] = $_SearchFields['Value'];
		}
		*/
	
		$options['options'] = $_SearchFields['Result']; 
		
		if(isset($_SearchFields['Value']->option) && count($_SearchFields['Value']->option) > 0){
			
			$options['options'] = array();
			$options['value'] = null;

			if(isset($this->request->data['Reportnumber']['status'])){
				$options['value'] = $this->request->data['Reportnumber']['status'];
			}

			foreach($_SearchFields['Value']->option as $_option){
				$options['options'][trim($_option->id)] = trim($_option->value->$locale);
			}
		}
		echo $this->Form->input('Reportnumber.'.trim($_key),$options);
	}
	echo '</fieldset><fieldset class="autocomplete">';
	
	foreach($SearchFields['autocompletes'] as $_SearchFields) {

		if(!empty($_SearchFields['Break'])) echo '</fieldset><fieldset class="autocomplete">';
		
		$options = array();
		if(!empty($_SearchFields['Caption'])) $options['label'] = $_SearchFields['Caption'];
		echo $this->Form->input('Reports.'.$_SearchFields['Model'].'.'.$_SearchFields['Key'], $options);
	}
	?>
	</fieldset>
<?php

if($testingreportsCount > 0){ 
	$options = array(
		'label'=>$testingreportsCount . ' ' .  __('Show matching reports', true)
	);
}
elseif($testingreportsCount == 0){ 
	$options = array(
 	   'label' => __('There are no reports matching your search', true),
 	   'disabled' => 'disabled',
	);
}

$options['class'] = 'list';
echo $this->Form->end($options);
?>

<div class="clear" id="testdiv"></div>
</div>
<div class="clear" id="testdiv"><?php //var_dump($thisAutocompletsJSON); ?></div>
<script type="text/javascript">
	$(document).ready(function(){
	<?php

	foreach($thisAutocompletsJSON as $_key => $_thisAutocompletsJSON){

		$value = $_thisAutocompletsJSON;
		echo 'var availableTags = ';
		echo $value;
		echo ';';

		echo '
		$("#'.$_key.'").autocomplete({
			source: availableTags,
			close: function(e, ui) {

				var data = $("#ReportnumberSearchReportListForm").serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});
				data.push({name: "id", value: '.$reportnumber['Reportnumber']['id'].'});

						$.ajax({
								type	: "POST",
								cache	: true,
								url		: "reportnumbers/searchReportList/'.$reportID.'/'.$projectID.'/'.$orderID.'",
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});				
				}  
			});
		';	
	}
	?>	

<?php

echo '

					$("#ReportnumberSearchReportListForm input, #ReportnumberSearchReportListForm select").change(function() {

						var data = $("#ReportnumberSearchReportListForm").serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "dialog", value: 1});
						data.push({name: "id", value: '.$reportnumber['Reportnumber']['id'].'});

						$.ajax({
								type	: "POST",
								cache	: true,
								url		: "reportnumbers/searchReportList/'.$reportID.'/'.$projectID.'/'.$orderID.'",
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});
							return false;
					});

';
?>
	});
</script>

<?php echo $this->JqueryScripte->ModalFunctions($reportnumber); ?>