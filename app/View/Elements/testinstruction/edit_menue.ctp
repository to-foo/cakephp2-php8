<?php // pr($this->request->data['Devisions']);?>
<ul class="editmenue">
<?php
echo '<li class="active" id="link_main_area">';
echo $this->Html->link(__('Description',true),'javascript:',array('title' => __('Description',true),'rel' => 'main_area'));
echo '</li>';

if(!isset($this->request->data['Devisions'])){
	echo '</ul>';
	return;
}
if(count($this->request->data['Devisions']['Division']) == 0) {
	echo '</ul>';
	return;
}

foreach ($this->request->data['Devisions']['Division'] as $key => $value) {
	echo '<li class="deactive" id="link_' . $this->request->data['Devisions']['Class'][$key] . '">';
	echo $this->Html->link(__($value,true),'javascript:',array('title' => __($value,true),'rel' => $this->request->data['Devisions']['Class'][$key]));
	echo '</li>';
}
?>
</ul>
