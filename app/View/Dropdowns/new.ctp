<div class="dropdowns form inhalt">
<?php echo $this->Form->create('Dropdown', array('class' => 'dialogform')); ?>
	<fieldset>
		<legend><?php echo __('Add Dropdown'); ?></legend>
	<?php
		echo $this->Form->input('eng');
		echo $this->Form->input('deu');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
