<div class="modalarea detail">
<h2><?php echo __('Add Equipment Type'); ?></h2>
<?php echo $this->Form->create('EquipmentType', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
//		echo $this->Form->input('topproject_id');
		echo $this->Form->input('discription');
//		echo $this->Form->input('status');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?> 
