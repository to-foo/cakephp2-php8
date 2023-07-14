<div class="modalarea">
<h2>
<?php echo __('Examiner') . ' ' . $eyecheck_data['Examiner']['name'] . ' ' . __('eye check');?>
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
<div class="hint"><p>
<?php echo $this->Html->link(__('Change eye check file',true), array_merge(array('action' => 'eyecheckfile'), $this->request->projectvars['VarsArray']), array('title' => __('Change eye check file',true),'class' => 'mymodal round'));?>
</p></div>
<?php echo $this->Form->create('EyecheckData', array('class' => 'login')); ?>
<fieldset>
<?php
echo $this->element('form/modulform',array('data' => $this->request->data,'setting' => $settings,'lang' => $locale,'step' => 'EyecheckData','testingmethods' => false));
//echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Eyecheck');
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));?>
<?php
$this->request->projectvars['VarsArray'][15] = $eyecheck_data['Examiner']['id'];
$this->request->projectvars['VarsArray'][16] = $eyecheck_data['Eyecheck']['id'];
$this->request->projectvars['VarsArray'][17] = $eyecheck_data['EyecheckData']['id'];
?>
<div class="uploadform">
<?php

$uploadurl = $this->Html->url(array_merge(array('action' => 'eyecheckfile'),$this->request->projectvars['VarsArray']));
$url = $this->Html->url(array_merge(array('controller' => 'examiners','action' => 'editeyecheck'), $this->request->projectvars['VarsArray']));

$uploadurl = explode('/',$uploadurl);
unset($uploadurl[0]);
unset($uploadurl[1]);
unset($uploadurl[2]);
$uploadurl = implode('/',$uploadurl);
?>
</div>
</div>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/form_send_modal',array('FormId' => 'EyecheckDataEditeyecheckForm'));
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
