<div class="modalarea testinginstructions form">
<h2><?php echo __('Add Testing instruction'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/close_modal_reload_container');
	return;
	} 

        
?>

<?php echo $this->Form->create('TestinginstructionData', array('class' => 'dialogform')); ?>
<fieldset>
<?php  echo $this->ViewData->EditModulData($this->request->data,$settings,$locale,'TestinginstructionData');?>
</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
</div>

<?php echo $this->JqueryScripte->ModalFunctions();
      $form = '#TestingstructionDataAddForm';
       echo $this->JqueryScripte->SessionFormData($form); 
?> 
<?php echo $this->element('js/form_multiple_fields');?>
<?php echo $this->element('js/minimize_modal');?>
<?php echo $this->element('js/close_modal');?>
<?php echo $this->element('js/ajax_mymodal_link');?>