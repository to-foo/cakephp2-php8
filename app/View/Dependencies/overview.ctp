<div class="modalarea dependencies index">
	<h2><?php echo __('Dependencies').': '.$dropdown['Dropdown'][$locale]. ' > ' .@$thisDropdownsValue['DropdownsValue']['discription'] . ' > ' .@$thisfieldDiscription;?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
	<th><?php echo $this->Paginator->sort('value'); ?></th>
	<th><?php echo $this->Paginator->sort('field'); ?></th>
	<th><?php echo $this->Paginator->sort('field',__('Main fild')); ?></th>
	<th><?php echo h(__('global')); ?></th>
	<th class="actions"><?php echo __('Actions'); ?></th>
    </tr>
    <?php $noyes = array(__('no'), __('yes')); ?>
	<?php foreach ($dependencies as $dependency): ?>
    <tr>
    <td><?php echo ($dependency['Dependency']['value']);?></td>
	<td><?php echo h($settings->{$dropdown['Dropdown']['model']}->{$dependency['Dependency']['field']}->discription->$locale); ?>&nbsp;</td>
    <td><?php echo $dropdown['Dropdown'][$locale];?></td>
    <td><?php echo $noyes[intval($dependency['Dependency']['global'])];?></td>
    <td class="actions">
	<?php $this->request->projectvars['VarsArray'][10] = $dependency['Dependency']['id'];?>
	<?php echo $this->Html->link(__('Delete'), array_merge(array('action' => 'delete'), $this->request->projectvars['VarsArray']), array('class'=>'icon icon_delete mymodal')); ?>
	<?php echo $this->Html->link(__('Edit'), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class'=>'icon icon_edit mymodal')); ?>
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
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
