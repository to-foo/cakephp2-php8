<div class="dropdowns form modalarea">
<h2><?php echo __('Add advance point to') . ' ' . $this->request->data['Order']['auftrags_nr']; ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>

<?php

echo $this->element('js/ajax_stop_loader');

if(isset($JSONName) && count($JSONName) > 0){
	echo $this->element('advance/js/schema_request_js');
	echo $this->element('js/close_modal_auto');
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

echo $this->Form->create('AdvancesDataDependency', array('class' => 'login'));
echo '<fieldset>';
echo $this->Form->input('cascade_id',array('type' => 'hidden'));
echo $this->Form->input('order_id',array('type' => 'hidden'));
echo $this->Form->input('count',array('type' => 'number','value' => 1));
echo $this->Form->input('AdvancesType',array('options' => $this->request->data['AdvancesDataDependency']['AdvancesType']));
echo $this->Form->input('description',array('options' => array('MP' => 'MP','Weld' => 'Weld')));
echo '</fieldset>';
echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));

?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'AdvancesDataDependencyAdvanceAddForm'));
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_button_set');
?>
