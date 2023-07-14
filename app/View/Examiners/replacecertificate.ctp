<div class="modalarea">
<h2>
<?php
echo __('Examiner') . ' ' .
$certificate_data_old_infos['Examiner']['name'] . ' - ' .
__('qualification') . ' ' .
$certificate_data_old_infos['Certificate']['third_part'] . '/' .
$certificate_data_old_infos['Certificate']['sector'] . '/' .
$certificate_data_old_infos['Certificate']['certificat'] . '/' .
ucfirst($certificate_data_old_infos['Certificate']['testingmethod']) . '/' .
$certificate_data_old_infos['Certificate']['level']
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
if(isset($certificate_data_old_infos['CertificateData']['renewal']) && $certificate_data_old_infos['CertificateData']['renewal'] == true){
	$h3 = __('Renewal certificat');
	$next_info = __('Scheduled renewal');
	$next_date = __('Use this date for renewal');
}
else {
	$h3 = __('Replace or create certificat (recertification)');
	$next_info = __('Scheduled certification');
	$next_date = __('Use this date for certification');
}
?>
<h3><?php echo $h3;?></h3>
<div class="hint">
<p>
<?php
echo $next_info. ': ';
echo '<span class="date_next_certifcation" id="date_next_certifcation" title="';
echo $next_date;
echo '">';
echo $certificate_data_old_infos['CertificateData']['next_certification'];
echo '</span>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['CertificateData']['time_to_next_certification']);?>
</p></div>
<?php echo $this->Form->create('CertificateData', array('class' => 'login')); ?>
<?php echo $this->Form->input('examiner_id',array('type' => 'hidden')); ?>
<?php echo $this->Form->input('certificate_id',array('type' => 'hidden')); ?>
<fieldset>
<?php echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'Certificate','testingmethods' => false));?>

<?php // echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Certificate');?>
</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
</div>

<script type="text/javascript">
$(document).ready(function(){

	$("span#date_next_certifcation").click(function() {

		check = confirm("<?php echo __('Soll dieses Datum als Zertifizierungszeitpunkt verwendet werden?');?>");

		if (check == false) {
			return false;
		}

		$("#CertificateDataCertifiedDate").val($(this).text());
	});

});
</script>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/form_send_modal',array('FormId' => 'CertificateDataReplacecertificateForm'));
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
