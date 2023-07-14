<div class="">
<h2><?php echo array_shift($this->request->data['DeviceTestingmethod']['DeviceTestingmethod']) . ' - ' . __('Devices') . ' ' . $this->request->data['Device']['name'];?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div class="quicksearch">
<?php echo $this->element('barcode_scanner');?>
<?php
if(isset($ControllerQuickSearch)) echo $this->element('searching/search_quick_device',array('target_id' => 'id','targedaction' => 'view','action' => 'quicksearch','minLength' => 2,'discription' => __('Intern number', true)));
echo $this->Html->link(__('Add device',true), array_merge(array('action' => 'add'), array()), array('class' => 'modal icon icon_devices_add','title' => __('Add device',true)));
?>
</div>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div class="clear"></div>
<div class="current_content hide_infos_div">
<h3><?php echo __('Device infos',true);?></h3>
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Device','dl');?>
<dl>
</dl>
<dl>

<?php
// Muss noch mit in die ShowDataList Funktion
echo __('Responsible person',true);?>:
<strong>
<?php echo ($this->request->data['Examiner']['name']);?>
<?php echo ($this->request->data['Examiner']['first_name']);?>
</strong>
</dl>
</div>
<div class="current_content">
<?php
echo $this->Html->link(__('Hide infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_hide_infos icon_toggle','title' => __('Hide infos',true)));
echo $this->Html->link(__('Edit device',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_devices_edit','title' => __('Edit device',true)));
echo $this->Html->link(__('Print device infos',true),array_merge(array('action' =>'pdf'),$this->request->projectvars['VarsArray']),array('class' => 'icon icon_devices_pdf showpdflink','title' => __('Print device infos',true)));
echo $this->Html->link(__('Show or upload documents',true),array_merge(array('action' =>'files'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_file','title' => __('Show or upload documents',true)));
echo $this->Html->link(__('Add monitoring',true),array_merge(array('action' =>'addmonitoring'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_monitoring_add','title' => __('Add monitoring',true)));
?>
<div class="clear"></div>
<ul class="listemax certificates">
<li><b class="head">
<?php
echo $this->Html->link(__('Monitoring infos',true), 'javascript:');
?>
</b>
<ul>
<?php

if(is_countable($device) && count($device) > 0){
	foreach($this->request->data['Certificates'] as $_key => $_device){
		$this->request->projectvars['VarsArray'][17] = $_device['DeviceCertificate']['id'];
		$this->request->projectvars['VarsArray'][18] = $_device['DeviceCertificateData']['id'];

		echo '<li>';
		echo '<ul class="';
		echo $_device['DeviceCertificateData']['valid_class'];
		echo '">';
		echo '<li>';



		echo '<p class="show_file"><strong>';
		echo $_device['DeviceCertificate']['certificat'];
		echo '</strong></p>';

		foreach($xmlcert->DeviceCertificate->children() as $_xmlkey => $_xml){

			if((trim($_xml->showinfo) != 1 || empty($_xml->showinfo))) continue;

			echo '<p class="show_file">';

			if($_xml->fieldtype == 'radio') echo trim($_xml->discription->$locale). ': ' . $_xml->radiooption->value[$_device['DeviceCertificateData'] [$_xmlkey]];
			else echo trim($_xml->discription->$locale). ': ' . $_device['DeviceCertificateData'] [$_xmlkey];

			echo '</p>';

    }

		echo'<br></br>';
		echo '<p class="show_file">';
		echo $_device['DeviceCertificateData']['certification_requested'];
		echo '</p>';
		echo '<p class="show_file">';
		echo $_device['DeviceCertificateData']['time_to_next_certification'];
		echo '</p>';
		echo '<p class="show_file">';
		echo $_device['DeviceCertificateData']['time_to_next_horizon'];
		echo '</p>';
		echo '<p class="show_file">';
		echo $_device['DeviceCertificateData']['next_certification_date'];
		echo '</p>';

		if(!isset($_device['DeviceCertificateData']['certified_file_pfath']) & $_device['DeviceCertificateData']['file'] == 1){
			echo '<p class="show_file"><strong class="error">' . __('There is no monitoring file available.',true) . '</strong></p>';
		}

		echo '<p class="show_file">';
		echo $this->Html->link(__('Edit monitoring',true), array_merge(array('action' => 'editmonitoring'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit','title' => __('Edit monitoring',true)));

		if($_device['DeviceCertificateData']['file'] == 1){
			if(isset($_device['DeviceCertificateData']['certified_file_pfath']) && $_device['DeviceCertificateData']['certified_file_pfath'] != ''){
				echo $this->Html->link(__('Show monitoring file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('target' => '_blank','class' => 'icon icon_monitoring_file','title' => __('Show monitoring file',true)));

			} else {
				echo $this->Html->link(__('Upload monitoring file',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('title' => __('Upload monitoring file',true),'class' => 'modal icon icon_upload_red'));
			}
		}

		echo $this->Html->link(__('Replace this monitoring',true), array_merge(array('action' => 'replacemonitoring'), $this->request->projectvars['VarsArray']), array('title' => __('Replace this monitoring',true),'class' => 'modal icon icon_replace'));
		echo $this->Html->link(__('Delete monitoring',true),array_merge(array('action' =>'delmonitoring'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_del','title' => __('Delete monitoring',true)));

		echo '<div class="clear"></div>';
		echo '</p>';
		echo '</li></ul></li>';

	}
}
?>
</ul>
</div>
</div>

<?php
echo $this->element('js/json_request_animation');
echo $this->element('js/show_pdf_link');
echo $this->element('js/toggle_element',array('Button' => '.icon_toggle','Element' => '.hide_infos_div'));
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/devices_bread_search_combined');

echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
