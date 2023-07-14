<div class="modalarea detail">
<h2><?php echo __('Delete technical place')?> <?php echo $headline;?></h2>
<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div> 
<?php 
if(isset($afterChange) && $afterChange == 1){
	echo $this->JqueryScripte->RefreshAfterDialog(1,1,$FormName);
	echo $this->JqueryScripte->DialogClose();
	//echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
}
?>
<div class="error">

<p><?php echo $message; ?></p>
</div>
<?php echo $this->Form->create('Technicalplace', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<div class="message_wrapper"><?php echo $this->Session->flash(); ?></div> 
<?php echo $this->Form->end(__('Delete')); ?>
</div>
<div class="clear" id="testdiv"></div>
<?php echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/form_accordion');
echo $this->element('js/form_datefield');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_send_modal_form');?>