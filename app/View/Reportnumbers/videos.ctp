<div class="reportnumbers detail">
	<div class="clear edit">
		<?php echo $this->element('navigation/report_menue',array('ReportMenue' => $ReportMenue,'data' => $reportnumber,'settings' => $settings));?>
	</div>
	<div class="uploadform">
		<h3><?php echo __('Videoupload', true);?></h3>
			<?php
			echo $this->Form->create('Order', array('type' => 'file'));
			echo $this->Form->input('ajax_true', array('type' => 'hidden', 'value' => 1));

			echo $this->Form->input('file', array(
				'type' => 'file',
				'label' => false, 'div' => false,
				'class' => 'fileUpload',
				'multiple' => 'multiple',
				'capture' => 'capture',
				'accept' => 'video/*'
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
				<?php foreach ($reportfiles as $_reportfiles): ?>
				<tr>
					<td><?php echo $this->Html->link($_reportfiles['Reportvideo']['name'],
								array("action" => 'video',
										 $reportnumber['Reportnumber']['topproject_id'],
										 $reportnumber['Reportnumber']['equipment_type_id'],
										 $reportnumber['Reportnumber']['equipment_id'],
										 $reportnumber['Reportnumber']['order_id'],
										 $reportnumber['Reportnumber']['report_id'],
										 $reportnumber['Reportnumber']['id'],
										 $_reportfiles['Reportvideo']['id']
									),
								array('class' => 'modal round', 'title' => $_reportfiles['Reportvideo']['name']));?></td>
					<td><?php echo $_reportfiles['Reportvideo']['user_id'];?></td>
					<td><?php echo $_reportfiles['Reportvideo']['created'];?></td>
					<td class="actions"></td>
				</tr>
				<?php endforeach; ?>
			</table>
		</div>
	</div>
</div>
<div class="clear" id="testdiv"></div>
<?php
$url = $this->Html->url(array('controller' => 'reportnumbers', 'action' => 'videos',
	 $reportnumber['Reportnumber']['topproject_id'],
	 $reportnumber['Reportnumber']['equipment_type_id'],
	 $reportnumber['Reportnumber']['equipment_id'],
	 $reportnumber['Reportnumber']['order_id'],
	 $reportnumber['Reportnumber']['report_id'],
	 $reportnumber['Reportnumber']['id']
	)
);
?>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
<?php
		if($reportnumber['Reportnumber']['status'] > 0){
			echo '
 						<script>
							$(function() {
								$("input, select, textarea, button").attr("disabled", "disabled");
							});
						</script>
						';
		}
?>

<script type="text/javascript">
$(function(){
$('.fileUpload').fileUploader({
		'limit': 1,
		'selectFileLabel': '<?php echo __('Select files', true); ?>',
		'allowedExtension': 'mp4|png|jpg',

		'afterUpload': function(e){
							var data = $(".fakeform").serializeArray();
							data.push({name: "ajax_true", value: 1});
							$.ajax({
								type	: "POST",
								cache	: true,
								url		: '<?php echo $url; ?>',
								data	: data,
								success: function(data) {
		    					$("#container").html(data);
		    					$("#container").show();
								}
							});
							return false;
						}

		}
	);
	$("div.images ul li").css("height","200px");
	$("div.images ul li").css("overflow","hidden");
});
</script>
