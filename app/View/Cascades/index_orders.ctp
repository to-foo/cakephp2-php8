<div class="quicksearch">
<?php echo $this->Navigation->quickReportSearching('quickreportsearch',1,__('Pr-Nr. (YYYY-NN)', true)); ?>
<?php echo $this->element('barcode_search');?>
</div>
<div class="modalarea">
	<h2><?php echo $Cascade['current']['Cascade']['discription']; ?></h2>
	<div class="reportnumbers index inhalt">
	<table cellpadding="0" cellspacing="0">
	<tr>
			<th><?php echo $this->Paginator->sort('discription'); ?></th>
			<th><?php echo __('Discription', true); ?></th>
			<th><?php echo $this->Paginator->sort('status'); ?></th>
	</tr>

	<?php
	$activ_deactiv = array(0 => __('deactive'),1 => __('active'));
	foreach ($Cascade['child'] as $_cascade):
	?>

	<tr>
		<td>
		<span class="for_hasmenu1 weldhead">
		<?php
		$this->request->projectvars['VarsArray'][1]	= $_cascade['Cascade']['id'];
		echo $this->Html->link($_cascade['Cascade']['discription'],
					array_merge(array('controller' => 'cascades', 'action' => 'index'),$this->request->projectvars['VarsArray']),
					array('class' => 'ajax round icon_edit mymodal hasmenu1','rev' => implode('/',$this->request->projectvars['VarsArray'])));
		?>
        </span>

        &nbsp;</td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Discription'); ?>:
		</span>
		<?php echo h($_cascade['Cascade']['discription']); ?>
        &nbsp;</td>
		<td>
        <span class="discription_mobil">
		<?php echo __('Status'); ?>:
		</span>
		<?php echo $activ_deactiv [$_cascade['Cascade']['status']]; ?>
        &nbsp;</td>
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
</div>
<?php if(isset($reportnumberID) && $reportnumberID > 0) echo $this->JqueryScripte->RefreshAfterDialog(0,$evalutionID,$FormName);?>

<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_paging');
?>
