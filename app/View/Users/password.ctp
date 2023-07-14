<div class="modalarea">
<h2><?php echo __('Edit password'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){

	echo $this->element('js/modal_redirect');
	echo '</div>';
	return;

}
?>

<?php
echo $this->element('password/js/password-change-jquery');
echo $this->element('password/js/password-change');
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/form_send_modal',array('FormId' => 'UserPasswordForm'));
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_modal_link');
?>

<?php echo $this->Form->create('User', array('class' => 'login','autocomplete' => 'off')); ?>

<?php echo $this->Form->input('id');?>

<fieldset>
<?php
echo $this->Form->input('password', array(
		'class' => 'old_password',
		'value' => '',
		'label' => __('Current password',true),
		'autocomplete'=>'off',
		'after' => '<span class=""></span>'
	)
);
?>
</fieldset>
<fieldset>
<?php
echo $this->Form->input('User.passwd', array(
		'class' => 'pr-password pass_new disabled',
		'label' => __('New password',true),
		'value' => '',
		'autocomplete'=>'off',
		'after' => '<span class=""></span>',
		'type' => 'password'
	)
);?>
</fieldset>
<fieldset>
<?php
echo $this->Form->input('User.passwd_confirm', array(
	'class' => 'pass_new passwd_confirm disabled',
	'type' => 'password',
	'label' => __('Confirm new password',true),
	'value' => '',
	'autocomplete'=>'off',
	'after' => '<span class=""></span>'
	)
);
?>
</fieldset>
<?php echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit', true)));?>
</div>
