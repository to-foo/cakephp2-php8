<?php //var_dump(Security::hash('', null, true)); ?>

<div class="quicksearch">
<?php echo $this->element('searching/search_quick_project',array('action' => 'quicksearch','minLength' => 1,'discription' => __('Projektname', true)));?>
<?php echo $this->element('searching/search_quick_order',array('action' => 'quickreportsearch','minLength' => 2,'discription' => __('Order', true)));?>
<?php echo $this->element('searching/search_quick_reportnumber',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
</div>
<div class="users index inhalt">
	<h2><?php echo __('Projects'); ?></h2>
	<table cellpadding="0" cellspacing="0">
	<tr>
<th><?php echo $this->Paginator->sort('projektname'); ?></th>
<th><?php echo __('Project discription'); ?></th>
	</tr>
	<?php
	$i = 0;
	foreach ($topprojects as $topproject):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' class="altrow"';
		}
	?>
	<tr<?php echo $class;?>>
		<td>
        <span class="for_hasmenu1">
		<?php
		echo $this->Html->link(($topproject['Topproject']['projektname']),array_merge(array(
							'controller' => $topproject['Cascade']['link']['controller'],
							'action' => $topproject['Cascade']['link']['action'],
							), $topproject['Cascade']['link']['term']
						),
						array(
							'class'=>'round ajax hasmenu1',
							'title' => __('Open this Project'),
							'rev' => $topproject['Topproject']['id']
						)
					);

		?>
		</span>
		</td>
		<td><?php echo h($topproject['Topproject']['projektbeschreibung']); ?>&nbsp;</td>
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
<div class="change_subdevisions" id="change_subdevisions"></div>
<script type="text/javascript">
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
		<?php echo $this->Navigation->ContextMenue('hasmenu1',$SubMenueArray);?>
});
</script>
<?php
echo $this->element('js/ajax_breadcrumb_link');
echo $this->element('js/ajax_link');
echo $this->element('js/ajax_modal_link');
echo $this->element('js/ajax_paging');
?>
