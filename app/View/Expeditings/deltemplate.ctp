<div class="modalarea detail">
<h2>
<?php echo __('Delete ITP'); ?>  > 
<?php echo $this->request->data['Expeditingset']['name'] ?> 
</h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	echo '</div>';
	return;
}
?>
<?php echo $this->Form->create('Expeditingset', array('class' => 'login','autocomplete' => 'off')); ?>
<?php
echo $this->Form->input('id');
?>
<?php echo $this->element('form_submit_button',array('description' => __('Delete',true),'action' => 'back'));?>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingsetDeltemplateForm'));
 ?>
