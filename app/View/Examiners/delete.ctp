<div class="modalarea examiners form">
<h2><?php echo __('Delete Examiner'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="hint"><p>
<?php echo __('Will you delete the examiner',true);?>
<b>
<?php echo ($this->request->data['Examiner']['name']);?>
</b>
</p>
</div>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
?>
<?php echo $this->Form->create('Examiner', array('class' => 'dialogform')); ?>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->end(__('Delete',true)); ?>

</div>
<?php
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
