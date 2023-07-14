<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$eyecheck_data['Welder']['name'] . ' ' . 
__('eye check') ;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>

<?php
if(isset($FormName) && count($FormName) > 0){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	echo $this->JqueryScripte->DialogClose();
	echo $this->JqueryScripte->ModalFunctions();
	echo '</div>';
	return;
} 
?>
<div class="hint"><p>
<?php echo $this->Html->link(__('Change eye check file',true), array_merge(array('action' => 'eyecheckfile'), $this->request->projectvars['VarsArray']), array('title' => __('Change eye check file',true),'class' => 'mymodal round'));?>
</p></div>
<?php echo $this->Form->create('Welder', array('class' => 'dialogform')); ?>
<fieldset>
<?php echo $this->ViewData->EditOrderData($this->request->data,$settings,$locale,'WelderEyecheck');?>
</fieldset>
<?php // echo $this->Form->button(__('Back'),array('type' => 'button','class' => 'back_button')); ?>
<?php echo $this->Form->end(__('Submit', true));?>
<?php
$this->request->projectvars['VarsArray'][15] = $eyecheck_data['Welder']['id'];
$this->request->projectvars['VarsArray'][16] = $eyecheck_data['WelderEyecheck']['id'];
$this->request->projectvars['VarsArray'][17] = $eyecheck_data['WelderEyecheckData']['id'];
?>
<div class="uploadform">
<?php

$uploadurl = $this->Html->url(array_merge(array('action' => 'eyecheckfile'),$this->request->projectvars['VarsArray']));
$url = $this->Html->url(array_merge(array('controller' => 'welders','action' => 'editeyecheck'), $this->request->projectvars['VarsArray']));

$uploadurl = explode('/',$uploadurl);
unset($uploadurl[0]);
unset($uploadurl[1]);
unset($uploadurl[2]);
$uploadurl = implode('/',$uploadurl);
?>
</div>
</div>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>

<script type="text/javascript">
$(document).ready(function(){
	
	$('.fileUpload').fileUploader({
		'limit': 1,
		'action': '<?php echo __('Select vision test file', true); ?>',
		'selectFileLabel': '<?php echo __('Select vision test file', true); ?>',
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
								url		: "<?php echo $url;?>",
								data	: data,
								success: function(data) {
		    					$("#certification").html(data);
		    					$("#certification").show();
								}
							});
							return false;
						}						
		}
	);

	$("div.checkbox input").button();
	$("div.radio:not(.ui-buttonset)").buttonset();

	$("a.del_certificate").click(function() {
		var url = $(this).attr('href');
		$("#certification").load(url, {
			"ajax_true": 1,
		})
		return false;
	});	

	$("button.back_button").click(function() {
		var url = $(this).closest('form').attr("action");
		$("#certification").load(url, {
			"ajax_true": 1,
			"back": 1,
		})
		return false;
	});	
	
	$("form.dialogsubform").bind("submit", function() {

		var data = $(this).serializeArray();
		data.push({name: "ajax_true", value: 1});
		data.push({name: "back", value: 1});

		$.ajax({
			type	: "POST",
			cache	: false,
			url		: $(this).attr("action"),
			data	: data,
			success: function(data) {
		    	$("#certification").html(data);
		    	$("#certification").show();
			}
		});
		return false;
	});	
});
</script>