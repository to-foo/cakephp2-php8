<div class="modalarea">
<h2><?php
if($reportnumber['Reportnumber']['parent_id'] == 0) echo __('Assigned reports for %s', $this->Pdf->ConstructReportName($reportnumber,3));
else echo __('Parent report for %s', $this->Pdf->ConstructReportName($reportnumber,3));
?></h2>
<?php echo $this->element('Flash/_messages');?>

<div class="hint">
<?php
	echo '<div class="hint">';
		echo '<form class="login">';
		echo '<fieldset>';
		echo __('Copy general data from source report') . '<br>';
		echo $this->Form->input('setGenerally', array('type'=>'radio', 'options'=>array(__('no'), __('yes')), 'value'=>1, 'legend'=>' '));
		echo '</fieldset>';
	echo '</form>';
	echo '</div>';

	echo '<div class="quicksearch">';
	echo $this->element('searching/search_quick_assigned_reports',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Link report',true)));
	echo '</div>';

	echo '<div class="quicksearch">';

	echo $this->Html->link(
	__('Add assigned report',true),
	array_merge(
	array('controller'=>'testingmethods', 'action'=>'listing'),
	array(
	$reportnumber['Reportnumber']['topproject_id'],
	$reportnumber['Reportnumber']['cascade_id'],
	$reportnumber['Reportnumber']['order_id'],
	$reportnumber['Reportnumber']['report_id'],
	$reportnumber['Reportnumber']['id']
	)
	),
	array('class'=>'round add_assigned')
	);

	echo '</div>';
?>
</div>
	<table cellpadding="0" cellspacing="0" class="advancetool">
	<tr>
		<th><?php echo __('Report'); ?></th>
		<th></th>
		<th><?php echo __('Testingmethod',true);?></th>
	</tr>
	<?php
	foreach($assignedReports as $report) {
//		pr($report);
		echo '<tr>';
		echo '<td>';

		echo $this->Html->link(
			$report['Report']['name'].' '.$this->Pdf->ConstructReportName($report),
			array_merge(
				array('controller'=>'reportnumbers', 'action'=>'view'),
				array(
					$report['Reportnumber']['topproject_id'],
					$report['Reportnumber']['cascade_id'],
					$report['Reportnumber']['order_id'],
					$report['Reportnumber']['report_id'],
					$report['Reportnumber']['id']
				)
			),
			array('class'=>'round ajax')
		);

		echo '</td>';
		echo '<td class="">';

		$VarsArray = $this->request->projectvars['VarsArray'];
		$VarsArray[4] = $report['Reportnumber']['id'];

		echo $this->Html->link(
			__('Remove assignment'),
			array_merge(
				array('controller'=>'reportnumbers', 'action'=>'removechild'),
				$VarsArray
			),
			array(
				'class'=>'round icon_remove_assignment mymodal',
				'title'=>__('Remove assignment'),
			)
		);

		echo '</td>';

		echo '<td>';
		echo $report['Testingmethod']['verfahren'];
		echo '</td>';

		echo '</tr>';
	}
		?>
	</table>
	<div class="clear" id="testdiv"></div>
</div>
<div class="clear" id="testdiv"></div>
<?php
echo $this->element('reports/js/assigned_reports');
echo $this->element('js/form_button_set');
?>
