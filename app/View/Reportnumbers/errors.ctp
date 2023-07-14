<div class="modalarea detail">
<h2><?php echo __('Validation'); ?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<?php
if (isset($reportnumber['Reportnumber']['revision_write']) && $reportnumber['Reportnumber']['revision_write'] == 1) {
  echo $this->element('reports/printing_revision_progress');
  return false;
}

echo $this->element('reports/printing_link_json');
//echo $this->element('reports/printing_link');
echo $this->element('reports/email_link');
?>
</div>
<?php
echo $this->element('reports/js/close_after_printing');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/testreport_erros_skip');
//echo $this->element('js/testreport_print_link',array('FormName' => $FormName));
echo $this->element('js/testreport_print_link_json');
echo $this->element('js/json_request_animation');
?>
