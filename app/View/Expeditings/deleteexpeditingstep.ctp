<div class="modalarea detail">
<h2>
<?php echo __('Delete Expediting Step'); ?> 
</h2>
<?php echo $this->element('Flash/_messages');?>
<?php
//pr($this->request->data);
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	echo '</div>';
	return;
	}
?>

<?php echo $this->Form->create('ExpeditingSetsExpeditingType', array('class' => 'login','autocomplete' => 'off')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<?php echo $this->element('form_submit_button',array('description' => __('Delete',true),'action' => 'back'));?>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingSetsExpeditingTypeDeleteexpeditingstepForm'));
?>
