<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$eyecheck_data['Welder']['name'] . ' ' . 
__('eye check') ;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div id="container_summary" class="container_summary" ></div>
<div class="current_content">
<?php 
return;
echo $this->Form->create('WelderEyecheck', array('class' => 'dialogform')); ?>
<fieldset>
<?php
$radio_desc = array(0 => 'nein', 1 => 'ja');
//echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'WelderEyecheck');
//echo $this->ViewData->ShowOrderData($this->request->data,$settings,$locale);

if($modus == 2){ 
	echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'Certificate');
	$modus = 0;
	$modus_disc = __('Show basic data infos',true);
}
else {
//	echo $this->ViewData->ShowOrderData($this->request->data,$settings,$locale);
	$modus = 2;
	$modus_disc = __('Refresh basic data infos',true);
}

?>
</fieldset>
<?php
echo $this->Html->link($modus_disc, array_merge(array('action' => 'eyecheck'), $this->request->projectvars['VarsArray']), array('rel' => $modus,'class' => 'round refreshwelder'));
?>

<?php echo $this->Form->end();?>
<h2><?php echo __('Eye check infos',true);?></h2>
<?php
echo '<div id="certification">';
$this->request->projectvars['VarsArray'][15] = $eyecheck_data['Welder']['id'];
$this->request->projectvars['VarsArray'][16] = $eyecheck_data['WelderEyecheck']['id'];
$this->request->projectvars['VarsArray'][17] = $eyecheck_data['WelderEyecheckData']['id'];

if($eyecheck_data['WelderEyecheckData']['valid_class'] == 'certification_deactive'){
	echo '<div class="error"><p>';
	echo __('This vision test is deactive',true);
	echo '</p></div>';
}
	
	if(isset($FileUploadErrors)){
		
		if($FileUploadErrors == 0) $hint_class = 'hint';
		else $hint_class = 'error';
		
		echo '<div class="'.$hint_class.'">';
		echo '<p>';
		echo $message;
		echo '</p>';
	echo '</div>';
	}

