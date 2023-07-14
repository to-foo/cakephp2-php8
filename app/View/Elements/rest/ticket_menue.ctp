<ul class="editmenue">
<?php
echo '<li class="deactive" id="link_main_area">';
echo $this->Html->link(__('Scan',true),'javascript:',array('class'=>'notstop','title' => __('Description',true),'rel' => 'main_area'));
echo '</li>';

echo '<li class="deactive" id="link_data_area">';
echo $this->Html->link(__('Tickets open',true),'javascript:',array('class'=>'notstop','title' => __('Tickets data open',true),'rel' => 'data_area'));
echo '</li>';

echo '<li class="deactive" id="link_data_areaclosed">';
echo $this->Html->link(__('Tickets closed',true),'javascript:',array('class'=>'notstop','title' => __('Tickets data closed',true),'rel' => 'data_areaclosed'));
echo '</li>';

?>
</ul>
<?php
echo $this->element('rest/js/edit_ticket_menue_js');
echo $this->element('rest/js/reload_tables_js');
echo $this->element('rest/js/edit_testingcomp');
echo $this->element('js/json_request_animation');
?>
