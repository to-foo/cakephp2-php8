<div id="fast_searchform_container">
<div class="quicksearch">
<?php
echo $this->Html->link(__('Back',true),
  array(
    'controller' => 'rkls',
    'action' => 'classes'
  ),
  array(
    'class' => 'icon backlink',
    'title' => __('Back',true)
  )
);
?>
</div>
<h4><?php echo __('Edit tube class');?> <?php echo $this->request->data['RklNumber']['description'];?></h4>
<?php echo $this->Form->create(); ?>
<fieldset>
<?php
//pr($this->request->data);
foreach($xml->children() as $key => $value){

  if($key == 'Rkl') continue;
  if($key == 'RklNumber') continue;
  if(empty($value->fields)) continue;

//  pr($key);
//  pr($value);

  $Legend = trim($value->discription->$locale);
  $Class = trim($value->modus);

  echo '</fieldset><fieldset class="'.$key.'">';
  echo '<legend>';
  echo $Legend;
  echo '</legend>';

  foreach($value->fields->children() as $_key => $_value){

    if(empty($_value)) continue;
    if(!isset($this->request->data[$key][$_key])) continue;

    $Description = trim($_value->discription->$locale);
    $Name = trim($_value->model);
    $Field = Inflector::camelize(trim($_value->option));

    if(isset($this->request->data[$key][$_key]) && count($this->request->data[$key][$_key]) > 1){

      $Option = $this->request->data[$key][$_key];

      echo $this->Form->input($Name . $Field,
        array(
          'name' => 'data[Rkl]['.$Name.']['.$Field.']',
          'label' => $Description,
          'options' => $Option,
          'empty' => 'chose one',
          'class' => $Class
        )
      );
    } else {

      $val = $this->request->data[$key][$_key][key($this->request->data[$key][$_key])];

      echo $this->Form->input($Name . $Field,
        array(
          'name' => 'data[Rkl]['.$Name.']['.$Field.']',
          'label' => $Description,
          'class' => $Class,
          'value' => $val

        )
      );
    }

  }
}
?>
</fieldset>
<?php echo $this->Form->end(__('Submit',true)); ?>
<?php
echo $this->element('js/ajax_link_landingpage_global',array('name' => 'a.backlink'));
//echo $this->element('js/form_send_json',array('name' => 'RklAutofastForm'));
echo $this->element('rkls/js/value_sync',array('name' => 'RklRklsForm'));
?>
