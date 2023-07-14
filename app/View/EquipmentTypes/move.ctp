<div class="modalarea detail">
<h2><?php echo __('Move equipment type')?> <?php // echo $headline;?></h2>
<div class="hint">
<p>
<?php echo __('Move equipment type',true);?>  
<?php echo '<strong>' . $equipmenttype['EquipmentType']['discription'] . '</strong>'; ?> 
<?php echo __('from',true);?> 
<?php echo '<strong>' . $equipmenttype['Topproject']['projektname'] . '</strong>'; ?>  
</p>
</div>
<?php echo $this->Form->create('Equipment', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php 
	echo $this->Form->input('topproject_id', array('type' => 'select','empty' => '', 'options' => $topprojects));		
	
	$select_discription = __('Select');

	if(isset($thisequipment) && isset($topprojects[$equipmenttypes])){


		echo '</fieldset><fieldset>';
		echo '<p>';
		echo __('Will you move equipment type'); 
		echo ' ';
		echo  '<strong>' . $equipmenttype['EquipmentType']['discription'] . '</strong> '; 
		echo ' ';
		echo __('from',true);
		echo ' ';
		echo '<strong>' . $equipmenttype['Topproject']['projektname'] . '</strong> ';
		echo ' ';
		echo __('to',true);
		echo ' ';
		echo '<strong>' . $topprojects[$equipmenttypes] . '</strong> ';
		echo '?';
		echo '</p>';

		echo $this->Form->input('thisequipment', array('type' => 'hidden','value' => $thisequipment));
		$select_discription = __('Move');
	}

	echo '</fieldset>';

	if(!isset($equipmenttypes))echo $this->Form->end();
	elseif(isset($equipmenttypes) && count($equipmenttypes) > 0)echo $this->Form->end($select_discription);

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
/*
					$("select#EquipmentTopprojectId").change(function() {
						if($("select#EquipmentEquipmentTypeId").val() > 0){
							$("select#EquipmentEquipmentTypeId").val() = null;
						}
					});
*/					
					$("#EquipmentMoveForm select").change(function() {

						var data = $("#EquipmentMoveForm").serializeArray();
						data.push({name: "ajax_true", value: 1});
						data.push({name: "dialog", value: 1});

						$.ajax({
								type	: "POST",
								cache	: true,
								url		: $("#EquipmentMoveForm").attr('action'),
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
