<div class="modalarea detail">
<h2><?php echo __('Edit expediting step'); ?> <?php echo $this->request->data['HeadLine'];?> <?php echo $this->request->data['Expediting']['description'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>
<?php echo $this->Form->create('Expediting', array('class' => 'login')); ?>
<?php

echo $this->Form->input('id');

echo '<fieldset>';

echo $this->Form->input('date_soll', array(
	'class' => 'date',
	'type' => 'text'
));

if(isset($this->request->data['ExpeditingStopEditable']['date_ist']) && $this->request->data['ExpeditingStopEditable']['date_ist'] == 1){

} else {

	echo $this->Form->input('date_ist', array(
		'class' => 'date',
		'type' => 'text'
	));

}

echo $this->Form->input('karenz');

echo $this->Form->input('stop_mail', array(
	'legend' => __('Email delivering',true),
	'options' => array(0 => __('active'),1 => __('deactive')),
	'class' => 'send_json_single',
	'type' => 'radio'
));
/*
echo $this->Form->input('Supplier.stop_mail', array(
	'legend' => __('Email delivering',true),
	'options' => array(0 => __('active'),1 => __('deactive')),
	'type' => 'radio'
));
*/
echo $this->Form->input('hold_witness_point', array(
	'legend' => __('Hold or withness point',true),
	'options' => array(0 => '-',1 => __('Witness'),2 => __('Hold')),
	'type' => 'radio'
));

echo $this->Form->input('remark');

echo '</fieldset>';

echo '<fieldset class="multiple_field">';
echo $this->Form->input('Roll',array(
							'id' => 'RollRoll',
							'name' => 'data[Roll][Roll]',
							'multiple' => true,
							'label' => __('Involved rolls for e-mail shipping',true),
							'options' => $this->request->data['Rolls'],
							'selected' => $this->request->data['Roll']['selected']
						)
					);
echo '</fieldset>';
?>
<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'reset'));?>
</div>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingDetailForm'));
echo $this->element('js/form_send_singlefield_json');
?>
