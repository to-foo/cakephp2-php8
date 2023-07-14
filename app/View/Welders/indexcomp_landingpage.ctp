<h3><?php echo __('Welding Companies',true); ?></h3>
<div class="quicksearch">
	<?php
	echo $this->element('searching/search_quick_welder',
		array(
			'target_id' => 'id',
			'targedaction' => 'overview',
			'action' => 'quicksearch',
			'minLength' => 2,
			'discription' => __('Name', true)
			)
		);
	?>
</div>
<ul class="listemax">
<?php
$this->request->projectvars['VarsArray'][15] = 0;
$this->request->projectvars['VarsArray'][16]= 0;
$this->request->projectvars['VarsArray'][17]=0;
foreach($welder_comp as $_key => $_welder_comp){
	echo '<li>';
	$this->request->projectvars['VarsArray'][14] = $_welder_comp['id'];
	echo $this->Html->link($_welder_comp['name'],array_merge(array('action' => 'indexpart'),$this->request->projectvars['VarsArray']),array('class' => 'open_project complink'));
	echo '</li>';
}
?>
</ul>
<?php
echo $this->element('js/ajax_link_global',array('name' => 'a.open_project'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.icon_edit'));
echo $this->element('landingpage/js/ajax_link_lage_window',array('name' => 'a.addsearching'));
?>
