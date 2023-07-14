<div class="modalarea welders index inhalt">
<h2>
<?php
echo  __('Company licence',true);
?>
</h2>
<?php echo $this->element('Flash/_messages');?>
<?php
$url = $this->Html->url(array_merge(array('controller' => 'welders', 'action' => 'filesweldingcomp'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "application/pdf"));

echo $this->element('form_upload_report',array('writeprotection' => false));
echo $this->element('js/form_upload_report_single',array('container' => '#dialog','url' => $this->request->here,'FileLabel' => __('Drop files her to upload', true),'Extension' => 'pdf|PDF'));

?>

<?php
if(count($_files) == 0){
	echo '<div class="hint"><p>';
	echo __('No documents avilable.',true);
	echo '</p></div>';
}
?>

<div class="files">
<table cellpadding="0" cellspacing="0">
<tr>
<th></th>
<th><?php echo __('File description', true); ?></th>
<th><?php echo __('Created', true); ?></th>
</tr>
<?php
foreach ($_files as $__files):
if(isset($__files['Weldingcompfile']['error'])) continue;
?>
<tr>
<td>
<?php
if(isset($__files['Weldingcompfile']['realpath'])){

	if(empty($__files['Weldingcompfile']['description']))$description = __('No description');
	else $description = $__files['Weldingcompfile']['description'];

	$VarsArray = $this->request->projectvars['VarsArray'];
	$VarsArray[15] = $__files['Weldingcompfile']['id'];

	echo $this->Html->link($description,
		array_merge(
			array(
				'controller' => 'welders',
				'action' => 'getweldercompfiles'
				),
			$VarsArray
		),
		array(
		'class'=>'icon icon_download filelink',
		)
	);

	echo $this->Html->link(__('edit'),
		array_merge(
			array(
				'controller' => 'welders',
				'action' => 'weldingcompfilesdescription'
			),
			$VarsArray
		),
		array(
			'class'=>'mymodal icon icon_edit',
		)
	);
	
	echo $this->Html->link(__('edit'),
		array_merge(
			array(
				'controller' => 'welders',
				'action' => 'deleteweldingcompfile'
			),
			$VarsArray
		),
		array(
		'class'=>'mymodal icon icon_delete',
		)
	);
		
}

unset($VarsArray);

?>
</td>
<td>
<span class="discription_mobil">
<?php echo __('Description'); ?>:
</span>
<?php echo $description;?>
</td>
<td>
<span class="discription_mobil">
<?php echo __('Created'); ?>:
</span>
<?php echo h($__files['Weldingcompfile']['created']);?>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
</div>
<?php
echo $this->element('js/form_datefield');
echo $this->element('js/ajax_mymodal_link');
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/scroll_modal_top');
?>