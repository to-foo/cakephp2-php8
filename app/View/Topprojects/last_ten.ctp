<h4><?php echo __('Last projects',true);?></h4>
<div class="users index inhalt">
	<table cellpadding="0" cellspacing="0" class="advancetool">
	<?php
	$i = 0;
	foreach ($LastProjects as $topproject):
	?>
	<tr>
		<td>
		<?php
		echo $this->Html->link(($topproject['projektname']),array_merge(array(
							'controller' => $topproject['link']['controller'],
							'action' => $topproject['link']['action'],
							),$topproject['link']['term']
						),
						array(
							'class'=>' ajax',
							'title' => __('Open this Project'),
						)
					);

		?>
		</td>
	</tr>
<?php endforeach; ?>
	</table>
</div>
<?php
echo $this->element('js/ajax_link');
?>
