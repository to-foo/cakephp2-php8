<div class="inhalt testinginstructions form">
<h2><?php echo __('Edit master dropdown'); ?> > <?php echo $this->request->data['DropdownsMaster']['name'];?> (<?php echo$this->request->data['ThisTestingcomp']['name'];?>)</h2>
<div class="quicksearch">
<?php
echo $this->Html->link(__('Add master dropdown',true), array_merge(array('action' => 'masteradd'), array()), array('class' => 'modal icon icon_examiners_add','title' => __('Add master dropdown',true)));
echo $this->element('searching/search_quick_dropdown',array('target_id' => 'id','targedaction' => 'masteredit','action' => 'quicksearch','minLength' => 2,'discription' => __('Dropdown name', true)));
?>
</div>
<?php echo $this->element('Flash/_messages');?>
<?php echo $this->element('dropdowns/edit_master_menue');?>
<?php
echo '<div class="areas" id="main_area">';
echo $this->Form->create('DropdownsMaster');
echo '<fieldset class="buttons">';
echo $this->Html->link(__('Submit',true), 'javascript:', array('class' => 'save_form round','title' => __('Submit',true)));
echo $this->Html->link(__('Delete this master dropdown',true),array_merge(array('action' => 'masterdelete'),$this->request->projectvars['VarsArray']),array('class' => 'round modal_post'));
echo $this->Html->link(__('Change modul',true),array_merge(array('action' => 'changemodul'),$this->request->projectvars['VarsArray']),array('class' => 'round modal_post'));
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('id');
echo $this->Form->input('name');

$disable_field = null;

if(!empty($this->request->data['DropdownsMaster']['field']) == true) $disable_field = array('disabled' => 'disabled');

echo $this->Form->input('field',array($disable_field,'options' => $this->request->data['DropdownsFields']));
echo $this->Form->input('dependencies',array('legend' => __('Dependency fields',true),'options' => array('0' => __('deactive',true), '1' => __('active',true)),'type' => 'radio'));
echo $this->Form->input('status',array('options' => array('0' => __('active',true), '1' => __('deactive',true)),'type' => 'radio'));
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('description');
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Testingmethod',array('multiple' => true,'label' => __('Involved testingmethods',true),'selected' => $DropdownsMaster['Testingmethod']['selected']));
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Topproject',array('multiple' => true,'label' => __('Involved projects',true),'selected' => $DropdownsMaster['Topproject']['selected']));
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Testingcomp',array('multiple' => true,'label' => __('Involved companies',true),'selected' => $DropdownsMaster['Testingcomp']['selected']));
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Report',array('multiple' => true,'label' => __('Involved reports',true),'selected' => $DropdownsMaster['Report']['selected']));
echo '</fieldset>';
echo $this->Form->end(__('Submit'));
echo '</div>';
echo $this->element('dropdowns/edit_dropdown_data_areas');
if($this->request->data['DropdownsMaster']['dependencies'] == 1) echo $this->element('dropdowns/dependency_setting_area');
?>
</div>
<?php
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_send', array('FormId' => 'DropdownsMasterMastereditForm'));
echo $this->element('js/form_send', array('FormId' => 'DropdownsMastersDependenciesField'));
echo $this->element('js/form_send_by_link');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/resize_table_column');
echo $this->element('js/ajax_link');
?>