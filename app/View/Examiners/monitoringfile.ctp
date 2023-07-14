<div class="modalarea">
<h2><?php echo __('Examiner') . ' ' . $data['Examiner']['name'] . ' - ' . $data['ExaminerMonitoring']['certificat'] . '/';?></h2>
<?php echo $this->element('Flash/_messages');?>

<?php

if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}

$this->request->projectvars['VarsArray'][16] = $data[$models['main']]['id'];
$this->request->projectvars['VarsArray'][17] = $data[$models['sub']]['id'];

echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "application/pdf"));

echo $this->element('form_upload_report',array('writeprotection' => false));
echo $this->element('js/form_upload_report_single',array('container' => '#dialog','url' => $this->request->here,'FileLabel' => __('Drop files her to upload', true),'Extension' => 'pdf|PDF'));
?>
</div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
