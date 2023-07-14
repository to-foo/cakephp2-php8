<div class="modalarea testingcomps form">
<!-- Beginn Headline -->
<h2><?php echo __('Delete user'); ?></h2>
<!-- Ende Headline -->
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	return;
}
?>
<!-- Beginn Message -->
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>

<?php echo $this->Form->create('User', array('class' => 'dialogform editreport')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<?php echo $this->Form->end(__('Delete', true));?>
</div>
<div class="clear" id="testdiv"></div>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_modal_request');
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
?>
