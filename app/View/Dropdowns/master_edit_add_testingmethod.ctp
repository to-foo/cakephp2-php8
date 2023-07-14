<div class="inhalt testinginstructions form">
<h2><?php echo __('Edit master dropdown'); ?> > <?php echo $this->request->data['DropdownsMaster']['name'];?> (<?php echo$this->request->data['ThisTestingcomp']['name'];?>)</h2>
<div class="quicksearch">
<?php
echo $this->Html->link(__('Add master dropdown',true), array_merge(array('action' => 'masteradd'), array()), array('class' => 'modal icon icon_examiners_add','title' => __('Add master dropdown',true)));
echo $this->element('searching/search_quick_dropdown',array('target_id' => 'id','targedaction' => 'masteredit','action' => 'quicksearch','minLength' => 2,'discription' => __('Dropdown name', true)));
?>
</div>
<?php echo $this->element('Flash/_messages');?>
<div>
<?php
echo $this->Form->create('DropdownsMaster');
echo '<fieldset class="buttons">';
echo $this->Html->link(__('Submit',true), 'javascript:', array('class' => 'save_form round','title' => __('Submit',true)));
echo $this->Html->link(__('Delete this master dropdown',true),array_merge(array('action' => 'masterdelete'),$this->request->projectvars['VarsArray']),array('class' => 'round modal_post'));
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('id');
echo $this->Form->input('name');
echo '</fieldset>';
echo '<fieldset class="multiple_field">';
echo $this->Form->input('Testingmethod',array('multiple' => true,'label' => __('Involved testingmethods',true),'selected' => $DropdownsMaster['Testingmethod']['selected']));
echo '</fieldset>';
echo $this->Form->end(__('Submit'));
?>
</div>
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