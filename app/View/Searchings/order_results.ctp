<div class="quicksearch">
<?php //echo $this->Navigation->quickSearching('quicksearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php // echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php // echo $this->element('subdevisions_form');?>
<?php echo $this->element('search_case');?>
</div>
<div class="pagin_links">
</div>
<div class="clear"></div>
<div class="reportnumbers index inhalt">
<h2><?php echo __('Searching result'); ?></h2>
<?php
if(count($Data) == 0){
echo '<div class="error">';
echo __('There are no orders from your testing company.', true);
echo '</div>';
}
?>
<?php // echo $this->element('search_history');?>
<table cellpadding="0" cellspacing="0">
<tr>
<th><?php echo $this->Paginator->sort('auftrags_nr'); ?></th>
<th><?php echo $this->Paginator->sort('status'); ?></th>
<?php
foreach($items as $item) {
	if(isset ($item->hidden) && $item->hidden == 1 ) continue;
	echo '<th>';
	if(isset($item->sortable) && $item->sortable == 1) echo $this->Paginator->sort(trim($item->key), isset($item->description) && trim($item->description) != '' ? __(ucfirst(trim($item->description))) : __(ucfirst(trim($item->key))));
	else echo h(isset($item->description) && trim($item->description) != '' ? __(ucfirst(trim($item->description))) : __(ucfirst(trim($item->key))));
	echo '</th>';
}
?>
</tr>
<?php
$i = 0;

foreach ($Data as $reportnumber):

	$class = null;
	if ($i++ % 2 == 0) {
	$class = ' altrow';
}
?>
</tr>
<tr class="<?php echo $class;?>">
<td>
<?php
$this->request->projectvars['VarsArray'][1] = $reportnumber['Order']['cascade_id'];
$this->request->projectvars['VarsArray'][2] = $reportnumber['Order']['id'];

echo $this->Html->link($reportnumber['Order']['auftrags_nr'],array_merge(array('controller' => 'reportnumbers','action' => 'index'),$this->request->projectvars['VarsArray']),
				array(
					'class' => 'round ajax testingreportlink',
					'rev' => implode('/',$this->request->projectvars['VarsArray']))
			);
?>
</td>
<td>
<?php
/*
echo $this->Html->link(count($reportnumber['Reportnumber']) . ' ' . __('test reports',true),array_merge(array('controller' => 'searchings','action' => 'results'),$this->request->projectvars['VarsArray']),
				array('class' => 'round ajax')
			);
*/
$this->Additions->ShowExpediting($reportnumber);
?>
</td>
<?php
foreach($items as $item) {
if(isset ($item->hidden) && $item->hidden == 1 ) continue;
echo '<td>';
echo '<span class="discription_mobil">';
echo __(isset($item->description) && trim($item->description) != '' ? __(ucfirst(trim($item->description))) : __(ucfirst(trim($item->key)))).':';
echo '</span>';
if(isset($item->model) && isset($item->key) && isset($reportnumber[trim($item->model)][trim($item->key)])){
	echo $reportnumber[trim($item->model)][trim($item->key)];
} else {
	echo ' ';
}
echo '</td>';
}

		?>
<?php endforeach; ?>
</table>

</div>
<div class="clear" id="testdiv"></div>
<?php
//echo $this->JqueryScripte->LeftMenueHeight();
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/form_button_set');
echo $this->element('js/searchresult_link_close');
echo $this->element('js/searchresult_link',array('url' => $this->request->url));
echo $this->element('js/maximize_modal');
?>
<script type="text/javascript">

$(document).ready(function(){

	$("#container,table,a").click(function(){
		$("a.short_view").tooltip("close");
	})
});

</script>
