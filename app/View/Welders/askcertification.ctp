<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
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
echo __('Use this date for certification',true);
echo '">';
echo $certificate_data_old_infos['WelderCertificateData']['next_certification'];
echo '</span>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['WelderCertificateData']['time_to_next_certification']);?>
</p></div>
<?php echo $this->Form->create('Welder', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'WelderCertificate');?>
</fieldset>
<?php // echo $this->Form->button(__('Back'),array('type' => 'button','class' => 'back_button')); ?>
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
