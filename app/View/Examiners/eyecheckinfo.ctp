<?php
$this->request->projectvars['VarsArray'][15] = $eyecheck_data['Examiner']['id'];
$this->request->projectvars['VarsArray'][16] = $eyecheck_data['Eyecheck']['id'];
$this->request->projectvars['VarsArray'][17] = $eyecheck_data['EyecheckData']['id'];

if($eyecheck_data['EyecheckData']['valid_class'] == 'certification_deactive'){
	echo '<div class="error"><p>';
	echo __('This vision test is deactive',true);
	echo '</p></div>';
}

if(!empty($eyecheck_data['EyecheckData']['certified_file_error'])){
	echo '<div class="error"><p>';
	echo $eyecheck_data['EyecheckData']['certified_file_error'];
	echo ' ';
	echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'eyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'editcertificate round'));
	echo '</p></div>';
}
if(!empty($eyecheck_data['EyecheckData']['certified_file'])){
	echo '<div class="hint"><p>';
	echo __('There is a valid certificate.');
	echo ' ';
	echo __('When you upload a new file, the existing file will be deleted.');
	echo '</p><p>';
	echo $this->Html->link(__('Show certificate file',true), array_merge(array('action' => 'geteyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'round','target' => '_blank'));

	echo $this->Html->link(__('Replace certificate file',true), array_merge(array('action' => 'eyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'editcertificate round'));

	echo $this->Html->link(__('Delete certificate file',true), array_merge(array('action' => 'deleyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

	echo '</p></div>';
}

$radio_desc = array(0 => 'nein', 1 => 'ja');

echo '<div class="hint certification ' .  $eyecheck_data['EyecheckData']['valid_class'] . '">';
if($eyecheck_data['EyecheckData']['certified'] == 1){
	echo '<p>';
	echo __('Sehtest erstellt',true) . ': ' . $eyecheck_data['EyecheckData']['certified_date'];
	echo '</p><p>';
	echo __('This vision test is still valid for',true) . ': ';
	echo $eyecheck_data['EyecheckData']['time_to_next_certification'];
	echo '</p><p>';
	echo __('For the next vision test they are reminded in',true) . ': ';
	echo $eyecheck_data['EyecheckData']['time_to_next_horizon'] . ' ';
//	echo $certificate_data['Certificate']['horizon'] . ' ' . __('months',true) . ' ' . __('before',true) . ')';
	echo '</p><p><strong>';
	echo $eyecheck_data['Examiner']['first_name'] . ' ' . $eyecheck_data['Examiner']['name']. ' ';
	echo __('hat eine ausreichende Sehfähigkeit für die folgenden Prüfverfahren:',true);
	echo '</strong></p><p><strong>';
	if($eyecheck_data['EyecheckData']['vt'] == 1) echo '"' . __('VT',true) . '" ';
	if($eyecheck_data['EyecheckData']['pt'] == 1) echo '"' . __('PT',true) . '" ';
	if($eyecheck_data['EyecheckData']['mt'] == 1) echo '"' . __('MT',true) . '" ';
	if($eyecheck_data['EyecheckData']['ut'] == 1) echo '"' . __('UT',true) . '" ';
	if($eyecheck_data['EyecheckData']['sv'] == 1) echo '"' . __('Prüfaufsicht',true) . '" ';
	echo '</strong></p><p><strong>';
	echo __('Eine Sehhilfe ist',true) . ' ';
	if($eyecheck_data['EyecheckData']['glasses'] == 0) echo __('nicht',true) . ' ';
	echo __('erforderlich.',true);
	echo '</strong></p>';
	echo '<p>' .  __('1. Nahsehen',true) . ': <strong>' . $radio_desc[$eyecheck_data['EyecheckData']['near_vision']] . '</strong></p>';
	echo '<p>' .  __('2. Ausreichendes Farbsehvermögen/Kontrastsehvermögen',true) . ': <strong>' . $radio_desc[$eyecheck_data['EyecheckData']['color_contrast']] . '</strong></p>';
	echo '<p>' .  __('2.1 Bei Anamolien ist eine betriebstärztliche Beratung erfolgt',true) . ': <strong>' . $radio_desc[$eyecheck_data['EyecheckData']['anomalies']] . '</strong></p>';
	echo '<p>' .  __('3. Gesichtsfelduntersuchung ist erfolgt',true) . ': <strong>' . $radio_desc[$eyecheck_data['EyecheckData']['face']] . '</strong></p>';
	echo '<p>' .  __('4. Sehfähigkeit für die Ferne',true) . ': <strong>' . $radio_desc[$eyecheck_data['EyecheckData']['distance_vision']] . '</strong></p>';
	echo '<p>' .  __('4.1 Mit Landolt Ringen',true) . ': <strong>' . $radio_desc[$eyecheck_data['EyecheckData']['landolt']] . '</strong></p>';
	echo '<p>' .  __('4.2 Mindestens auf einem Auge erreicht',true) . ': <strong>' . $radio_desc[$eyecheck_data['EyecheckData']['one_eye']] . '</strong></p>';
	echo '<p>' .  __('4.3 Andere Art',true) . ': <strong>' . $radio_desc[$eyecheck_data['EyecheckData']['different_way']] . '</strong></p>';
}
if($eyecheck_data['EyecheckData']['certified'] == 2){
	echo '<p>';
	echo __('This vision test has the status',true) . ' "' . __('invalid',true) . '"';
	echo '</p>';
}
if($eyecheck_data['EyecheckData']['certified'] == 0){
	echo '<p>';
	echo __('This vision test has no status',true);
	echo '</p>';
}
echo '<p>';
echo $this->Html->link(__('Edit this vision test',true), array_merge(array('action' => 'editeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

echo $this->Html->link(__('Delete this vision test',true), array_merge(array('action' => 'deleyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

if(!empty($eyecheck_data['EyecheckData']['certified_file'])){
//	echo $this->Html->link(__('Show vision test file',true), array_merge(array('action' => 'geteyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'round','target' => '_blank'));
}

$this->request->projectvars['VarsArray'][17] = 0;

echo $this->Html->link(__('Replace this vision test',true), array_merge(array('action' => 'replaceeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

echo '</p>';
echo '</div>';
?>

<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
}
?>

<script type="text/javascript">
$(document).ready(function(){
	$("a.editcertificate").click(function() {
		$("#certification").load($(this).attr("href"), {
			"ajax_true": 1,
		})
		return false;
	});
});
</script>
