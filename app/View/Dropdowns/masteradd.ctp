<div class="dropdowns form modalarea">
<h2><?php echo __('Add master dropdown'); ?></h2>
<?php echo $this->element('Flash/_messages');?>

<?php
if(isset($Errors) && !empty($Errors)){
	echo $this->Form->input('ErrorFields',array('type' => 'hidden','value' => $Errors));
}

echo $this->element('js/ajax_stop_loader');

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container', array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto_now');
	echo '</div>';
	return;
}

echo '<div class="areas" id="main_area">';
echo $this->Form->create('DropdownsMaster', array('class' => 'login'));
echo '<fieldset>';
echo $this->Form->input('name');
echo $this->Form->input('modul',array(
		'options' => $this->request->data['modul'],
		'empty' => ''
	)
);
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Testingmethod',array('multiple' => true,'label' => __('Involved testingmethods',true),'selected' => $this->request->data['Testingmethod']['selected']));
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
//echo $this->Form->end(__('Submit'));
echo $this->element('form_submit_button', array('action' => 'reset','description' => __('Submit',true)));
echo '</div>';
?>
</div>
<?php
echo $this->element('js/form_send_modal', array('FormId' => 'DropdownsMasterMasteraddForm'));
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_button_set');
echo $this->element('dropdowns/js/master_add_require_fields');
?>
