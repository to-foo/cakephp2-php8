<div class="modalarea detail">
<h2><?php echo $Headline . ': ' .   __('Add Equipment'); ?></h2>
<?php echo $this->Form->create('Equipment', array('class' => 'dialogform')); ?>
	<fieldset>
	<?php
//		echo $this->Form->input('equipment_type_id');
		echo $this->Form->input('discription');
//		echo $this->Form->input('status');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?> 
