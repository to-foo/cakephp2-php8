<div class="modalarea">
<h2><?php echo __('Validation'); ?></h2>
<?php
if(isset($errors) && count($errors) >  0){
	echo '<div class="error">';
	echo $this->Html->tag('p', __('You can not close the report until all required fields have been completed.'));
	echo '</div>';
	$this->ViewData->showValidationErrors($errors);
	echo $this->JqueryScripte->ModalFunctions();
}
else{
    echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
    echo $this->JqueryScripte->DialogClose();
}
?>
</div>