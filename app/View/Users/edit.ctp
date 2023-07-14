<div class="modalarea testingcomps form">
<!-- Beginn Headline -->
<h2><?php echo __('Edit user'); ?></h2>
<?php
if(isset($FormName) && count($FormName) > 0){

//	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/modal_redirect',array('FormName' => $FormName));
	echo '</div>';
	return;

}
?>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->Form->create('User', array('class' => 'login')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('name');
		echo $this->Form->input('username');
		echo $this->Form->input('Roll.id', array(
					'label' => __('Roll',true),
					'options' => $rolls,
					'empty' => '(choose one)'
				));
		echo $this->Form->input('Testingcomp.id', array(
					'label' => __('Testingcomp',true),
					'options' => $testingcomps,
					'empty' => '(choose one)'
		));

		echo '</fieldset><fieldset>';

		echo $this->Form->input('email');
		echo $this->Form->input('tel');

		echo '</fieldset><fieldset>';

		echo $this->Form->input('enabled', array('type'=>'radio', 'options'=>array(__('no', true), __('yes', true))));

		if($this->data['User']['counter_blocked'] == 1){
			echo $this->Form->input('counter_blocked',
				array(
					'label' => __('blocked',true),
					'before' => '<fieldset><legend>' . __('Blocked due to invalid logins',true) . '</legend>',
					'after' => '</fieldset>',
					'type'=>'checkbox'
				)
			);
		}

		echo '</fieldset><fieldset>';

		if($this->data['User']['time_blocked'] == 1){
			echo $this->Form->input('time_blocked',
				array(
					'label' => __('blocked',true),
					'before' => '<fieldset><legend>' . __('Blocked due to inactivity',true) . '</legend>',
					'after' => '</fieldset>',
					'options' => array(1 =>__('yes', true), 0 => __('no', true)),
					'type' => 'radio',
					'legend' => false,
				)
			);
		}

		echo $this->Html->link(__('Change password'),
			array_merge(
				array(
					'action' => 'password'),
				array(
					$this->data['User']['testingcomp_id'],
					$this->data['User']['id'],
					1
				)
			),
			array(
				'title' => __('Change password'),
				'class' => 'mymodal round'
			)
		);

		echo '</fieldset>';
		echo '<h3>' . __('Landing page settings') . '</h3>';
		echo $this->element('landingpage/edit_landing_page');


		echo '<h3>' . __('Assign Examiners settings') . '</h3>';
		echo $this->element('user/edit_assigned_examiners');

	?>
	</fieldset>
	<?php echo $this->element('form_submit_button',array('description' => __('Submit',true),'action' => 'back'));?>
</div>
<?php
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_button_set');
echo $this->element('js/form_checkbox');
echo $this->element('js/form_send_modal',array('FormId' => 'UserEditForm'));
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
//echo $this->element('js/ajax_mymodal_link');
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
