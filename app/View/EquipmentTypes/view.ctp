<div class="quicksearch">
<?php echo $this->Navigation->quickSearching('quicksearch',1,__('Equipment', true)); ?>
<?php echo $this->Navigation->quickOrderSearching('quickreportsearch',2,__('Order', true)); ?>
<?php echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php echo $this->element('subdevisions_form');?>
</div>
<div class="equipmentTypes index inhalt">
<h2><?php echo __('Equipment'); ?></h2>
<?php 
if(count($equipments) == 0){
	echo $this->Navigation->makeLink('equipments','add',__('Add new equipment'),'modal round',null,$this->request->projectvars['VarsArray']);
}
?>

	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('discription'); ?></th>
			<th class="actions"><?php echo __('Actions'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($equipments as $topproject):
	
		$this->request->projectvars['VarsArray'][2] = $topproject['Equipment']['id'];

		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td>
		<?php echo $this->Navigation->makeLink('equipments','view',h($topproject['Equipment']['discription']),'round ajax',null,array(0=>$topproject['Equipment']['topproject_id'],1=>$topproject['Equipment']['equipment_type_id'],2=>$topproject['Equipment']['id'])); ?>
        </td>
		<td class="actions">
			<?php // echo $this->Navigation->makeLink('equipments','edit',__('Edit'),'icon icon_edit modal',null,$this->request->projectvars['VarsArray']); ?>
			<?php // echo $this->Navigation->makeLink('equipments','view',__('View'),'icon icon_view modal',null,$this->request->projectvars['VarsArray']); ?>
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
<?php  echo $this->JqueryScripte->LeftMenueHeight(); ?>