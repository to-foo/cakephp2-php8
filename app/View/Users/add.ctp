<div class="modalarea testingcomps form">
<!-- Beginn Headline -->
<h2><?php echo __('Add user'); ?></h2>
<?php
if(isset($FormName) && count($FormName) > 0){
//	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	return;

}
?>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($max_user) && $max_user == true){
	echo '</div>';
	echo $this->element('js/ajax_mymodal_link');
	return;
}
?>
<?php echo $this->Form->create('User', array('class' => 'login')); ?>

<fieldset>
<?php
	echo $this->Form->input('id');
	echo $this->Form->input('testingcomp_id',array('type' => 'hidden'));
	echo $this->Form->input('name');
	echo $this->Form->input('username');
	echo '</fieldset><fieldset>';

	echo $this->Form->input('User.passwd', array(
			'class' => 'pr-password pass_new',
			'value' => '',
			'label' => __('Password',true),
			'autocomplete'=>'off',
			'after' => '<span class=""></span>'
		)
	);

	echo $this->Form->input('User.passwd_confirm', array(
		'class' => 'pass_new passwd_confirm',
		'type' => 'password',
		'label' => __('Confirm new password',true),
		'value' => '',
		'autocomplete'=>'off',
		'after' => '<span class=""></span>'
		)
	);

	echo '</fieldset><fieldset>';
	echo $this->Form->input('Roll.id', array(
				'label' => __('Roll',true),
				'options' => $rolls,
				'empty' => __('choose one',true)
			));
	echo $this->Form->input('Testingcomp.id', array('value' => $this->request->data['User']['testingcomp_id']));

	echo '</fieldset><fieldset>';

	echo $this->Form->input('email');
	echo $this->Form->input('tel');

	echo '</fieldset>';
	echo '<fieldset>';

	echo $this->Form->input('enabled', array('type'=>'radio', 'options'=>array(__('no', true), __('yes', true))));
	echo '</fieldset>';
?>
<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'back'));?>
</div>
<div class="clear" id="testdiv"></div>
<?php
echo $this->element('password/js/password-new-jquery');
echo $this->element('password/js/password-new');
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/form_send_modal',array('FormId' => 'UserAddForm'));
echo $this->element('js/close_modal');
?>
<?php
echo $this->Html->scriptBlock('
	$(document).ready(function() {
		var comprolls = '.json_encode($testingcomprolls).';
		$("#UserTestingcompId").bind("change", function() {
			$("#UserRollId option").removeAttr("style").prop("selected", false).filter(function() {
				return parseInt(this.value,10) < comprolls[$("#UserTestingcompId").val()];
			}).css("display","none");

			$("#UserRollId").val($("#UserRollId option:not([style])").first().val());
		})
		$("#UserTestingcompId").trigger("change");
	});
');
?>
