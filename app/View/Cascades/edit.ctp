<div class="modalarea examiners form">
<h2><?php echo __('Edit cascade'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($validationErrors)) echo $this->element('validation_error_list');
if(Configure::check('Cascadeautocomplete') && Configure::read('Cascadeautocomplete') == true) {
//	echo $this->element('searching/autocomplete_cascade',array('action' => 'autocomplete','minLength' => 1,'discription' => __('Item', true)));
}

if(isset($FormName) && count($FormName) > 0 && Configure::check('CascadeLoadModules') && Configure::read('CascadeLoadModules') == true){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
 	echo $this->element('js/modal_redirect',array('FormName' => $FormName2));
	echo '</div>';
	return;
}else {
if(isset($FormName) && count($FormName) > 0){
	echo '</div>';
	return;
	}

}
?>

<?php echo $this->Form->create('Cascade', array('class' => 'login')); ?>

<fieldset>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->input('discription',array('class' => 'require','label' => __('Description',true)));?>
</fieldset>
<fieldset>
<?php
echo $this->Form->input('orders',
	array(
		'legend' => __('Cascade managment',true),
		'label' => __('Cascade managment',true),
		'title' => __('Cascade managment',true),
		'type' => 'radio',
		'options' =>
			array(
				0 => __('no orders under this cascade',true),
				1 => __('orders under this cascade',true),
				2 => __('technical place under this cascade',true)
			)
		)
	);
?>
</fieldset>
<fieldset>
<?php
if(Configure::check('CascadeLoadModules') && Configure::read('CascadeLoadModules') == true){
 	echo $this->Form->input('CascadeGroup',
		array(
			'multiple' => false,
			'class' => 'require',
			'disabled' => 'disabled',
			'empty' => __('choose one',true),
			'options' => $Cascade['CascadeGroups'],
			'label' => __('Cascade Group',true),
			'selected' => $Cascade['CascadeCurrent']['CascadeGroup']['selected']
		)
	);

//	echo $this->element('cascades/technical_place_form',array('data' => $Cascade,'step' => 'Technicalplace'));

}
?>
</fieldset>
<fieldset class="multiple_field">
<?php
echo $this->Form->input('Testingcomp',
	array(
		'multiple' => true,
		'options' => $Testingcomps,
		'label' => __('Involved companies',true),
		'selected' => $Cascade['CascadeCurrent']['Testingcomp']['selected']
	)
);
?>
</fieldset>
<fieldset class="Tasks">
<?php
//	echo $this->Form->input('Testingcomp','description');
//	echo $this->Form->input('Testingcomp','date_to');
?>
</fieldset>
<fieldset>
<?php
echo $this->Form->input(__('Expediting'),
	array(
		'label' => __('activate expediting',true),
		'title' => __('activate expediting',true),
		'disabled' => 'disabled',
		'type' => 'radio',
		'options' =>
			array(
				0 => __('no',true),
				1 => __('yes',true),
			)
		)
);
echo $this->Form->input(__('Advance'),
	array(
		'label' => __('activate advances',true),
		'title' => __('activate advances',true),
		'disabled' => 'disabled',
		'type' => 'radio',
		'options' =>
			array(
				0 => __('no',true),
				1 => __('yes',true),
			)
		)
);
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit')));?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'CascadeEditForm'));
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_button_set');
echo $this->element('cascades/form_send_json',array('name' => 'CascadeEditForm'));
echo $this->element('js/scroll_modal_top');
?>
