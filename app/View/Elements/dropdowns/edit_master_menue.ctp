<?php // pr($this->request->data['Devisions']);?>
<ul class="editmenue">
<?php
echo '<li class="active" id="link_main_area">';
echo $this->Html->link(__('Settings',true),'javascript:',array('title' => __('Description',true),'rel' => 'main_area'));
echo '</li>';
if($this->request->data['DropdownsMaster']['dependencies'] == 1){
  echo '<li class="deactive" id="link_dependency_setting_area">';
  echo $this->Html->link(__('Dependency settings',true),'javascript:',array('title' => __('Dropdown data',true),'rel' => 'dependency_setting_area'));
  echo '</li>';
}
echo '<li class="deactive" id="link_data_area">';
echo $this->Html->link(__('Dropdown data',true),'javascript:',array('title' => __('Dropdown data',true),'rel' => 'data_area'));
echo '</li>';
?>
</ul>
<?php echo $this->element('dropdowns/js/edit_master_menue_js');?>
