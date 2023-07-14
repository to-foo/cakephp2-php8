<div class="modalarea">
<h2>
<?php
echo __('Welder') . ' ' . 
$certificate_data_old_infos['Welder']['name'] . ' - ' . 
$certificate_data_old_infos['WelderEyecheck']['certificat']
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

<h3><?php echo __('Replace vision test');?></h3>
<div class="hint">
<p>
<?php 
echo __('Next scheduled  visiontest'). ': ';
echo '<span class="date_next_certifcation" id="date_next_certifcation" title="';
echo __('Use this date for this vision test',true);
echo '">';
echo $certificate_data_old_infos['WelderEyecheckData']['next_certification'];
echo '</span>';
?>
</p>
<p>
<?php echo ($certificate_data_old_infos['WelderEyecheckData']['time_to_next_certification']);?>
</p></div>
<?php echo $this->Form->create('WelderEyecheckData', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('welder_id',array('type' => 'hidden')); ?>
<?php echo $this->Form->input('certificate_id',array('type' => 'hidden')); ?>
<fieldset>
<?php echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Eyecheck');?>
</fieldset>
<?php echo $this->Form->end(__('Submit', true));?>
<?php
$this->request->projectvars['VarsArray'][16] = $certificate_data_old['WelderEyecheck']['id'];
$this->request->projectvars['VarsArray'][17] = $certificate_data_old['WelderEyecheckData']['id'];
$url = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'editeyecheck'), $this->request->projectvars['VarsArray']));
?>
</div>

<script type="text/javascript">
$(document).ready(function(){
	
	$("span#date_next_certifcation").click(function() {

		check = confirm("<?php echo __('Soll dieses Datum als Zertifizierungszeitpunkt verwendet werden?');?>");
		
		if (check == false) {
			return false;
		}

		$("#WelderEyecheckDataCertifiedDate").val($(this).text());
	});
});
</script>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>