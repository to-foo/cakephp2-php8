<div class="modalarea">
<h2><?php echo __('Delete details')?> <?php echo $this->request->data['HeadLine']?></h2>
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
<div class="hint">
<?php
if(isset($this->request->data['Supplierfile']['file_exists']) && $this->request->data['Supplierfile']['file_exists'] == true){
	echo $this->request->data['Supplierfile']['basename'];
}
?>
</div>

<?php echo $this->Form->create('Supplierfile', array('class' => 'expeditingform login','autocomplete' => 'off'));?>
<?php echo $this->Form->input('id');?>
<?php echo $this->element('form_submit_button',array('description' => __('Delete',true),'action' => 'close'));?>
</div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/form_send_modal',array('FormId' => 'SupplierfileDelfileForm'));
?>
