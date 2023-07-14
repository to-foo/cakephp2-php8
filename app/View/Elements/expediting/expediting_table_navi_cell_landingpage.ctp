<td class="">
<?php
$OrderLink = $this->request->projectvars['VarsArray'];
$OrderLink[0] = $_examiner['Supplier']['topproject_id'];
$OrderLink[1] = $_examiner['Supplier']['cascade_id'];
$OrderLink[2] = $_examiner['Supplier']['id'];

$status_icon = 'icon_epediting';

if(isset($Priority[$_examiner['Supplier']['priority']])) $status_icon = $Priority[$_examiner['Supplier']['priority']];

echo $this->Html->link($_examiner['Supplier']['unit'] . '-' . $_examiner['Supplier']['equipment'],
			array_merge(array('controller'=>'suppliers','action' => 'overview'),$OrderLink),
			array('title' => __('Goto',true) . ' ' . $_examiner['Supplier']['unit'] . '-' . $_examiner['Supplier']['equipment'],'class'=>'ajax')
		);
?>
</td>
<td class="">
<?php

	$OrderLink = $this->request->projectvars['VarsArray'];
	$OrderLink[0] = $_examiner['Supplier']['topproject_id'];
	$OrderLink[1] = $_examiner['Supplier']['cascade_id'];
	$OrderLink[2] = $_examiner['Supplier']['order_id'];

	$status_icon = 'icon_epediting';

	if(isset($Priority[$_examiner['Supplier']['priority']])) $status_icon = $Priority[$_examiner['Supplier']['priority']];

	if(isset($ExpeditingLinks['overview'])){
		echo $this->Html->link($_examiner['Supplier']['unit'],
		'javascript:',
		array('title' => __('Expediting info',true),'class'=>'icon_larger icon_info '.$status_icon . '_overview')
	);
}
/*
if(isset($ExpeditingLinks['files'])){
	echo $this->Html->link(__('File manager',true),
	array_merge(array('controller'=>'suppliers','action' => 'files'),$OrderLink),
	array(
		'title' => __('File manager',true),
		'class'=>'icon_larger icon_file short_view summary_fieles modal',
		'rev' => Router::url(array_merge(array('controller'=>'expeditings','action'=>'shortview'), $this->request->projectvars['VarsArray']))
	)
);
}
if(isset($ExpeditingLinks['images'])){
	echo $this->Html->link(__('Image manager',true),
	array_merge(array('controller'=>'suppliers','action' => 'images'),$OrderLink),
	array(
		'title' => __('Image manager',true),
		'class'=>'icon_larger icon_image   modal',
		'rev' => Router::url(array_merge(array('controller'=>'expeditings','action'=>'shortview'), $this->request->projectvars['VarsArray']))
	)
);
}
*/
if(isset($ExpeditingLinks['detail'])){
	echo $this->Html->link($_examiner['Supplier']['unit'] . '-' . $_examiner['Supplier']['equipment'],
	array_merge(array('controller' => 'expeditings','action' => 'detail'),$this->request->projectvars['VarsArray']),
	array(
		'title' => '',
		'class'=> 'icon_larger modal short_view summary_expediting '.$status_icon,
		'id' => 'expediting_list_' . $_examiner['Supplier']['id'],
		'rev' => Router::url(array_merge(array('controller'=>'expeditings','action'=>'shortview'), $this->request->projectvars['VarsArray']))
	)
);
}
if(isset($ExpeditingLinks['control'])){
	if($_examiner['Supplier']['stop_mail'] == 1){
		echo $this->Html->link($_examiner['Supplier']['unit'] . '-' . $_examiner['Supplier']['equipment'],
		array_merge(array('controller' => 'suppliers','action' => 'control'),$this->request->projectvars['VarsArray']),
		array(
			'title' => __('Email delivering is active, click to deactivate',true),
			'id' => 'mail_status_' . $_examiner['Supplier']['id'],
			'class'=>'icon_larger icon_stop_mail mail_status',
		)
	);
	} elseif($_examiner['Supplier']['stop_mail'] == 0){
		echo $this->Html->link($_examiner['Supplier']['unit'] . '-' . $_examiner['Supplier']['equipment'],
		array_merge(array('controller' => 'suppliers','action' => 'control'),$this->request->projectvars['VarsArray']),
		array(
			'title' => __('Email delivering is deactive, click to activate',true),
			'id' => 'mail_status_' . $_examiner['Supplier']['id'],
			'class'=>'icon_larger icon_on_mail mail_status',
		)
	);

	}
}

?>
</td>
<?php
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
?>
