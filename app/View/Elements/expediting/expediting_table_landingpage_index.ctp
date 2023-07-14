<table id="" class="advancetool">

<?php // echo $this->element('expediting/expediting_table_head_landingpage_index');?>

<?php
if(!isset($paging)) $paging['tr_marker'] = null;

$i = 0;
if(isset($this->request->data['Supplier'])){

	foreach($this->request->data['Supplier'] as $value){

		$class = null;
	
		$this->request->projectvars['VarsArray'][2] = $value['Supplier']['id'];
	
		if($i++ % 2 == 0) $class = ' class="altrow infinite_sroll_item ' . $paging['tr_marker'] . '"';
		if($i++ % 2 == 0) $class = ' class="altrow infinite_sroll_item ' . $paging['tr_marker'] . '"';
	
		echo '<tr ' . $class . '>';
	
		echo $this->element('expediting/expediting_table_navi_cell_landingpage_index',array('_examiner' => $value));
	
		echo '</tr>';
	
	}
}
?>
</table>

<?php // TODO: URL überarbeiten ?>
<div class="testtesttest" data-url="/suppliers/index/0/0"></div>
<?php echo $this->element('expediting/js/expediting_table_load_legend',array('loc' => 'div.testtesttest','attr' => 'data-url'));?>
