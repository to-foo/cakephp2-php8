<?php
echo '<div class="areas inhalt" id="advance_area">';
echo '<h3>';
echo __('Advances',true) . ' ';
echo '</h3>';
echo '<div id="advances">';
echo '<div class="hint">';

if(isset($this->request->data['BreadcrumpArray'])) echo $this->element('advance/bread_crump');

$SchemeUrl = $this->Html->url(array_merge(array('action' => 'json_scheme'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('SchemeUrl',array('type' => 'hidden','value' => $SchemeUrl));
echo $this->Form->input('CascadeGroupId',array('type' => 'hidden','value' => ''));
echo $this->element('advance/advance_area_navigation');
echo '</div>';
echo $this->element('advance/advance_area_table_content_start');
echo '</div>';
echo '</div>';

?>
