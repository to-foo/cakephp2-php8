<h3><?php echo __('Testing Instructions',true); ?></h3>
<div class="inhalt">
<div class="quicksearch">
<?php
echo $this->Html->link(__('Open testinginstructions modul',true),
	array(
		'controller' => 'testinginstructions',
		'action' => 'index'
	),
	array(
		'class' => 'round ajax',
		'title' => __('Open testinginstructions modul',true)
	)
);

if(isset($ControllerQuickSearch)){
	echo $this->Navigation->quickComponentSearching('quicksearch',$ControllerQuickSearch,true);
}
?>
</div>
<?php
if (isset($Testinginstructions)){
if(count($Testinginstructions) == 0){
	echo '<div class="hint"><p>';
	echo __('No results available.',true);
	echo '</p></div>';
}
}
?>

<table class="table_resizable table_infinite_sroll">
<tr>
<th><?php echo __('Description',true);?></th>
<th><?php echo __('Linked',true);?></th>
<th><?php echo __('Remark',true);?></th>
</tr>
<?php
foreach($Testinginstructions as $_key => $_data){
	echo '<tr>';
        $this->request->projectvars['VarsArray'][15] = $_data['Testinginstruction']['id'];
	echo '<td>'; 	echo $this->Html->link($_data['Testinginstruction']['name'],array_merge(array('action' => 'edit'),$this->request->projectvars['VarsArray']),array('class' => 'round ajax'));
echo '</td>';
	echo '<td>';
	echo '<ul>';
	foreach($_data['Linked'] as $__key => $__data){
		echo '<li>';
		echo __('For',true) . ' ' . $__data['Testingcomp']['name'] . ' ' .  __('in',true) . ' ';
		echo $__data['Topproject']['projektname'] . ' > ';
		echo $__data['Report']['name'] . ' > ';
		echo $__data['Testingmethod']['verfahren'];
		echo '</li>';
	}
	echo '</ul>';
	echo '</td>';
	echo '<td>' . $_data['Testinginstruction']['description'] . '</td>';
	echo '</tr>';
}
?>
</table>
</div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
