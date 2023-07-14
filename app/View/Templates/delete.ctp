<div class="modalarea">
<h2><?php echo __('Delete template');?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){

	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	return;

}
?>
<div class="hint">
<p><b><?php echo $this->request->data['Template']['name'];?></b></p>
<p><?php echo $this->request->data['Template']['description'];?></p>
</div>
<?php echo $this->Form->create('Template', array('class' => 'login'));?>
<?php echo $this->Form->input('id');?>
<?php echo $this->element('form_submit_button',array('action' => 'close','description' => __('Delete',true)));?>

<?php
echo $this->element('js/form_send_modal',array('FormId' => 'TemplateDeleteForm'));
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
