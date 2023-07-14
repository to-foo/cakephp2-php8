<div class="quicksearch">
<?php if(isset($ControllerQuickSearch)) echo $this->element('searching/search_quick_examiner',array('target_id' => 'examiner_id','targedaction' => 'overview','action' => 'quicksearch','minLength' => 2,'discription' => __('Examiner last name', true)));?>
</div>
<h2><?php echo __('Examiner details') . ' ' . $examiner['Examiner']['name'] . ' ' . $examiner['Examiner']['first_name'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="current_content hide_infos_div">
<?php  echo $this->ViewData->ShowDataList($arrayData,$locale,'Examiner','ul');?>
<div class="clear"></div>
</div>
<div class="current_content">
<?php echo $this->Html->link(__('Hide examiner infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_toggle icon_hide_infos','title' => __('Hide examiner infos',true)));?>
<?php echo $this->Html->link(__('Edit examiner',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit_examiner','title' => __('Edit examiner',true)));?>
<?php echo $this->Html->link(__('Print examiner infos',true), array_merge(array('action' => 'pdf'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf showpdflink','title' => __('Print examiner infos',true)));?>
<?php echo $this->Html->link(__('Show or upload documents',true),array_merge(array('action' =>'files'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_file','title' => __('Show or upload documents',true)));?>
<?php echo $this->Html->link(__('Show or upload stamps',true),array_merge(array('action' =>'stamps'),$this->request->projectvars['VarsArray']),array('class' => 'modal icon icon_stamp','title' => __('Show or upload stamps',true)));?>
<ul class="listemax current_content">
<li><?php echo $this->Html->link(__('Qualifications'), array_merge(array('action' => 'qualifications'), $this->request->projectvars['VarsArray']), array('class' => 'ajax'));?></li>
<li><?php echo $this->Html->link(__('Certificates'), array_merge(array('action' => 'certificates'), $this->request->projectvars['VarsArray']), array('class' => 'ajax'));?></li>
<li>
<?php
if(!empty($eyechecks)) echo $this->Html->link(__('Eye checks'), array_merge(array('action' => 'eyechecks'), $this->request->projectvars['VarsArray']), array('class' => 'ajax'));
elseif(empty($eyechecks)) echo $this->Html->link(__('Add eye check',true),array_merge(array('action' =>'neweyecheck'),$this->request->projectvars['VarsArray']),array('class' => 'modal'));
?>
</li>
<li>
<?php
if($examiner['Examiner']['active'] > 0){
	if(count($examiner['ExaminerMonitoring']) > 0) echo $this->Html->link(__('Monitoring',true),array_merge(array('action' =>'monitorings'),$this->request->projectvars['VarsArray']),array('class' => 'ajax'));
	elseif(count($examiner['ExaminerMonitoring']) == 0) echo $this->Html->link(__('Add monitoring',true),array_merge(array('action' =>'addmonitoring'),$this->request->projectvars['VarsArray']),array('class' => 'modal'));
}
elseif($examiner['Examiner']['active'] == 0){
	echo '<div class="hint"><p>';
	echo __('Der Prüfer wurde deaktiviert, Überwachungen sind nicht verfügbar.',true);
	echo '</p></div>';
}
?>
</li>
</ul>
</div>
<?php
echo $this->element('js/scroll_top');
echo $this->element('js/toggle_element',array('Button' => '.icon_toggle','Element' => '.hide_infos_div'));
echo $this->element('js/show_pdf_link');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
