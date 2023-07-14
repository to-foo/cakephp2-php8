<div class="modalarea">
<h2><?php echo __('Create order'); ?></h2>
<div class="clear uploadform">
<?php

echo $this->Form->create('Order', array('type' => 'file'));
echo $this->Form->input('ajax_true', array('type' => 'hidden', 'value' => 1));
echo $this->Form->input('file', array(
'type' => 'file',
'label' => false, 'div' => false,
'class' => 'fileUpload',
'multiple' => 'multiple'
));	
echo $this->Form->button('Upload', array('type' => 'submit', 'id' => 'px-submit', 'class' => 'upload'));
echo $this->Form->button('Clear', array('type' => 'reset', 'id' => 'px-clear', 'class' => 'upload'));


//echo $this->Form->input('ajax_true', array('type' => 'hidden', 'value' => 1));
echo $this->Form->input('upload_true', array('type' => 'hidden', 'value' => 1));
echo $this->Form->input('fileselect', array('type' => 'hidden', 'value' => 1));

echo $this->Form->end();
?>
</div>
<div class="clear edit">
</div>		
<?php // echo $CvsErrors;?>
</div>
<div class="clear" id="testdiv"></div>
<?php // echo $this->JqueryScripte->DialogClose(); ?>
<script type="text/javascript">
function escapeHtml(text) {
  var map = {
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;'
  };

  return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

$(function(){
	$('.fileUpload').fileUploader({
		limit: 1,
		allowedExtension: 'pdf',
		afterEachUpload: function(data) {
			//$('#dialog #testdiv').html('<pre>'+ escapeHtml(data) + '</pre>');
			data = JSON.parse(data.text());
			if(data.files[0].redirect) {
				$('#dialog').load(data.files[0].redirect.url, data.files[0].redirect.data);
			}
		},
	});
	
	$("a#closethismodal").click(function() {
		$("#dialog").dialog("close");
		return false;	
	});	
});
</script>
<?php // echo $this->JqueryScripte->ModalFunctions(); ?>
