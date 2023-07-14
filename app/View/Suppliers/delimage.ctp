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
if(isset($this->request->data['Supplierimage']['imagedata'])){

  echo $this->Html->tag('img',null,
    array(
      'alt' => $this->request->data['Supplierimage']['description'],
      'title' => $this->request->data['Supplierimage']['description'],
      'src' => $this->request->data['Supplierimage']['imagedata']
    )
  );
}
?>
</div>

<?php echo $this->Form->create('Supplierimage', array('class' => 'expeditingform login','autocomplete' => 'off'));?>
<fieldset>
<?php
echo $this->Form->input('id');
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('description' => __('Delete',true),'action' => 'back'));?>
</div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/form_send_modal',array('FormId' => 'SupplierimageDelimageForm'));
?>
