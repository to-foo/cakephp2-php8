<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$data['Welder']['name'] . ' - ' . 
__('qualification') . ' ' .
$data['WelderCertificate']['third_part'] . '/' .
$data['WelderCertificate']['sector'] . '/' .
$data['WelderCertificate']['certificat'] . '/' .
ucfirst($data['WelderCertificate']['weldingmethod']) . '/' .
$data['WelderCertificate']['level']
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>

<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
} 
?>

<?php
$this->request->projectvars['VarsArray'][16] = $data[$models['main']]['id'];
$this->request->projectvars['VarsArray'][17] = $data[$models['sub']]['id'];
$url_upload = $this->Html->url(array_merge(array('action' => 'certificatefile'),$this->request->projectvars['VarsArray']));

$this->request->projectvars['VarsArray'][17] = 0;
$url_back = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'certificate'), $this->request->projectvars['VarsArray']));

$uploadurl = explode('/',$url_upload);
unset($uploadurl[0]);
unset($uploadurl[1]);

// local nicht auskommentieren
//unset($uploadurl[2]);

$uploadurl = implode('/',$uploadurl);
pr($uploadurl);
?>

<div class="uploadform">
<?php
echo $this->Form->create('Welder', array('id' => 'FileformModal','type' => 'file','class' => '','action' => $uploadurl));
echo $this->Form->input('ajax_true', array('type' => 'hidden', 'value' => 1));

echo $this->Form->input('file', array(
		'type' => 'file',
		'label' => false, 'div' => false,
		'class' => 'fileUploadModal',
		'multiple' => 'multiple'
));

echo $this->Form->button(__('Upload'), array('type' => 'submit', 'id' => 'px-submit'));
echo $this->Form->button(__('Clear'), array('type' => 'reset', 'id' => 'px-clear'));
echo $this->Form->input('upload_true', array('type' => 'hidden', 'value' => 1));
echo $this->Form->input('fileselect', array('type' => 'hidden', 'value' => 1));
echo $this->Form->end();
?>
</div>

<div class="
<?php 
if(isset($hint)) echo 'error';
else echo 'hint';
?>
"><p>
<?php if(isset($hint)) echo $hint;?> 
</p><p>
<?php // echo $this->Html->link(__('Back'),array_merge(array('controller' => 'welders','action' => 'certificate'), $this->request->projectvars['VarsArray']),array('class' => 'back_button round')); ?>
</p>
</div>

</div>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>

<script type="text/javascript">
$(document).ready(function(){
	
	$('.fileUploadModal').fileUploader({
		'limit': 1,
		'action': '<?php echo __('Select file', true); ?>',
		'selectFileLabel': '<?php echo __('Select file', true); ?>',
		'allowedExtension': 'pdf',
		'afterUpload': function(e){
							var data = $(".fakeform").serializeArray();
							data.push({name: "ajax_true", value: 1});
							$.ajax({
								type	: "POST",
								cache	: true,
								url		: "<?php echo $url_back;?>",
								data	: data,
								success: function(data) {
		    					$("#dialog").html(data);
		    					$("#dialog").show();
								}
							});
							return false;
						}						
		}
	);

});
</script>