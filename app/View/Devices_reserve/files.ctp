<div class="modalarea">
<h2><?php echo __('Device') . ' ' . $_data['Device']['name'] . ' - ' . __('documents',true);?></h2>
<div id="message_wrapper"><?php echo $this->Session->flash();?></div>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'devices', 'action' => 'files'),$this->request->projectvars['VarsArray']));
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

<div class="clear"></div>
			<table cellpadding="0" cellspacing="0">
				<tr>
					<th><?php echo __('File description', true); ?></th>
					<th></th>
					<th><?php echo __('Originally filename', true); ?></th>
					<th><?php echo __('Uploaded from', true); ?></th>
					<th><?php echo __('Uploaded time', true); ?></th>
				</tr>
				<?php
				foreach ($_files as $__files):
					if(isset($__files['Devicefile']['error'])) continue;
				?>
				<?php echo '<tr id="tr_' . $__files['Devicefile']['id'] . '" class="">';?>

					<td class="value">
					<?php
					if(isset($__files['Devicefile']['error'])){
						echo $__files['Devicefile']['error'];
					} elseif(isset($__files['Devicefile']['realpath'])){
						if(empty($__files['Devicefile']['description'])) $description = __('No description');
						else $description = $__files['Devicefile']['description'];

						echo $description;

					}
					?>
					</td>
					<td>
					<?php

					if(isset($__files['Devicefile']['error'])){
						echo $__files['Devicefile']['error'];
					}
					elseif(isset($__files['Devicefile']['realpath'])){
						if(empty($__files['Devicefile']['description']))$description = __('No description');
						else $description = $__files['Devicefile']['description'];
						$this->request->projectvars['VarsArray'][17] = $__files['Devicefile']['id'];
						echo $this->Html->link($description,
						array_merge(array('controller' => 'devices','action' => 'getfiles'),$this->request->projectvars['VarsArray']),
						array(
							'class'=>'icon icon_download filelink hasmenu1',
							'title'=>__('Download file',true)
							)
						);
					}

					echo $this->Html->link(__('File description') . ' ' . __('edit'),array_merge(array('action' => 'devicefilesdescription'),$this->request->projectvars['VarsArray']),array('json-data' => $description,'title' => __('File description') . ' ' . __('edit'),'class' => 'json_edit promt icon icon_edit'));
					echo $this->Html->link(__('Delete file'),array_merge(array('action' => 'deldevicefiles'),$this->request->projectvars['VarsArray']),array('title' => __('Should this file be deleted') .': ' . $description,'class' => 'json_delete confirm icon icon_delete'));
					unset($this->request->projectvars['VarsArray'][17]);
					?>
					</td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Originally filename'); ?>:
					</span>
					<?php echo h($__files['Devicefile']['originally_filename']);?>
                    (<?php echo $__files['Devicefile']['file_size'];?>)
                    </td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Examiner ID'); ?>:
					</span>
					<?php echo $__files['Devicefile']['user_id'];?>
                    </td>
					<td>
        			<span class="discription_mobil">
					<?php echo __('Created'); ?>:
					</span>
					<?php echo $__files['Devicefile']['created'];?>
                    </td>
				</tr>
				<?php endforeach; ?>
			</table>
</div>
<?php
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/ajax_json_table_editlink');
echo $this->element('js/ajax_json_table_deletelink');
?>
