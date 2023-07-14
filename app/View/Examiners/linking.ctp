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
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
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
echo $certificate_data_old_infos['CertificateData']['next_certification'];
echo '<br>';
echo ($certificate_data_old_infos['CertificateData']['time_to_next_certification']);
?>
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
	echo '</p><p>';
	echo $this->Html->link(__('Show certificate file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round ','target' => '_blank'));
	echo '</p></div>';
}
?>

<?php echo $this->Form->create('Examiner', array('class' => 'dialogform')); ?>
<fieldset>
</fieldset>
<?php // echo $this->Form->button(__('Back'),array('type' => 'button','class' => 'back_button')); ?>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php
echo $this->element('js/form_buttons');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/examiner_put_zert_date');
?>
<?php //  echo $this->JqueryScripte->ModalFunctions(); ?>
