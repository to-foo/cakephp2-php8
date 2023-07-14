<div class="modalarea">
<h2>
<?php
echo __('Document') . ' ' .
$_data['Document']['name'] . ' - ' . __('documents',true)
;
?>
</h2>
<div id="">
<?php
if(count($_files) == 0){
	echo '<div class="hint"><p>';
	echo __('No documents avilable.',true);
	echo '</p></div>';
}
?>
	<div class="uploadform">
		<legend><?php echo __('Fileupload', true);?></legend>
			<?php
			echo $this->Form->create('Document', array('type' => 'file'));
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
	</div>
<?php echo $this->element('Flash/_messages');?>
</div>
</div>
<div class="clear"></div>
		<div class="files">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<th><?php echo __('File description', true); ?></th>
					<th><?php echo __('Originally filename', true); ?></th>
					<th><?php echo __('Uploaded from', true); ?></th>
					<th><?php echo __('Uploaded time', true); ?></th>
				</tr>
				<?php foreach ($_files as $__files):?>
				<tr>
					<td>
					<span class="for_hasmenu1 weldhead">
					<?php
						if(empty($__files['Documentfile']['description']))$description = __('No description');
						else $description = $__files['Documentfile']['description'];

						$this->request->projectvars['VarsArray'][17] = $__files['Documentfile']['id'];
						echo $this->Html->link($description,
						array_merge(array('action' => 'getfiles'),$this->request->projectvars['VarsArray']),
						array(
							'class'=>'round icon_file filelink hasmenu1',
							'rev' => implode('/',$this->request->projectvars['VarsArray']),
						)
					);

					unset($this->request->projectvars['VarsArray'][17]);
					?>
			        </span>
					</td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Originally filename'); ?>:
					</span>
					<?php echo h($__files['Documentfile']['originally_filename']);?>
                    (<?php echo $__files['Documentfile']['file_size'];?>)
                    </td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Examiner ID'); ?>:
					</span>
					<?php echo $__files['Documentfile']['user_id'];?>
                    </td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Created'); ?>:
					</span>
					<?php echo $__files['Documentfile']['created'];?>
                    </td>
				</tr>
				<?php endforeach; ?>
			</table>
			<span class="clear"></span>
		</div>
<?php
//$url = $this->Html->url(array_merge(array('controller' => 'documents', 'action' => 'documentfilesdescription'),$this->request->projectvars['VarsArray']));
$url = $this->Html->url(array_merge(array('controller' => 'documents', 'action' => 'files'),$this->request->projectvars['VarsArray']));
?>
<script type="text/javascript">
$(function(){

		$(".closemodal").click(function(evt) {
			$("#dialog").dialog("close");

			evt.stopPropagation();
			evt.stopImmediatePropagation();
			evt.preventDefault();

			return false;
		});

		$("a.mymodal").click(function() {

			if($(this).attr("id") == "closethismodal"){return false;}

			$("#dialog").load($(this).attr("href"), {
				"ajax_true": 1,
			})
			return false;
		});

		$('.fileUpload').fileUploader({
		'limit': 1,
		'selectFileLabel': '<?php echo __('Select files', true); ?>',
		'allowedExtension': '',
		'afterUpload': function(e){
							var data = $(".fakeform").serializeArray();
							data.push({name: "ajax_true", value: 1});
							data.push({name: "edit_description", value: 1});
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

		$("span.for_hasmenu1").contextmenu({
			delegate: ".hasmenu1",
			autoFocus: true,
			preventContextMenuForPopup: true,
			preventSelect: true,
			taphold: true,
			menu: [
				{
				title: "<?php echo __('Edit file description');?>",
				cmd: "status",
				action :	function(event, ui) {
							$("#dialog").load("documents/documentfilesdescription/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_edit",
				disabled: false
				},
				{
					title: "----"
				},
				{
				title: "<?php echo __('Delete file');?>",
				cmd: "status",
				action :	function(event, ui) {
							$("#dialog").load("documents/deldocumentfiles/" + ui.target.attr("rev"), {
									"ajax_true": 1
								});
							$("#dialog").dialog("open");
							},
				uiIcon: "qm_delete",
				disabled: false
				}
				],

			select: function(event, ui) {},
		});

	$("a.mymodal").click(function() {
		$("#dialog").load($(this).attr("href"), {
			"ajax_true": 1,
		})
		return false;
	});

	});
</script>

<?php //echo $this->JqueryScripte->ModalFunctions(); ?>
