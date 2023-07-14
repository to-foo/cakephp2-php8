<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$data['Welder']['name'] . ' - ' . 
$data['WelderEyecheck']['certificat'] 
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>
<div class="
<?php 
if(isset($hint)) echo 'hint';
?>
"><p>
<?php if(isset($hint)) echo $hint;?> 
</p>
</div>

<?php
$this->request->projectvars['VarsArray'][16] = $data[$models['main']]['id'];
$this->request->projectvars['VarsArray'][17] = $data[$models['sub']]['id'];
$url_upload = $this->Html->url(array_merge(array('action' => 'eyecheckfile'),$this->request->projectvars['VarsArray']));

$this->request->projectvars['VarsArray'][17] = 0;
$url_back = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'eyechecks'), $this->request->projectvars['VarsArray']));

$uploadurl = explode('/',$url_upload);
unset($uploadurl[0]);
unset($uploadurl[1]);

// local nicht auskommentieren
unset($uploadurl[2]);

$uploadurl = implode('/',$uploadurl);
?>

<div class="uploadform">
<?php
echo $this->Form->create('Welder', array('id' => 'Fileforme','type' => 'file','class' => '','action' => $uploadurl));
echo $this->Form->input('ajax_true', array('type' => 'hidden', 'value' => 1));

echo $this->Form->input('file', array(
		'type' => 'file',
		'label' => false, 'div' => false,
		'class' => 'fileUpload',
		'multiple' => 'multiple'
));

echo $this->Form->button(__('Upload'), array('type' => 'submit', 'id' => 'px-submit'));
echo $this->Form->button(__('Clear'), array('type' => 'reset', 'id' => 'px-clear'));
echo $this->Form->input('upload_true', array('type' => 'hidden', 'value' => 1));
echo $this->Form->input('fileselect', array('type' => 'hidden', 'value' => 1));
echo $this->Form->end();
?>
</div>
</div>

<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
} 
?>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>

<script type="text/javascript">
$(document).ready(function(){
	
	$('.fileUpload').fileUploader({
		'limit': 1,
		'action': '<?php echo __('Select file', true); ?>',
		'selectFileLabel': '<?php echo __('Select file', true); ?>',
		'allowedExtension': 'pdf',
		'onFileChange': function(e){
							$("#dialog").scrollTop(3000);
						},						
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

	$("div.checkbox input").button();
	$("div.radio:not(.ui-buttonset)").buttonset();

	$("a.back_button").click(function() {
		$("#dialog").load($(this).attr('href'), {
			"ajax_true": 1,
			"back": 1,
		})
		return false;
	});	
});
</script>
