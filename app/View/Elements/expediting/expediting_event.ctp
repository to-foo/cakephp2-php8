<?php
if(!isset($this->request->data['ExpeditingEvent'])) return false;
if(count($this->request->data['ExpeditingEvent']) == 0) return false;

echo '<table class="advancetool">';
echo '<tr>';
echo '<th>' . __('Date',true) . '</th>';
echo '<th>' . __('Soll',true) . '</th>';
echo '<th>' . __('Ist',true) . '</th>';
echo '<th>' . __('Remark',true) . '</th>';
echo '</tr>';

foreach ($this->request->data['ExpeditingEvent'] as $key => $value) {
  echo '<tr>';
  echo '<td>' . $value['created'] . '</td>';
  echo '<td>' . $value['date_soll'] . '</td>';
  echo '<td>' . $value['date_ist'] . '</td>';
  echo '<td>' . $value['remark'] . '</td>';
  echo '</tr>';
}

echo '</table>';
?>
