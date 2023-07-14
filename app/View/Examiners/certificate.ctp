<div class="modalarea">
<h2><?php echo __('Examiner') . ' ' . $certificate_data['Examiner']['name'] . ' ' . __('edit certificate');?></h2>
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
<?php echo $this->Form->create('Certificate', array('class' => 'login')); ?>
<fieldset><?php echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Certificate');?></fieldset>
<?php echo $this->Form->end(__('Submit', true));?>

<script type="text/javascript">
$(document).ready(function(){

	$("#CertificateCertificateDataActive1").click(function() {
		$("#CertificateCertificat").val(null);
		$("#CertificateFirstCertification").val(null);
		$("#CertificateRenewalInYear").val(null);
		$("#CertificateRecertificationInYear").val(null);
		$("#CertificateHorizon").val(null);
	});
	$("#CertificateCertificateDataActive0").click(function() {
		$("#CertificateCertificat").val("-");
		$("#CertificateFirstCertification").val(0);
		$("#CertificateRenewalInYear").val(0);
		$("#CertificateRecertificationInYear").val(0);
		$("#CertificateHorizon").val(0);
	});
});
</script>
</div>
<div class="clear"></div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'CertificateQualificationForm'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
