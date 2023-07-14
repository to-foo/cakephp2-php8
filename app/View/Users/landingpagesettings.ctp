<div class="modalarea testingcomps form">
<h3><?php echo __('Landing page settings'); ?></h3>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){

	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/site_reload');
	echo '</div>';
	return;

}
?>
<?php echo $this->Form->create('User', array('class' => 'login')); ?>
	<fieldset>
	<?php
	echo $this->Form->input('id');
	echo $this->Form->input('enabled',array('type' => 'hidden'));
	echo $this->Form->input('Roll.id', array('label' => __('Roll',true),));
	echo $this->Form->input('Testingcomp.id', array('label' => __('Testingcomp',true),));
	?>
	</fieldset>
	<?php echo $this->element('landingpage/edit_landing_page');?>
	</fieldset>
	<?php echo $this->Form->end(__('Submit', true));?>
</div>
<?php
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send_landing_large_window',array('FormId' => 'UserPasswordForm'));
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
?>
