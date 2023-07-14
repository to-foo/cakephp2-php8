<?php
$StatusArray[0] = 'deaktiv';
$StatusArray[1] = 'okay';
$StatusArray[2] = 'error';

$SettingsLInk = $this->request->projectvars['VarsArray'];
$SettingsLInk[1] = $StartId;

$OrderLink = $this->request->projectvars['VarsArray'];
$OrderLink[1] = $this->request->data['Scheme']['AdvancesOrder']['AdvancesOrder']['cascade_id'];
$OrderLink[2] = $this->request->data['Scheme']['AdvancesOrder']['AdvancesOrder']['order_id'];

$SchemeUrl = $this->Html->url(array_merge(array('action' => 'json_scheme'),$this->request->projectvars['VarsArray']));
$DiagrammUrl = $this->Html->url(array_merge(array('action' => 'reload_statistic'),$this->request->projectvars['VarsArray']));
$EditUrl = $this->Html->url(array_merge(array('action' => 'advance'),$this->request->projectvars['VarsArray']));
$DelUrl = $this->Html->url(array_merge(array('action' => 'advance_delete'),$this->request->projectvars['VarsArray']));
$AddUrl = $this->Html->url(array_merge(array('action' => 'advance_add'),$this->request->projectvars['VarsArray']));

echo $this->Form->input('SchemeUrl',array('type' => 'hidden','value' => $SchemeUrl));
echo $this->Form->input('DiagrammUrl',array('type' => 'hidden','value' => $DiagrammUrl));
echo $this->Form->input('EditUrl',array('type' => 'hidden','value' => $EditUrl));
echo $this->Form->input('DelUrl',array('type' => 'hidden','value' => $DelUrl));
echo $this->Form->input('AddUrl',array('type' => 'hidden','value' => $AddUrl));
echo $this->Form->input('CascadeGroupId',array('type' => 'hidden','value' => $this->request->data['CascadeGroupId']));
if(isset($this->request->data['Scheme']['AdvancesOrder']['Date']['days_gone'])) echo $this->Form->input('StatisticDaysGone',array('type' => 'hidden','value' => $this->request->data['Scheme']['AdvancesOrder']['Date']['days_gone'] . '%'));

echo $this->element('advance/advance_orders_navigation');
echo $this->element('advance/advance_orders_statistic_content');
echo $this->element('advance/advance_orders_table_content');
echo $this->element('advance/advance_orders_report_content');
?>
