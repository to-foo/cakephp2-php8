<div class="stripData view modalarea">
	<h2><?php  echo __('Strip details'); ?></h2>
	<form>
	<fieldset>
	<div>
		<p class="title"><?php echo __('Description'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['description']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Batch No'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['batch_no']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Certificate'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['certificate']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Sr'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['Sr']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Cr'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['Cr']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Processor Type'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['processor_type']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Processor F No'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['processor_f_no']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Developer Type'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['developer_type']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Developer Temp'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['developer_temp']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Developer Replenishment'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['developer_replenishment']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Fixer Type'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['fixer_type']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Fixer Temp'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['fixer_temp']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Fixer Replenishment'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['fixer_replenishment']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Examination Object'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['examination_object']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Year'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['year']); ?></span>
	</div>
	<div>
		<p class="title"><?php echo __('Development'); ?></p>
		<span class="value"><?php echo h($stripDatum['StripDatum']['development']); ?></span>
	</div>
	</fieldset>
	</form>
</div>
<div class="related">
	<?php $noyes = array(__('no', true), __('yes', true)); ?>
	<h3><?php echo __('Related Strip Evaluations'); ?></h3>
	<?php if (!empty($stripDatum['stripEvaluation'])): ?>
	<table cellpadding = "0" cellspacing = "0">
	<tr>
		<th><?php echo __('Date'); ?></th>
		<th><?php echo __('D0'); ?></th>
		<th><?php echo __('Dx'); ?></th>
		<th><?php echo __('Dx4'); ?></th>
		<th><?php echo __('Developer Fresh'); ?></th>
		<th><?php echo __('Fixer Fresh'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($stripDatum['stripEvaluation'] as $stripEvaluation): ?>
		<tr class="weld" rel="<?php echo $stripEvaluation['id']; ?>">
			<td><?php echo $stripEvaluation['date']; ?></td>
			<td><?php echo $stripEvaluation['D0']; ?></td>
			<td><?php echo $stripEvaluation['Dx']; ?></td>
			<td><?php echo $stripEvaluation['Dx4']; ?></td>
			<td><?php echo $noyes[$stripEvaluation['developer_fresh']]; ?></td>
			<td><?php echo $noyes[$stripEvaluation['fixer_fresh']]; ?></td>
		</tr>
	<?php endforeach; ?>
	</table>
<?php endif; ?>

</div>

<script type="text/javascript">
$(document).ready(function() {
	$('#dialog .settingslink a:not(.printlink)').on('click', function() {
		if($(this).attr('id') == 'closethismodal') {
			$('#dialog').dialog('close');
			return false;
		}
		
		data = [];
		data.push({'name': 'ajax_true', 'value':1});
		data.push({'name': 'id', 'value': <?php echo $strip_id; ?>});
		
		$.ajax({
			'url': $(this).attr('href'),
			'type': 'post',
			'data': data,
			'success': function(data) {
				$('#dialog').html(data);
			}
		});
		return false;
	});
	
	$('.weld').on('click', function() {
		$("#dialog").load("<?php echo Router::url(array_merge(array('action'=>'editevaluation'), $VarsArray)); ?>", {
			"ajax_true": 1,
			"id": parseInt($(this).attr('rel'))
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
								$("#dialog").load("<?php echo Router::url(array_merge(array('action'=>'editevaluation'), $VarsArray)); ?>", {
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
								$("#dialog").load("<?php echo Router::url(array_merge(array('action'=>'deleteevaluation'), $VarsArray)); ?>", {
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