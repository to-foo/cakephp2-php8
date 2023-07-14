<div class="modalarea">
<h2>
<?php
echo __('Examiner') . ' ' .
$certificate_data['Examiner']['name'] . ' ' .
__('history') . ' ' .
$certificate_data['Certificate']['third_part'] . '/' .
$certificate_data['Certificate']['sector'] . '/' .
$certificate_data['Certificate']['certificat'] . '/' .
ucfirst($certificate_data['Certificate']['testingmethod'])
;
?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<div id="dialogform_content">
<ul class="listemax certificates">
<?php
echo '<li><b>';
echo __('Certification created on',true) . ' ' . $certificate_data['CertificateData']['first_registration'];
echo '</b></li>';

foreach($certificate_datas as $_certificate_datas){
	$this->request->projectvars['VarsArray'][17] = $_certificate_datas['CertificateData']['id'];
	echo '<li>';
	if(isset($_certificate_datas['CertificateData']['file']) && $_certificate_datas['CertificateData']['file'] == true){
		echo $this->Html->link(__('Qualification certificated on') . ' ' . $_certificate_datas['CertificateData']['certified_date'], array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('target' => '_blank'));
	}
	else {
		echo $this->Html->link(__('No certificate file found'), 'javascript:');
	}
	echo '</li>';
}
?>
<ul>
</div>

</div>
<div class="clear"></div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
