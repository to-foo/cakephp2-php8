<div class="modalarea detail">
<h2><?php echo __('Search company related');?></h2>
	<?php 
		echo $this->Form->create('Testingcomps', array('class' => 'Ordersearchform', 'url' => array('action' => 'search',
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

			foreach($_SearchFields['Value']->option as $_option){
				$options['options'][trim($_option->id)] = trim($_option->value->$locale);
			}
		}
		
		if(isset($this->request->data['testingcomp_id']) && $_SearchFields['OptionField'] == 'testingcomp_id') $options['value'] = $this->request->data['testingcomp_id'];			

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
	</fieldset>    
<?php 
echo $this->Form->end();
?>

<div class="clear" id="testdiv"></div>
</div>
<div class="clear" id="testdiv"><?php  ?></div>

<script type="text/javascript">
	$(document).ready(function(){
		
	<?php
	foreach($autocomplete as $_key => $_thisAutocompletsJSON){

		echo '
		var availableTags = '.json_encode(array_values($_thisAutocompletsJSON)).';

		$("#'.$_key.'").autocomplete({
			source: availableTags,
			close: function(e, ui) {
				data = [];
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});
				data.push({name: "type", value:"geturl"});
				
				list = '.json_encode($_thisAutocompletsJSON).';

				id = 0;
				for(i in list) {
					if($(e.target).val() == list[i]) {
						data.push({name: $(e.target).attr("name"), value: i});
						break;
					}
				}

				$.ajax({
					type	: "POST",
					cache	: true,
					url		: "'.Router::url(array_merge(array('action'=>'search'), $VarsArray)).'",
					data	: data,
					success: function(data) {
						if(data.length > 0) {
							$("#dialog").load(data, [{name: "ajax_true", value: 1}]).show();
						}
					}
				});
			}
		});
		';	
	}

?>
	$(".modalarea form div.select select#TestingcompTestingcompId").on("change", function() {
		var data = [];
		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});
		data.push({name: "data[testingcomp_id]", value: $("#TestingcompTestingcompId").val()});
		
		$.ajax({
			type	: "POST",
			cache	: true,
			url		: "<?php echo Router::url(array_merge(array('action'=>'search'), $VarsArray)); ?>",
			data	: data,
			success: function(data) {
				$("#dialog").html(data).show();
			}
		});
		return false;
	});
});
</script>
-
<?php echo $this->JqueryScripte->ModalFunctions(); ?>