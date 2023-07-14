<div class="modalarea">
<h2><?php echo __('Expediting delete')?> <?php echo $this->request['data']['Supplier']['equipment']?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>
<?php echo $this->Form->create('Suppliere', array('class' => 'login')); ?>
<?php echo $this->Form->input('Supplier.id');?>
<?php echo $this->Form->input('Supplier.deleted',array('value' => 1,'type' => 'hidden'));?>
<?php echo $this->element('form_submit_button',array('description' => __('Delete',true),'action' => 'close'));?>
</div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_modal',array('FormId' => 'SuppliereDeleteForm'));
 ?>
