<div class="actions" id="top-menue">
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showNavigation($menues); ?></ul>
</div>
<div class="testingmethods form inhalt">
<?php echo $this->Form->create('Testingmethod', array('class' => 'dialogform')); ?>
	<fieldset>
		<legend><?php echo __('Edit testingmethod'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('value');
		echo $this->Form->input('name');
		echo $this->Form->input('verfahren');
		echo $this->Form->input('Topproject', array('options' => $topprojects_select));
	?>
	</fieldset>
<?php 
echo $this->Form->button(__('Cancel', true), array('id' => 'back', 'type' => 'button', 'value' => $previous_url));
echo $this->Form->end(__('Submit', true));
?>
</div>
<div class="clear" id="testdiv"></div>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>