<div class="modalarea examiners form">
<h2><?php echo __('Print overview'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/ajax_stop_loader');
	echo '</div>';
	return;
}
echo $this->Form->create('Examiner', array('class' => 'login'));
?>
<fieldset>
<?php
echo $this->Form->input('page_break', array(
    'options' =>  $this->request->data['Examiner']['page_break_options'],
    'type' => 'radio',
		'value' => 0
));
?>
</fieldset>
<fieldset class="multiple_field">
<?php
echo $this->Form->input('working_place',array(
	'required' => false,
	'multiple' => true,
	'options' => $this->request->data['Examiner']['working_place'],
	'label' => __('Wählen Sie alle Dienststellen aus, die berücksichtigt werden sollen. Wählen Sie nichts aus, wenn alle Dienststellen ausgegeben werden sollen.',true),
	'selected' => ''
	)
);
?>
</fieldset>
<?php echo $this->element('form_submit_button', array('action' => 'close','description' => __('Print',true)));?>

<?php
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_button_set');
echo $this->element('js/ajax_mymodal_link');

echo $this->element('js/ajax_stop_loader');
echo $this->element('examiner/js/print_overview',array('FormId' => 'ExaminerPrintoverviewForm'));
?>
</div>
