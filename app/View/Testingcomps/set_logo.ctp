<?php echo $this->Html->script('jquery.fileUploader', array('inline'=>false)); ?>
<div class="modalarea">
	<h2><?php echo __('Fileupload', true);?></h2>
		<?php
		echo $this->Form->create('Order', array('type' => 'file'));
		echo $this->Form->input('ajax_true', array('type' => 'hidden', 'value' => 1));

		echo $this->Form->input('file', array(
			'type' => 'file',
			'label' => false, 'div' => false,
			'class' => 'fileUpload'
		));
			
		//echo $this->Form->button(__('Upload'), array('type' => 'submit', 'id' => 'px-submit', 'disabled'=>'disabled'));
		//echo $this->Form->button(__('Clear'), array('type' => 'reset', 'id' => 'px-clear', 'disabled'=>'disabled'));
		echo $this->Form->input('upload_true', array('type' => 'hidden', 'value' => 1));
		echo $this->Form->input('fileselect', array('type' => 'hidden', 'value' => 1));
		echo $this->Form->end();
		?>
		
	<div class="clear"></div>
</div>
<?php
echo $this->Html->scriptBlock('
$(document).ready(function(){
	$(".fileUpload").fileUploader({
		limit: 1,
		selectFileLabel: "'.__('Select files', true).'",
		allowedExtension: "jpg|jpeg|gif|png",
		autoUpload: true,
		afterUpload: function(e){
			$(".fileupload").fileUploader("destroy");
			$("#dialog").load("'.$this->Html->url(array_intersect_key($SettingsArray['backlink'], array_flip(array('controller', 'action', 0)))).'", {ajax_true: 1});
			$("#dialog").dialog();
		},
	});
});
', array('inline'=>false));
echo $this->JqueryScripte->ModalFunctions();
