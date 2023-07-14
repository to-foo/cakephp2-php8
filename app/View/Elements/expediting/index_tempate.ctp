<?php
if(count($Expeditingset) == 0) return;
?>
<table id="" class="advancetool">
<thead>
<tr>
<th></th>
<th><?php echo __('Name');?></th>	
<th><?php echo __('Description');?></th>	
</tr>
</thead>
<tbody>
<?php
foreach($Expeditingset as $key => $value){

	$VarsArray = $this->request->projectvars['VarsArray'];
	$VarsArray[3] = $value['Expeditingset']['id'];

	echo '<tr>';
	echo '<td class="collaps nowrap">';
	echo '<div class="flex_info_table_cell">';
	echo $this->Html->link(__('Choose'),
	array_merge(array('controller' => 'expeditings', 'action' => 'addtemplate'), $VarsArray),
	array(
	  'class' => 'round mymodal',
	  'title' => __('Choose this ITP', true),

	  )
  	);

	echo $this->Html->link('Edit',
  	array_merge(array('controller' => 'expeditings', 'action' => 'edittemplate'), $VarsArray),
      array(
        'class' => 'icon icon_edit mymodal',
        'title' => __('Edit this ITP', true),

    	)
	);
	echo $this->Html->link('Delete',
  	array_merge(array('controller' => 'expeditings', 'action' => 'deltemplate'), $VarsArray),
      array(
        'class' => 'icon icon_delete mymodal',
        'title' => __('Delete this ITP', true),

    	)
	);
	echo '</div>';
	echo '</td>';
	echo '<td class="collaps nowrap">';
	echo $value['Expeditingset']['name'];
	echo '</td>';
	echo '<td>';
	echo $value['Expeditingset']['description'];
	echo '</td>';
	echo '</tr>';
}
?>
</tbody>
</table>
