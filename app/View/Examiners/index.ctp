<div class="examiners index inhalt">
<h2><?php echo __('Examiners'); ?></h2>
<div class="quicksearch">
<?php
if(isset($ControllerQuickSearch)) echo $this->element('searching/search_quick_examiner',array('target_id' => 'examiner_id','targedaction' => 'overview','action' => 'quicksearch','minLength' => 2,'discription' => __('Examiner last name', true)));

echo $this->Html->link(__('Add examiner',true), array_merge(array('action' => 'add'), array()), array('class' => 'modal icon icon_examiners_add','title' => __('Add examiner',true)));
echo $this->Html->link(__('Print overview',true), array_merge(array('action' => 'printoverview'), array()), array('class' => 'modal icon icon_print','title' => __('Print overview',true)));
echo $this->Html->link(__('Send overview',true), array_merge(array('action' => 'sendoverview'), array()), array('class' => 'modal icon icon_on_mail','title' => __('Send overview',true)));
?>
</div>
<?php echo $this->element('Flash/_messages');?>
<div id="container_table_summary" class="content">
<?php echo $this->element('qualification_legend');?>
<?php echo $this->element('monitoring_legend');?>
<?php echo $this->element('examiner/sort_form');?>
<table id="" class="table_infinite_sroll table_fixed_header">
<thead>
<tr>
<th><?php echo __('Functions',true);?></th>
<?php
foreach($xml->section->item as $_key => $_xml){

	if(trim($_xml->condition->key) != 'enabled') continue;

	$class = null;

	if(!empty($_xml->class)) $class = trim($_xml->class);

	echo '<th class="'.$class.'">';
	echo trim($_xml->description->$locale);
	echo '</th>';
}
?>
<?php echo $this->element('examiner/examiner_table_header');?>

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
<?php echo $this->element('examiner/item_cells_table',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/qualifications_in_table',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/eyecheck_in_table',array('_examiner' => $_examiner));?>
<?php echo $this->element('examiner/monitorings_in_table',array('_examiner' => $_examiner));?>
</tr>
<?php endforeach;?>
<?php
if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	/*
	$colspan = count($xml->section->item) + 4;

	echo '<tr data-page="' . $paging['page'] . '" class="infinite_sroll_item '.$paging['tr_marker'].'" >';
	echo '<td colspan="'. $colspan . '">';
	echo $this->Paginator->counter(array('format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')));
	echo '</td>';
	echo '<td>';
	echo '</td>';
	echo '</tr>';
	*/
}
?>
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

echo $this->element('js/examiner_summary', array('sender' => 'index'));
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_paging');

if(Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == true){
	echo $this->element('infinite/infinite_links');
	echo $this->element('infinite/infinite_scroll');
}
if(!Configure::check('InfiniteScroll') || (Configure::check('InfiniteScroll') && Configure::read('InfiniteScroll') == false)){
	echo $this->element('page_navigation');
}

echo $this->element('js/table_fixed_header');
echo $this->element('js/show_pdf_link');
?>
<script>
$(document).ready(function (){

	if($("#TableAnchor").length){

		elm = "#examiner_tr_" + $("#TableAnchor").val();

		if($(elm).offset() == undefined) return false;
		if($(elm).offset().top == undefined) return false;


		$('html, body').animate({scrollTop: $(elm).offset().top}, 1000);

	}
		});
</script>
</div>
</div>
