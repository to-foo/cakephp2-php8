<div class="quicksearch">
<?php echo $this->element('searching/search_quick_reportnumber',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
<?php echo $this->element('subdevisions_form');?>
<?php echo $this->element('rest/rest_link');?>
</div>
<div class="pagin_links">
<?php
if(!empty($reportBefore)){
	$reportBefore = reset($reportBefore);
	$desc_before = __('Previous report') . ' (' . $reportBefore['Report']['name'] . ')';
        $_VarsArray = $VarsArray;
        $_VarsArray[4] = $reportBefore['Report']['id'];
	echo $this->Html->Link($desc_before,array_merge(array('action' => 'show'), $_VarsArray),array('class' => 'icon icon_prev ajax','title' => $desc_before));
}
if(!empty($reportNext)){
	$reportNext = reset($reportNext);
	$desc_next = __('Next report') . ' (' . $reportNext['Report']['name'] . ')';
         $_VarsArray = $VarsArray;
        $_VarsArray[4] = $reportNext['Report']['id'];
	echo $this->Html->Link($desc_next,array_merge(array('action' => 'show'), $_VarsArray),array('class' => 'icon icon_next ajax','title' => $desc_next));
}
?>
</div>
<div class="clear"></div>
<div class="reportnumbers index inhalt">
	<h2><?php echo __('Reports').' '.$reportName; ?></h2>
	<?php if(isset($writeprotection) && $writeprotection) echo $this->Html->tag('p', __('Report is writeprotected - changes will not be saved.'), array('class'=>'error')); ?>

    <?php
	if(count($reportnumbers) == 0){
		echo '<div class="error">';
		echo __('There are no reports from your testing company.', true);
		echo '</div>';
	}
	?>

  <?php echo $this->element('reports/ask_master_mass_action_select');?>

	<table class="reports_table">
	<tr>
			<th><?php echo $this->element('reports/ask_master_mass_action');?> </th>
			<th><?php echo $this->Paginator->sort('number'); ?></th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
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
			<th class="actions">&nbsp;</th>
	</tr>
	<?php
	$i = 0;
	foreach ($reportnumbers as $reportnumber):

		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' altrow';
		}
		if($reportnumber['Reportnumber']['status'] == 1){
			$class = ' closed';
		}
		if($reportnumber['Reportnumber']['status'] == 2){
			$class = ' closed2';
		}
		if($reportnumber['Reportnumber']['status'] == 3){
			$class = ' settled';
		}
		if($reportnumber['Reportnumber']['delete'] == 1){
			$class = ' delete';
		}
	?>
	<tr id="<?php echo 'report_table_row_' . $reportnumber['Reportnumber']['id'];?>" class="<?php echo $class;?>">
		<td class="checkbox_massfunction"><?php echo $this->element('reports/checkbox_mass_function_report_table',array('reportnumber' => $reportnumber['Reportnumber']['id']));?></td>
		<td><span class="for_hasmenu">
		<?php
		$this->request->projectvars['VarsArray'][4] = $reportnumber['Reportnumber']['id'];
		$separatpr = Configure::read('Separator');

		if($reportnumber['Reportnumber']['delete'] != 1){

		$class = 'round ajax hasmenu';
		$action = 'edit';

		if(($authUser->user('testingcomp_id') != $reportnumber['Reportnumber']['testingcomp_id']) && $authUser->user('roll_id') > 4 && $reportnumber['Testingmethod']['value']<>'vtst') {
			$class = 'round modal repairreport';
			$action = 'repairs';
		}

		echo $this->Html->link(h($this->Pdf->ConstructReportName($reportnumber)),array_merge(array('action' => $action),$this->request->projectvars['VarsArray']),
				array(
					'class' => $class,
					'rev' => implode('/',$this->request->projectvars['VarsArray']))
			);
		}
		elseif($reportnumber['Reportnumber']['delete'] == 1){
			echo h($reportnumber['Reportnumber']['year']).'-'.h($reportnumber['Reportnumber']['number']);
			$roll = $authUser->user('Roll');
		}

		?>
        </span></td>
        <td><?php echo $this->ViewData->ShowInfo($reportnumber);?></td>
		<td>
		<?php
		if($authUser->user('testingcomp_id') == $reportnumber['Reportnumber']['testingcomp_id'] || $authUser->user('roll_id') < 5){
			echo $this->element('reports/testing_areas',array('reportnumber' => $reportnumber));
		}
		?>
		</td>
		<td>
		<?php echo '<span class="discription_mobil">';?>
		<?php echo __('Status');?>:
		<?php echo '</span>';?>
		<?php echo $this->ViewData->ShowStatus($reportnumber); ?>
    </td>
		<?php
			foreach($items as $item) {
        if(isset ($item->hidden) && $item->hidden == 1 ) continue;

				echo '<td>';
				echo '<span class="discription_mobil">';
				echo __(isset($item->description) && trim($item->description) != '' ? __(ucfirst(trim($item->description))) : __(ucfirst(trim($item->key)))).':';
				echo '</span>';
				if(isset($item->model) && isset($item->key) && isset($reportnumber[trim($item->model)][trim($item->key)])){
					if(isset($item->fieldtype) && $item->fieldtype == 'radio'){
									echo $item->radiooption->value[$reportnumber[trim($item->model)][trim($item->key)]];
					}else{
						echo $reportnumber[trim($item->model)][trim($item->key)];
					}
				} else {
					echo ' ';
				}
				echo '</td>';
			}

		?>
		<td class="actions">
		<?php
		?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<!--
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
-->

</div>
<?php
$CurrentUrl = $this->Html->url(array_merge(array('controller'=>'reportnumbers','action'=> $this->request->params['action']),$this->request->projectvars['VarsArray']));
echo $this->Form->input('CurrentUrl',array('value' => $CurrentUrl,'type' => 'hidden'));
?>
<div class="clear" id="testdiv"></div>
<?php
echo $this->element('reports/js/checkbox_mass_function_report_table');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_paging');
echo $this->element('js/contextmenue',array('SubMenueArray' => $SubMenueArray));

if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	echo $this->element('infinite/infinite_links');
	echo $this->element('infinite/infinite_scroll');
}
if(!Configure::check('InfiniteScroll') || (Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == false)){
	echo $this->element('page_navigation');
}
?>
