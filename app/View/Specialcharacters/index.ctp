<div class="modalarea">
<h2><?php echo __('Specialcharacter');?></h2>
<div class="specialcharacter">
<div class="flex">
<?php
if(isset($field) && !empty($field)) echo $this->Html->tag('div', __('Specialcharacter will be appended to field %s', $field), array('class'=>'hint'));
//else echo $this->Html->tag('div', __('No field selected to append Specialcharacter'), array('class'=>'error'));

foreach($Specialcharacter as $_Specialcharacter){

	echo '<div class="item">';
	echo $this->Html->link(
		$_Specialcharacter['Specialcharacter']['value'],
		'javascript:',
		array(
			'title' => __('Click to copy', true) . ': ' . $_Specialcharacter['Specialcharacter']['discription'] . ' (' . $_Specialcharacter['Specialcharacter']['value'] . ')',
			'class' => 'item'
			)
		);
	echo '</div>';
}
?>
</div>
</div>
</div>
<?php
echo $this->element('specialcharacter/put_special_character');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
?>
