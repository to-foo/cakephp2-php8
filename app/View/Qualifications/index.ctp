<div class="modalarea">
	<h2><?php echo __('Qualifications'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('testingcomp_id'); ?></th>
			<th><?php echo $this->Paginator->sort('user_id'); ?></th>
			<th><?php echo $this->Paginator->sort('examiner-id'); ?></th>
			<th><?php echo $this->Paginator->sort('certification-number'); ?></th>
			<th><?php echo $this->Paginator->sort('testingmethod_id'); ?></th>
			<th><?php echo $this->Paginator->sort('level'); ?></th>
			<th><?php echo $this->Paginator->sort('timeperiod'); ?></th>
			<th><?php echo $this->Paginator->sort('created'); ?></th>
			<th><?php echo $this->Paginator->sort('modified'); ?></th>
			<th><?php echo $this->Paginator->sort('supervisors'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php foreach ($qualifications as $qualification): ?>
	<tr>
		<td><?php echo $this->Html->link($qualification['Testingcomp']['name'], array('controller' => 'testingcomps', 'action' => 'view', $qualification['Testingcomp']['id'])); ?></td>
		<td><?php echo $this->Html->link($qualification['User']['name'], array('controller' => 'users', 'action' => 'view', $qualification['User']['id'])); ?></td>
		<td><?php echo h($qualification['Qualification']['examiner-id']); ?>&nbsp;</td>
		<td><?php echo h($qualification['Qualification']['certification-number']); ?>&nbsp;</td>
		<td>
			<?php // echo $this->Html->link($qualification['Testingmethod']['name'], array('controller' => 'testingmethods', 'action' => 'view', $qualification['Testingmethod']['id'])); ?>
			<?php echo h($qualification['Testingmethod']['name']); ?>&nbsp;
		</td>
		<td><?php echo h($qualification['Qualification']['level']); ?>&nbsp;</td>
		<td><?php echo h($qualification['Qualification']['timeperiod']); ?>&nbsp;</td>
		<td><?php echo h($qualification['Qualification']['created']); ?>&nbsp;</td>
		<td><?php echo h($qualification['Qualification']['modified']); ?>&nbsp;</td>
		<td><?php echo h($qualification['Qualification']['supervisors']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $qualification['Qualification']['id']), array('class'=>'right delete_generally mymodal')); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $qualification['Qualification']['id']), array('class'=>'middle mymodal')); ?>
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $qualification['Qualification']['id']), array('class'=>'left mymodal')); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
	?>
	</div>
	<p class="paging_query">
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	
	</p>	
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>