<div class="modalarea examiners index inhalt">
<h2><?php echo __('Imageupload', true);?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName)){
  //echo $this->element('js/close_modal_reload_container',array('FormName' => $FormName));
echo $this->element('js/reload_container',array('FormName' => $FormName));
echo $this->element('js/close_modal_auto');
echo '</div>';
return;
}
?>
<?php
echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "image/*"));
echo $this->element('form_upload_report',array('writeprotection' => false));
echo $this->element('js/form_upload_report_single',array('container' => '#dialog','FileLabel' => __('Drop image her to upload', true),'Extension' => 'png|PNG|jpg|JPG|jpeg|JPEG'));
?>
</div>

<?php
echo $this->element('js/form_button_set');
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/scroll_modal_top');
?>
