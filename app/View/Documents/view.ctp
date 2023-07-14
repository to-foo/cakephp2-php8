<div class="examiners index inhalt">
<h2>
<?php echo __('Documents'); ?> -
<?php echo $testingmethod['DocumentTestingmethod']['verfahren'];?> -
<?php echo $document['Document']['document_type'];?>/
<?php echo $document['Document']['name'];?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<div class="current_content hide_infos_div">
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Document','ul');?>
<div class="clear"></div>
</div>
<dl class="icon_links">
<?php
echo $this->Html->link(__('Hide document infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_hide_infos','title' => __('Hide document infos',true)));
echo $this->Html->link(__('Edit document infos',true),array_merge(array('action' =>'edit'),$this->request->projectvars['VarsArray']),array('class' => 'icon icon_documents_edit modal','title' => __('Edit document infos',true)));

if($document['Document']['active'] == 1){
	if(isset($this->request->data['Document']['certified_file_valide']) && $this->request->data['Document']['certified_file_valide'] == 1){
		echo $this->Html->link(__('Show file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('target' => '_blank','class' => 'icon icon_download_file','title' => __('Show file',true)));
	} else {
		echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('title' => __('Upload',true),'class' => 'modal icon icon_upload_red'));
	}
	echo $this->Html->link(__('Replace file',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('title' => __('Replace file',true),'class' => 'modal icon icon_replace'));
	echo $this->Html->link(__('Delete document',true),array_merge(array('action' =>'del'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_del','title' => __('Delete document',true)));
}
elseif($document['Document']['active'] == 0){
	if(isset($this->request->data['Document']['certified_file_valide']) && $this->request->data['Document']['certified_file_valide'] == 1){
		echo $this->Html->link(__('Show file',true) . ' (' . __('deactive',true) . ')', 'javascript:', array('class' => 'icon icon_gray_download_file','title' => __('Show file',true) . ' (' . __('deactive',true) . ')'));
	} else {
		echo $this->Html->link(__('Upload',true) . ' (' . __('deactive',true) . ')', 'javascript:', array('title' => __('Upload',true) . ' (' . __('deactive',true) . ')','class' => 'icon icon_gray_upload_red'));
	}

	echo $this->Html->link(__('Replace file',true) . ' (' . __('deactive',true) . ')', 'javascript:', array('title' => __('Replace file',true) . ' (' . __('deactive',true) . ')','class' => ' icon icon_gray_replace'));
	echo $this->Html->link(__('Delete document',true) . ' (' . __('deactive',true) . ')','javascript:',array('class' => ' icon icon_gray_del','title' => __('Delete document',true) . ' (' . __('deactive',true) . ')'));
}
?>
<div class="clear"></div>
</dl>
</div>
<?php
echo $this->element('js/json_request_animation');
echo $this->element('js/show_pdf_link');
echo $this->element('js/toggle_element',array('Button' => '.icon_toggle','Element' => '.hide_infos_div'));
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/devices_bread_search_combined');
?>
