<div class="modalarea">
<h2>
<?php
echo __($models['top']) . ' ' .
$_data[$models['top']]['name'] . ' - ' .
__('qualification') . ' ' .
$_data[$models['main']]['third_part'] . '/' .
$_data[$models['main']]['sector'] . '/' .
$_data[$models['main']]['certificat'] . '/' .
ucfirst($_data[$models['main']]['testingmethod']) . ' - ' .
__('documents',true)
;
?>
</h2>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'examiners', 'action' => 'certificatesfiles'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "application/pdf"));
echo $this->element('form_upload_report',array('writeprotection' => false));
echo $this->element('js/form_upload_report',array('container' => '#dialog','url' => $url,'FileLabel' => __('Drop files her to upload', true),'Extension' => 'pdf|PDF'));
?>
<div id="container_summary" class="container_summary" ></div>
<div id="">
<?php
//pr($_files);
if(count($_files) == 0){
	echo '<div class="hint"><p>';
	echo __('No documents avilable.',true);
	echo '</p></div>';
}
?>
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
				<?php
				foreach ($_files as $__files):
					if(isset($__files['Certificatefile']['error'])){
						echo '<tr>';
						echo '<td>' . $__files['Certificatefile']['error'] . '</td>';
						echo '<td>' . h($__files['Certificatefile']['originally_filename']) . '</td>';
						echo '<td>' . $__files['Certificatefile']['user_id'] . '</td>';
						echo '<td>' . $__files['Certificatefile']['created'] . '</td>';
						echo '</tr>';
						continue;
					}
				?>
				<tr>
					<td>
					<span class="for_hasmenu1 weldhead">
					<?php
						if(empty($__files[$models['file']]['description']))$description = __('No description');
						else $description = $__files[$models['file']]['description'];

						$this->request->projectvars['VarsArray'][17] = $__files[$models['file']]['id'];
						echo $this->Html->link($description,
						array_merge(array('action' => 'getcertificatefiles'),$this->request->projectvars['VarsArray']),
						array(
							'class'=>'round icon_file filelink hasmenu1',
							'target'=>'_blank',
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
					<?php echo h($__files[$models['file']]['originally_filename']);?>
					<?php echo '('.$__files[$models['file']]['file_size'].')';?>
                    </td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Examiner ID'); ?>:
					</span>
					<?php echo $__files[$models['file']]['user_id'];?>
                    </td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Created'); ?>:
					</span>
					<?php echo $__files[$models['file']]['created'];?>
                    </td>
				</tr>
				<?php endforeach; ?>
			</table>
			<span class="clear"></span>
		</div>
<script type="text/javascript">
$(function(){
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
							$("#dialog").load("examiners/certificatefilesdescription/" + ui.target.attr("rev"), {
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
							$("#dialog").load("examiners/delcertificatefiles/" + ui.target.attr("rev"), {
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

	});
</script>
<?php
$url = $this->Html->url(array_merge(array('action' => 'certificatesfiles'),$this->request->projectvars['VarsArray']));
echo $this->element('js/ajax_stop_loader');
echo $this->element('js/ajax_send_modal_form');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
