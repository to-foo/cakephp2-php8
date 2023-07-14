<div class="modalarea form">
<h2><?php echo __('Delete'); ?></h2>
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
<div class="hint"><p>
<?php echo __('Will you delete this mesuring point?',true);?>
</p></div>
<?php echo $this->Form->create('DevelopmentData', array('class' => 'dialogform')); ?>
<fieldset>
<?php
echo $this->Form->input('id');
?>
</fieldset>
<?php echo $this->Form->end(__('Delete')); ?>
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
