<div class="rolls index">
	<h2><?php echo __('Rolls'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($rolls as $roll): ?>
	<tr>
		<td><?php echo h($roll['Roll']['id']); ?>&nbsp;</td>
		<td><?php echo h($roll['Roll']['name']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $roll['Roll']['id'])); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $roll['Roll']['id'])); ?>
			<?php echo $this->Form->postLink(__('Delete'), array('action' => 'delete', $roll['Roll']['id']), null, __('Are you sure you want to delete # %s?', $roll['Roll']['id'])); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<p>
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
</div>
<div class="actions">
	<h3><?php echo __('Actions'); ?></h3>
	<ul>
		<li><?php echo $this->Html->link(__('New roll'), array('action' => 'add')); ?></li>
		<li><?php echo $this->Html->link(__('List testingcomps'), array('controller' => 'testingcomps', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New testingcomp'), array('controller' => 'testingcomps', 'action' => 'add')); ?> </li>
		<li><?php echo $this->Html->link(__('List users'), array('controller' => 'users', 'action' => 'index')); ?> </li>
		<li><?php echo $this->Html->link(__('New user'), array('controller' => 'users', 'action' => 'add')); ?> </li>
	</ul>
</div>
