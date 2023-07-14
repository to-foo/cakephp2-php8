<div class="modalarea detail">
<h2><?php echo __('Delete expediting step'); ?></h2>
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

<?php echo $this->Form->create('Expediting', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<?php echo $this->element('form_submit_button',array('description' => __('Delete',true),'action' => 'close'));?>
</div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/form_send_modal',array('FormId' => 'ExpeditingDeleteForm'));
?>
