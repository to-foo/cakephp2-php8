<div class="">
<h2>
<?php echo $testingmethod['DocumentTestingmethod']['verfahren'];?> -
<?php echo __('Documents'); ?> -
<?php echo $document[0]['Document']['name'];?>
</h2>
<?php echo $this->element('Flash/_messages');?>    
<div class="quicksearch">
<?php echo $this->element('barcode_scanner');?>
<?php
if(isset($ControllerQuickSearch)){
	echo $this->Navigation->quickComponentSearching('quicksearch',$ControllerQuickSearch,false);
}
echo $this->Html->link(__('Add document',true), array_merge(array('action' => 'add'), array()), array('class' => 'modal icon icon_documents_add','title' => __('Add document',true)));
?>
</div>

<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div class="current_content">
<h3><?php echo __('Document infos',true);?></h3>
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Document','dl');?>
<dl>
<?php
echo __('Registered in the following testing methods',true) . ': ';
if(count($document[0]['Testingmethods']) > 0){
	foreach($document[0]['Testingmethods'] as $_key => $_data){
		if($_key > 0)echo '; ';
		echo '<strong>' . $_data['verfahren'] . '</strong>';
	}
}
?>
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

<div class="current_content">
<?php
echo $this->Html->link(__('Edit document',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_documents_edit','title' => __('Edit document',true)));
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
if(count($document > 0)){
	foreach($document as $_key => $_document){
		$this->request->projectvars['VarsArray'][17] = $_document['DocumentCertificate']['id'];
		$this->request->projectvars['VarsArray'][18] = $_document['DocumentCertificateData']['id'];
		echo '<li>';
		echo $this->Html->link($_document['DocumentCertificate']['certificat'],array_merge(array('action' => 'monitoring'),$this->request->projectvars['VarsArray']),array('class'=>'modal'));
		echo '<ul class="';
		echo $_document['DocumentCertificateData']['valid_class'];
		echo '">';
		echo '<li>';

		echo '<p class="show_file"><strong>';
		echo $_document['DocumentCertificate']['certificat'];
		echo '</strong></p>';

		echo '<p class="show_file">';
		echo $_document['DocumentCertificateData']['certification_requested'];
		echo '</p>';
		echo '<p class="show_file">';
		echo $_document['DocumentCertificateData']['time_to_next_certification'];
		echo '</p>';
		echo '<p class="show_file">';
		echo $_document['DocumentCertificateData']['time_to_next_horizon'];
		echo '</p>';
		echo '<p class="show_file">';
		echo $_document['DocumentCertificateData']['next_certification_date'];
		echo '</p>';

		if(!isset($_document['DocumentCertificateData']['certified_file_pfath']) & $_document['DocumentCertificate']['file'] == 1){
			echo '<p class="show_file"><strong class="error">' . __('There is no certifcate file available.',true) . '</strong></p>';
		}

		echo '<p class="show_file">';
		echo $this->Html->link(__('Edit monitoring',true), array_merge(array('action' => 'monitoring'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit','title' => __('Edit monitoring',true)));

		if($_document['DocumentCertificate']['file'] == 1){
			if(isset($_document['DocumentCertificateData']['certified_file_pfath']) && $_document['DocumentCertificateData']['certified_file_pfath'] != ''){
				echo $this->Html->link(__('Show certificate file',true), array_merge(array('action' => 'getcertificatefile'), $this->request->projectvars['VarsArray']), array('target' => '_blank','class' => 'icon icon_monitoring_file','title' => __('Show certificate file',true)));

			} else {
				echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'certificatefile'), $this->request->projectvars['VarsArray']), array('title' => __('Upload',true),'class' => 'modal icon icon_upload_red'));
			}


		}

		echo $this->Html->link(__('Show document',true),array_merge(array('action' =>'files'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_file','title' => __('Show document',true)));

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
</div>

<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
