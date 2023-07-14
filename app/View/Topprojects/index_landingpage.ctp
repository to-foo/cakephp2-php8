<h3><?php echo __('Projects NDT',true);?></h3>
<div class="quicksearch">
<?php
	echo $this->Html->link(__('Open projects',true),
		array(
			'controller' => 'topprojects',
			'action' => 'index'
		),
		array(
			'class' => 'round open_project',
			'title' => __('Open projects',true)
		)
	);
?>
<br>
<?php echo $this->element('searching/search_quick_project',array('action' => 'quicksearch','minLength' => 1,'discription' => __('Projektname', true)));?>
<?php echo $this->element('searching/search_quick_order',array('action' => 'quickreportsearch','minLength' => 2,'discription' => __('Order', true)));?>
<?php echo $this->element('searching/search_quick_reportnumber',array('action' => 'quickreportsearch','minLength' => 1,'discription' => __('Pr-Nr. (YYYY-NN)')));?>
<?php echo $this->element('barcode_search');?>
</div>
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
<div class="users index inhalt">
	<table cellpadding="0" cellspacing="0" class="advancetool">
	<tr>
		<th><?php echo __('Projektname',true); ?></th>
		<th></th>
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
								),$topproject['Cascade']['link']['term']
							),
							array(
								'class'=>'open_project',
								'title' => __('Open this Project'),
								'rev' => $topproject['Topproject']['id']
							)
						);
				?>
			</span>
		</td>
		<td>
			<span class="for_hasmenu1">
			<?php
			echo $this->Html->link(($topproject['Topproject']['projektname']),array_merge(array(
								'controller' => 'topprojects',
								'action' => 'edit',
							),array($topproject['Topproject']['id'])
							),
							array(
								'class'=>'icon icon_edit',
								'title' => __('Edit this Project'),
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
</div>
<div class="clear" id="testdiv"></div>
<div class="change_subdevisions" id="change_subdevisions"></div>
<?php
echo $this->element('js/ajax_link_global',array('name' => 'a.open_project'));
echo $this->element('js/ajax_modal_link_global',array('name' => 'a.icon_edit'));
echo $this->element('landingpage/js/ajax_link_lage_window',array('name' => 'a.addsearching'));
?>
