<?php
if(!isset($this->request->data['Rkl'])) return false;
if(!isset($this->request->data['Xml']['Rkl'])) return false;
?>
<div class="flex_info">
<?php
foreach($this->request->data['Xml']['Rkl']->Rkl->children() as $key => $value){

  if(trim($value->output->screen) != 1) continue;

  $Model = trim($value->model);
  $Key = trim($value->key);
  $Description = trim($value->discription->$locale);

  if(!isset($this->request->data[$Model][$Key])) continue;

  echo '<div class="flex_item">';
  echo '<div class="label">';
  echo $Description;
  echo '</div>';
  echo '<div class="content" data-model="' . $Model . '" data-field="' . $Key . '">';
  if(!empty($this->request->data[$Model][$Key])) echo $this->request->data[$Model][$Key];
  else echo '-';
  echo '</div>';
  echo '</div>';
}
?>
</div>
