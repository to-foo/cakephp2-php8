<div class="modalarea">
	<h2><?php echo __('Welding method'); ?></h2>
         <div class="message_wrapper"><?php echo $this->Session->flash(); ?></div>  
	<table cellpadding="0" cellspacing="0">
	<tr>
	
	
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('verfahren'); ?></th>
			<th><?php echo $this->Paginator->sort('version'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($weldingmethods as $weldingmethod):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
	
		<td>
                <?php 
		echo $this->Html->link(h($weldingmethod['Weldingmethod']['name']), array(
								'action' => 'edit', 
								$weldingmethod['Weldingmethod']['id']
								), 
							array(
								'class'=>'round icon_edit mymodal hasmenu1',
								'rev' => $weldingmethod['Weldingmethod']['id']
								)
							);

		  ?>&nbsp;</td>
		<td><?php echo h($weldingmethod['Weldingmethod']['verfahren']); ?>&nbsp;</td>
		<td><?php echo h($weldingmethod['Weldingmethod']['version']); ?>&nbsp;</td>
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