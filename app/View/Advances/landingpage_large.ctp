<h3><?php echo __('Advance manager',true); ?></h3>
<div class="users index inhalt">
<div class="quicksearch">
<?php
echo $this->Html->link(__('Open progress modul',true),
	array(
		'controller' => 'advances',
		'action' => 'index'
	),
	array(
		'class' => 'round ajax',
		'title' => __('Open progress modul',true)
	)
);
?>
</div>
<table class="advancetool table_infinite_sroll">
<tr>
<th><?php echo __('Description',true);?></th>
<th><?php echo __('Linked',true);?></th>
<th><?php echo __('Remark',true);?></th>
</tr>
<?php

foreach($Advance as $_key => $_data){

	if($_data['Advance']['status'] == 1) $class = 'deactive';
	else $class = null;

	echo '<tr class="' . $class . '">';
  $this->request->projectvars['VarsArray'][0] = $_data['Advance']['id'];
	echo '<td>'; 	echo $this->Html->link($_data['Advance']['name'],array_merge(array('action' => 'edit'),$this->request->projectvars['VarsArray']),array('class' => 'round ajax'));
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '<td>' . $_data['Advance']['description'] . '</td>';
	echo '</tr>';
}

?>
</table>
</div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/resize_table_column');
?>
