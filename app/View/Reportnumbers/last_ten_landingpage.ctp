<h4><?php echo __('My last reports')?></h4>
<ul class="listemax" id="last_reports_menue">
	<?php

	foreach($reportnumbers as $_key => $_reportnumbers){

		if(!isset($_reportnumbers['reportnumber_id'])) continue;

		$disc  = $_reportnumbers['Testingmethod']['verfahren'] . ' (';
		$disc .= count($_reportnumbers['reportnumber_id']);
		$disc .= ') ';
		echo '<li>';
		echo $this->Html->Link(h($disc),'javascript:',array('class' => 'show_reports', 'rel' => 'testingmetod_'.$_key));
		echo '</li>';

	}

	?>
</ul>
<ul class="listemax" id="last_reports_icon_back">
<li>
	<?php
	echo '<span class="icon_back" title="'. __('Back') .'">';
	echo ' ' . __('Back',true);
	echo '</span>';
	?>
</li>
</ul>
<div class="users index inhalt">
<table id="last_reports_tables" class="advancetool" cellspacing="0" cellpadding="0">
<?php

foreach($reportnumbers as $_key => $_reportnumbers){

	if(!isset($_reportnumbers['reportnumber_id'])) continue;
//	if(!isset($_reportnumbers['Xml']->{$_reportnumbers['model']}->examination_object->discription)) continue;

	echo '<tbody id="testingmetod_'.$_key.'">';
	echo '<tr>';
	echo '<th></th>';
	echo '<th></th>';
	echo '<th>'. __('Project') . '</th>';
	echo '<th></th>';
	echo '<th>'. __('modified') . '</th>';
	echo '</tr>';

	foreach ($_reportnumbers['reportnumber_id'] as $_key => $_value) {

		echo '<tr class ="';
		if($_value['Reportnumber']['status'] == 1) echo 'closed';
		if($_value['Reportnumber']['status'] == 2) echo 'closed2';
		echo '">';

		echo '<td>';
		echo $this->Html->link(h($this->Pdf->ConstructReportName($_value)),array(
					'action' => 'edit',
					$_value['Reportnumber']['topproject_id'],
					$_value['Reportnumber']['cascade_id'],
					$_value['Reportnumber']['order_id'],
					$_value['Reportnumber']['report_id'],
					$_value['Reportnumber']['id']
				),
				array(
					'title' => __('Edit',true),
					'class' => 'ajax icon icon_edit'
				)
			);

		if($_value['Reportnumber']['status'] == 0){

			echo $this->Html->link(h($this->Pdf->ConstructReportName($_value)),array(
						'action' => 'pdf',
						$_value['Reportnumber']['topproject_id'],
						$_value['Reportnumber']['cascade_id'],
						$_value['Reportnumber']['order_id'],
						$_value['Reportnumber']['report_id'],
						$_value['Reportnumber']['id'],0,0,0,3
					),
					array(
						'title' => __('Print',true),
						'class' => 'icon icon_print showpdflink'
					)
				);
		}

		if($_value['Reportnumber']['status'] > 0){

			echo $this->Html->link(h($this->Pdf->ConstructReportName($_value)),array(
						'action' => 'pdf',
						$_value['Reportnumber']['topproject_id'],
						$_value['Reportnumber']['cascade_id'],
						$_value['Reportnumber']['order_id'],
						$_value['Reportnumber']['report_id'],
						$_value['Reportnumber']['id']
					),
					array(
						'title' => __('Print',true),
						'class' => 'icon icon_print showpdflink'
					)
				);
		}

		echo $this->ViewData->ShowStatus($_value);

		echo '</td>';

		echo '<td>' . h($this->Pdf->ConstructReportName($_value)) . '</td>';
		echo '<td>' . $_reportnumbers['Topproject'][$_value['Reportnumber']['id']]['projektname'] . '</td>';
		echo '<td>';
		if(isset($_reportnumbers['Report'][$_value['Reportnumber']['id']]['examination_object'])) echo $_reportnumbers['Report'][$_value['Reportnumber']['id']]['examination_object'];
		echo '</td>';
		echo '<td>' . $_value['Reportnumber']['modified'] . '</td>';
		echo '</tr>';

	}

	echo '</tbody>';

}
?>
</table>
</div>
<?php echo $this->element('navigation/js/last_ten_landingpage');?>
<?php echo $this->element('js/ajax_link');?>
<?php echo $this->element('js/show_pdf_link');?>
