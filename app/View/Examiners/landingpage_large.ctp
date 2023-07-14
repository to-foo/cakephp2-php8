<h3><?php echo __('Examiners / Certificates'); ?></h3>
<div class="users index inhalt">
<div class="quicksearch">
<?php
echo $this->Html->link(__('Open certificate modul',true),
	array(
		'controller' => 'examiners',
		'action' => 'index',
		'origin' => 'topprojects'
	),
	array(
		'class' => 'round ajax',
		'title' => __('Open certificate modul',true)
	)
);

if(isset($ControllerQuickSearch)) echo $this->element('searching/search_quick_examiner',array('target_id' => 'examiner_id','targedaction' => 'overview','action' => 'quicksearch','minLength' => 2,'discription' => __('Examiner last name', true)));

?>
</div>
<table id="" class="table_infinite_sroll advancetool">
<thead>
<tr>
<th><?php echo __('Functions',true);?></th>
<th class="small_cell"><?php echo __('Name',true);?></th>
<th class="small_cell"></th>
<?php  echo $this->element('examiner/examiner_table_header');?>
</tr>
</thead>
<tbody>
<?php

if(!isset($paging)) $paging['tr_marker'] = null;

	$i = 0;

	foreach($examiners as $_examiner):

		$class = ' class="infinite_sroll_item ' . $paging['tr_marker'];
		$title = null;

		if ($i++ % 2 == 0) {
			$class .= ' altrow ';
		}

		if($_examiner['Examiner']['active'] == 0){
			$class .= ' deactive ';
			$title .= ' title="'.__('This user is deactive',true).'" ';
		}

		$class .= '" ';

		$this->request->projectvars['VarsArray'][13] = 0;
		$this->request->projectvars['VarsArray'][14] = 0;
		$this->request->projectvars['VarsArray'][15] = $_examiner['Examiner']['id'];
?>
<tr id="examiner_tr_<?php echo $_examiner['Examiner']['id'] ?>" data-page="<?php echo (isset($paging['page']) ? $paging['page'] : '');?>" <?php echo $class;?>>
<?php echo $this->element('examiner/examiner_link_table',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/item_cells_table_landingpage',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/qualifications_in_table',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/eyecheck_in_table',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/monitorings_in_table',array('_examiner' => $_examiner));?>
</tr>
<?php endforeach;?>
</tr>
</tbody>
</table>
<div id="fixed_element" class="fixed_element">
	<div id="fixed_quicksearch" class="fixed_quicksearch"></div>
	<div id="fixed_form"></div>
	<table id="table_header_fixed"></table>
</div>
<?php
$PageUrl = $this->Html->url(array_merge(array(
		'controller' => $this->request->params['controller'],
		'action' => $this->request->params['action']
	),$this->request->projectvars['VarsArray'])
);
$PageUrl .=  '/page:';

if(isset($TableAnchor)) echo $this->Form->input('TableAnchor',array('type' => 'hidden','value' => $TableAnchor));

echo $this->Form->input('CurrentLoadetPage',array('type' => 'hidden','value' => isset($paging['page']) ? $paging['page'] : 1));
echo $this->Form->input('PageCount',array('type' => 'hidden','value' => (isset($paging['pageCount']) ? $paging['pageCount'] : '')));
echo $this->Form->input('DefaultPageUrl',array('type' => 'hidden','value' => $PageUrl));

echo $this->element('js/examiner_summary');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/show_pdf_link');
?>
</div>
