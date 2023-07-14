<?php // pr($this->request->data['Devisions']);?>
<ul class="editmenue">
<?php
echo '<li class="deactive" id="link_main_area">';
echo $this->Html->link(__('Settings',true),'javascript:',array('title' => __('Description',true),'rel' => 'main_area'));
echo '</li>';
echo '<li class="deactive" id="link_data_area">';
echo $this->Html->link(__('Allgemeine/Prüfdaten',true),'javascript:',array('title' => __('Allgemeine/Prüfdaten',true),'rel' => 'data_area'));
echo '</li>';
/*
echo '<li class="deactive" id="link_evaluation_area">';
echo $this->Html->link(__('Auswertungsdaten',true),'javascript:',array('title' => __('Auswertungsdaten',true),'rel' => 'evaluation_area'));
echo '</li>';
*/
?>
</ul>
<?php echo $this->element('templates/js/edit_master_menue_js');?>
