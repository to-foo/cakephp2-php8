<div class="quicksearch">
<?php
echo $this->element('navigation/change_modul_ndt');
echo $this->element('searching/search_quick_reportnumber',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));
?>
</div>
<div class="pagin_links">
</div>
<div class="clear"></div>
<div class="reportnumbers index inhalt">
<?php if(isset($reportsDevelopmentMenu)) echo $this->element('development/development_menue',array('progress' => $reportsDevelopmentMenu));?>
<?php echo $this->Navigation->showReports($reportsContainer); ?>
</div>
<div class="clear" id="testdiv"></div>
<div monitoring>
<?php

  if(isset($this->request->data) && !empty($this->request->data)){
//  	echo $this->element('monitorings/index_part');
  }



?>


</div>
<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_stop_loader');

?>
