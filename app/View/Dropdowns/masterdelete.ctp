<div class="modalarea">
<h2><?php echo __('Delete master dropdown'); ?></h2>
<?php
	echo $this->element('js/ajax_stop_loader');

	if(isset($FormName) && !empty($this->request->data['DropdownsMaster'])){
		if(count($FormName) > 0){
			echo $this->element('js/reload_container',array('FormName' => $FormName));
			echo $this->element('js/close_modal_auto_now');
			echo '</div>';
			return;
		}
	}

?>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->Form->create('DropdownsMaster', array('class' => 'login')); ?>
<?php echo $this->Form->input('id'); ?>
<?php
	if(isset($notAllowed)){
		if($notAllowed == 0){
			echo $this->element('form_submit_button', array('action' => 'close','description' => __('Delete',true)));
		}
	}
?>
</div>
<?php echo $this->element('js/form_send_modal', array('FormId' => 'DropdownsMasterMasterdeleteForm')); ?>
<?php echo $this->element('js/form_button_set');?>
<?php echo $this->element('js/minimize_modal');?>
<?php echo $this->element('js/close_modal');?>
<?php echo $this->element('js/ajax_mymodal_link');?>
