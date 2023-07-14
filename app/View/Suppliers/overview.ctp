<div class="users index inhalt">
<h2><?php echo __('Overview')?> <?php echo $this->request->data['Supplier']['equipment']?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="quicksearch"><?php echo $this->element('expediting/quicksearch');?></div>
<div class="hint">
<?php
echo $this->element('expediting/bread_crump');
echo '<div class="advance_navi">';
echo $this->element('navigation/change_modul_ndt');
echo '</div>';
echo $this->Html->link(__('Edit technical place',true),
	array_merge(
		array(
			'action' => 'edit'
		),
		$this->request->projectvars['VarsArray']
	),
	array(
		'class' => 'modal round',
	)
);
echo $this->Html->link(__('Delete Technical Place',true),
	array_merge(
		array('action' => 'delete'),
		$this->request->projectvars['VarsArray']
		),
	array(
		'class' => 'modal round',
		)
);
echo $this->Html->link(__('Manage ITPs',true),
	array_merge(
		array(
			'controller' => 'expeditings',
			'action' => 'indextemplate'
		),
		$this->request->projectvars['VarsArray']
	),
	array(
		'class' => 'modal round',
	)
);
	
/*
echo $this->Html->link(__('Duplicate',true),
	array_merge(
		array('action' => 'duplicate'),
		$this->request->projectvars['VarsArray']
		),
	array(
		'class' => 'modal round',
		)
);
*/


echo $this->element('expediting/supplier_infos');

echo $this->element('expediting/rkl_infos');

echo '<div class="advance_navi">';

echo $this->Form->input('ChooseExpeditingStep',
	array(
		'label' => false,
		'empty' => __('Choose expediting step',true),
		'options' => $this->request->data['ExpeditingTypes']
	)
);
echo '</div>';
?>
</div>
<div class="expediting_flex">
<?php
echo $this->element('expediting/expediting_event_table');
echo $this->element('expediting/expediting_file_table');
echo $this->element('expediting/expediting_image_table');
?>
</div>
</div>
<?php
echo $this->element('js/close_modal_auto');
echo $this->element('expediting/js/open_supplier_edit');
echo $this->element('expediting/js/sort_table');
echo $this->element('expediting/js/expediting_edit_js');
echo $this->element('image/show_fancyimage');
echo $this->element('js/json_request_animation');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
//echo $this->element('js/ajax_modal_link');
echo $this->element('js/resize_table_column');
echo $this->element('expediting/table_short_view');
?>
