<div class="modalarea">
<h2><?php echo __('Fileupload', true);?></h2>
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
		<div class="files">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<th><?php echo __('Filename', true); ?></th>
					<th><?php echo __('Uploaded from', true); ?></th>
					<th><?php echo __('Uploaded time', true); ?></th>
					<th>&nbsp;</th>
				</tr>
				<?php foreach ($orderfiles as $_orderfiles): ;?>
				<tr>
					<td>
					<?php
					if($_orderfiles['Orderfile']['file_exists'] == true){ 
						echo $this->Html->link($_orderfiles['Orderfile']['basename'],
							array("action" => 'getfile', 
								$this->request->projectvars['VarsArray'][0],
								$_orderfiles['Orderfile']['id']),
								array('class' => 
									'filelink round', 
									'title' => $_orderfiles['Orderfile']['basename']
								)
							);
					} else {
						echo $_orderfiles['Orderfile']['basename'] . ' (' . __('file not found',true) . ')';
					}
					?>
                    </td>
					<td><?php echo $_orderfiles['Orderfile']['user_id'];?></td>
					<td><?php echo $_orderfiles['Orderfile']['created'];?></td>
					<td class="actions">
					<?php 
					if($_orderfiles['Orderfile']['file_exists'] == true){
						echo $this->Html->link(__('Delete', true),
								array("action" => 'delfile',
									$orders['Order']['topproject_id'],
									$orders['Order']['cascade_id'],
									$orders['Order']['id'],
									$_orderfiles['Orderfile']['id']
									),
								array('class' => 'icon icon_delete filelinkdel mymodal delete_generally', 'title' => $_orderfiles['Orderfile']['basename']));
					}
					?></td>
				</tr>
				<?php endforeach; ?>
			</table>
			<span class="clear"></span>
		</div>
	</div>
</div>
<div class="clear" id="testdiv"></div>
<?php
$url = $this->Html->url(array('controller' => 'orders', 'action' => 'files',
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
$(function(){
$('.fileUpload').fileUploader({
		'limit': 1,
		'action': '<?php echo __('Select files', true); ?>',
		'selectFileLabel': '<?php echo __('Select files', true); ?>',
		'allowedExtension': '',

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