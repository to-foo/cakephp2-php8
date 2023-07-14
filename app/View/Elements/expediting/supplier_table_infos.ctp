<div class="flex_info">
<?php
$xml = $this->request->data['xml'];

foreach($xml['settings']->section->children() as $_key => $_children){
  if($_key == 'url') continue;

  $Model = trim($_children->model);
  $Key = trim($_children->key);
  $Description = trim($_children->description->$locale);

  if(!isset($value[$Model][$Key])) continue;

  echo '<div class="flex_item">';
  echo '<div class="label">';
  echo $Description;
  echo '</div>';
  echo '<div class="content">';
  if(!empty($value[$Model][$Key])) echo $value[$Model][$Key];
  else echo '-';
  echo '</div>';
  echo '</div>';

}
?>
</div>
