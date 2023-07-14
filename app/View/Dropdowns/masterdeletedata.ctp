<div class="modalarea">
<h2><?php echo __('Delete master dropdown'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
echo $this->element('js/ajax_stop_loader');

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
?>
<div class="hint"><p>
<?php
echo '<p><b>';
echo $this->request->data['DropdownsMastersData']['value'];
echo '</b></p>';
?>
</div>
<?php echo $this->Form->create('DropdownsMastersData', array('class' => 'login')); ?>
<?php echo $this->Form->input('id');?>
<?php echo $this->element('form_submit_button',array('action' => 'back','description' => __('Delete')));?>
</div>
<?php echo $this->element('js/form_send_modal',array('FormId' => 'DropdownsMastersDataMasterdeletedataForm'));?>
<?php echo $this->element('js/form_button_set');?>
<?php echo $this->element('js/minimize_modal');?>
<?php echo $this->element('js/close_modal');?>
<?php echo $this->element('js/ajax_mymodal_link');?>
