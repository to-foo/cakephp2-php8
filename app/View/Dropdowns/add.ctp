<div class="actions" id="top-menue">
	<h3><?php __('Actions'); ?></h3>
	<ul><?php echo $this->Navigation->showNavigation($menues); ?></ul>
</div>
<div class="dropdowns form inhalt">
<?php echo $this->Form->create('Dropdown', array('class' => 'dialogform')); ?>
	<fieldset>
		<legend><?php echo __('Add Dropdown'); ?></legend>
	<?php
		echo $this->Form->input('wert');
		echo $this->Form->input('Testingcomp');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<!--
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Html->link(__('List Dropdowns'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Testingcomps'), array('controller' => 'testingcomps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Testingcomp'), array('controller' => 'testingcomps', 'action' => 'add')); ?> </li>
	</ul>
</div>
-->
<div class="clear" id="testdiv"></div>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
