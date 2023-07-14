<div id="fast_searchform_container">
<div class="quicksearch">
<?php
echo $this->Html->link(__('Back',true),
  array(
    'controller' => 'rkls',
    'action' => 'index'
  ),
  array(
    'class' => 'icon backlink',
    'title' => __('Back',true)
  )
);
?>
</div>
<h4><?php echo __('Edit tube class');?></h4>
<?php echo $this->Form->create(); ?>
<fieldset>
<?php
//$local
foreach($xml->Rkl->children() as $key => $value){
//  pr($value);
  echo $this->Form->input(trim($value->key),
    array(
      'label' => trim($value->discription->{$locale})
    )
  );
}
?>
</fieldset>
<?php echo $this->Form->end(__('Submit',true)); ?>
<?php
echo $this->element('js/ajax_link_landingpage_global',array('name' => 'a.backlink'));
echo $this->element('js/form_send_json',array('name' => 'RklAutofastForm'));
echo $this->element('js/json_request_animation');
?>
