<?php
if(!isset($this->request->data['Template']['evaluation_temp'])) return;
if(count($this->request->data['Template']['evaluation_temp']) == 0) return;
if(!isset($this->request->data['TemplatesEvaluation'])) return;
if(empty($this->request->data['TemplatesEvaluation'])) return;
$model = $this->request->data['Template']['models'][2];
$lang = $locale;
$setting = $this->request->data['Template']['xml']['settings']->{$model};
?>

<?php
echo '<h4> ';
echo __('Evaluations',true)  . ' - ' . $this->request->data['Template']['name'];
echo '</h4>';
?>

<div class="current_content flex_info">
<?php foreach ($value['TemplatesEvaluation']['data'] as $_key => $_value):?>
<?php foreach ($_value as $__key => $__value):?>
<?php foreach ($__value as $___key => $___value):?>

  <div class="flex_item">
  <div class="content">
  <p class="editable" rev="<?php echo $___value['field'];?>" rel="<?php echo $___value['model'];?>">
  <?php
  if(empty($___value['value'])) echo '-';
  else echo $___value['value'];

  ?>
  </p>
  </div>
  <div class="label">
  <?php
  if(!empty($setting->{$___value['field']}->discription->$lang)) echo trim($setting->{$___value['field']}->discription->$lang);
  else echo $___value['field'];
  ?>
  </div>
  </div>

<?php endforeach; ?>
</div>
<div class="current_content flex_info">
<?php endforeach; ?>
<?php endforeach; ?>
</div>
