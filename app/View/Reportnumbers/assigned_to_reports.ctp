<div class="modalarea">
<h2><?php echo $this->Pdf->ConstructReportName($reportnumbers['Child'],3);?></h2>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
?>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($reportnumbers['Parent'])){

	echo '<div class="hint">';

	echo $this->Html->link(__('Go to') . ' ' . $this->Pdf->ConstructReportName($reportnumbers['Parent']),
		array(
			'action' => 'view',
			$reportnumbers['Parent']['Reportnumber']['topproject_id'],
			$reportnumbers['Parent']['Reportnumber']['cascade_id'],
			$reportnumbers['Parent']['Reportnumber']['order_id'],
			$reportnumbers['Parent']['Reportnumber']['report_id'],
			$reportnumbers['Parent']['Reportnumber']['id']

		),
		array(
			'title' => $this->Pdf->ConstructReportName($reportnumbers['Parent']),
			'class' => 'round ajax'
		)
	);

	echo $this->Html->link(__('Remove association') . ' ' . __('from',true) . ' ' . $this->Pdf->ConstructReportName($reportnumbers['Child']),
		array(
			'action' => 'removechild',
			$reportnumbers['Child']['Reportnumber']['topproject_id'],
			$reportnumbers['Child']['Reportnumber']['cascade_id'],
			$reportnumbers['Child']['Reportnumber']['order_id'],
			$reportnumbers['Child']['Reportnumber']['report_id'],
			$reportnumbers['Child']['Reportnumber']['id']

		),
		array(
			'title' => __('Remove'),
			'class' => 'round mymodal'
		)
	);

	echo '</div>';

} else {

	echo '<div class="hint">';
		echo '<form class="login">';
		echo '<fieldset>';
		echo __('Copy general data from source report') . '<br>';
		echo $this->Form->input('setGenerally', array('type'=>'radio', 'options'=>array(__('no'), __('yes')), 'value'=>1, 'legend'=>' '));
		echo '</fieldset>';
	echo '</form>';
	echo '</div>';

	echo '<div class="quicksearch">';
	echo $this->element('searching/search_quick_master_reports',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Link report',true)));
	echo '</div>';

}
?>
</div>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/ajax_link');
?>
