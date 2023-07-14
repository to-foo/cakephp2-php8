<div class="modalarea">
	<h2><?php echo __('Testingmethods'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('id'); ?></th>
			<th><?php echo $this->Paginator->sort('value'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('verfahren'); ?></th>
			<th><?php echo $this->Paginator->sort('version'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($testingmethods as $testingmethod):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td><?php echo h($testingmethod['Testingmethod']['id']); ?>&nbsp;</td>
		<td><?php echo h($testingmethod['Testingmethod']['value']); ?>&nbsp;</td>
		<td><?php echo h($testingmethod['Testingmethod']['name']); ?>&nbsp;</td>
		<td><?php echo h($testingmethod['Testingmethod']['verfahren']); ?>&nbsp;</td>
		<td><?php echo h($testingmethod['Testingmethod']['version']); ?>&nbsp;</td>
		<td class="actions">&nbsp;</td>
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
	?>	</p>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>