<div class="modalarea">
<h2><?php echo __('Examiner',true) . ' ' . __('documents',true);?></h2>
<?php echo $this->element('Flash/_messages');?>

<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	echo '</div>';
	return;
}
?>
<div>
<?php echo $this->Form->create('Examinerfile', array('class' => 'login')); ?>
<fieldset>
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->input('description');?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'back','description' => __('Submit',true)));?>

</div>

</div>
<div class="clear"></div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'ExaminerfileExaminerfilesdescriptionForm'));
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
