<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$certificate_data['Welder']['name'] . ' ' . 
__('add certificate')
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);

	if(isset($CloseModal) && $CloseModal == 1){
		echo $this->JqueryScripte->DialogClose();
	}
	if(isset($NextModal) && $NextModal == 1){
		$url = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'editcertificate'), $this->request->projectvars['VarsArray']));
		echo $this->JqueryScripte->LoadModal($url);
	}

	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return false;
}
?>
<?php echo $this->Form->create('WelderCertificate', array('class' => 'dialogform')); ?>
	<fieldset>
                     <fieldset>
<?php 
		echo $this->Form->input('weldingmethod',array(
				'label' => __('Welding method',true),
//				'multiple' => 'multiple',
				'empty' => ' ',
				'options' => $WeldingmethodOption
				)
			);
	?>
        </fieldset>
<?php // pr($this->request->data['DropdownInfo']);// echo $this->Form->input('third_part',array('type' => 'select','options' => array('' => '', 1 => 'DGZfP', 2 => 'TÜV'))); ?>
	<?php
        pr($this->request->data['WelderCertificate']['errors_vt']);
//$this->request->data['WelderCertificate']['errors_vt'] ='100,101';
		echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'WelderCertificate');
	?>
	</fieldset>


<?php echo $this->Form->end(__('Submit', true));?>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'certificate'), $this->request->projectvars['VarsArray']));

if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
} 

?>
<script type="text/javascript">
$(document).ready(function(){

	$("#WelderCertificateWelderCertificateDataActive1").click(function() {
		$("#WelderCertificateCertificat").val(null);
		$("#WelderCertificateFirstCertification").val(null);
		$("#WelderCertificateRenewalInYear").val(null);
		$("#WelderCertificateRecertificationInYear").val(null);
		$("#WelderCertificateHorizon").val(null);
	});	
	$("#WelderCertificateWelderCertificateDataActive0").click(function() {
		$("#WelderCertificateCertificat").val("-");
		$("#WelderCertificateFirstCertification").val(0);
		$("#WelderCertificateRenewalInYear").val(0);
		$("#WelderCertificateRecertificationInYear").val(0);
		$("#WelderCertificateHorizon").val(0);
	});	
});
</script>
</div>
<div class="clear"></div>
<?php echo $this->JqueryScripte->ModalFunctions();
       $form = '#WelderCertificateQualificationnewForm';
       echo $this->JqueryScripte->SessionFormData($form); 
?> 
