<div class="quicksearch"><?php if(isset($ControllerQuickSearch)) echo $this->element('searching/search_quick_examiner',array('target_id' => 'examiner_id','targedaction' => 'overview','action' => 'quicksearch','minLength' => 2,'discription' => __('Examiner last name', true)));?></div>
<h2><?php  echo __('Examiner') . ' ' . $examiner['Examiner']['name'] . ' ' . __('eye checks');?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="infos">
<div class="current_content hide_infos_div">
<h3><?php echo __('Examiner infos',true);?></h3>
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Examiner','ul');?>
</div>
</div>
<div class="current_content">
<?php
echo $this->Html->link(__('Hide examiner infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_toggle icon_hide_infos','title' => __('Hide examiner infos',true)));
echo $this->Html->link(__('Edit examiner',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit_examiner','title' => __('Edit examiner',true)));
echo $this->Html->link(__('Print examiner infos',true), array_merge(array('action' => 'pdf'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf showpdflink','title' => __('Print examiner infos',true)));

if(count($eyechecks) == 0) echo $this->Html->link(__('Add eye check',true), array_merge(array('action' => 'neweyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_add_eyecheck','title' => __('Add eye check',true)));
?>
<ul class="listemax certificates">
<li>
<b class="head">
	<?php echo $this->Html->link(__('eye checks',true), 'javascript:',array('class' => 'nolink'));?>
</b>
<ul>
<li>
<ul class="<?php echo isset($eyechecks['EyecheckData']['valid_class']) ? $eyechecks['EyecheckData']['valid_class'] : '';?>">
<li>
<?php echo __('Eye check carried out/renewed on',true);?> :
<?php echo isset($eyechecks['EyecheckData']['certified_date']) ? $eyechecks['EyecheckData']['certified_date'] : '';?>
(<?php echo isset($eyechecks['EyecheckData']['time_to_next_certification']) ? $eyechecks['EyecheckData']['time_to_next_certification'] : '';?>)
</li>
<li>
<?php echo __('The next eye check must be carried out',true);?> :
<?php echo isset($eyechecks['EyecheckData']['next_certification']) ? $eyechecks['EyecheckData']['next_certification'] : '';?>
</li>
<li>
<?php echo __('The next reminder is on',true);?> :
<?php echo isset($eyechecks['EyecheckData']['next_certification_horizon'])? $eyechecks['EyecheckData']['next_certification_horizon'] : '';?> (
<?php echo isset($eyechecks['EyecheckData']['time_to_next_horizon']) ? $eyechecks['EyecheckData']['time_to_next_horizon'] : '';?>)
</li>
<?php

if(isset($eyechecksummary)){
	foreach($eyechecksummary as $_key => $_eyechecksummary){
		if(count($_eyechecksummary) == 0) continue;
		if($_key == 'hint') continue;
		echo '<li class="';
		echo $_key;
		echo '">';
		foreach($_eyechecksummary[key($_eyechecksummary)] as $__key => $__eyechecksummary){
			if(is_int($__key)) echo $__eyechecksummary . '<br>';
		}
		echo '</li>';
	}
}

?>
<li><p class="show_file">
<?php
echo $this->Html->link(__('Edit vision test',true), array_merge(array('action' => 'editeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_edit modal','title' => __('Edit vision test',true)));

if(isset($eyechecks['EyecheckData']['certified_file_pfath']) && $eyechecks['EyecheckData']['certified_file_pfath'] != ''){
	$this->request->projectvars['VarsArray'][17] = $eyechecks['EyecheckData']['id'];
	echo $this->Html->link(__('Show vision test file',true), array_merge(array('action' => 'geteyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_certificate','target' => '_blank','title' => __('Show vision test file',true)));
}
else {
	echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'eyecheckfile'), $this->request->projectvars['VarsArray']), array('title' => __('Upload',true),'class' => 'modal icon icon_upload_red'));
}

$VarsArray_17 = $this->request->projectvars['VarsArray'][17];
//$this->request->projectvars['VarsArray'][17] = 0;
echo $this->Html->link(__('Replace this vision test',true), array_merge(array('action' => 'replaceeyecheck'), $this->request->projectvars['VarsArray']), array('title' => __('Replace this vision test',true),'class' => 'modal icon icon_replace'));

echo $this->Html->link(__('Show vision test history',true), array_merge(array('action' => 'historyeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_history modal','title' => __('Show vision test history',true)));

$this->request->projectvars['VarsArray'][17] = $VarsArray_17;
echo $this->Html->link(__('Delete vision test',true), array_merge(array('action' => 'removeeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_del modal','title' => __('Delete vision test',true)));

?>
</p>
</li>
<?php if(!empty($eyechecks['EyecheckData']['remark'])) echo '<li>' . $eyechecks['EyecheckData']['remark'] . '</li>';?>
</ul>
</li>
</ul>
</li>
</ul>
<?php
echo $this->element('js/toggle_element',array('Button' => '.icon_toggle','Element' => '.hide_infos_div'));
echo $this->element('js/show_pdf_link');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
