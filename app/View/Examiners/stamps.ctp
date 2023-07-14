<div class="modalarea">
<h2><?php echo __('Examiner') . ' ' . $examiner_name . ' - ' . __('Stamps',true);?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="clear"></div>
<div id="stamp_container">
<?php
if(!$HasFile) {
	echo $this->element('examiner/examiner_upload_stamp', array(('projectsvars') => implode('/', $this->request->projectvars['VarsArray']), ('show_submit') => true));
} else {
	echo $this->element('examiner/examiner_show_stamp', array(('projectsvars') => implode('/', $this->request->projectvars['VarsArray'])));
	echo "<br/>";
	echo "<br/>";
	echo "<br/>";
}
 ?>
 </div>
</div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/file_context_menue');
//echo $this->element('js/form_upload',array('url' => $url,'FileLabel' => __('Select files', true),'Extension' => 'pdf'));
?>
