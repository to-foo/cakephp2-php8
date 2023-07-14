<?php if(!isset($this->request->data['Supplier'])) return;?>
<table id="" class="advancetool">

<?php echo $this->element('expediting/expediting_table_head');?>

<?php

if(!isset($paging)) $paging['tr_marker'] = null;

$i = 0;

foreach($this->request->data['Supplier'] as $_examiner){

	$class = null;

	$this->request->projectvars['VarsArray'][2] = $_examiner['Supplier']['id'];

	if($i++ % 2 == 0) $class = ' class="altrow infinite_sroll_item ' . $paging['tr_marker'] . '"';
	if($i++ % 2 == 0) $class = ' class="altrow infinite_sroll_item ' . $paging['tr_marker'] . '"';

	echo '<tr ' . $class . '>';

echo $this->element('expediting/expediting_table_navi_cell',array('_examiner' => $_examiner));
echo $this->element('expediting/expediting_table_cells',array('_examiner' => $_examiner));

	echo '</tr>';

}
?>

</table>
