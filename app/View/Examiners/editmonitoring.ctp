<div class="modalarea">
<h2><?php echo __('Monitoring') . ' - ' . $certificate_data['ExaminerMonitoring']['certificat'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
?>
<div class="hint">
<p>
<?php
echo __('Next scheduled monitoring',true). ': ';
echo '<span class="" id="date_next_certifcation" title="';
echo __('Use this date for certification',true);
echo '">';
echo $certificate_data_old_infos['ExaminerMonitoringData']['next_certification'];
echo '</span>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['ExaminerMonitoringData']['time_to_next_certification']);?>
</p>
<p>
<?php
if($this->request->data['ExaminerMonitoring']['file'] == 1){
	echo $this->Html->link(__('Change monitoring file',true), array_merge(array('action' => 'monitoringfile'), $this->request->projectvars['VarsArray']), array('title' => __('Change monitoring file',true),'class' => 'mymodal round'));
}
?>
</p>
</div>
<?php echo $this->Form->create('ExaminerMonitoringData', array('class' => 'login')); ?>
<fieldset>
<?php
echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'Device','testingmethods' => false));
//echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'ExaminerMonitoringData');
?>
</fieldset>
<?php
echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));
?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'ExaminerMonitoringDataEditmonitoringForm'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
