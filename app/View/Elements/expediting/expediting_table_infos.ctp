<?php
if(empty($value['Expediting'])) return;
?>
<div class="flex_info">
<?php
$Email = array(0 => __('active',true),1 => __('deaktiv',true));
foreach ($value['Expediting'] as $_key => $_value) {

  $Emailinfos = $Email[$_value['Expediting']['stop_mail']];

  if($value['Supplier']['stop_mail'] == 1) $Emailinfos = $Email[1];
  
  echo '<div class="flex_item ' . $_value['Expediting']['class'] . '">';
  echo '<div class="label">';
  if(!empty($value['ExpeditingTypes'][$_value['Expediting']['expediting_type_id']])) echo $value['ExpeditingTypes'][$_value['Expediting']['expediting_type_id']];
  else echo '-';
  echo '</div>';
  echo '<div class="content">';

  echo '<p>';
  echo __('Soll Date') . ': ' . $_value['Expediting']['date_soll'];
  echo '</p>';
  echo '<p>';
  echo __('Ist Date') . ': ' . $_value['Expediting']['date_ist'];
  echo '</p>';
  echo '<p>';
  echo __('Email') . ': ' . $Emailinfos;
  echo '</p>';

  echo '</div>';
  echo '</div>';

}
?>
</div>
