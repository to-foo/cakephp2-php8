<div class="clear"></div>
<div class="modalarea detail">
	<h2><?php echo __('Search devices'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo h('barcode'); ?></th>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo h(__('testingmethod')); ?></th>
			<th><?php echo $this->Paginator->sort('producer'); ?></th>
			<th><?php echo $this->Paginator->sort('device_type'); ?></th>
			<th><?php echo $this->Paginator->sort('registration_no'); ?></th>
			<th><?php echo $this->Paginator->sort('webplan'); ?></th>
			<th><?php echo $this->Paginator->sort('working_place'); ?></th>
			<th><?php echo $this->Paginator->sort('first_registration'); ?></th>
			<th><?php echo $this->Paginator->sort('remark'); ?></th>
			<th class="actions">&nbsp;</th>
	</tr>
	<?php
	foreach ($results as $result):
	?>
	<tr>
		<td></td>
        <td><?php
        	$method = reset($result['DeviceTestingmethod']);
        	echo $this->Html->link($result['Device']['name'], array('controller'=>'devices', 'action'=>'view', 0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,$method['id'],$result['Device']['id']), array('class'=>'round ajax')); ?></td>
        <td><?php echo join(', ', Hash::extract($result, 'DeviceTestingmethod.{n}.verfahren')); ?></td>
        <td><?php echo h($result['Device']['producer']); ?></td>
        <td><?php echo h($result['Device']['device_type']); ?></td>
        <td><?php echo h($result['Device']['registration_no']); ?></td>
        <td><?php echo h($result['Device']['webplan']); ?></td>
        <td><?php echo h($result['Device']['working_place']); ?></td>
        <td><?php echo h($result['Device']['first_registration']); ?></td>
        <td><?php echo h($result['Device']['remark']); ?></td>
		<td class="actions">
			<?php
			?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled mymodal'));
		echo $this->Paginator->numbers(array('separator' => '', 'class'=>'mymodal'));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled mymodal'));
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
<script type="text/javascript">
	$(document).ready(function(){
		$('th .mymodal, .paging .mymodal').data(<?php echo json_encode($this->request->data); ?>);
		$(document).tooltip({
			open: function( event, ui ) {
				ui.tooltip.html(ui.tooltip.text().replace(/[\r\n]+/,"<br />"));
			}
		});
	});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
