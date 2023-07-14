<div class="modalarea detail">
<h2><?php echo __('Searching for Orders');?></h2>
	<?php 
		echo $this->Form->create('Topprojects', array('class' => 'Ordersearchform', 'url' => array('action' => 'search',
			$this->request->projectvars['VarsArray'][0], 
			$this->request->projectvars['VarsArray'][1], 
			$this->request->projectvars['VarsArray'][2], 
			$this->request->projectvars['VarsArray'][3], 
			$this->request->projectvars['VarsArray'][4], 
			$this->request->projectvars['VarsArray'][5], 
			$this->request->projectvars['VarsArray'][6], 
			$this->request->projectvars['VarsArray'][7], 
			$this->request->projectvars['VarsArray'][8], 
			$this->request->projectvars['VarsArray'][9], 
			$this->request->projectvars['VarsArray'][10], 
			$this->request->projectvars['VarsArray'][11], 
			$this->request->projectvars['VarsArray'][12], 
			$this->request->projectvars['VarsArray'][13] 
		)));
	?>
	<fieldset>

	<?php
	echo $this->Navigation->showSearchHeader();

	foreach($SearchFields['dropdowns'] as $_key => $_SearchFields){
		if(!empty($_SearchFields['Break'])) echo '</fieldset><fieldset>';

		$options = array();

		if(isset($_SearchFields['Value']) && $_SearchFields['Value'] != ''){
			$options['value'] = $_SearchFields['Value'];
		}
	
		if(isset($_SearchFields['Result'])){
			$options['options'] = $_SearchFields['Result'];
		}

		if($reportID > 0 && trim($_key) != 'report_id'){
			$options['empty'] = ' ';
		}
		elseif($reportID == 0){
			$options['empty'] = ' ';
		}
		
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
		
		if($_SearchFields['OptionField'] == 'topproject_id') $options['class'] = 'dynamic';			

		echo $this->Form->input($_SearchFields['Model'].'.'.trim($_key),$options);
	}
	echo '</fieldset><fieldset class="autocomplete">';
	
	foreach($SearchFields['autocompletes'] as $_SearchFields) {

		if(!empty($_SearchFields['Break'])) echo '</fieldset><fieldset class="autocomplete">';
		
		$options = array('type' => 'text');
		if(!empty($_SearchFields['Caption'])) $options['label'] = $_SearchFields['Caption'];
		echo $this->Form->input($_SearchFields['Model'].'.'.$_SearchFields['Key'], $options);
	}
	?>    
	</fieldset><fieldset><legend>Zuletzt bearbeitet</legend>
    <?php echo $this->Form->input('Starttime', array('id' => 'TimeStarttime','name' => 'data[Time][starttime]','class' => 'date','value' => @$this->request->data['Time']['starttime']));?>
    <?php echo $this->Form->input('Endtime', array('id' => 'TimeEndtime','name' => 'data[Time][endtime]','class' => 'date','value' => @$this->request->data['Time']['endtime']));?>
    </fieldset>
    
<?php 
if(isset($testingreportsCount) && $testingreportsCount > 0){ 
	$options = array(
		'label' => $testingreportsCount . ' ' .  __('Show matching results', true),
	);
}
else{ 
	$options = array(
 	   'label' => __('There are no results matching your search', true),
 	   'disabled' => 'disabled',
	);
}

echo $this->Form->end($options);
?>

<div class="clear" id="testdiv"></div>
</div>
<div class="clear" id="testdiv"><?php //pr($thisAutocompletsJSON); ?></div>

<script type="text/javascript">
	$(document).ready(function(){
		
		$(".date").datetimepicker({ format: "Y-m-d", timepicker:false, lang:"de"});

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

				var data = $("#TopprojectsSearchForm").serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});

						$.ajax({
								type	: "POST",
								cache	: true,
								url		: "'.Router::url(array('action'=>'search')).$this->Navigation->makeTerms($this->request->projectvars['VarsArray']).'",
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
					var reset = 0;

					$("select.dynamic:first").bind("change", function() {
						reset = 1;
					})


					$("#TopprojectsSearchForm div.text input, #TopprojectsSearchForm div.select select").change(function() {

						var data = $("#TopprojectsSearchForm").serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "dialog", value: 1});
						
						if(reset == 1){
							data.push({name: "reset", value: 1});
						}
						else {
							data.push({name: "reset", value: 0});
						}
						
						$.ajax({
								type	: "POST",
								cache	: true,
								url		: "'.Router::url(array('action'=>'search')).$this->Navigation->makeTerms($this->request->projectvars['VarsArray']).'",
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

<?php

echo '

					$("#TopprojectsSearchForm").bind("submit", function() {
							
						var data = $(this).serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "showsearch", value: 1});
						
						$.ajax({
								type	: "POST",
								cache	: true,
								url		: $(this).attr("action"),
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
-
<?php echo $this->JqueryScripte->ModalFunctions(); ?>