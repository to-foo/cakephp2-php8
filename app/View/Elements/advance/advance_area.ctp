<?php
echo '<div class="hint">';
if(isset($this->request->data['BreadcrumpArray'])) echo $this->element('advance/bread_crump');

$SchemeUrl = $this->Html->url(array_merge(array('action' => 'json_scheme'),$this->request->projectvars['VarsArray']));

echo $this->Form->input('SchemeUrl',array('type' => 'hidden','value' => $SchemeUrl));
echo $this->Form->input('CascadeGroupId',array('type' => 'hidden','value' => ''));

echo $this->element('advance/advance_area_navigation');
echo $this->element('advance/advance_area_statistic_content');
echo $this->element('advance/advance_area_table_content');
echo '</div>';
?>
