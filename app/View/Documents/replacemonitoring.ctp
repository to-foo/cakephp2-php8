<div class="modalarea">
<h2><?php echo __('Monitoring') . ' - ' . $certificate_data_old_infos['DocumentCertificate']['certificat'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="hint">
<p>
<?php
echo __('Next scheduled certification',true). ': ';
echo '<span class="date_next_certifcation" id="date_next_certifcation" title="';
echo __('Use this date for certification',true);
echo '">';
echo $certificate_data_old_infos['DocumentCertificateData']['next_certification'];
echo '</span>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['DocumentCertificateData']['time_to_next_certification']);?>
</p></div>
<?php
if(!empty($certificate_data['DocumentCertificateData']['certified_file_error'])){
	echo '<div class="error"><p>';
	echo $certificate_data['DocumentCertificateData']['certified_file_error'];
	echo ' ';
	echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'editcertificate round'));
	echo '</p></div>';
}
if(!empty($certificate_data['DocumentCertificateData']['certified_file'])){
	echo '<div class="hint"><p>';
	echo __('There is a valid certificate.');
	echo ' ';
	echo __('When you upload a new file, the existing file will be deleted.');
	echo '</p><p>';
	echo $this->Html->link(__('Show certificate file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round','target' => '_blank'));

	echo $this->Html->link(__('Replace certificate file',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'editcertificate round'));

	echo $this->Html->link(__('Delete certificate file',true), array_merge(array('action' => 'delcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

	echo '</p></div>';
}
?>

<?php echo $this->Form->create('DocumentCertificateData', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'DocumentCertificateData');?>
</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	}

$back_url = $this->Html->url(array_merge(array('action' => 'monitoring'),$this->request->projectvars['VarsArray']));
?>
<script type="text/javascript">
$(document).ready(function(){

	$("span#date_next_certifcation").click(function() {

		check = confirm("<?php echo __('Soll dieses Datum als Zertifizierungszeitpunkt verwendet werden?');?>");

		if (check == false) {
			return false;
		}

		$("#DocumentCertificateDataCertifiedDate").val($(this).text());
	});

});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
