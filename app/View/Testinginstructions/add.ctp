<div class="testingstructionsdata form modalarea">
<h2><?php echo __('Edit Testing Instruction'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>

<?php
echo $this->element('js/ajax_stop_loader');
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}

	echo '<div class="areas" id="main_area">';
	echo $this->Form->create('Testinginstruction', array('class' => 'dialogform'));
	echo '<fieldset>';
	echo $this->Form->input('testingmethod_id',array('options' => $testingmethods));
	echo $this->Form->input('name');
	echo '</fieldset>';
	echo '<fieldset class="multiple_field">';
	echo $this->Form->input('Testingcomp',array('multiple' => true,'label' => __('Involved companies',true),'selected' => $this->request->data['Testingcomp']['selected']));
	echo '</fieldset>';
	echo '<fieldset class="multiple_field">';
	echo $this->Form->input('Topproject',array('multiple' => true,'label' => __('Involved projects',true),'selected' => $this->request->data['Topproject']['selected']));
	echo '</fieldset>';
	echo '<fieldset class="multiple_field">';
	echo $this->Form->input('Report',array('multiple' => true,'label' => __('Involved reports',true),'selected' => $this->request->data['Report']['selected']));
	echo '</fieldset>';
	echo '</fieldset>';
	echo $this->Form->end(__('Submit'));
	echo '</div>';

?>
</div>
<?php
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_button_set');
?>
