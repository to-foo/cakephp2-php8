<div class="modalarea detail">
	<h3><?php echo __('Assign Report').': '.$reportName; ?></h3>
<form class="Searchform">
<?php
// pr($reportnumber);
// pr($testingreports);
/*
echo $this->Html->nestedList(array_map(function($elem, $view) {
	pr($elem);
	$options = array('type'=>'button', 'href'=>$view->Html->Url(array('action'=>'setParent',$elem['Reportnumber']['id'])));
	return $view->Form->button($view->Pdf->ConstructReportName($elem), $options);
}, $reports, array_fill(0, count($reports), $this)));
*/
//var_dump($projectID);
$testingreports = array_map(
	function($elem, $view) {
		return array(
			'id'=>$elem['Reportnumber']['id'],
			'name'=>$view->Pdf->ConstructReportName($elem)
		);
	},
	$testingreports,
	array_fill(0, count($testingreports), $this)
);

foreach($testingreports as $report) {
	$url = array(
		'action'=>'setparent',
		$type ? $reportnumber['Reportnumber']['id'] : $report['id'],
		$type ? $report['id'] : $reportnumber['Reportnumber']['id'],
		$reportnumber['Reportnumber']['id']
	);
	
	$options = array(
		'type'=>'button',
		'class'=>'modal setParent',
		'href'=>$this->Html->Url($url),
	);
	echo $this->Html->tag('div', $this->Form->button($report['name'], $options), array('class'=>'button'));
}

/*
echo $this->Html->nestedList(array_map(function ($elem, $view, $parent) {
	$options = array(
		'type'=>'button',
		'href'=>$view->Html->Url(array('action'=>'setParent', $elem['id'], $parent)),
	);
	return $view->Html->tag('div', $view->Form->button($elem['name'], $options), array('class'=>'button'));
}, $testingreports, array_fill(0, count($testingreports), $this), array_fill(0, count($testingreports), $projectID)));
 */
?>
<div class="clear" id="testdiv"></div>
</form>
</div>

<?php
echo $this->JqueryScripte->ModalFunctions();
