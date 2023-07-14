<div class="modalarea">
<h2><?php echo __('File') . ' - ' . $data['Document']['document_type'] . '/' . $data['Document']['name'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="<?php if(isset($hint) && isset($message_class)) echo $message_class;?>"><p><?php if(isset($hint)) echo $hint;?></p></div>

<?php
if(isset($FormName) && count($FormName) > 0){

	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
unset($this->request->projectvars['VarsArray'][15]);
unset($this->request->projectvars['VarsArray'][16]);

$url = $this->Html->url(array_merge(array('controller' => 'documents', 'action' => 'certificatefile'),$this->request->projectvars['VarsArray']));

echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "application/pdf,image/*,video/*,audio/*,.csv, text/csv, application/vnd.ms-excel, application/csv, text/x-csv, application/x-csv, text/comma-separated-values, text/x-comma-separated-values,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/docx,text/plain,application/msword,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"));

echo $this->element('form_upload_report',array('writeprotection' => false));
echo $this->element('js/form_upload_report_single',array('container' => '#dialog','url' => $this->request->here,'FileLabel' => __('Drop files her to upload', true),'Extension' => 'pdf|PDF'));

echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
