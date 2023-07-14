<div class="modalarea">
<h2><?php echo __('Examiner') . ' ' . $_data['Examiner']['name'] . ' - ' . __('documents',true);?></h2>
<?php echo $this->element('Flash/_messages');?>
<div class="clear"></div>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'examiners', 'action' => 'files'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "application/pdf"));
echo $this->element('form_upload_report',array('writeprotection' => false));
echo $this->element('js/form_upload_report',array('container' => '#dialog','url' => $url,'FileLabel' => __('Drop files her to upload', true),'Extension' => 'pdf|PDF'));

if(count($_files) == 0){
	echo '<div class="hint"><p>';
	echo __('No documents avilable.',true);
	echo '</p></div>';
}
?>

		<div class="files">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<th><?php echo __('File description', true); ?></th>
					<th><?php echo __('Originally filename', true); ?></th>
					<th><?php echo __('Uploaded time', true); ?></th>
				</tr>
				<?php
				foreach ($_files as $__files):
					if(isset($__files['Examinerfile']['error'])) continue;
				?>
				<tr>
					<td>
					<span class="for_hasmenu1 weldhead">
					<?php
						if(isset($__files['Examinerfile']['error'])){
							echo $__files['Examinerfile']['error'];
						}
						elseif(isset($__files['Examinerfile']['realpath'])){
							if(empty($__files['Examinerfile']['description']))$description = __('No description');
							else $description = $__files['Examinerfile']['description'];

							$this->request->projectvars['VarsArray'][17] = $__files['Examinerfile']['id'];
							echo $this->Html->link($description,
							array_merge(array('controller' => 'examiners','action' => 'getfiles'),$this->request->projectvars['VarsArray']),
							array(
								'class'=>'round icon_file filelink hasmenu1',
								'rev' => implode('/',$this->request->projectvars['VarsArray']),
								)
							);
						}

					unset($this->request->projectvars['VarsArray'][17]);
					?>
			        </span>
					</td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Originally filename'); ?>:
					</span>
					<?php echo h($__files['Examinerfile']['originally_filename']);?>
                    (<?php echo $__files['Examinerfile']['file_size'];?>)
                    </td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Created'); ?>:
					</span>
					<?php echo $__files['Examinerfile']['created'];?>
                    </td>
				</tr>
				<?php endforeach; ?>
			</table>
			<span class="clear"></span>
		</div>
</div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/file_context_menue');
//echo $this->element('js/form_upload',array('url' => $url,'FileLabel' => __('Select files', true),'Extension' => 'pdf'));
?>
