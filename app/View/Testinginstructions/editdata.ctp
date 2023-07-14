<div class="testingstructionsdata form modalarea">
<h2><?php echo __('Edit Testingstruction Data'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>

<?php
echo $this->element('js/ajax_stop_loader');

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
//	echo $this->element('js/close_modal_reload_container');
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>

<?php echo $this->Form->create('TestinginstructionsData', array('class' => 'dialogform')); ?>
<fieldset>
<?php
if(isset($this->request->data['data_area']) && !empty($this->request->data['data_area'])) echo $this->Form->input('data_area',array('type' => 'hidden','value' => $this->request->data['data_area']));
echo $this->Form->input('id');
echo $this->Form->input('model',array('options' => $ModelOption));
echo $this->Form->input('field',array('options' => $FieldOptions));
echo $this->Form->input('type',array('options' => $TypeOptions));
echo $this->Form->input('value');
echo $this->Form->input('description');
?>

<?php //  echo $this->ViewData->EditModulData($this->request->data,$settings,$locale,'TestinginstructionsData');?>
</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>

<?php echo $this->element('testinstruction/change_model_input_js');?>
<?php echo $this->element('js/ajax_send_modal_form');?>
<?php echo $this->element('js/form_multiple_fields');?>
<?php echo $this->element('js/minimize_modal');?>
<?php echo $this->element('js/close_modal');?>
<?php echo $this->element('js/ajax_mymodal_link');?>
