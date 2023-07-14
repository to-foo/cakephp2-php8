<div class="modalarea detail">
<h2><?php echo __('Mass actions') . ' ' . $this->Pdf->ConstructReportName($reportnumber)?></h2>
<?php echo $this->element('Flash/_messages');?>

<?php
if(isset($send_template) && $send_template == true){

	echo $this->element('js/send_mass_action_template');

	echo '</div>';
	return;

}

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/send_mass_action');
	echo $this->element('js/close_modal_auto');
	echo $this->element('js/reload_evaluation',array('FormName' => $FormName));
	echo '</div>';
	return;
}
?>

<?php if($evaluation != null):?>
<table cellpadding = "0" cellspacing = "0">
<tr>
<?php
foreach($evaluationoutput as $_key => $_evaluationoutput){
	if(trim($_evaluationoutput->showintable) == 1){
		echo '<th>' . trim($_evaluationoutput->discription->$locale) . '</th>';
	}
}
?>
</tr>
<?php
foreach($evaluation as $_evaluation) {
	echo '<tr>';
	foreach($_evaluation as $__evaluation) {

foreach($evaluationoutput as $_key => $_evaluationoutput){
	if(trim($_evaluationoutput->showintable) == 1){
		echo '<td>';

		echo '<span class="discription_mobil">';
		echo trim($_evaluationoutput->discription->$locale) . ': ';
		echo '</span>';

		$output = $__evaluation[trim($_evaluationoutput->key)];

		if(isset($_evaluationoutput->radiooption->value))$output = trim($_evaluationoutput->radiooption->value[$__evaluation[trim($_evaluationoutput->key)]]);

		if(trim($_evaluationoutput->key) == 'description' && isset($__evaluation['position']) && !empty($__evaluation['position'])) $output .= '/' . $__evaluation['position'];

		echo $output;
		echo '</td>';
	}
}
	}
	echo '</tr>';
}
?>
</table>
<?php endif?>
<div class="<?php echo $message_class;?>">
<p>
<?php
echo $this->Html->link(__('Cancel'),array('action' => ''),array('class' => 'round mymodal','id' => 'closethismodal', 'title' => __('Close Window')));
if(isset($message_button)){
	$class = 'mymodal round';
	if(isset($this->request->data['Reportnumber']['MassSelect']) && $this->request->data['Reportnumber']['MassSelect']=='weldlabel') {
		$class = 'round';
	}

	echo $this->Html->link($message_button,array_merge(
			array('action' => 'massActions',),
			$this->request->projectvars['VarsArray']
		),
			array(
				'class'=>'round send_mass_action',
				'id' => 'send_mass_action')
		);
}

?>
<div class="clear"></div>
</p>
</div>
</div>
<?php
if(isset($this->request->data['Reportnumber']['MassSelect']) && $this->request->data['Reportnumber']['MassSelect'] == 'weldlabel') {
	$url = $this->Html->url(array_merge(array('controller' => 'reportnumbers','action' => 'printweldlabel'),$this->request->projectvars['VarsArray']));

} else {
	$url = $this->Html->url(array_merge(array('controller' => 'reportnumbers','action' => 'massActions'),$this->request->projectvars['VarsArray']));
}

$this->request->data['Reportnumber']['okay'] = 1;

echo '<form style="display: none;" id="tmpForm" action="' . $url . '" method="post">';
foreach($this->request->data['Reportnumber'] as $_key => $_reportnumber) echo '<input type="hidden" name="data[Reportnumber]['.$_key.']", value="'.$_reportnumber.'" />';
echo '</form>';

echo $this->Form->input('MassSelectUrl',array('type' => 'hidden','value' => $url));
echo $this->Form->input('MassSelectType',array('type' => 'hidden','value' => $this->request->data['Reportnumber']['MassSelect']));

echo $this->element('js/send_mass_action');
?>
