<div class="modalarea">
  
	<h2><?php   pr($dropdown);echo __('Dependencies').': '.   isset($dropdown['Dropdown'][$locale]) ? $dropdown['Dropdown'][$locale] : ' '; ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('dropdowns_value_id', __('Dropdown value')); ?></th>
			<th><?php echo $this->Paginator->sort('dropdowns_value_id', __('Dependent fields')); ?></th>
	</tr>
	<?php foreach ($dependencies as $dependency): ?>
	<tr>
			<?php
			$x = 1;
			echo '<td><strong>'. $dependency['DropdownsValue']['discription']. '</strong></td>';
			echo '<td>';
			foreach($fields as $_key => $_fields){
				echo $this->Html->link($_fields['discription'] . ' (' . intval(@$fieldsCount[$dependency['DropdownsValue']['id']][$_key]['count']) . ') ', array('controller' => 'dependencies', 'action' => 'overview',
				$this->request->projectvars['VarsArray'][0],
				$this->request->projectvars['VarsArray'][1],
				$this->request->projectvars['VarsArray'][2],
				$this->request->projectvars['VarsArray'][3],
				$this->request->projectvars['VarsArray'][4],
				$this->request->projectvars['VarsArray'][5],
				$this->request->projectvars['VarsArray'][6],
				$this->request->projectvars['VarsArray'][7],
				$dependency['DropdownsValue']['id'],
				$x
				),
				array('class' => 'mymodal round'));
			$x++;
			}
			echo '</td>';
			?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging mymodal">
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
