<?php echo $this->element('advance/js/order_edit_js');?>
<div class="dropdowns form modalarea">
<h2><?php echo __('Edit advance of ') . ' ' . $this->request->data['Order']['auftrags_nr']; ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>

<?php
echo $this->element('js/ajax_stop_loader');

if(isset($JSONName) && count($JSONName) > 0){
	echo $this->element('advance/js/schema_request_js');
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
echo $this->Form->create('AdvancesOrder', array('class' => 'dialogform'));
echo $this->Form->input('id');
echo '<fieldset>';
echo $this->Form->input('equipment_type',array('options' => $this->request->data['CascadeGroup'],'value' => $this->request->data['CascadegroupsCascade']));
echo '</fieldset>';
echo '<fieldset>';
echo $this->Form->input('start_global',array('type' => 'text','disabled' => 'disabled'));
echo $this->Form->input('end_global',array('type' => 'text','disabled' => 'disabled'));
echo $this->Form->input('start',array('type' => 'text','class' => 'date'));
echo $this->Form->input('end',array('type' => 'text','class' => 'date'));
echo '</fieldset>';
echo '<fieldset>';
echo '</fieldset>';
echo $this->element('form_submit_button',array('action' => 'reset','description' => __('Submit',true)));

echo $this->Form->create();
echo '<fieldset>';

echo '<table class="advancetool"><tbody>';
echo '<tr>';
echo '<th>' . __('Advances Type',true) . '</th>';
echo '<th></th>';
echo '<th>' . __('Start',true) . '</th>';
echo '<th>' . __('End',true) . '</th>';
echo '</tr>';

if(isset($this->request->data['AdvancesData']) && count($this->request->data['AdvancesData']) > 0){
	foreach ($this->request->data['AdvancesData'] as $key => $value) {
		echo '<tr id="advance_detail_' . $value['AdvancesData']['id'] . '">';
		echo '<td class="collaps">' . $AdvanceType[$value['AdvancesData']['type']] . '</td>';
		echo '<td class="collaps">';
		echo $this->Html->link(__('Delete this advance'),array_merge(array('action' => 'order_edit'),$this->request->projectvars['VarsArray']),array('rel' => $value['AdvancesData']['id'],'title' => __('Delete this advance'),'class' => 'icon icon_delete'));
		echo '</td>';
		echo '<td class="collaps">' . $value['AdvancesData']['start'] . '</td>';
		echo '<td>' . $value['AdvancesData']['end'] . '</td>';
		echo '</tr>';
	}
}
echo '</tbody></table>';
echo $this->Html->link(__('Add new advance points'),array_merge(array('action' => 'advance_add'),$this->request->projectvars['VarsArray']),array('title' => __('Add new advance points'),'rel' => $this->request->data['Order']['id'],'class' => 'round add_advance_point'));
echo '</fieldset>';
echo $this->Form->end();
?>
</div>
<?php
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/minimize_modal');
echo $this->element('js/close_modal');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield_min_max',array('MinDate' => $this->request->data['Advance']['start'],'MaxDate' => $this->request->data['Advance']['end']));
?>
