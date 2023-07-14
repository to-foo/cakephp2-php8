	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('Project name'); ?></th>
			<th><?php echo __('Project discription'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($topprojects as $topproject):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo $topproject['Topproject']['projektname'];?></td>
		<td><?php echo h($topproject['Topproject']['projektbeschreibung']); ?>&nbsp;</td>
		<td class="actions">
			<?php echo $this->Html->link(__('Reopen'), array('action' => $action, $topproject['Topproject']['id']), array('class'=>'round mymodal','title' => __('Reopen this Project'))); ?>
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
