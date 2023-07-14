<div class="modalarea devicetestingmethods form">
<h2><?php echo __('Edit Devicetestingmethod'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>

<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	//echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
} 
?>

<?php echo $this->Form->create('DeviceTestingmethod', array('class' => 'modal dialogform')); ?>
<fieldset>
<?php echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'DeviceTestingmethod');?>
</fieldset>
<?php echo $this->Form->end(__('Submit', true)); ?>
</div>
<?php // echo $this->element('fast_save_examiner_js');?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
