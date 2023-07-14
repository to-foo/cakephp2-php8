<div class="modalarea detail">
<h2>
<?php echo __('Add Expediting Step'); ?> > 
<?php echo $this->request->data['Expeditingset']['name'];?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	echo '</div>';
	return;
}

if(count($this->request->data['ExpeditingTypes']) == 0){

	echo '<div class="hint">';

	echo $this->Html->link(__('Add expediting type'),
		array_merge(
			array(
				'action' => 'addexpediting',
			),
			$this->request->projectvars['VarsArray']
		),
		array(
			'class'=>'mymodal round',
			'title'=> __('Add expediting type', true)
		),
	);

	echo '</div>';
	echo '</div>';
	return;
}
	
?>

<?php echo $this->Form->create('ExpeditingSetsExpeditingType', array('class' => 'login','autocomplete' => 'off')); ?>
<fieldset>
<?php
echo $this->Form->input('expediting_type_id',array('options' => $this->request->data['ExpeditingTypes']));
echo $this->Form->input('period_datum',array('label' => __('Starting Date'),'options' => $priode_date));
echo $this->Form->input('period_sign',array('label' => __('Key Signature'),'options' => $priode_sign));
echo $this->Form->input('period_time',array('label' => __('Period')));
echo $this->Form->input('period_measure',array('label' => __('Measure'),'options' => $priode_measure));
echo $this->Form->input('karenz',array('label' => __('Karenz in days')));
echo $this->Form->input('hold_witness_point', array('options' => array(0 => '-',1 => __('Witness'),2 => __('Hold')),'type' => 'radio','value' => 0));

?>
</fieldset>
<fieldset class="multiple_field">
<?php	
echo $this->Form->input('Roll',array(
	'multiple' => true,
	'label' => __('Involved rolls for e-mail shipping',true),
	'options' => $Rolls,
	)
);
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'back'));?>

<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingSetsExpeditingTypeAddexpeditingstepForm'));
?>
