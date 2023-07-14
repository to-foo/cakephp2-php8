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
foreach ($this->request->data['TemplatesEvaluation'] as $key => $value):
?>
<div class="current_content flex_info">
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
<?php echo $this->element('templates/evaluation_data_show_eval_temp',array('value' => $value));?>
<?php endforeach; ?>
