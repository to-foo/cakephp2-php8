<div class="dropdowns form modalarea">
<h2><?php echo __('Advance settings') . ' ' .  __('for',true) . ' ' . $this->request->data['AdvancesCascade']['cascade_discription']; ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
echo $this->element('js/ajax_stop_loader');

if(isset($JSONName) && count($JSONName) > 0){
//	echo $this->element('advance/js/schema_request_js');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}

/*
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
*/

echo $this->element('advance/advance_settings_links');

$this->request->data['AdvancesCascade']['cascade_date'] = 0;
$this->request->data['AdvancesCascade']['cascade_users'] = 0;

echo $this->Form->create('AdvancesCascade', array('class' => 'login'));

echo $this->Form->input('advance_id',array('type' => 'hidden'));
echo $this->Form->input('cascade_id',array('type' => 'hidden'));

echo '<fieldset>';

echo $this->Form->input('start_global',array('type' => 'text','disabled' => 'disabled'));
echo $this->Form->input('end_global',array('type' => 'text','disabled' => 'disabled'));
echo $this->Form->input('start',array('type' => 'text','class' => 'date'));
echo $this->Form->input('end',array('type' => 'text','class' => 'date'));
echo $this->Form->input('karenz');
echo $this->Form->input('cascade_date', array('legend' => __('Apply date info for subordinate elements',true),'class' => 'warning_text','options' => array(0 => __('no',true), 1 => __('yes',true)),'type' => 'radio'));
echo $this->Form->input('cascade_users', array('legend' => __('Apply user access info for subordinate elements',true),'class' => 'warning_text','options' => array(0 => __('no',true), 1 => __('yes',true)),'type' => 'radio'));
echo '</fieldset>';
echo '<fieldset class="multiple_field">';

echo $this->Form->input('AdvancesUsers',array(
	'multiple' => true,
	'class' => 'multiple',
	'label' => __('Involved user',true),
	'options' => $this->request->data['Testingcomps'],
	'selected' => $this->request->data['SelectedUsers']
	)
);
echo '</fieldset>';
echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));
?>
</div>
<?php
echo $this->element('js/form_send_modal',array('FormId' => 'AdvancesCascadeAdvanceSettingsForm'));
echo $this->element('advance/js/edit_cascade_warning');
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/form_button_set');
if(isset($this->request->data['AdvancesCascade']['start_global']) && isset($this->request->data['AdvancesCascade']['end_global'])) echo $this->element('js/form_datefield_min_max',array('MinDate' => $this->request->data['AdvancesCascade']['start_global'],'MaxDate' => $this->request->data['AdvancesCascade']['end_global']));
else echo $this->element('js/form_datefield');
?>
