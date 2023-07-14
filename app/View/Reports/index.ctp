<div class="modalarea">
<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div>        
	<h2><?php echo __('Reports'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('identification'); ?></th>
			<th><?php echo $this->Paginator->sort('projektbeschreibung'); ?></th>
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
		<td>
		<span class="for_hasmenu1 weldhead">
		<?php 
		echo $this->Html->link($report['Report']['name'], 
			array('action' => 'edit', 
				$report['Report']['id']), 
			array(
				'class'=>'round icon_edit mymodal hasmenu1',
				'rev' => $report['Report']['id']
				)
			); 
		?>
        </span>
        </td>
		<td>
        <span class="discription_mobil">
		<?php echo __('identification'); ?>: 
		</span>
		<?php echo h($report['Report']['identification']); ?>&nbsp;
        </td>
		<td>
        <span class="discription_mobil">
		<?php echo __('projektbeschreibung'); ?>: 
		</span>
		<?php echo h($report['Report']['projektbeschreibung']); ?>&nbsp;
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
<script type="text/javascript">
	$(document).ready(function(){

		$("span.for_hasmenu1").contextmenu({
			delegate: ".hasmenu1",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: "<?php echo __('View');?>", 
				cmd: "status", 
				action :	function(event, ui) {
							$("#dialog").load("reports/view/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_show", 
				disabled: false 
				},
				{
					title: "----"
				},
				{
				title: "<?php echo __('Edit');?>", 
				cmd: "status", 
				action :	function(event, ui) {
							$("#dialog").load("reports/edit/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_edit", 
				disabled: false 
				},
				{
					title: "----"
				},
				{
				title: "<?php echo __('Delete');?>", 
				cmd: "status", 
				action :	function(event, ui) {
								checkDuplicate = confirm("<?php echo __('Soll diese Prüfberichtsmappe gelöscht werden?');?>");
								if (checkDuplicate == false) {
									return false;
								}
					
							$("#dialog").load("reports/delete/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_delete", 
				disabled: false 
				}
				],

			select: function(event, ui) {},
		});
	});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>