<div class="modalarea detail">
<h2><?php echo __('Add expediting type'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName)){
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>
<?php echo $this->Form->create('ExpeditingType', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->Form->input('description');?>
</fieldset>
<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'reset'));?>
</div>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingTypeAddexpeditingForm'));
 ?>
