<div class=" modalarea welders index inhalt">
	<h2><?php echo __('Welder Tests'); ?></h2>

<?php echo $this->element('Flash/_messages');?>
<div id="container_table_summary" class="content" >

<table id="" class="table_resizable table_infinite_sroll">
	<tr>
			<th class="small_cell"><?php echo __('Date'); ?></th>

			<?php
			foreach($xml->section->item as $_key => $_xml){
				if(trim($_xml->condition->key) != 'enabled') continue;
				$class = null;
				if(!empty($_xml->class)) $class = trim($_xml->class);
				echo '<th class="'.$class.'">';
				echo trim($_xml->description->$locale);
				echo '</th>';
			}
			?>
        <th class="small_cell"><?php echo __('Dimensions'); ?></th>
        <th class="small_cell"><?php echo __('Material'); ?></th>
	</tr>

<?php

if(!isset($paging)) $paging['tr_marker'] = null;

	$i = 0;

	foreach($Reports as $report):

		$class = null;

		if ($i++ % 2 == 0) {
			$class = ' class="altrow infinite_sroll_item ' . $paging['tr_marker'] . '"';
		}

			$weldertestlink= array($report['Reportnumber']['topproject_id'],$report['Reportnumber']['cascade_id'],$report['Reportnumber']['order_id'],$report['Reportnumber']['report_id'],$report['Reportnumber']['id']);

?>
<tr<?php echo $class;?>>
		<td class="small_cell">
		<span class="for_hasmenu1 weldhead">
		<?php echo $this->Html->link($report['Reportnumber']['date'] ,
			array_merge(array('controller' =>'reportnumbers','action' => 'pdf'),
			$weldertestlink),
			array(
				'class'=>'round icon_show ajax hasmenu1',
				'rev' => implode('/',$weldertestlink)
			)
		); ?>
        </span>
        </td>
        <?php
		foreach($xml->section->item as $_key => $_xml){
			if(trim($_xml->condition->key) != 'enabled') continue;
			$class= null;
			if(!empty($_xml->class)) $class = trim($_xml->class);
			echo '<td class="'.$class.'">';
			echo '<span class="discription_mobil">';
			echo trim($_xml->description->$locale);
			echo '</span>';
			echo h((trim($report[trim($_xml->model)][trim($_xml->key)])));
			echo '</td>';
		}
		?>
        <td class="small_cell">
            <span class="for_hasmenu1 weldhead">
            <?php
                foreach ($report ['ReportVtstEvaluation'] as $kreportev => $reportev) {
                    echo $reportev  ['ReportVtstEvaluation']['dimension']; echo '<br>';

                }



            ?>
            </span>
        </td>
          <td class="small_cell">
            <span class="for_hasmenu1 weldhead">
            <?php
                foreach ($report ['ReportVtstEvaluation'] as $kreportev => $reportev) {
                    echo $reportev  ['ReportVtstEvaluation']['material']; echo '<br>';

                }



            ?>
            </span>
        </td>
</tr>
<?php endforeach; ?>

</tr>
</table>

<script type="text/javascript">
$(document).ready(function(){
});
</script>

<?php
if($this->elementExists('js/welder_test_back_to_welder')){
	echo $this->element('js/welder_test_back_to_welder');
}
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/ajax_paging');


?>

</div>
</div>
