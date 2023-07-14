<?php if($this->request->data['DropdownsMaster']['dependencies'] == 0) return;?>
<div class="areas" id="dependency_setting_area">
<h3><?php echo __('Dependency settings',true);?></h3>
<?php echo $this->Form->create('DropdownsMastersDependenciesField', array('id' => 'DropdownsMastersDependenciesField','class' => 'dialogform')); ?>
<fieldset class="multiple_field">
<?php echo $this->Form->input('Dependency',array('multiple' => true,'label' => __('Involved data fields',true),'selected' => $DropdownsMaster['DependencyFields']['selected']));?>
</fieldset>
<?php echo $this->Form->end(__('Submit')); ?>
</div>
