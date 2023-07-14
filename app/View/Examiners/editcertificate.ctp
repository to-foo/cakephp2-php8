<div class="modalarea">
<h2>
<?php
echo __('Examiner') . ' ' .
$certificate_data['Examiner']['name'] . ' - ' .
__('certification') . ' ' .
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
if($certificate_data['CertificateData']['certified'] == 1){
	echo __('Next scheduled certification',true). ': ';
	echo '<span class="date_next_certifcation" id="date_next_certifcation" title="';
	echo __('Use this date for certification',true);
	echo '">';
	echo $certificate_data_old_infos['CertificateData']['next_certification'];
	echo '</span><br>';
	echo ($certificate_data_old_infos['CertificateData']['time_to_next_certification']);
	echo '<p>';

	if($certificate_data['CertificateData']['apply_for_recertification'] == 1) {
		$apply_for_recertification_desc = __('Withdraw application for recertification or renewal.',true);
		echo __('To renew the certificate please use the following function',true);
		echo '</p><p>';
	}
	else {
		$apply_for_recertification_desc = __('Apply for recertification or renewal.',true);
		echo __('To renew the certificate an application for recertification must be made.',true);
		echo '</p><p>';
	}

	if($certificate_data['CertificateData']['apply_for_recertification'] == 1){

		$replace_link_desc = __('Replace this information',true);

		if(isset($__certificates['CertificateData']['renewal']) && $__certificates['CertificateData']['renewal'] == true) $replace_link_desc = __('Renewal this information',true);

		echo $this->Html->link($replace_link_desc , array_merge(array('action' => 'replacecertificate'), $this->request->projectvars['VarsArray']), array('class' => 'mymodal round'));
	}

	echo $this->Html->link($apply_for_recertification_desc, array_merge(array('action' => 'askcertification'), $this->request->projectvars['VarsArray']), array('class' => 'mymodal round'));
}

if($certificate_data['CertificateData']['certified'] == 0){

	echo __('Scheduled certification',true). ': ';
	echo '<span class="date_next_certifcation" id="date_next_certifcation" title="';
	echo __('Use this date for certification',true);
	echo '">';
	echo $certificate_data_old_infos['CertificateData']['next_certification'];
	echo '</span><br>';
	echo ($certificate_data_old_infos['CertificateData']['time_to_next_certification']);
	echo '<p>';

}
?>
</p>
</div>
<hr />
<?php
if(!empty($certificate_data['CertificateData']['certified_file_error'])){
	echo '<div class="error"><p>';
	echo $certificate_data['CertificateData']['certified_file_error'];
	echo ' ';
	echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'mymodal round'));
	echo '</p></div>';
}
if(!empty($certificate_data['CertificateData']['certified_file'])){
	echo '<div class="hint"><p>';
	echo __('There is a valid certificate.');
	echo ' ';
	echo __('When you upload a new file, the existing file will be deleted.');
	echo '</p><p>';
	echo $this->Html->link(__('Show certificate file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round ','target' => '_blank'));
	echo $this->Html->link(__('Replace certificate file',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));
	echo $this->Html->link(__('Delete certificate file',true), array_merge(array('action' => 'delcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));
	echo '</p></div>';
}
?>

<?php echo $this->Form->create('Examiner', array('class' => 'login','autocomplete' => 'off')); ?>
<fieldset>
<?php echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'Certificate','testingmethods' => false));?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'ExaminerEditcertificateForm'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
//echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
if(empty($certificate_data['CertificateData']['certified_date'])) echo $this->element('js/examiner_put_zert_date',array('DateID' => 'CertificateDataCertifiedDate'));
?>
