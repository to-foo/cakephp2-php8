<div class="modalarea">
<!-- Beginn Headline -->
<h2><?php echo __('Add evaluation template'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){

	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	return;

}
?>
<?php echo $this->Form->create('TemplatesEvaluation',array('class' => 'login'));?>
<fieldset>
<?php echo $this->Form->input('testingmethod_id',array('options' => $Testingmethod,'empty' => ' '));?>
<?php echo $this->Form->input('name');?>
<?php echo $this->Form->input('description');?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Add',true)));?>
</div>
<?php
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/form_send_modal',array('FormId' => 'TemplatesEvaluationAddevaluationForm'));
?>