if(!empty($eyecheck_data['WelderEyecheckData']['certified_file_error'])){
	echo '<div class="error">';
	
	if(isset($FileUploadErrors) && $FileUploadErrors > 0){
		echo '<p>';
		echo $message;
		echo '</p>';
	}
	
	echo '<p>';
	echo $eyecheck_data['WelderEyecheckData']['certified_file_error'];
	echo ' ';
	echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'eyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'mymodal round'));
	echo '</p></div>';
}
if(!empty($eyecheck_data['WelderEyecheckData']['certified_file'])){
	echo '<div class="hint"><p>';
	echo __('There is a valid certificate.');
	echo ' ';
	echo __('When you upload a new file, the existing file will be deleted.');
	echo '</p><p>';
	echo $this->Html->link(__('Show certificate file',true), array_merge(array('action' => 'geteyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'round','target' => '_blank'));

	echo $this->Html->link(__('Replace certificate file',true), array_merge(array('action' => 'eyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));

	echo $this->Html->link(__('Delete certificate file',true), array_merge(array('action' => 'deleyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));

	echo '</p></div>';
}

echo '<div class="hint certification ' .  $eyecheck_data['WelderEyecheckData']['valid_class'] . '">';
if($eyecheck_data['WelderEyecheckData']['certified'] == 1){
	echo '<p>';
	echo __('Sehtest erstellt',true) . ': ' . $eyecheck_data['WelderEyecheckData']['certified_date'];
	echo '</p><p>';
	echo __('This vision test is still valid for',true) . ': ';
	echo $eyecheck_data['WelderEyecheckData']['time_to_next_certification']; 
	echo '</p><p>';
	echo __('For the next vision test they are reminded in',true) . ': ';
	echo $eyecheck_data['WelderEyecheckData']['time_to_next_horizon'] . ' ';
//	echo $certificate_data['Certificate']['horizon'] . ' ' . __('months',true) . ' ' . __('before',true) . ')'; 
	echo '</p><p><strong>';
	echo $eyecheck_data['Welder']['first_name'] . ' ' . $eyecheck_data['Welder']['name']. ' ';
	echo __('hat eine ausreichende Sehfähigkeit für die folgenden Prüfverfahren:',true);
	echo '</strong></p><p><strong>';
	if($eyecheck_data['WelderEyecheckData']['vt'] == 1) echo '"' . __('VT',true) . '" ';
	if($eyecheck_data['WelderEyecheckData']['pt'] == 1) echo '"' . __('PT',true) . '" ';
	if($eyecheck_data['WelderEyecheckData']['mt'] == 1) echo '"' . __('MT',true) . '" ';
	if($eyecheck_data['WelderEyecheckData']['ut'] == 1) echo '"' . __('UT',true) . '" ';
	if($eyecheck_data['WelderEyecheckData']['sv'] == 1) echo '"' . __('Prüfaufsicht',true) . '" ';
	echo '</strong></p><p><strong>';
	echo __('Eine Sehhilfe ist',true) . ' ';
	if($eyecheck_data['WelderEyecheckData']['glasses'] == 0) echo __('nicht',true) . ' ';
	echo __('erforderlich.',true);
	echo '</strong></p>';
	echo '<p>' .  __('1. Nahsehen',true) . ': <strong>' . $radio_desc[$eyecheck_data['WelderEyecheckData']['near_vision']] . '</strong></p>';
	echo '<p>' .  __('2. Ausreichendes Farbsehvermögen/Kontrastsehvermögen',true) . ': <strong>' . $radio_desc[$eyecheck_data['WelderEyecheckData']['color_contrast']] . '</strong></p>';
	echo '<p>' .  __('2.1 Bei Anamolien ist eine betriebstärztliche Beratung erfolgt',true) . ': <strong>' . $radio_desc[$eyecheck_data['WelderEyecheckData']['anomalies']] . '</strong></p>';
	echo '<p>' .  __('3. Gesichtsfelduntersuchung ist erfolgt',true) . ': <strong>' . $radio_desc[$eyecheck_data['WelderEyecheckData']['face']] . '</strong></p>';
	echo '<p>' .  __('4. Sehfähigkeit für die Ferne',true) . ': <strong>' . $radio_desc[$eyecheck_data['WelderEyecheckData']['distance_vision']] . '</strong></p>';
	echo '<p>' .  __('4.1 Mit Landolt Ringen',true) . ': <strong>' . $radio_desc[$eyecheck_data['WelderEyecheckData']['landolt']] . '</strong></p>';
	echo '<p>' .  __('4.2 Mindestens auf einem Auge erreicht',true) . ': <strong>' . $radio_desc[$eyecheck_data['WelderEyecheckData']['one_eye']] . '</strong></p>';
	echo '<p>' .  __('4.3 Andere Art',true) . ': <strong>' . $radio_desc[$eyecheck_data['WelderEyecheckData']['different_way']] . '</strong></p>';
}
if($eyecheck_data['WelderEyecheckData']['certified'] == 2){
	echo '<p>';
	echo __('This vision test has the status',true) . ' "' . __('invalid',true) . '"';
	echo '</p>';
}
if($eyecheck_data['WelderEyecheckData']['certified'] == 0){
	echo '<p>';
	echo __('This vision test has no status',true);
	echo '</p>';
}
echo '<p>';
echo $this->Html->link(__('Edit this vision test',true), array_merge(array('action' => 'editeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));

//echo $this->Html->link(__('Delete this vision test',true), array_merge(array('action' => 'deleyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

if(!empty($eyecheck_data['WelderEyecheckData']['certified_file'])){
//	echo $this->Html->link(__('Show vision test file',true), array_merge(array('action' => 'geteyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'round','target' => '_blank'));
}

$this->request->projectvars['VarsArray'][17] = 0;

//echo $this->Html->link(__('Replace this vision test',true), array_merge(array('action' => 'replaceeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

echo '</p>';

echo '<p>';
echo '<strong>';
echo __('Remarks',true);
echo '</strong><br />';
echo $eyecheck_data['WelderEyecheckData']['remark'];
echo '</p>';

echo '</div>';

?>

<?php echo $this->element('fast_save_welder_js');?>

<script type="text/javascript">
$(document).ready(function(){
	$("a.editcertificate").click(function() {
		$("#certification").load($(this).attr("href"), {
			"ajax_true": 1,
		})
		return false;
	});	
	$("a.refreshwelder").click(function() {
		$("#dialog").load($(this).attr("href"), {
			"ajax_true": 1,
			"modus": $(this).attr("rel"),
		})
		return false;
	});			
});
</script>
<?php
echo '</div>';
?>
</div>
</div>
<div class="clear"></div>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>
