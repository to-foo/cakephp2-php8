<div class="modalarea examiners form">
<h2><?php echo __('Delete cascade'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(isset($FormName) && count($FormName) > 0){
  echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
	}
?>
<div class="error"><p><?php echo __('Achtung, beim Löschen dieser Kaskade werden alle untergeordneten Elemente ebenfalls gelöscht.',true);?></p></div>

<?php echo $this->Form->create('Cascade', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id'); ?>
<?php echo $this->element('form_submit_button',array('action' => 'back','description' => __('Delete')));?>
</div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_modal_request');
echo $this->element('js/form_button_set');
?>
