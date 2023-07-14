<div class="modalarea">
<h2><?php echo __('Imageupload', true);?> <?php echo $Supplier['Supplier']['equipment'];?></h2>
<?php echo $this->element('Flash/_messages');?>
<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->element('js/reload_container',array('FormName' => $FormName));
	echo $this->element('js/ajax_stop_loader');
	echo $this->element('js/close_modal_auto');
	echo '</div>';
	return;
	}
?>

	<?php
	$url = $this->Html->url(array('controller' => 'suppliers', 'action' => 'files',
		 $Supplier['Supplier']['topproject_id'],
		 $Supplier['Supplier']['cascade_id'],
		 $Supplier['Supplier']['id'],
		)
	);

	echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
	echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
	echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "image/*"));
	echo $this->element('expediting/form_upload_report',array('writeprotection' => false));
	echo $this->element('expediting/js/form_upload_report_reload_container',array('container' => '#dialog','url' => $url,'FileLabel' => __('Drop images her to upload', true),'Extension' => 'jpg|jpeg|png|JPG|JPEG|PNG'));
	?>
	</div>
</div>
<div class="clear" id="testdiv"></div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('image/show_fancyimage');
echo $this->element('image/del_fancyimage');
?>
