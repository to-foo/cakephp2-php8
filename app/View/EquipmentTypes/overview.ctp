<div class="quicksearch">
<?php echo $this->Navigation->quickSearching('quicksearchtype',1,__('Equipment type', true)); ?>
<?php echo $this->Navigation->quickOrderSearching('quickreportsearch',2,__('Order', true)); ?>
<?php echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php echo $this->element('subdevisions_form');?>
</div>
<div class="equipmentTypes index inhalt">
	<h2><?php echo __('Equipment Types'); ?></h2>
<?php 
if(count($equipmentTypes) == 0){
	echo $this->Navigation->makeLink('equipmenttypes','add',__('Add new equipment type'),'modal round',null,$this->request->projectvars['VarsArray']);
	echo '<div class="clear"></div>';
}
?>
	<ul class="listemax">
	<?php foreach ($equipmentTypes as $equipmentType): ?>
		<?php $this->request->projectvars['VarsArray'][1] = $equipmentType['EquipmentType']['id'];?>            
   		<li class="icon_discription icon_view"><span></span>
		<?php echo $this->Navigation->makeLink('equipmenttypes','view',$equipmentType['EquipmentType']['discription'],'ajax',null,$this->request->projectvars['VarsArray']); ?>
        </li>
	<?php endforeach; ?>
	</ul> 
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