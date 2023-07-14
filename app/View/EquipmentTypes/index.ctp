<?php
$activ_deactiv = array(0 => __('active'),1 => __('deactive'));
?>
<div class="modalarea">
	<h2><?php echo __('Equipment Types'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('discription'); ?></th>
			<th><?php echo __('Project', true); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
	</tr>
	<?php foreach ($equipmentTypes as $equipmentType): ?>
	<?php $this->request->projectvars['VarsArray'][1] = $equipmentType['EquipmentType']['id'];?>            
	<tr>
		<td>
		<span class="for_hasmenu1 weldhead">
		<?php echo $this->Html->link(h($equipmentType['EquipmentType']['discription']), 
			array_merge(array('action' => 'edit'), 
			$this->request->projectvars['VarsArray']), 
			array(
				'class'=>'round icon_edit mymodal hasmenu1',
				'rev' => implode('/',$this->request->projectvars['VarsArray'])
			)
		); ?>
        </span>
        
        &nbsp;</td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Project'); ?>: 
		</span>
		<?php echo h($equipmentType['Topproject']['projektname']); ?>
        &nbsp;</td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Status'); ?>: 
		</span>
		<?php echo $activ_deactiv [$equipmentType['EquipmentType']['status']]; ?>
        &nbsp;</td>
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
				title: "<?php echo __('Edit');?>", 
				cmd: "edit", 
				action :	function(event, ui) {
							$("#dialog").load("equipmenttypes/edit/" + ui.target.attr("rev"), {
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
				cmd: "delete", 
				action :	function(event, ui) {
								checkDuplicate = confirm("<?php echo __('Soll dieser Komponentetype gelöscht werden?');?>");
								if (checkDuplicate == false) {
									return false;
								}
					
							$("#dialog").load("equipmenttypes/delete/" + ui.target.attr("rev"), {
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
<?php 
if(isset($reportnumberID) && $reportnumberID > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,$evalutionID,$FormName);
	} 
?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?> 
