<div class="modalarea">
<h2>
<?php  
echo __('Welder') . ' ' . 
$certificate_data['Welder']['name'] . ' ' . 
__('add qualification')
;
?>
</h2>
<div id="message_wrapper"><?php echo $this->Session->flash(); ?></div>
<div class="modalarea">
	<h2><?php echo __('Wählen Sie die entsprechenden Prüfberichte aus!'); ?></h2>	
<div class="clear"></div>
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo __('Number VT'); ?></th>
                        <th><?php echo __('Number RT'); ?></th>

			<th><?php echo __('modified'); ?></th>
	</tr>
<?php
	$i = 0;
	foreach ($reportlist as $modelData):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
                $this->request->projectvars['VarsArray'] [0] = $modelData['VT']['Reportnumber'] ['id'];
	?>
	<tr<?php echo $class;?>>
                    
		<td>		<?php echo $this->Html->link($modelData['VT']['Reportnumber']['number'].'-'.$modelData['VT']['Reportnumber']['year'], 
			array_merge(array('action' => 'qualificationnew'), 
			$this->request->projectvars['VarsArray']), 
			array(
				'class'=>'mymodal round icon_show ajax hasmenu1',
				'rev' => implode('/',$this->request->projectvars['VarsArray'])
			)
		); ?><?php 
                //echo $this->Html->link('Test', array_merge(array('action' => 'qualificationnew'), $this->request->projectvars['VarsArray']), array('class' => 'mymodal icon round','title' => __('Test',true)));
?>&nbsp;</td>
                
                <td><?php echo h($modelData['RT']['Reportnumber']['number'].'-'.$modelData['RT']['Reportnumber']['year']); ?>&nbsp;</td>
		
		<td><?php echo h($modelData['VT']['ReportVtstGenerally']['modified']); ?>&nbsp;</td>
	</tr>
<?php endforeach; ?>
	</table>

</div>
<div class="clear"></div>
<script type="text/javascript">
$(document).ready(function(){

	$("#WelderCertificateWelderCertificateDataActive1").click(function() {
		$("#WelderCertificateCertificat").val(null);
		$("#WelderCertificateFirstCertification").val(null);
		$("#WelderCertificateRenewalInYear").val(null);
		$("#WelderCertificateRecertificationInYear").val(null);
		$("#WelderCertificateHorizon").val(null);
	});	
	$("#WelderCertificateWelderCertificateDataActive0").click(function() {
		$("#WelderCertificateCertificat").val("-");
		$("#WelderCertificateFirstCertification").val(0);
		$("#WelderCertificateRenewalInYear").val(0);
		$("#WelderCertificateRecertificationInYear").val(0);
		$("#WelderCertificateHorizon").val(0);
	});	
});
</script>
</div>
<div class="clear"></div>
<?php echo $this->JqueryScripte->ModalFunctions();
       $form = '#WelderCertificateQualificationnewForm';
       echo $this->JqueryScripte->SessionFormData($form); 
?> 
