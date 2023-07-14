<div class="rolls form">
<?php echo $this->Form->create('Roll'); ?>
	<fieldset>
		<legend><?php echo __('Edit roll'); ?></legend>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>

		<li><?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $this->Form->value('Roll.id')), null, __('Are you sure you want to delete # %s?', $this->Form->value('Roll.id'))); ?></li>
		<li><?php echo $this->Html->link(__('List rolls'), array('action' => 'index')); ?></li>
		<li><?php echo $this->Html->link(__('List testingcomps'), array('controller' => 'testingcomps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New testingcomp'), array('controller' => 'testingcomps', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New user'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
