<div class="modalarea detail">
<h2><?php echo __('Move order')?> <?php // echo $headline;?></h2>
<div class="hint">
<p>
<?php echo __('Move order',true);?>  
<?php echo '<strong>' . $order['Order']['auftrags_nr'] . '</strong>'; ?> 
<?php echo __('from',true);?> 
<?php echo '<strong>' . $order['Topproject']['projektname'] . '</strong>'; ?> > 
<?php echo '<strong>' . $order['Deliverynumber']['EquipmentType']['discription'] . '</strong>'; ?> > 
<?php echo '<strong>' . $order['Deliverynumber']['Equipment']['discription'] . '</strong>'; ?>  
</p>
</div>
<?php echo $this->Form->create('Order', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
	echo $this->Form->input('topproject_id', array('type' => 'select','empty' => '', 'options' => $topproject));
	if(isset($equipmenttypes) && count($equipmenttypes) > 0) echo $this->Form->input('equipment_type_id', array('type' => 'select','empty' => '','options' => $equipmenttypes));
	if(isset($equipments) && count($equipments) > 0) echo $this->Form->input('equipment_id', array('type' => 'select','empty' => '','options' => $equipments));
		
	
	$select_discription = __('Select');
	
	if(isset($thisequipment) && count($thisequipment) > 0){

		echo '</fieldset><fieldset>';
		echo '<p>';
		echo __('Will you move Order'); 
		echo ' ';
		echo  '<strong>' . $order['Order']['auftrags_nr'] . '</strong> '; 
		echo ' ';
		echo __('to',true);
		echo ' ';
		echo '<strong>' . $topproject[$this->request->data['Order']['topproject_id']] . '</strong> ';
		echo ' > ';
		echo '<strong>' . $equipmenttypes[$this->request->data['Order']['equipment_type_id']] . '</strong> ';  
		echo ' > ';
		echo '<strong>' . $equipments[$this->request->data['Order']['equipment_id']] . '</strong>';  
		echo '?';
		echo '</p>';

		echo $this->Form->input('thisequipment', array('type' => 'hidden','value' => key($thisequipment)));
		$select_discription = __('Move');
	}

	echo '</fieldset>';
	if(!isset($equipments))echo $this->Form->end();
	elseif(isset($equipments) && count($equipments) > 0)echo $this->Form->end($select_discription);

	?>
</div>
<div class="clear" id="testdiv"></div>

<?php 
if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	} 
?>

<script type="text/javascript">
	$(document).ready(function(){
					$("#OrderMoveForm select").change(function() {

						var data = $("#OrderMoveForm").serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "dialog", value: 1});

						$.ajax({
								type	: "POST",
								cache	: true,
								url		: $("#OrderMoveForm").attr('action'),
								data	: data,
								success: function(data) {
		    						$("#dialog").html(data);
		    						$("#dialog").show();
								}
							});
							return false;
					});

	});
</script>					

<?php 
if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog($reportnumberID,$evalutionID,$FormName);
	echo $this->JqueryScripte->DialogClose(1.5);
	} 

echo $this->JqueryScripte->ModalFunctions(); 
?>
