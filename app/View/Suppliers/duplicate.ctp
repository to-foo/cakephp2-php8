<div class="modalarea">
<h2><?php echo __('Duplicate')?></h2>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->Form->create('Suppliere', array('class' => 'login')); ?>
<fieldset>
<?php
echo $this->element('expediting/duplicate_step_1');
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('description' => __('Duplicate',true),'action' => 'close'));?>
</div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/form_send_modal',array('FormId' => 'SuppliereDuplicateForm'));
?>
