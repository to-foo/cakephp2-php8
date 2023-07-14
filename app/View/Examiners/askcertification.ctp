<div class="modalarea">
<h2>
<?php
echo __('Examiner') . ' ' .
$certificate_data['Examiner']['name'] . ' - ' .
__('qualification') . ' ' .
$certificate_data['Certificate']['third_part'] . '/' .
$certificate_data['Certificate']['sector'] . '/' .
$certificate_data['Certificate']['certificat'] . '/' .
ucfirst($certificate_data['Certificate']['testingmethod']) . '/' .
$certificate_data['Certificate']['level']
;
?>
</h2>
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

<?php
$this->request->projectvars['VarsArray'][15] = $certificate_data['Examiner']['id'];
$this->request->projectvars['VarsArray'][16] = $certificate_data['Certificate']['id'];
$this->request->projectvars['VarsArray'][17] = $certificate_data['CertificateData']['id'];
?>
<div class="hint">
<p>
<?php
echo __('Next scheduled certification',true). ': ';
echo '<b>';
echo $certificate_data_old_infos['CertificateData']['next_certification'];
echo '</b>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['CertificateData']['time_to_next_certification']);?>
</p></div>
<?php echo $this->Form->create('Examiner', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'Certificate','testingmethods' => false));?>
<?php // echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Certificate');?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'back','description' => __('Submit',true)));?>

</div>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_send_modal',array('FormId' => 'ExaminerAskcertificationForm'));
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
