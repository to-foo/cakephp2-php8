<div class="modalarea">
	<h2><?php echo __('Testingcomps'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('name'); ?></th>
			<th><?php echo $this->Paginator->sort('roll_id', __('highest valid roll')); ?></th>
			<th><?php echo $this->Paginator->sort('firmenname'); ?></th>
			<th><?php echo $this->Paginator->sort('firmenzusatz'); ?></th>
			<th><?php echo $this->Paginator->sort('strasse'); ?></th>
			<th><?php echo $this->Paginator->sort('plz'); ?></th>
			<th><?php echo $this->Paginator->sort('ort'); ?></th>
	</tr>
	<?php
		$i = 0;
		foreach ($testingcomps as $testingcomp):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}

	?>
	<tr<?php echo $class;?>>
		<td>
		<span class="for_hasmenu1 weldhead">
		<?php 
		echo $this->Html->link(($testingcomp['Testingcomp']['name']), array(
								'action' => 'view', 
								$testingcomp['Testingcomp']['id']
								), 
							array(
								'class'=>'round mymodal hasmenu1',
								'rev' => $testingcomp['Testingcomp']['id']
								)
							);

		?>
        </span>
        </td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Rang'); ?>: 
		</span>
		<?php echo h($testingcomp['Roll']['name']); ?>&nbsp;
		</td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Firmenname'); ?>: 
		</span>        
		<?php echo h($testingcomp['Testingcomp']['firmenname']); ?>&nbsp;
        </td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Firmenzusatz'); ?>: 
		</span>        
		<?php echo h($testingcomp['Testingcomp']['firmenzusatz']); ?>&nbsp;
        </td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Straße'); ?>: 
		</span>        
		<?php echo h($testingcomp['Testingcomp']['strasse']); ?>&nbsp;
        </td>
		<td>
        <span class="discription_mobil">
		<?php echo __('PLZ'); ?>: 
		</span>        
		<?php echo h($testingcomp['Testingcomp']['plz']); ?>&nbsp;
        </td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Ort'); ?>: 
		</span>        
		<?php echo h($testingcomp['Testingcomp']['ort']); ?>&nbsp;
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
							$("#dialog").load("testingcomps/view/" + ui.target.attr("rev"), {
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
							$("#dialog").load("testingcomps/edit/" + ui.target.attr("rev"), {
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
								checkDuplicate = confirm("<?php echo __('Soll dieses Unternehmen gelöscht werden?');?>");
								if (checkDuplicate == false) {
									return false;
								}
					
							$("#dialog").load("testingcomps/delete/" + ui.target.attr("rev"), {
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