<div class="users form login" id="loginformuser">
<?php // var_dump(Security::hash('test', null, true)); ?>
<?php echo $this->Form->create('User', array('url' => 'login'));?>
	<fieldset>
 		<legend><?php __('Please log in to your account'); ?></legend>
		<?php echo $this->Session->flash(); ?>
	<?php
		echo $this->Form->input('username');
		echo $this->Form->input('password');
		echo $this->Form->input('Language.beschreibung', array('label' => __('Language', true), 'selected' => $selected, 'options' => $lang));
	?>
	</fieldset>

<?php echo $this->Form->end(__('Login', true)); ?>
<?php  ?>
</div>

<?php echo $this->element('js/login'); ?>
