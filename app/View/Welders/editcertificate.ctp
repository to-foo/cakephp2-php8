<div class="modalarea">
<h2>
<?php  
echo __('Welder test') . ' ' . 
$certificate_data['Welder']['name'] . ' - ' . 
__('qualification') . ' ' .
$certificate_data['WelderCertificate']['third_part'] . '/' .
$certificate_data['WelderCertificate']['sector'] . '/' .
$certificate_data['WelderCertificate']['certificat'] . '/' .
ucfirst($certificate_data['WelderCertificate']['weldingmethod']) . '/' .
$certificate_data['WelderCertificate']['level']
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>

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
$this->request->projectvars['VarsArray'][15] = $certificate_data['Welder']['id'];
$this->request->projectvars['VarsArray'][16] = $certificate_data['WelderCertificate']['id'];
$this->request->projectvars['VarsArray'][17] = $certificate_data['WelderCertificateData']['id'];
?>
<div class="hint">
<p>
<?php 
echo __('Next scheduled qualification',true). ': ';
echo '<span class="date_next_certifcation" id="date_next_certifcation" title="';
echo __('Use this date for qualification',true);
echo '">';
echo $certificate_data_old_infos['WelderCertificateData']['next_certification'];
echo '</span>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['WelderCertificateData']['time_to_next_certification']);?>
</p></div>
<?php

if(!empty($certificate_data['WelderCertificateData']['certified_file_error'])){
	echo '<div class="error"><p>';
	echo $certificate_data['WelderCertificateData']['certified_file_error'];
	echo ' ';
	echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'mymodal round'));
	echo '</p></div>';
}
if(!empty($certificate_data['WelderCertificateData']['certified_file'])){
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

<?php echo $this->Form->create('Welder', array('class' => 'dialogform')); ?>
<fieldset>
<?php 
echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'WelderCertificate');?>
</fieldset>
<?php // echo $this->Form->button(__('Back'),array('type' => 'button','class' => 'back_button')); ?>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<script type="text/javascript">
$(document).ready(function(){

	$("span#date_next_certifcation").click(function() {
		
		check = confirm("<?php echo __('Soll dieses Datum als Qualifizierungszeitpunkt verwendet werden?');?>");
		
		if (check == false) {
			return false;
		}
		
		$("#WelderCertificateDataCertifiedDate").val($(this).text());
	});
	
	
});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
