<div class="modalarea">
<h2><?php echo __('Select progress');?></h2>


	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('Project name'); ?></th>
			<th><?php echo __('Discription', true); ?></th>
	</tr>
	<?php 
	$i = 0;
	foreach ($developments as $development):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td>
        <span class="contexmenu_weldposition for_hasmenu_2">
		<?php 
		echo $this->Html->link(h($development['Development']['name']), 
				array('action' => 'orders', 
				$development['Development']['id'], 
				0, 
				0), 
				array(
				'class'=>'round icon_edit ajax hasmenu_2',
				'title' => __('Open this progress'),
				'rev' => $development['Development']['id']
				)
			);
		?>
        </span>
        </td>
		<td>
		<span class="discription_mobil">
		<?php echo __('Discription');?>: 
		</span>
		<?php echo h($development['Development']['discription']); ?>
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
<div class="clear" id="testdiv"></div> 
<script type="text/javascript">
	$(document).ready(function(){
		$("span.for_hasmenu_2").contextmenu({
			delegate: ".hasmenu_2",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: "<?php echo __('Bearbeiten');?>",
				cmd: "editevalution",
				action :	function(event, ui) {
							$("#dialog").load("developments/edit/" + ui.target.attr("rev"), {
									"ajax_true": 1
								})
							},
				uiIcon: "qm_edit"
				}
				],

			select: function(event, ui) {},
		});
	});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>


