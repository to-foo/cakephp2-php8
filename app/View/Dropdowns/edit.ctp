<div class="dropdowns form inhalt">
<?php echo $this->Form->create('Dropdown', array('class' => 'dialogform')); ?>
	<fieldset>
		<legend><?php echo __('Edit Dropdown'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('deu', array('label' => __('Discription', true).' DE'));
		echo $this->Form->input('eng', array('label' => __('Discription', true).' EN'));
		echo $this->Form->input('Testingcomp');
	?>
	</fieldset>
<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div>        
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<!--
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Dropdown.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Dropdown.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List Dropdowns'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List Testingcomps'), array('controller' => 'testingcomps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Testingcomp'), array('controller' => 'testingcomps', 'action' => 'add')); ?> </li>
	</ul>
</div>
-->
<div class="clear" id="testdiv"></div>
<?php if(isset($afterEDIT)){echo $afterEDIT;} ?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>