<div class="modalarea detail">
<h2><?php echo __('Add expediting step'); ?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
}
?>
<div class="hint">

<?php
echo $this->Html->link(__('Add expediting type'),
							array_merge(array(
								'action' => 'addexpediting',
							),$this->request->projectvars['VarsArray']),
							array(
								'class'=>'mymodal round',
								'title'=> __('Add expediting type', true)
							),
						);

echo $this->Html->link(__('Edit expediting types'),
							array_merge(array(
								'action' => 'editexpediting',
							),$this->request->projectvars['VarsArray']),
							array(
								'class'=>'mymodal round',
								'title'=> __('Edit expediting types', true)
							),
						);

?>
</div>
</div>

</div>