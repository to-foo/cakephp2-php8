<?php

$this->request->projectvars['VarsArray'][15] = $certificate_data['Examiner']['id'];
$this->request->projectvars['VarsArray'][16] = $certificate_data['Certificate']['id'];
$this->request->projectvars['VarsArray'][17] = $certificate_data['CertificateData']['id'];

if(!empty($certificate_data['CertificateData']['certified_file_error'])){
	echo '<div class="error"><p>';
	echo $certificate_data['CertificateData']['certified_file_error'];
	echo ' ';
	echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'editcertificate round'));
	echo '</p></div>';
}
if(!empty($certificate_data['CertificateData']['certified_file'])){
	echo '<div class="hint"><p>';
	echo __('There is a valid certificate.');
	echo ' ';
	echo __('When you upload a new file, the existing file will be deleted.');
	echo '</p><p>';
	echo $this->Html->link(__('Show certificate file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round','target' => '_blank'));

	echo $this->Html->link(__('Replace certificate file',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'editcertificate round'));

	echo $this->Html->link(__('Delete certificate file',true), array_merge(array('action' => 'delcertificatefile'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

	echo '</p></div>';
}
echo '<div class="hint certification ' .  $certificate_data['CertificateData']['valid_class'] . '">';
if($certificate_data['CertificateData']['active'] == 0){
	echo '<p><strong>';
	echo __('This Qualification was deactivated.',true);
	echo '</strong></p>';
}
echo '<p>';
echo __('Zertifiziert am',true) . ': ' . $certificate_data['CertificateData']['certified_date'];
echo '</p>';
if($certificate_data['CertificateData']['certified'] == 1){
	echo '<p>';
	echo $certificate_data['CertificateData']['time_to_next_certification'];
	echo '</p><p>';
	echo $certificate_data['CertificateData']['time_to_next_horizon'] . ' ';
	echo '</p>';
}
if($certificate_data['CertificateData']['certified'] == 2){
	echo '<p>';
	echo __('This certificate has the status',true) . ' "' . __('not certificated',true) . '"';
	echo '</p>';
}
if($certificate_data['CertificateData']['certified'] == 0){
	echo '<p>';
	echo __('This certificate has no status',true);
	echo '</p>';
}
if($certificate_data['CertificateData']['active'] == 0){
	echo '<p>';
	echo __('This certificate has the status deaktive.',true);
	echo '</p>';
}
echo '<p>';
echo '<p>' . $certificate_data['CertificateData']['next_certification_date'] . '</p>';
echo '<p><b class="';
echo $certificate_data['CertificateData']['certification_requested_class'];
echo '">' . $certificate_data['CertificateData']['certification_requested'] . '</b></p>';
echo '<p>';
if($certificate_data['CertificateData']['certification_requested_class'] == 'is_not_requested' && $certificate_data['CertificateData']['valid'] != 0){
//	echo $this->Html->link(__('Rezertifizierung/Erneuerung beantragt',true), array_merge(array('action' => 'askcertification'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));
}
//if($certificate_data['CertificateData']['valid'] != 0){
	if($certificate_data['CertificateData']['apply_for_recertification'] == 1){
		$apply_for_recertification_disc = __('Beantragung Rezert./Erneuer. zurückziehen',true);
	}
	else {
		$apply_for_recertification_disc = __('Beantragung Rezert./Erneuer. stellen',true);
	}

	echo $this->Html->link($apply_for_recertification_disc, array_merge(array('action' => 'askcertification'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));
//}
echo $this->Html->link(__('Edit this information',true), array_merge(array('action' => 'editcertificate'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));

$this->request->projectvars['VarsArray'][17] = 0;

if($certificate_data['CertificateData']['apply_for_recertification'] == 1){

	$replace_link_desc = __('Replace this information',true);

	if(isset($certificate_data['CertificateData']['renewal']) && $certificate_data['CertificateData']['renewal'] == true){
		$replace_link_desc = __('Renewal this information',true);
	}

	echo $this->Html->link($replace_link_desc, array_merge(array('action' => 'replacecertificate'), $this->request->projectvars['VarsArray']), array('class' => 'round editcertificate'));
}
echo '</p>';
/*
print '<pre>';
print_r($certificate_data['CertificateData']);
print '</pre>';
*/
echo '</div>';

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
