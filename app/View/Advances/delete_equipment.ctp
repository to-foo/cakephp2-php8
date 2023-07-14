<div class="dropdowns form modalarea">
<h2><?php echo __('Delete equipment to') . ' ' . $this->request->data['Cascade']['discription']; ?></h2>
<?php echo $this->element('Flash/_messages');?>

<?php

echo $this->element('js/ajax_stop_loader');

if(isset($JSONName) && count($JSONName) > 0){
	echo $this->element('js/close_modal_auto');
	echo $this->element('advance/js/schema_request_js');
	echo '</div>';
	return;
}

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}

echo $this->Form->create('Order', array('class' => 'login'));
echo '<fieldset>';
echo $this->Form->input('cascade_id',array('type' => 'hidden','value' => $this->request->data['Cascade']['id']));
echo '</fieldset>';
echo $this->element('form_submit_button',array('action' => 'back','description' => __('Delete',true)));
?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'OrderDeleteEquipmentForm'));
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_button_set');
?>
