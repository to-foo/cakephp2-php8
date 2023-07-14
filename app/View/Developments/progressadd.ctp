<div class="modalarea form">
<h2><?php echo __('Add'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($FormName) && count($FormName) > 0){
  echo $this->element('js/reload_container',array('FormName' => $FormNameContainer));
  echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
	}
?>

<?php echo $this->Form->create('DevelopmentData', array('class' => 'dialogform')); ?>
<fieldset>
<?php
echo $this->Form->input('measuring_point',array('type' => 'number','label' => __('Anzahl')));
echo $this->Form->input('testing_method', array(
    'options' => $alltestingmethods,
    'empty' => '(choose one)'
));
echo $this->Form->input('equipment_discription');
echo $this->Form->input('dimension');

$option = array('0' => '-', '1' => 'Rep', '2' => 'Ok');
echo $this->Form->input('result',array('options' => $option));

echo '</fieldset><fieldset>';

echo $this->Form->input('remark');
?>
</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
</div>
