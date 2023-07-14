<div class="modalarea">
<h2><?php echo __('Monitoring') . ' - ' . $certificate_data['DeviceCertificate']['certificat'];?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>
<?php
echo '</div>';
return;
?>
<div class="current_content">

<?php
echo '<div id="certification">';

echo $this->Form->create('DeviceCertificateData');
echo '<fieldset>';

echo '<div class="infos">';
foreach($arrayData['settings']->DeviceCertificate->children() as $_key => $_xml){

	if(trim($_xml->output->screen) != 1) continue;

	$value = $certificate_data[trim($_xml->model)][trim($_xml->key)];

	if($_xml->fieldtype == 'radio' && $_xml->radiooption){
		$value = trim($_xml->radiooption->value->$value);
	}

	if(!$value) $value = '-';

	echo '<p class="output">';
	echo $value;
	echo '<span class="title">';
	echo trim($_xml->discription->$locale);
	echo '</span>';
	echo '</p>';

}
echo '</div>';

echo '</fieldset>';
echo $this->Form->end();

//$this->request->projectvars['VarsArray'][15] = $certificate_data['Examiner']['id'];
//$this->request->projectvars['VarsArray'][16] = $certificate_data['Certificate']['id'];

if($certificate_data['DeviceCertificate']['file'] == 1){
echo '<div id="file_container">';
if(!empty($certificate_data['DeviceCertificateData']['certified_file_error'])){
	echo '<div class="error"><p>';
	echo $certificate_data['DeviceCertificateData']['certified_file_error'];
	echo ' ';
	echo $this->Html->link(__('Upload',true), array_merge(array('controller' => 'devices','action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'editcertificate mymodal round'));
	echo '</p></div>';
}

if(!empty($certificate_data['DeviceCertificateData']['certified_file'])){
	echo '<div class="hint"><p>';
	echo __('There is a valid monitoring file.');
	echo ' ';
	echo __('When you upload a new file, the existing file will be deleted.');
	echo '</p><p>';
	echo $this->Html->link(__('Show certificate file',true), array_merge(array('controller' => 'devices','action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round','target' => '_blank'));

	echo $this->Html->link(__('Replace certificate file',true), array_merge(array('controller' => 'devices','action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));

	echo $this->Html->link(__('Delete certificate file',true), array_merge(array('controller' => 'devices','action' => 'delcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal'));

	echo '</p></div>';
}
echo '</div>';
}
echo '<div class="hint certification ' .  $certificate_data['DeviceCertificateData']['valid_class'] . '">';
if($certificate_data['DeviceCertificateData']['certified'] == 1){
	echo '<p>';
	echo $certificate_data['DeviceCertificateData']['time_to_next_certification'];
	echo '</p><p>';
	echo $certificate_data['DeviceCertificateData']['time_to_next_horizon'] . ' ';
	echo '</p>';
}
if($certificate_data['DeviceCertificateData']['certified'] == 2){
	echo '<p>';
	echo __('This certificate has the status',true) . ' "' . __('not certificated',true) . '"';
	echo '</p>';
}
if($certificate_data['DeviceCertificateData']['certified'] == 0){
	echo '<p>';
	echo __('This certificate has no status',true);
	echo '</p>';
}
if($certificate_data['DeviceCertificateData']['active'] == 0){
	echo '<p>';
	echo __('This certificate has the status deaktive.',true);
	echo '</p>';
}
echo '<p>' . $certificate_data['DeviceCertificateData']['next_certification_date'] . '</p>';
echo '<p><b class="';
echo $certificate_data['DeviceCertificateData']['certification_requested_class'];
echo '">' . $certificate_data['DeviceCertificateData']['certification_requested'] . '</b></p>';
echo '<p>';
echo '<p>';
echo '<strong>' . __('Remarks',true) . '</strong><br />';
echo $certificate_data['DeviceCertificateData']['remark'];
echo '</p>';
echo $this->Html->link(__('Edit this monitoring information',true), array_merge(array('controller' => 'devices','action' => 'editmonitoring'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal editcertificate'));
// echo $this->Html->link(__('Replace this monitoring',true), array_merge(array('controller' => 'devices','action' => 'replacemonitoring'), $this->request->projectvars['VarsArray']), array('class' => 'round mymodal editcertificate'));
echo '</p>';
echo '</div>';
?>

</div>
</div>
<div class="clear"></div>
<script type="text/javascript">
$(document).ready(function(){

});
</script>

<?php  echo $this->JqueryScripte->ModalFunctions(); ?>
