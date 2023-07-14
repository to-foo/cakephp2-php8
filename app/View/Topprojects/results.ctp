<div class="modalarea">
<h2><?php echo __('Results for Orders');?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('order-number'); ?></th>
			<th><?php echo __('Project', true) ?></th>
			<th><?php echo __('EquipmentType', true) ?></th>
			<th><?php echo __('Equipment', true) ?></th>
                        <th><?php echo __('Remarks', true) ?></th>
			
			<th><?php echo __('Modified', true) ?></th>
			<th><?php echo __('Status', true) ?></th>
	</tr>
	<?php
	$i = 0;
	
	$status[0] = __('Open');
	$status[1] = __('Closed');

	foreach ($results as $order):

		if(isset($order['Order']['revision']) && $order['Order']['revision'] == 1){
			$orderKat = 2;
		}
		elseif (isset($order['Order']['laufender_betrieb']) && $order['Order']['laufender_betrieb'] == 1){
			$orderKat = 1;
		}

		$class = null;
		if ($i++ % 2 == 0) {
			$class = 'altrow';
		}
		
	?>
	<tr class="<?php echo $class;?>">
		<td>
		<?php
		echo $this->Html->link(__('Order', true).' '.h($order['Order']['auftrags_nr']), array(
				'controller' => 'reportnumbers', 
				'action' => 'index',
					$order['Deliverynumber']['topproject_id'], 
					$order['Deliverynumber']['equipment_type_id'], 
					$order['Deliverynumber']['equipment_id'], 
					$order['Deliverynumber']['order_id'], 
				), array('class'=>'round ajax'));	
		?>
		</td>
		<td><?php echo $order['Topproject']['projektname'];?></td>
		<td><?php echo $order['Deliverynumber']['EquipmentType']['discription'];?></td>
		<td><?php echo $order['Deliverynumber']['Equipment']['discription'];?></td>
		<td><?php echo $order['Order']['remarks'];?></td>
		<td><?php echo $order['Order']['modified'];?></td>
		<td><?php echo $status[$order['Order']['status']];?></td>
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
<?php echo $this->JqueryScripte->ModalFunctions(); ?>

