<div class="modalarea">
	<h2><?php echo __('Reports'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('identification'); ?></th>
			<th><?php echo $this->Paginator->sort('projektbeschreibung'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($reports as $report):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>

	<tr<?php echo $class;?>>
		<td><?php echo h($report['Report']['id']); ?>&nbsp;</td>
		<td><?php echo h($report['Report']['name']); ?>&nbsp;</td>
		<td><?php echo h($report['Report']['identification']); ?>&nbsp;</td>
		<td><?php echo h($report['Report']['projektbeschreibung']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Delete'), array('action' => 'delete', $report['Report']['id']), array('class'=>'right delete_generally modal')); ?>
			<?php echo $this->Html->link(__('Edit'), array('action' => 'edit', $report['Report']['id']), array('class'=>'middle modal')); ?>
			<?php echo $this->Html->link(__('View'), array('action' => 'view', $report['Report']['id']), array('class'=>'left modal')); ?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled modal'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled modal'));
	?>
	</div>
	<p class="paging_query">
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>	</p>	
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>