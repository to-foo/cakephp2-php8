<div class="modalarea">
<h2><?php echo __('Fileupload', true);?></h2>

<?php
if(Configure::check('OrderFileUploadAcceptedFiles') === true) echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => Configure::read('OrderFileUploadAcceptedFiles')));
else echo $this->Form->input('ThisAcceptedFiles',array('type' => 'hidden','value' => "application/pdf"));

echo $this->Form->input('ThisUploadUrl',array('type' => 'hidden','value' => $this->request->here));
echo $this->Form->input('ThisMaxFileSize',array('type' => 'hidden','value' => (int)(ini_get('upload_max_filesize'))));
echo $this->element('form_upload_report',array('writeprotection' => false));
echo $this->element('js/form_upload_report',array('container' => '#dialog','url' => $this->request->here,'FileLabel' => __('Select files', true),'Extension' => ''));
?>

	<div class="uploadform">

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
						echo $this->Html->link(__('Delete', true),
								array("action" => 'delfile',
									$orders['Order']['topproject_id'],
									$orders['Order']['cascade_id'],
									$orders['Order']['id'],
									$_orderfiles['Orderfile']['id']
									),
								array('class' => 'icon icon_delete filelinkdel mymodal delete_generally', 'title' => $_orderfiles['Orderfile']['basename']));
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
<?php
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
echo $this->element('js/ajax_mymodal_link');
?>
