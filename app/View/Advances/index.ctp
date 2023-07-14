<div class="inhalt">
<h2><?php echo __('Advance manager',true); ?></h2>
<div class="quicksearch">
<?php
echo $this->Html->link(__('Add advance',true), array_merge(array('action' => 'add'), array()), array('class' => 'modal icon icon_examiners_add','title' => __('Add advance',true)));
?>
<div class="clear"></div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
</div>

<table class="table_resizable table_infinite_sroll">
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
