<div class="modalarea">
<h2><?php echo __('Examiners'); ?>  <?php if(isset($this->request->data['Examiner']['name'])) echo ' - ' . $this->request->data['Examiner']['name'];?> </h2>
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
echo '<strong>'.$this->request->data['ExaminerMonitoring']['certificat'].'</strong><br>';
echo __('Examiners') . ': ';
echo $this->request->data['Examiner']['name'] ;
echo '</p><p>';
echo __('Next scheduled monitoring',true). ': ';
echo '<span class="date_next_certifcation" id="date_next_certifcation" title="';
echo __('Use this date for certification',true);
echo '">';
echo $certificate_data_old_infos['ExaminerMonitoringData']['next_certification'];
echo '</span>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['ExaminerMonitoringData']['time_to_next_certification']);?>
</p></div>
<?php echo $this->Form->create('Device', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->Form->input('ExaminerMonitoring.id');?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Delete',true)));?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'DeviceDelmonitoringForm'));
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
