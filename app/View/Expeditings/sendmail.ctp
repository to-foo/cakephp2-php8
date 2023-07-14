<div class="modalarea detail">
<h2><?php echo __('Send file'); ?></h2>
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

<?php echo $this->Form->create('Expediting', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->Form->input('notes',array('type' => 'textarea', 'escape' => false));?>
</fieldset>
<fieldset class="multiple_field">
<?php echo $this->Form->input('emailaddress',array('multiple' => true,'options' => $this->request->data['emailaddress'] ,'label' => __('Involved email adresses',true)));?>
</fieldset>
<?php echo $this->element('form_submit_button',array('description' => __('Send',true),'action' => 'close'));?>
</div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingSendmailForm'));
?>
