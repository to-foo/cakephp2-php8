<?php
if(!isset($this->request->data['Template']['evaluation_temp'])) return;
if(count($this->request->data['Template']['evaluation_temp']) == 0) return;
if(!isset($this->request->data['TemplatesEvaluation'])) return;
if(empty($this->request->data['TemplatesEvaluation'])) return;

$lang = $locale;
$setting = $this->request->data['Template']['xml']['settings']->ReportRtEvaluation;
?>
<div class="current_content flex_info">
<?php
//pr($this->request->data['TemplatesEvaluation']);
foreach ($this->request->data['TemplatesEvaluation'] as $key => $value):
?>
<div class="flex_item">
<div class="content">
<?php
if(empty($value['TemplatesEvaluation']['name'])) echo '-';
else echo $value['TemplatesEvaluation']['name'];
?>
</div>
<div class="label">
<?php echo __('Name',true);?>
</div>
</div>
<div class="flex_item">
<div class="content">
<?php
if(empty($value['TemplatesEvaluation']['description'])) echo '-';
else echo $value['TemplatesEvaluation']['description'];
?>
</div>
<div class="label">
<?php echo __('Description',true);?>
</div>
</div>
</div>
<div class="current_content flex_info">
<?php foreach ($value['TemplatesEvaluation']['data'] as $_key => $_value):?>
<?php foreach ($_value as $__key => $__value):?>
<?php foreach ($__value as $___key => $___value):?>

  <div class="flex_item">
  <div class="content">
  <?php
  if(empty($___value['value'])) echo '-';
  else echo $___value['value'];
  ?>
  </div>
  <div class="label">
  <?php echo $___value['field'];?>
  </div>
  </div>

<?php endforeach; ?>
</div>
<div class="current_content flex_info">

<?php endforeach; ?>
<?php endforeach; ?>
<?php endforeach; ?>
</div>
