<?php
if(empty($data)) return false;
?>
<div class="current_content flex_info">
<?php
if($step == 'General' && isset($this->request->data['Template']['models'][0]))$model = $this->request->data['Template']['models'][0];
if($step == 'Specific' && isset($this->request->data['Template']['models'][1]))$model = $this->request->data['Template']['models'][1];

$lang = $locale;
$setting = $this->request->data['Template']['xml']['settings']->{$model};
foreach ($this->request->data['Template']['data'] as $key => $value):
if(strstr($key, $step, true) === false) continue;
?>
</div>
<?php
echo '<h4> ';
echo $step  . ' - ' . $this->request->data['Template']['name'];
echo '</h4>';
?>
<div class="current_content flex_info">
<?php foreach ($value as $_key => $_value):?>

  <div class="flex_item">
  <div class="content">
  <p class="editable" rev="<?php echo $_key;?>" rel="<?php echo $model;?>">
  <?php
  if(empty($_value)) echo '-';
  else echo $_value;
  ?>
  </p>
  </div>
  <div class="label">
  <?php
  if(!empty($setting->$_key->discription->$lang)) echo trim($setting->$_key->discription->$lang);
  else echo Inflector::humanize($_key);
  ?>
  </div>
  </div>

<?php endforeach; ?>
<?php endforeach; ?>
</div>
