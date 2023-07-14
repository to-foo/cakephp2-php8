<div class="modalarea dependencies form">
<h2>
<?php 
echo __('Edit Dependency').' > '. 
isset($settings->{$this->request->data['Dropdown']['model']}->{$this->request->data['Dependency']['field']}->discription->$locale) ? $settings->{$this->request->data['Dropdown']['model']}->{$this->request->data['Dependency']['field']}->discription->$locale : ' ' . ' > ' . 
isset($thisDropdownsValue['DropdownsValue']['discription']) ? $thisDropdownsValue['DropdownsValue']['discription'] : ' '; 
?>
</h2>
<?php echo $this->Form->create('Dependency', array('class'=>'dialogform')); ?>
	<fieldset>
	<?php
		echo $this->Form->input('id');
		echo $this->Form->input('testingcomp_id', array('type'=>'hidden', 'value'=>$this->request->data['Dependency']['testingcomp_id']));
		echo $this->Form->input('dropdown_id', array('type'=>'hidden', 'value'=>$this->request->data['Dependency']['dropdown_id']));
		echo $this->Form->input('dropdowns_value_id', array('type'=>'hidden', 'value'=>$this->request->data['Dependency']['dropdowns_value_id']));
		echo $this->Form->input('field', array('type'=>'hidden', 'value'=>$this->request->data['Dependency']['field']));
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