<div class="examiners index inhalt">
<?php
if(!isset($this->request->data['Device']) || $this->request->data['Device']['deleted'] > 0){
	echo '<div class="error"><p>';
	echo __('The device has been deleted',true);
	echo '</p></div>';

	echo $this->element('js/ajax_breadcrumb_link');
	echo $this->element('js/ajax_link');
	echo $this->element('js/ajax_modal_link');

	echo '</div>';
	return false;
}
?>
<h2>
<?php echo(array_shift($this->request->data['DeviceTestingmethod']['DeviceTestingmethod']));?> -
<?php echo __('Devices'); ?> -
<?php echo $this->request->data['Device']['name'];?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<div class="quicksearch">
<?php echo $this->element('barcode_scanner');?>
<?php
echo $this->element('searching/search_quick_device',array('target_id' => 'id','targedaction' => 'view','action' => 'quicksearch','minLength' => 2,'discription' => __('Intern number', true)));
echo $this->Html->link(__('Add device',true), array_merge(array('action' => 'add'), array()), array('class' => 'modal icon icon_devices_add','title' => __('Add device',true)));
?>
<div class="clear"></div>
</div>
<div class="current_content hide_infos_div">
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Device','ul');?>
<dl>
<?php
// Muss noch mit in die ShowDataList Funktion
echo __('Responsible person',true);?>:
<strong>
<?php echo $this->request->data['Examiner']['name'] . ' ' . $this->request->data['Examiner']['first_name'];?>
</strong>
</dl>
<div class="clear"></div>
</div>
<div class="current_content">
<dl class="icon_links">
<?php
echo $this->Html->link(__('Hide infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_hide_infos icon_toggle','title' => __('Hide infos',true)));
echo $this->Html->link(__('Edit device',true),array_merge(array('action' =>'edit'),$this->request->projectvars['VarsArray']),array('class' => 'icon icon_devices_edit modal','title' => __('Edit device',true)));
echo $this->Html->link(__('Print device infos',true),array_merge(array('action' =>'pdf'),$this->request->projectvars['VarsArray']),array('class' => 'icon icon_devices_pdf showpdflink','title' => __('Print device infos',true)));
echo $this->Html->link(__('Show or upload documents',true),array_merge(array('action' =>'files'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_file','title' => __('Show or upload documents',true)));
echo $this->Html->link(__('Delete device',true),array_merge(array('action' =>'del'),$this->request->projectvars['VarsArray']),array('class' => 'icon icon_del modal','title' => __('Delete device',true)));

?>
<div class="clear"></div>
</dl>
<ul class="listemax">
<li>
<?php
if($this->request->data['Device']['active'] > 0){
	if(count($this->request->data['DeviceCertificate']) > 0){
	echo $this->Html->link(__('Monitoring',true),array_merge(array('action' =>'monitorings'),$this->request->projectvars['VarsArray']),array('class' => 'ajax'));
	}
	elseif(count($this->request->data['DeviceCertificate']) == 0){
		echo $this->Html->link(__('Add monitoring',true),array_merge(array('action' =>'addmonitoring'),$this->request->projectvars['VarsArray']),array('class' => 'modal'));
	}
}
elseif($this->request->data['Device']['active'] == 0){
	echo '<div class="hint"><p>';
	echo __('Das Gerät wurde deaktiviert, Überwachungen sind nicht verfügbar.',true);
	echo '</p></div>';
}
?>
</li>
</ul>
</div>
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
