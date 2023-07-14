<div class="modalarea">
<h2>
<?php  
echo __('Welder test') . ' ' . 
$certificate_data['Welder']['name'] . ' ' . 
__('tested for');
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
	return;
} 
?>
<?php echo $this->Form->create('WelderCertificate', array('class' => 'dialogform')); ?>
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
	<fieldset>
<?php // pr($this->request->data['DropdownInfo']);// echo $this->Form->input('third_part',array('type' => 'select','options' => array('' => '', 1 => 'DGZfP', 2 => 'TÜV'))); ?>
	<?php
		echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'WelderCertificate');
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'qualification'), $this->request->projectvars['VarsArray']));

?>
<script type="text/javascript">
$(document).ready(function(){

	$("#WelderCertificateWelderCertificateDataActive1").change(function() {
		$("#dialog form input,#dialog form select,#dialog form textarea").attr("disabled","disabled");
		$("#dialog").load("<?php echo $url;?>", {"ajax_true": 1,"certificate_data_active": $(this).val()});
	});
		
	$("#WelderCertificateWelderCertificateDataActive0").change(function() {		
		$("#dialog form input,#dialog form select,#dialog form textarea").attr("disabled","disabled");
		$("#dialog").load("<?php echo $url;?>", {"ajax_true": 1,"certificate_data_active": $(this).val()});
	});	
	
});
</script>
</div>
<div class="clear"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
<?php $form = '#WelderCertificateQualificationForm';
      
       echo $this->JqueryScripte->SessionFormData($form); ?> 
