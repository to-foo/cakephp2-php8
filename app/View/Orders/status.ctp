<div class="modalarea">
<h2><?php  echo __('Change status'); ?></h2>
<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div> 
<?php 
if(isset($afterChange) && $afterChange == 1){
	echo $this->JqueryScripte->RefreshAfterDialog(null,null,$FormName);
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
}
?>
<div class="hint"><p><?php echo $message; ?></p></div>
<?php echo $this->Form->create('Order', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
       
<?php echo $this->Form->end(__('Change state')); ?>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>