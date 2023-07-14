<div class="modalarea dependencies form">
<h2><?php echo __('Add Dependency').' > '.$dropdown['Dropdown'][$locale] . ' > ' . @$thisDropdownsValue['DropdownsValue']['discription']; ?></h2>
<?php echo $this->Form->create('Dependency', array('class'=>'dialogform')); ?>
	<fieldset>
	<?php
//		echo $this->Form->input('dropdowns_value_id', array('options' => $dropdowns_value_id));
		echo $this->Form->input('field', array('options'=>$field));
		echo $this->Form->input('value');
		if(AuthComponent::user('Roll.id') < 5){
			echo $this->Form->input('global', array('type'=>'radio', 'options' => array(__('no'), __('yes'))));
		}
	?>
	</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
<?php if(isset($afterEdit)) echo $afterEdit; ?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>