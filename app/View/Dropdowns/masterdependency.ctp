<div class="modalarea">
<h2>
<?php
echo __('Edit dependency data') . ' ';
echo __('for',true) . ' ';
echo $this->request->data['DropdownsMaster']['field_name'] . ' > ';
echo $this->request->data['DropdownsMastersData']['value'] . ' ';
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
	echo $this->element('js/ajax_stop_loader');

	if(isset($FormName) && $this->request->data['DropdownsMastersData']['action_after_saving'] == 1){
		if(count($FormName) > 0){
			echo $this->element('js/reload_container',array('FormName' => $FormName));
			echo $this->element('js/close_modal_auto');
			echo '</div>';
			return;
			}

	} elseif(isset($FormName) && $this->request->data['DropdownsMastersData']['action_after_saving'] == 0){

		if(count($FormName) > 0 && isset($FormName2) && count($FormName2) > 0){
			echo $this->element('js/reload_container',array('FormName' => $FormName));
			echo $this->element('js/modal_redirect',array('FormName' => $FormName2));
			echo '</div>';
			return;
			}

	}
?>
<div class="hint"><p>
<?php echo __('Take a choice in following dropdown field.',true);?>
</p><p class="status_text">
<?php
	if(count($this->request->data['DependencyFields']) == 0){
		echo __('There are no dependency fields. Close these windows to add dependency fields.',true);
	}
?>
</p></div>

<?php
if(count($this->request->data['DependencyFields']) == 0){
	echo $this->element('js/minimize_modal');
	echo $this->element('js/maximize_modal');
	echo $this->element('js/close_modal');
	return;
}
?>

<?php echo $this->Form->create('DropdownsMastersData', array('class' => 'login'));?>
</span>
<fieldset class="for_edit_dependencies">
<?php echo $this->Form->input('id');?>
<?php echo $this->Form->input('value',array('class' => array('skip_to_next'),'selected' => $this->request->here,'options' => $this->request->data['DropdownsMastersDatasUrl']));?>
<?php echo $this->Form->input('DependencyFields',array('class' => array('skip_to'),'options' => $this->request->data['DependencyFields']));?>
</fieldset>
<?php echo $this->Form->end(__('Submit'));?>
<?php echo $this->element('dropdowns/depedency_table');?>
<?php echo $this->element('dropdowns/get_depedency_input');?>
<?php echo $this->element('dropdowns/depedency_current_table');?>
<?php echo $this->element('js/form_send_modal',array('FormId' => 'DropdownsMastersDataMasterdependencyForm'));?>
<?php echo $this->element('js/form_button_set');?>
<?php echo $this->element('js/minimize_modal');?>
<?php echo $this->element('js/maximize_modal');?>
<?php echo $this->element('js/close_modal');?>
<?php echo $this->element('js/ajax_mymodal_link');?>
<?php echo $this->element('js/ajax_json_table_editlink');?>
<?php echo $this->element('js/ajax_json_table_deletelink');?>
</div>
