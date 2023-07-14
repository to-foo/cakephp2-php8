<div class="quicksearch">
<?php //echo $this->Navigation->quickSearching('quicksearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php //echo $this->element('barcode_search');?>
<?php echo $this->element('subdevisions_form');?>
<?php echo $this->element('search_case');?>
<?php
	if (count($SearchFormData['Result']['Reports']) < 2600 ){
		echo $this->Html->link(__('export results',true), array_merge(array('controller' => 'reportnumbers','action' => 'printresultcsv'), $this->request->projectvars['VarsArray']), array('title' => __('export results',true),'class' => 'printlink icon icon_export'));
	}
	?>
</div>
<div class="pagin_links">
</div>
<div class="clear"></div>
<div class="reportnumbers index inhalt">

<h2><?php echo __('Searching result'); ?></h2>
<?php if(isset($writeprotection) && $writeprotection) echo $this->Html->tag('p', __('Report is writeprotected - changes will not be saved.'), array('class'=>'error')); ?>

<?php
if(count($reportnumbers) == 0){
echo '<div class="error">';
echo __('There are no reports from your testing company.', true);
echo '</div>';
}
?>

<?php echo $this->Form->end(); ?>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('number'); ?></th>
			<th><?php echo __('Testingmethod',true) ?></th>
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
	?>
	<tr class="<?php echo $class;?>">
		<td><span class="for_hasmenu">
		<?php
		$this->request->projectvars['VarsArray'][4] = $reportnumber['Reportnumber']['id'];
		$separatpr = Configure::read('Separator');

		if($reportnumber['Reportnumber']['delete'] != 1){

/* 06.09.2017 Florian Zumpe --- Anzeige von NE-Berichten f�r Fremdfirmen als Reparaturlink ohne Kontextmen� */

		$class = 'round ajax hasmenu testingreportlink';
		$action = 'edit';
		// Wenn der Pr�fbericht nicht der Firma geh�rt, dann nur Reparaturrbericht zulassen
		if(($authUser->user('testingcomp_id') != $reportnumber['Reportnumber']['testingcomp_id']) && $authUser->user('roll_id') > 4) {
			$class = 'round ajax repairreport testingreportlink';
			$action = 'view';
			$Term = array($reportnumber['Reportnumber']['topproject_id'],$reportnumber['Reportnumber']['cascade_id'],$reportnumber['Reportnumber']['order_id'],$reportnumber['Reportnumber']['report_id'],$reportnumber['Reportnumber']['id']);

			echo $this->Html->link(h($this->Pdf->ConstructReportName($reportnumber)),
				array_merge(
					array('controller' => 'reportnumbers','action' => $action),$Term),
				array(
					'class' => $class
				)
			);

		} else {
			$Term = array($reportnumber['Reportnumber']['topproject_id'],$reportnumber['Reportnumber']['cascade_id'],$reportnumber['Reportnumber']['order_id'],$reportnumber['Reportnumber']['report_id'],$reportnumber['Reportnumber']['id']);
			echo $this->Html->link(h($this->Pdf->ConstructReportName($reportnumber)),array_merge(array('controller' => 'reportnumbers','action' => $action),$Term),
				array(
					'class' => $class,
					'rev' => implode('/',$this->request->projectvars['VarsArray']))
			);
		}
		}
		if($reportnumber['Reportnumber']['delete'] == 1){
			echo h($reportnumber['Reportnumber']['year']).'-'.h($reportnumber['Reportnumber']['number']);
			$roll = $authUser->user('Roll');
		}

		?>
        </span></td>
        <td><?php echo $reportnumber['Testingmethod']['verfahren'];?></td>
        <td><?php echo $this->ViewData->ShowInfo($reportnumber);?>
        </td>
		<td><?php echo $this->element('reports/testing_areas',array('reportnumber' => $reportnumber));?></td>
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
					echo $reportnumber[trim($item->model)][trim($item->key)];
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
	<div class="paging">
	<?php
		echo $this->Paginator->first(__('first', true), array('class' => 'first disabled'));
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
		echo $this->Paginator->last(__('last', true), array('class' => 'last disabled'));
	?>
	</div>
	<p class="paging_query">
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>
	</p>

<a href=""
</div>
<div class="clear" id="testdiv"></div>

<?php
echo $this->element('js/searchresult_link',array('url' => $this->request->url));
echo $this->element('js/modal_testingareas_result',array('url' => $this->request->url));
echo $this->element('js/searchresult_link_close');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_paging_searchresult');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/form_button_set');
echo $this->element('js/maximize_modal');
?>

<?php //echo $this->JqueryScripte->LeftMenueHeight(); ?>
