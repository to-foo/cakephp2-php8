<div class="modalarea">
<h2><?php echo __('Image details')?> <?php echo $this->request->data['HeadLine']?></h2>
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

echo $this->Html->link(__('Delete image'),
    array_merge(array(
      'controller' => 'suppliers',
      'action' => 'delimage',
    ),      $this->request->projectvars['VarsArray']),
  array(
    'title' => __('Delete image',true),
    'class'=>'round mymodal'
  )
);

?>
</div>
<?php echo $this->Form->create('Supplierimage', array('class' => 'expeditingform login','autocomplete' => 'off'));?>
<fieldset>
<?php
echo $this->Form->input('id');
echo $this->Form->input('description');
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'close'));?>
</div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/form_send_modal',array('FormId' => 'SupplierimageEditForm'));
?>
