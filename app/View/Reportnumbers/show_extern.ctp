<div class="quicksearch">
<?php //echo $this->Navigation->quickSearching('quicksearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php echo $this->element('subdevisions_form');?>
</div>
<div class="pagin_links">
<?php
if(!empty($reportBefore)){
	$reportBefore = reset($reportBefore);
	$desc_before = __('Previous report') . ' (' . $reportBefore['Report']['name'] . ')';
        $_VarsArray = $VarsArray;
        $_VarsArray[4] = $reportBefore['Report']['id'];
	echo $this->Html->Link($desc_before,array_merge(array('action' => 'show'), $_VarsArray),array('class' => 'icon icon_prev ajax','title' => $desc_before));
}
if(!empty($reportNext)){
	$reportNext = reset($reportNext);
	$desc_next = __('Next report') . ' (' . $reportNext['Report']['name'] . ')';
         $_VarsArray = $VarsArray;
        $_VarsArray[4] = $reportNext['Report']['id'];
	echo $this->Html->Link($desc_next,array_merge(array('action' => 'show'), $_VarsArray),array('class' => 'icon icon_next ajax','title' => $desc_next));
}
?>
</div>
<div class="clear"></div>
<div class="reportnumbers index inhalt">
	<h2><?php echo __('Reports').' '.$reportName; ?></h2>
	<?php if(isset($writeprotection) && $writeprotection) echo $this->Html->tag('p', __('Report is writeprotected - changes will not be saved.'), array('class'=>'error')); ?>

    <?php
	if(count($reportnumbers) == 0){
		echo '<div class="error">';
		echo __('There are no reports from your testing company.', true);
		echo '</div>';
	}
	?>

	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('number'); ?></th>
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
			<?php
				foreach($items as $item) {
					echo '<th>';
					if(isset($item->sortable) && $item->sortable == 1) echo $this->Paginator->sort(trim($item->key), isset($item->description) && trim($item->description) != '' ? __(ucfirst(trim($item->description))) : __(ucfirst(trim($item->key))));
					else echo h(isset($item->description) && trim($item->description) != '' ? __(ucfirst(trim($item->description))) : __(ucfirst(trim($item->key))));
					echo '</th>';
				}
			?>
			<th class="actions">&nbsp;</th>
	</tr>
	<?php
	$i = 0;
	foreach ($reportnumbers as $reportnumber):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' altrow';
		}
		if($reportnumber['Reportnumber']['status'] == 1){
			$class = ' closed';
		}
		if($reportnumber['Reportnumber']['status'] == 2){
			$class = ' closed2';
		}
		if($reportnumber['Reportnumber']['status'] == 3){
			$class = ' settled';
		}
		if($reportnumber['Reportnumber']['delete'] == 1){
			$class = ' delete';
		}
	?>
	<tr class="<?php echo $class;?>">
		<td><span class="for_hasmenu">
		<?php
		$this->request->projectvars['VarsArray'][5] = $reportnumber['Reportnumber']['id'];
		$separatpr = Configure::read('Separator');

		if($reportnumber['Reportnumber']['delete'] != 1){

/* 06.09.2017 Florian Zumpe --- Anzeige von NE-Berichten f�r Fremdfirmen als Reparaturlink ohne Kontextmen� */

		$class = 'round ajax hasmenu';
		$action = 'view';

		echo $this->Html->link(h($this->Pdf->ConstructReportName($reportnumber, 2)),array(
					'action' => $action,
					$reportnumber['Reportnumber']['topproject_id'],
					$reportnumber['Reportnumber']['cascade_id'],
					$reportnumber['Reportnumber']['order_id'],
					$reportnumber['Reportnumber']['report_id'],
//					$reportnumber['Reportnumber']['testingmethod_id'],
					$reportnumber['Reportnumber']['id']
				),
				array(
					'class' => $class,
					'rev' =>
						$reportnumber['Reportnumber']['topproject_id'] . '/' .
						$reportnumber['Reportnumber']['cascade_id'] . '/' .
						$reportnumber['Reportnumber']['order_id'] . '/' .
						$reportnumber['Reportnumber']['report_id'] . '/' .
//						$reportnumber['Reportnumber']['testingmethod_id'] . '/' .
						$reportnumber['Reportnumber']['id']
				)
			);
		}

		?>
        </span></td>
        <td>
        <?php
		echo $this->ViewData->ShowInfo($reportnumber);
		?>
        </td>
		<td>
		</td>
		<td>
		<?php echo '<span class="discription_mobil">';?>
        <?php echo __('Status');?>:
		<?php echo '</span>';?>
		<?php echo $this->ViewData->ShowStatus($reportnumber); ?>
        </td>
		<?php
			foreach($items as $item) {
				echo '<td>';
				echo '<span class="discription_mobil">';
				echo __(isset($item->description) && trim($item->description) != '' ? __(ucfirst(trim($item->description))) : __(ucfirst(trim($item->key)))).':';
				echo '</span>';
				if(isset($reportnumber[trim($item->model)][trim($item->key)])) {
					echo $reportnumber[trim($item->model)][trim($item->key)];
				}
				echo '</td>';
			}

		?>
		<td class="actions">
		<?php
		?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
	<div class="paging">
	<?php
		echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
		echo $this->Paginator->numbers(array('separator' => ''));
		echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled'));
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
<div class="clear" id="testdiv"></div>
<script>
$(document).ready(function(){

		var modalheight = Math.ceil(($(window).height() * 90) / 100);
		var modalwidth = Math.ceil(($(window).width() * 90) / 100);

		var dialogOpts = {
			modal: false,
			width: modalwidth,
			height: modalheight,
			autoOpen: false,
			draggable: true,
			resizeable: true
			};

		$("#dialog").dialog(dialogOpts);

		$('#content table a[title]').tooltip({
			'track': true,
			'create': function(ev, ui) {
				title = $(ev.target).attr('title').replace('/[\r\n]+/', '<br />');
				$(ev.target).attr('title', title);
			}
		});

		<?php echo $this->Navigation->ContextMenue('hasmenu',$SubMenueArray);?>
});
</script>
<?php echo $this->JqueryScripte->LeftMenueHeight(); ?>
