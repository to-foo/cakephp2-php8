<div class="inhalt testinginstructions form">
<h2><?php echo __('Edit Testing Instruction'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php echo $this->element('testinstruction/edit_menue');?>
<div class="current_content">
<?php
echo $this->Html->link(__('Print testinginstructions infos',true),
	array_merge(
		array('action' => 'pdf'),
		$this->request->projectvars['VarsArray']
	),
	array(
		'class' => 'icon icon_devices_pdf showpdflink',
		'title' => __('Print testinginstructions infos',true)
	)
);?>

<?php
echo '<div class="areas" id="main_area">';
echo $this->Form->create('Testinginstruction', array('class' => 'dialogform'));
echo '<fieldset>';
echo $this->Form->input('testingmethod_id',array('options' => $testingmethods));
echo $this->ViewData->EditModulData($this->request->data,$settings,$locale,'Testinginstruction');
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Topproject',array('multiple' => true,'label' => __('Involved projects',true),'selected' => $this->request->data['Topproject']['selected']));
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Testingcomp',array('multiple' => true,'label' => __('Involved companies',true),'selected' => $this->request->data['Testingcomp']['selected']));
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Report',array('multiple' => true,'label' => __('Involved reports',true),'selected' => $this->request->data['Report']['selected']));
echo '</fieldset>';
echo '</fieldset>';
echo $this->Form->end(__('Submit'));
echo '</div>';
?>

<div class="inhalt">
<?php
$Testinginstructions =  $this->request->data;
if (isset($Testinginstructions['TestinginstructionsData'] )){
if(count($Testinginstructions['TestinginstructionsData'] ) == 0){

	echo '<div class="hint"><p>';
	echo __('No results available.',true);
	echo '</p></div>';
}
}
?>
<?php echo $this->element('testinstruction/edit_testinstruction_data_areas');?>
</div>
</div>

<?php
$form = '#TestingstructionAddForm';
echo $this->JqueryScripte->SessionFormData($form);
echo $this->element('testinstruction/edit_menue_js');
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send',array('FormId' => 'TestinginstructionEditForm'));
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/resize_table_column');
?>
