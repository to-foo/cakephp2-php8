<div class="modalarea">
<h2><?php echo __('Delete file', true);?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
} 
?>
<div class="hint">
<p><?php echo __('Soll folgende Datei gelöscht werden?',true);?></p>
<p><b><?php echo $Reportfiles['Orderfile']['basename'];?></b></p>
</div>
<?php echo $this->Form->create('Orderfile', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<?php echo $this->Form->end(__('Delete', true));?>                                                    

</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
