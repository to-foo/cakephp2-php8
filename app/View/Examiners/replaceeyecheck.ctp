<div class="modalarea">
<h2>
<?php
echo __('Examiner') . ' ' .
$this->request->data['Examiner']['name'] . ' - ' . $this->request->data['Examiner']['first_name'] . ' ' . $this->request->data['Eyecheck']['certificat']
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

<?php echo $this->Form->create('EyecheckData', array('class' => 'login')); ?>
<?php echo $this->Form->input('examiner_id',array('type' => 'hidden')); ?>
<?php echo $this->Form->input('certificate_id',array('type' => 'hidden')); ?>
<fieldset>
<?php
echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'EyecheckData','testingmethods' => false));
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Submit',true)));?>
<?php
$VarsArray = $this->request->projectvars['VarsArray'];
$VarsArray[16] = $this->request->data['Eyecheck']['id'];
$VarsArray[17] = $this->request->data['EyecheckData']['id'];
$url = $this->Html->url(array_merge(array('controller' => 'examiners','action' => 'editeyecheck'), $VarsArray));
?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'EyecheckDataReplaceeyecheckForm'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
