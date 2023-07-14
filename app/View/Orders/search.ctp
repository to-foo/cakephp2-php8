<div class="modalarea detail">
<h2><?php echo __('Searching for Orders');?></h2>
	<?php 
		echo $this->Form->create('Orders', array('class' => 'Ordersearchform', 'url' => array('action' => 'search',
			$this->request->projectID, 
			$this->request->orderKat, 
			$this->request->orderID, 
			$this->request->reportID, 
			$this->request->reportnumberID, 
			$this->request->evalId, 
			$this->request->weldedit 
		)));
	?>
	<fieldset>
	<legend class="links">
	<?php 
	echo $this->Navigation->makeLink('orders','search',__('Search for orders'),'mymodal active',null,$this->request->projectvars['VarsArray']);
	echo ' '; 
//	echo $this->Navigation->makeLink('reportnumbers','search',__('Search for KKS'),'mymodal',null,$this->request->projectvars['VarsArray']);
//	echo ' '; 
	echo $this->Navigation->makeLink('reportnumbers','search',__('Search for reports'),'mymodal',null,$this->request->projectvars['VarsArray']);
	?>
    <span class="clear"></span>
    </legend>
	<?php 
	 
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
/*
			if(isset($this->request->data['Order']['status'])){
				$options['value'] = $this->request->data['Order']['status'];
			}
*/
			foreach($_SearchFields['Value']->option as $_option){
				$options['options'][trim($_option->id)] = trim($_option->value->$locale);
			}
		}
		echo $this->Form->input('Order.'.trim($_key),$options);
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
	$options = $testingreportsCount . ' ' .  __('Show matching results', true);
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
<div class="clear" id="testdiv"><?php //var_dump($thisAutocompletsJSON); ?></div>

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

				var data = $("#OrdersSearchForm").serializeArray();
				data.push({name: "ajax_true", value: 1});
				data.push({name: "dialog", value: 1});

						$.ajax({
								type	: "POST",
								cache	: true,
								url		: "orders/search'.$this->Navigation->makeTerms($this->request->projectvars['VarsArray']).'",
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

					$("#OrdersSearchForm div.input input, #OrdersSearchForm div.input select").change(function() {

						var data = $("#OrdersSearchForm").serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "dialog", value: 1});

						$.ajax({
								type	: "POST",
								cache	: true,
								url		: "orders/search'.$this->Navigation->makeTerms($this->request->projectvars['VarsArray']).'",
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

					$("form#OrdersSearchForm").bind("submit", function() {
							
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

<?php echo $this->JqueryScripte->ModalFunctions(); ?>