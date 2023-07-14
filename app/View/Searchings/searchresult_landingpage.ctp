<div class="reportnumbers index inhalt">
<div class="quicksearch">
<?php
echo $this->Html->link(__('Search',true),
	array(
		'controller' => 'searchings',
		'action' => 'search'
	),
	array(
		'class' => 'icon addsearching',
		'title' => __('Search',true)
	)
);
?>
</div>
<?php
if(count($reportnumbers) == 0){
echo '<div>';
echo __('There are no reports from your testing company.', true);
echo '</div>';
}
?>
<table cellpadding="0" cellspacing="0" class="advancetool">
	<tr>
			<th>&nbsp;</th>
			<th><?php echo __('Report number',true); ?></th>
			<th></th>
			<th></th>
			<th><?php echo __('modified',true); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($reportnumbers as $reportnumber):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' altrow';
		}
	?>
	<tr class="<?php echo $class;?>">

		<td>
		<?php
		echo $this->Html->link(h($this->Pdf->ConstructReportName($reportnumber)),array(
					'controller' => 'reportnumbers',
					'action' => 'edit',
					$reportnumber['Reportnumber']['topproject_id'],
					$reportnumber['Reportnumber']['cascade_id'],
					$reportnumber['Reportnumber']['order_id'],
					$reportnumber['Reportnumber']['report_id'],
					$reportnumber['Reportnumber']['id']
				),
				array(
					'title' => __('Edit',true),
					'class' => 'ajax icon icon_edit testingreportlink'
				)
			);

		if($reportnumber['Reportnumber']['status'] == 0){

			echo $this->Html->link(h($this->Pdf->ConstructReportName($reportnumber)),array(
						'controller' => 'reportnumbers',
						'action' => 'pdf',
						$reportnumber['Reportnumber']['topproject_id'],
						$reportnumber['Reportnumber']['cascade_id'],
						$reportnumber['Reportnumber']['order_id'],
						$reportnumber['Reportnumber']['report_id'],
						$reportnumber['Reportnumber']['id'],0,0,0,3
					),
					array(
						'title' => __('Print',true),
						'class' => 'icon icon_print showpdflink'
					)
				);
		}

		if($reportnumber['Reportnumber']['status'] > 0){

			echo $this->Html->link(h($this->Pdf->ConstructReportName($reportnumber)),array(
						'controller' => 'reportnumbers',
						'action' => 'pdf',
						$reportnumber['Reportnumber']['topproject_id'],
						$reportnumber['Reportnumber']['cascade_id'],
						$reportnumber['Reportnumber']['order_id'],
						$reportnumber['Reportnumber']['report_id'],
						$reportnumber['Reportnumber']['id']
					),
					array(
						'title' => __('Print',true),
						'class' => 'icon icon_print showpdflink'
					)
				);
		}

		echo $this->ViewData->ShowStatus($reportnumber);
		?>

		</td>
		<td>
		<?php echo h($this->Pdf->ConstructReportName($reportnumber,2));?>
		</td>
		<td><?php echo $reportnumber['Testingmethod']['name'];?></td>
		<td><?php echo $reportnumber['Topproject']['projektname'];?></td>
		<td><?php echo $reportnumber['Reportnumber']['modified'];?></td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging">
	<?php
		echo $this->Paginator->first(__('first', true), array('class' => 'first disabled'));
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
		echo $this->Paginator->last(__('last', true), array('class' => 'last disabled'));
	?>
	</div>
	<p class="paging_query">
	<?php
	echo $this->Paginator->counter(array(
	'format' => __('Page {:page} of {:pages}, showing {:current} records out of {:count} total, starting on record {:start}, ending on {:end}')
	));
	?>
	</p>
</div>
<?php
echo $this->element('js/ajax_link_global',array('name' => '.testingreportlink'));
echo $this->element('landingpage/js/ajax_link_lage_window',array('name' => 'a.addsearching'));
echo $this->element('js/show_pdf_link');
echo $this->element('js/ajax_paging_searchresult_landingpage');
?>
