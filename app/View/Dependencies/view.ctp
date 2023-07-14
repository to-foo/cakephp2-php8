<div class="dependencies view">
<h2><?php  echo __('Dependency'); ?></h2>
	<dl>
		<dt><?php echo __('Id'); ?></dt>
		<dd>
			<?php echo h($dependency['Dependency']['id']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Testingcomp'); ?></dt>
		<dd>
			<?php echo $this->Html->link($dependency['Testingcomp']['name'], array('controller' => 'testingcomps', 'action' => 'view', $dependency['Testingcomp']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Dropdown'); ?></dt>
		<dd>
			<?php echo $this->Html->link($dependency['Dropdown']['id'], array('controller' => 'dropdowns', 'action' => 'view', $dependency['Dropdown']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Dropdowns Value'); ?></dt>
		<dd>
			<?php echo $this->Html->link($dependency['DropdownsValue']['id'], array('controller' => 'dropdowns_values', 'action' => 'view', $dependency['DropdownsValue']['id'])); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Field'); ?></dt>
		<dd>
			<?php echo h($dependency['Dependency']['field']); ?>
			&nbsp;
		</dd>
		<dt><?php echo __('Value'); ?></dt>
		<dd>
			<?php echo h($dependency['Dependency']['value']); ?>
			&nbsp;
		</dd>
	</dl>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('Edit Dependency'), array('action' => 'edit', $dependency['Dependency']['id'])); ?> </li>
		<li><?php echo $this->Form->postLink(__('Delete Dependency'), array('action' => 'delete', $dependency['Dependency']['id']), null, __('Are you sure you want to delete # %s?', $dependency['Dependency']['id'])); ?> </li>
		<li><?php echo $this->Html->link(__('List Dependencies'), array('action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Dependency'), array('action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Testingcomps'), array('controller' => 'testingcomps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Testingcomp'), array('controller' => 'testingcomps', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Dropdowns'), array('controller' => 'dropdowns', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Dropdown'), array('controller' => 'dropdowns', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List Dropdowns Values'), array('controller' => 'dropdowns_values', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New Dropdowns Value'), array('controller' => 'dropdowns_values', 'action' => 'add')); ?> </li>
	</ul>
</div>
