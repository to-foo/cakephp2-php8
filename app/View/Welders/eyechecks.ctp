<div class="">
<h2><?php  echo __('Welder') . ' ' . $welder['Welder']['name'] . ' ' . __('eye checks');?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if(count($arrayData['settings']) == 0){
	echo '</div>';
	echo $this->JqueryScripte->LeftMenueHeight();
	return;
}

?>
<div class="infos">
<div class="current_content hide_infos_div">
<h3><?php echo __('Welder infos',true);?></h3>
<?php echo $this->ViewData->ShowDataList($arrayData,$locale,'Welder','ul');?>

<div class="clear"></div>
</div>
<div class="clear"></div>
</div>
<div id="container_summary" class="container_summary" ></div>
<div class="current_content">
<?php 
echo $this->Html->link(__('Hide welder infos',true), 'javascript:', array('id' => '_infos_link','class' => 'icon icon_hide_infos','title' => __('Hide welder infos',true)));
echo $this->Html->link(__('Edit welder',true), array_merge(array('action' => 'edit'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_edit_welder','title' => __('Edit welder',true)));
echo $this->Html->link(__('Print welder infos',true), array_merge(array('action' => 'pdf'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_devices_pdf','title' => __('Print welder infos',true)));

if(count($eyechecks) == 0){
	echo $this->Html->link(__('Add eye check',true), array_merge(array('action' => 'neweyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'modal icon icon_add_eyecheck','title' => __('Add eye check',true)));
}
?>
<div class="clear"></div>
<ul class="listemax certificates">
<li>
<b class="head">
	<?php echo $this->Html->link(__('eye checks',true), 'javascript:',array('class' => 'nolink'));?>
</b>
<ul>
<li>
<ul class="<?php echo isset($eyechecks['WelderEyecheckData']['valid_class']) ? $eyechecks['WelderEyecheckData']['valid_class'] : '';?>">
<li>
<?php echo __('Eye check carried out by',true);?> :
<?php echo isset($eyechecks['WelderEyecheckData']['user_id']) ? $eyechecks['WelderEyecheckData']['user_id'] : '' ;?>
</li>
<li>
<?php echo __('Eye check carried out/renewed on',true);?> :
<?php echo isset($eyechecks['WelderEyecheckData']['certified_date']) ? $eyechecks['WelderEyecheckData']['certified_date'] : '';?>
</li>
<li>
<?php echo __('The next eye check must be carried out',true);?> :
<?php echo isset($eyechecks['WelderEyecheckData']['next_certification']) ? $eyechecks['WelderEyecheckData']['next_certification'] : '';?> (
<?php echo isset($eyechecks['WelderEyecheckData']['time_to_next_certification']) ? $eyechecks['WelderEyecheckData']['time_to_next_certification'] : '';?>) 
</li>
<li>
<?php echo __('The next reminder is on',true);?> :
<?php echo isset($eyechecks['WelderEyecheckData']['next_certification_horizon'])? $eyechecks['WelderEyecheckData']['next_certification_horizon'] : '';?> (
<?php echo isset($eyechecks['WelderEyecheckData']['time_to_next_horizon']) ? $eyechecks['WelderEyecheckData']['time_to_next_horizon'] : '';?>) 
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
if(isset($eyechecksummary)){
//	echo $this->Quality->WelderEyecheckSummary($eyechecksummary,$welder['Welder']['id'],$welder['Welder']['name']);
}
echo $this->Html->link(__('Edit vision test',true), array_merge(array('action' => 'editeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_edit modal','title' => __('Edit vision test',true)));
// echo $this->Html->link(__('Show vision test documents',true), array_merge(array('action' => 'eyecheckfiles'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_file modal','title' => __('Show vision test documents',true)));

if(isset($eyechecks['WelderEyecheckData']['certified_file_pfath']) && $eyechecks['WelderEyecheckData']['certified_file_pfath'] != ''){
	$this->request->projectvars['VarsArray'][17] = $eyechecks['WelderEyecheckData']['id'];
	echo $this->Html->link(__('Show vision test file',true), array_merge(array('action' => 'geteyecheckfile'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_certificate','target' => '_blank','title' => __('Show vision test file',true)));
}
else {
	echo $this->Html->link(__('Upload',true), array_merge(array('action' => 'eyecheckfile'), $this->request->projectvars['VarsArray']), array('title' => __('Upload',true),'class' => 'modal icon icon_upload_red'));
}

$VarsArray_17 = $this->request->projectvars['VarsArray'][17];
$this->request->projectvars['VarsArray'][17] = 0;
echo $this->Html->link(__('Replace this vision test',true), array_merge(array('action' => 'replaceeyecheck'), $this->request->projectvars['VarsArray']), array('title' => __('Replace this vision test',true),'class' => 'modal icon icon_replace'));

echo $this->Html->link(__('Show vision test history',true), array_merge(array('action' => 'historyeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_history modal','title' => __('Show vision test history',true)));

$this->request->projectvars['VarsArray'][17] = $VarsArray_17;
echo $this->Html->link(__('Delete vision test',true), array_merge(array('action' => 'removeeyecheck'), $this->request->projectvars['VarsArray']), array('class' => 'icon icon_del modal','title' => __('Delete vision test',true)));

?> 
</p>
<div class="clear"></div>
</li>
</ul>
</li>
<ul>
</li>
</ul>


<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
