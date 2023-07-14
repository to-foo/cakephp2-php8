<div class="stripData index modalarea">
	<h2><?php echo __('Strip Data'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('year'); ?></th>
			<th><?php echo $this->Paginator->sort('description'); ?></th>
			<th><?php echo $this->Paginator->sort('certificate'); ?></th>
			<th><?php echo $this->Paginator->sort('batch_no'); ?></th>
			<th><?php echo $this->Paginator->sort('examination_object'); ?></th>
			<th><?php echo $this->Paginator->sort('development'); ?></th>
	</tr>
	<?php foreach ($stripData as $stripDatum): ?>
	<tr class="weld" rel="<?php echo $stripDatum['StripDatum']['id']; ?>">
		<td><?php echo h($stripDatum['StripDatum']['year']); ?>&nbsp;</td>
		<td><?php echo h($stripDatum['StripDatum']['description']); ?>&nbsp;</td>
		<td><?php echo h($stripDatum['StripDatum']['certificate']); ?>&nbsp;</td>
		<td><?php echo h($stripDatum['StripDatum']['batch_no']); ?>&nbsp;</td>
		<td><?php echo h($stripDatum['StripDatum']['examination_object']); ?>&nbsp;</td>
		<td><?php echo h($stripDatum['StripDatum']['development']); ?>&nbsp;</td>
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
		if($this->request->params['paging']['StripDatum']['pageCount'] > 1) {
			echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
			echo $this->Paginator->numbers(array('separator' => ''));
			echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
		}
	?>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		$('.modalarea tr.weld').on('click', function() {
			id = $(this).attr('rel');
			data = [{'name': 'ajax_true', 'value': 1}, {'name': 'id', 'value': id}];
			
			$.ajax({
				'url': '<?php echo Router::url(array_merge(array('action'=>'view'), $this->request->projectvars['VarsArray'])); ?>',
				'type': 'post',
				'data': data,
				'success': function(data) {
					$('#dialog').html(data);
				}
			});
		}).css('cursor','pointer').contextmenu({
    		autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: " <?php echo __('edit'); ?> ",
				cmd: "edit",
				action :	function(event, ui) {
								$("#dialog").load("<?php echo Router::url(array_merge(array('action'=>'edit'), $VarsArray)); ?>", {
									"ajax_true": 1,
									"id": parseInt(ui.target.closest('tr').attr('rel'))
								});
							},
				uiIcon: "qm_edit"
				},
				{
				title: "----"
				},
				{
				title: " <?php echo __('delete'); ?> ",
				cmd: "delete",
				action :	function(event, ui) {
								$("#dialog").load("<?php echo Router::url(array_merge(array('action'=>'delete'), $VarsArray)); ?>", {
									"ajax_true": 1,
									"id": parseInt(ui.target.closest('tr').attr('rel'))
								});
							},
				uiIcon: "qm_delete"
				}
			]
				
    	});
	});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>