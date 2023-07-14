<div class="modalarea examiners index inhalt">
<h2><?php echo __('Delete device');?></h2>
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

<?php echo $this->Form->create('Device', array('class' => 'login'));?>
<fieldset>
<?php echo $this->Form->input('id');?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'back','description' => __('Delete',true)));?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'DeviceDelForm'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.dropdown'));
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/scroll_modal_top');
?>
