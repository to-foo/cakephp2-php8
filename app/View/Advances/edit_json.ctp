<div class="inhalt advances form">
<h2><?php echo __('Edit this') . ' ' . __('advance'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
echo $this->element('advance/edit_menue');
//echo $this->element('advance/js/schema_menue_js');

if(isset($JsonScheme)){

  echo '<div id="advances">';
  echo $JsonScheme;
  echo '</div>';
  
}

echo $this->element('js/ajax_stop_loader');
echo $this->element('js/form_button_set');
echo $this->element('js/form_multiple_fields');
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/resize_table_column');
?>
