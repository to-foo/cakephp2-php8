<div class="modalarea">
<h2>
<?php
echo __('Examiner') . ' ' .
$certificate_data['Examiner']['name'] . ' - ' .
__('history') . ' ' .
$certificate_data['Eyecheck']['certificat'];
?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<div id="dialogform_content">
<ul class="listemax certificates">
<?php
echo '<li><b>';
echo $certificate_data['Eyecheck']['certificat'] . ' ' . __('created on') . ' ' . $certificate_data['Eyecheck']['first_registration'];
echo '</b></li>';

foreach($certificate_datas as $_certificate_datas){
	$this->request->projectvars['VarsArray'][17] = $_certificate_datas['EyecheckData']['id'];
	echo '<li>';
	if(isset($_certificate_datas['EyecheckData']['file']) && $_certificate_datas['EyecheckData']['file'] == true){
		echo $this->Html->link($certificate_data['Eyecheck']['certificat'] . ' ' . __('renewed on') . ' ' . $_certificate_datas['EyecheckData']['certified_date'], array_merge(array('action' => 'geteyecheckfile'), $this->request->projectvars['VarsArray']), array('target' => '_blank'));
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
