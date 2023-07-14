<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$certificate_data_old_infos['Welder']['name'] . ' - ' . 
__('qualification') . ' ' .
$certificate_data_old_infos['WelderCertificate']['third_part'] . '/' .
$certificate_data_old_infos['WelderCertificate']['sector'] . '/' .
$certificate_data_old_infos['WelderCertificate']['certificat'] . '/' .
ucfirst($certificate_data_old_infos['WelderCertificate']['weldingmethod']) . '/' .
$certificate_data_old_infos['WelderCertificate']['level']
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
if(isset($certificate_data_old_infos['WelderCertificateData']['renewal']) && $certificate_data_old_infos['WelderCertificateData']['renewal'] == true){
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
echo $certificate_data_old_infos['WelderCertificateData']['next_certification'];
echo '</span>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['WelderCertificateData']['time_to_next_certification']);?>
</p></div>
<?php echo $this->Form->create('WelderCertificateData', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('welder_id',array('type' => 'hidden')); ?>
<?php echo $this->Form->input('certificate_id',array('type' => 'hidden')); ?>
<fieldset>
<?php echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'WelderCertificate');?>
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

		$("#WelderCertificateDataCertifiedDate").val($(this).text());
	});

});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>