<div class="modalarea detail">
<h2><?php echo __('Delete order')?> <?php echo $headline;?></h2>
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
<div class="error">
<p><?php echo __('Achtung, beim Löschen dieser Komponente werden gegebenenfalls vorhandene Prüfberichte ebenfalls gelöscht!',true);?></p>
<p><?php echo $message; ?></p>
</div>
<?php echo $this->Form->create('Order', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div> 
<?php echo $this->Form->end(__('Delete')); ?>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>