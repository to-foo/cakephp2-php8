<div class="modalarea detail">
<h2><?php echo __('Search examiners');?></h2>
	<?php 
		echo $this->Form->create('Testingcomps', array('novalidate'=>true, 'class' => 'Ordersearchform', 'url' => array('action' => 'search')));
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
		if(!empty($_SearchFields['Description'])) $options['label'] = $_SearchFields['Description'];
		
		if($_SearchFields['Area'] == 1) {
			echo $this->Form->input($_SearchFields['Model'].'.'.$_SearchFields['Key'].'_from', $options);
			echo $this->Form->input($_SearchFields['Model'].'.'.$_SearchFields['Key'].'_to', $options);
		} else {
			echo $this->Form->input($_SearchFields['Model'].'.'.$_SearchFields['Key'], $options);
		}
	}
	?>    
	</fieldset>    
<?php 
if($resultsCount > 0){
	$options = $resultsCount . ' ' .  __('results', true);
}
else {
	$options = array(
 	   'label' => __('There are no results matching your search', true),
 	   'disabled' => 'disabled',
	);
}

echo $this->Form->end($options);
?>

<div class="clear" id="testdiv"></div>
</div>
<div class="clear" id="testdiv"><?php  ?></div>

<script type="text/javascript">
	$(document).ready(function(){
		
	<?php
	if(isset($autocomplete)) {
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
						dataType: "json",
						url		: "'.Router::url(array_merge(array('action'=>'search'), $VarsArray)).'",
						data	: data,
						success: function(data) {
							if(Object.keys(data).length > 0) {
								if(data.modal == 1) $("#dialog").load(data.url, [{name: "ajax_true", value: 1}]).show();
								else {
									$("#container").load(data.url, [{name: "ajax_true", value: 1}]).show();
									$("#dialog").dialog().dialog("close");
								}
							}
						}
					});
				}
			});
			';	
		}
	}
?>
	$(".modalarea #TestingcompsSearchForm div.select select#TestingcompTestingcompId").on("change", function() {
		var data = [];
		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});
		data.push({name: "data[testingcomp_id]", value: $("#TestingcompTestingcompId").val()});
		
		<?php if(isset($this->request->data['type'])) { echo 'data.push({name: "type", value: "'.$this->request->data['type'].'"});'; } ?>
		
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
	
	
	$(".modalarea #TestingcompsSearchForm input").on("change", function() {
		data =  $('#TestingcompsSearchForm').serializeArray();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});
		data.push({name: "data[testingcomp_id]", value: $("#TestingcompTestingcompId").val()});
		
		<?php if(isset($this->request->data['type'])) { echo 'data.push({name: "type", value: "'.$this->request->data['type'].'"});'; } ?>
		
		$.ajax({
			type	: "POST",
			url		:  $('#TestingcompsSearchForm').attr("action"),
			data	: data,
			success: function(data) {
				$("#dialog").html(data).show();
			}
		});
	});
	
	 $('#TestingcompsSearchForm').on("submit", function(ev) {
		data =  $('#TestingcompsSearchForm').serializeArray();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "dialog", value: 1});
		data.push({name: "data[testingcomp_id]", value: $("#TestingcompTestingcompId").val()});
		data.push({name: "showresult", value: 1});
		
		<?php if(isset($this->request->data['type'])) { echo 'data.push({name: "type", value: "'.$this->request->data['type'].'"});'; } ?>
		
		$.ajax({
			type	: "POST",
			url		:  $('#TestingcompsSearchForm').attr("action"),
			data	: data,
			success: function(data) {
				$("#dialog").html(data).show();
			}
		});
		
		ev.preventDefault();
		ev.stopPropagation();
		ev.stopImmediatePropagation();
	});
});
</script>
-
<?php echo $this->JqueryScripte->ModalFunctions(); ?>