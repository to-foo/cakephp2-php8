<?php return;?>
<div class="hint">
<ul class="expediting_navi ">
<?php
//pr($this->request->data['ExpeditingNavi']);
foreach($this->request->data['ExpeditingNavi'] as $_key => $_ExpeditingNavi){

	if(!isset($this->request->data['ExpeditingNavi'][$_ExpeditingNavi['action']])) continue;

	echo '<li>';
	echo $this->Html->link($_ExpeditingNavi['discription'],
			array_merge(array('controller'=>$_ExpeditingNavi['controller'],'action'=>$_ExpeditingNavi['action']),$_ExpeditingNavi['pass']),
			array('title' => $_ExpeditingNavi['discription'],'class'=>$_ExpeditingNavi['class'])
		);
	echo '</li>';
}

//pr($this->request->data['StatusMessages']);

echo '<li>';
echo $this->Html->link('Diagramm Termine',
		array_merge(
			array(
				'controller' => 'suppliers',
				'action' => 'diagramm'
			),
			$this->request->projectvars['VarsArray']
		),
		array(
			'title' => 'Diagramm Termine',
			'id' => 'time_statistik',
			'class' => 'icon_clock'
		)
	);
echo '</li>';

$StatusOption = array(
	'class' => 'filter',
	'div' => false,
	'label' => false,
    'options' => $this->request->data['StatusMessages']['StatusOption'],
    'empty' => __('choose status',true),
);
$SupplierOption = array(
	'class' => 'filter',
	'div' => false,
	'label' => false,
    'options' => $this->request->data['SupplierOption'],
    'empty' => __('choose supplier',true)
);
$OrderedOption = array(
	'class' => 'filter',
	'div' => false,
	'label' => false,
    'options' => array(0 => __('all',true),1 => __('not ordered',true),2 => __('ordered',true)),
    'empty' => __('choose ordered status',true)
);
$PlannerOption = array(
	'class' => 'filter',
	'div' => false,
	'label' => false,
    'options' => $this->request->data['PlannerOption'],
    'empty' => __('choose planner',true)
);
$AreaOfResponsibilityOption = array(
	'class' => 'filter',
	'div' => false,
	'label' => false,
    'options' => $this->request->data['AreaOfResponsibilityOption'],
    'empty' => __('choose area of responsibility',true)
);

if(isset($this->request->data['Statistic']['supplier'])){
	$SupplierOption['selected'] = $this->request->data['Statistic']['selected']['supplier'];
	echo $this->Form->input('Supplier.supplier',array('type' => 'hidden', 'value' => $this->request->data['Statistic']['supplier']));
}
if(isset($this->request->data['Statistic']['status'])) {
	$StatusOption['selected'] = $this->request->data['Statistic']['status'];
	echo $this->Form->input('Supplier.status',array('type' => 'hidden', 'value' => $this->request->data['Statistic']['status']));
}
if(isset($this->request->data['Statistic']['ordered'])) {
	$OrderedOption['selected'] = $this->request->data['Statistic']['ordered'];
	echo $this->Form->input('Supplier.ordered',array('type' => 'hidden', 'value' => $this->request->data['Statistic']['ordered']));
}
if(isset($this->request->data['Statistic']['area_of_responsibility'])) {
	$AreaOfResponsibilityOption['selected'] = $this->request->data['Statistic']['selected']['area_of_responsibility'];
	echo $this->Form->input('Supplier.area_of_responsibility',array('type' => 'hidden', 'value' => $this->request->data['Statistic']['area_of_responsibility']));
}
if(isset($this->request->data['Statistic']['planner'])) {
	$PlannerOption['selected'] = $this->request->data['Statistic']['selected']['planner'];
	echo $this->Form->input('Supplier.planner',array('type' => 'hidden', 'value' => $this->request->data['Statistic']['planner']));
}

if($this->request->data['ChildrenList'] != false){
echo '<li>';
echo $this->Form->input('childcascade', array(
	'div' => false,
	'label' => false,
    'options' => $this->request->data['ChildrenList'],
    'empty' => __('choose one',true)
));
echo '</li>';
}
echo '<li>';
echo $this->Form->input('supplier',$SupplierOption);
echo '</li>';
echo '<li>';
echo $this->Form->input('status',$StatusOption);
echo '</li>';
echo '<li>';
echo $this->Form->input('ordered',$OrderedOption);
echo '</li>';
echo '<li>';
echo $this->Form->input('planner',$PlannerOption);
echo '</li>';
echo '<li>';
echo $this->Form->input('area_of_responsibility',$AreaOfResponsibilityOption);
echo '</li>';
?>
</ul>
<ul class="expediting_legend">
<?php
foreach($this->request->data['StatusMessages']['PriorityStatus'] as $_key => $_PriorityStatus){
	echo '<li class="'.$_key.' tooltip" title="'.$_PriorityStatus['count']. ' ' . __('equipments',true) . ' - ' . $_PriorityStatus['text'].'">';
	echo '<span>';
	echo $_PriorityStatus['count'];
	echo '</span>';
	echo '</li>';
}
?>
<div class="clear"></div>
</ul>
<div class="clear"></div>
</div>
<?php echo $this->element('expediting/js/expediting_legend_js');?>
