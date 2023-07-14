<div class="modalarea welders form">
<h2><?php echo __('Delete file'); ?></h2>
<div class="hint"><p>
<?php echo __('Will you delete the file',true);?> 
<b>
<?php echo ($this->request->data['Weldingcompfile']['name']);?>
</b>
 ?
</p>
</div>
<?php if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
	} 
?>
<?php echo $this->Form->create('Weldingcompfile', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->end(__('Delete',true)); ?>

</div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
