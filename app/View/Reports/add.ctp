<div class="modalarea">
<!-- Beginn Headline -->
<h2><?php echo __('Add report'); ?></h2>
<!-- Ende Headline -->
<!-- Beginn Message -->
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<!-- Ende Message -->
<?php 
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/close_modal_reload_container');
	return;
}
?>
<?php echo $this->Form->create('Report', array('class' => 'dialogform fastsave')); ?>
<fieldset>
<?php
echo $this->Form->input('name');
echo $this->Form->input('identification');
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Testingmethod',array('multiple' => true,'label' => __('Involved testingmethods',true)));
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('projektbeschreibung');
?>
</fieldset>
<?php
echo $this->Form->button(__('Cancel', true), array('id' => 'back', 'type' => 'button', 'value' => $previous_url));
echo $this->Form->end(__('Submit', true));
?>
</div>
<?php echo $this->element('js/form_multiple_fields');?>
<?php echo $this->element('js/ajax_modal_request');?>
<?php echo $this->element('js/minimize_modal');?>
<?php echo $this->element('js/close_modal');?>
<?php echo $this->element('js/ajax_mymodal_link');?>
