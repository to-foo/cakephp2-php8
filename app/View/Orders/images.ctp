<div class="modalarea">
<h2><?php echo __('Imageupload', true);?></h2>
	<div class="uploadform">
			<?php
			echo $this->Form->create('Order', array('type' => 'file','class' => ''));
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

		<div class="clear"></div>
		<div class="images">
			<ul>
				<?php
				if(count($reportimages) > 0){
				foreach($reportimages as $_reportimages){
					
						if(!isset($_reportimages['Orderimage']['imagedata'])) continue;

						echo '<li class="image">';
						echo '<span class="for_hasmenu1">';
						$this->request->projectvars['VarsArray'][3] = $_reportimages['Orderimage']['id']; 
						echo $this->Html->link('&nbsp;',
						array_merge(array('action' => 'image'),$this->request->projectvars['VarsArray']),
						array(
							'class' => 'fancybox',
							'rev' => implode('/',$this->request->projectvars['VarsArray']),
							'rel' => $_reportimages['Orderimage']['id'],
							'style' => 'background-image:url('.$_reportimages['Orderimage']['imagedata'].')',
//							'title' => $image_checkbox_title,
							'data-fancybox' => 'group',
							'data-caption' => $_reportimages['Orderimage']['discription'],
							'escape' => false
							)
						);

						echo '</span>';
						echo '</li>';
					}
				}
				?>
			</ul>
			<span class="clear"></span>
		</div>        
		<span class="clear"></span>
		</div>
	</div>
</div>
<div class="clear" id="testdiv"></div>
<?php
$url = $this->Html->url(array('controller' => 'orders', 'action' => 'images',
	 $orders['Order']['topproject_id'],
	 $orders['Order']['cascade_id'],
	 $orders['Order']['id'],
	)
);

?>

<?php
if(isset($saveOK) && $saveOK  == 1){
	echo $this->JqueryScripte->RefreshAfterDialog(0,0,$FormName);
	} 
?>
<?php echo $this->JqueryScripte->ModalFunctions(); ?>

<script type="text/javascript">

$(document).ready(function(){
	$(".fancybox").fancybox({
		type: "ajax",
	});
});

$(function(){
$('.fileUpload').fileUploader({
		'limit': 1,
		'action': '<?php echo __('Select image', true); ?>',
		'selectFileLabel': '<?php echo __('Select image', true); ?>',
		'allowedExtension': 'jpg|jpeg|png',

		'afterUpload': function(e){
							var data = $(".fakeform").serializeArray();
							data.push({name: "ajax_true", value: 1});
							$.ajax({
								type	: "POST",
								cache	: true,
								url		: '<?php echo $url; ?>',
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