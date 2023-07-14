<?php
//pr($logs);
//pr($settings['settings']->ReportHtEvaluation);
?>
<div class="modalarea">
<div>
	<h2><?php echo __('History', true); ?></h2>

	<table cellpadding="0" cellspacing="0">
	<tr>
		<th><?php echo __('Date/Time', true);?></th>
		<th><?php echo __('Editor', true);?></th>
<!--		<th><?php echo __('Action', true);?></th>-->
		<th><?php echo __('Details', true);?></th>
	</tr>
	<?php
	$i = 0;
	foreach($logs as $_key => $_logs):
		$class = null;
		if ($i++ % 2 == 0) {
			$class = ' altrow';
		}
	?>
	<tr class="<?php echo $class;?>">
    <td><?php echo $_logs['Log']['created']?></td>
    <td><?php echo $_logs['User']['name']?></td>
<!--    <td><?php echo $_logs['Log']['action']?></td>-->
    <td><?php echo $_logs['Log']['message_formatiert']?></td>
	</tr>
	<?php endforeach; ?>
	</table>

</div>
<div class="clear" id="testdiv">
</div>
<?php
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
