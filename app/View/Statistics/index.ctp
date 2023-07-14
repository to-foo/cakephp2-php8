<div class="modalarea detail">
	<div class="Searchform hidden"></div>
	<h2><?php echo __('Statistics'); ?></h2>
    <?php
    if(isset($hint) && $hint != null){
		echo '<div class="hint"><p>';
		echo $hint;
		echo '</p></div>';
	}
	?>
<?php
if(isset($searchoutput) && is_array($searchoutput) && count($searchoutput) > 0){
	echo '<div class="hint"><p>';
	echo __('Suchkriterien',true) . '<br>';
	foreach($searchoutput as $_key => $_searchoutput){
		echo $_searchoutput['field'] . ': ';
		echo $_searchoutput['value'] . '&nbsp;&nbsp;';
	}
	echo '</p></div>';
}

if(count($testingreports) > 0) {
	if($welds['statistics']['all'] > 0)
	{
		$result = __('Found %s evaluations in %s report(s)',$welds['statistics']['all']['all'],$CountTestreports);
	} else {
		$result = __('There are no matching reports containing evaluations', true);
	}
} else {
	$result = __('There are no reports matching your search', true);
}

echo $this->Html->tag('h3', $result);

?>
<div class="statistics">
	<?php
	// Tabs f�r Statistikgrafiken - "data-"-Attribute werden in AJAX �bergeben
	echo $this->Html->nestedList(array(
		$this->Html->link(__('Data tables', true), '#datatable', array()),
/*
		(
			$showDailyOverview
			? $this->Html->link(__('Welds by days', true), '#overview', array('data-type'=>'overview_day'))
			: $this->Html->link(__('Welds by months', true), '#overview', array('data-type'=>'overview'))
		),
*/		

		$this->Html->link(__('Overview', true), '#overview', array('data-type'=>'overview')),
		$this->Html->link(__('Involved welders', true), '#welders', array('data-type'=>'welders')),
		$this->Html->link(__('Weld errors by frequency', true), '#errors'),
		$this->Html->link(__('Testing reports', true), '#reports')
	));	
	?>
	<div id='datatable'>
		<h3 class="link"><?php echo __('Weld type total', true); ?></h3>
		<table border="0" cellspacing="0" cellpadding="0">

		<?php
			echo $this->Html->tableHeaders(array(__('Type of weld', true), __('Amount', true), __('Percentage', true)));
			$row = array();
			$row[0] = array(__('All welds',true),$welds['statistics']['all']['all'], round(100 * $welds['statistics']['all']['all'] / $welds['statistics']['all']['all'],2) . '%');
			$row[1] = array(__('E-welds',true),$welds['statistics']['all']['e'], round(100 * $welds['statistics']['all']['e'] / $welds['statistics']['all']['all'],2) . '%');
			$row[2] = array(__('NE-welds',true),$welds['statistics']['all']['ne'],round(100 * $welds['statistics']['all']['ne'] / $welds['statistics']['all']['all'],2) . '%');
			$row[3] = array(__('not evaluated',true),$welds['statistics']['all']['-'],round(100 * $welds['statistics']['all']['-'] / $welds['statistics']['all']['all'],2) . '%');

			echo $this->Html->tableCells($row);
		?>
		</table>

		<h3 class="link"><?php echo __('Overview', true); ?></h3>
		<table border="0" cellspacing="0" cellpadding="0">
		<?php
			$weldsoutput = array();
			echo $this->Html->tableHeaders(array(__('Date', true), __('All welds', true), __('E-welds', true), __('NE-welds', true), __('not evaluated', true)));
			if(isset($welds['statistics'][$diagrammdata]) && count($welds['statistics'][$diagrammdata]) > 0){
				foreach($welds['statistics'][$diagrammdata] as $_key => $_month) {
					$date = explode('.',$_key);

					$month = null;
					$year = null;
					$day = null;
					$tabledate = null;
				
					if(isset($date[0])) $year = $date[0];
					if(isset($date[1])) $month = $date[1];
					if(isset($date[2])) $day = $date[2] . '. ';

					if($day != null) 			$tabledate .= $day;
					if(isset($months[$month]))	$tabledate .= $months[$month];
					if($year != null) 			$tabledate .= ' ' . $year;
					
					$row = array($tabledate, $_month['all'], $_month['e'], $_month['ne'], $_month['-']);
					
					echo $this->Html->tableCells($row);
				}
			}
			
		?>
		</table>

		<h3 class="link"><?php echo __('Involved welders', true); ?></h3>
<?php
?>
		<table border="0" cellspacing="0" cellpadding="0">
		<?php
			echo $this->Html->tableHeaders(array(__('Welders', true), __('All welds', true), __('E-welds', true), __('NE-welds', true), __('not evaluated', true), __('Percentage', true)));

			foreach($welders as $_key => $_welders) {
				$row = array($_key,$_welders['all'],$_welders['e'],$_welders['ne'],$_welders['-'], @round(100 * $_welders['e'] / ($_welders['all'] - $_welders['-']),2) . '%');
				echo $this->Html->tableCells($row);
			}
		?>
		</table>

		<h3 class="link"><?php echo __('Weld errors by frequency', true); ?></h3>
        
		<table border="0" cellspacing="0" cellpadding="0">
		<?php 
			// Auswertungsbereiche in Tabelle spaltenweise anzeigen
			// Anzahl Spalten in der Ausgabetabelle
			$numColumns = 1;

			// Gesamtanzahl der N�hte an die erste Position des Arrays verschieben
			$total = 500;

			$headers = array();
			for($i=0; $i<$numColumns; $i++) { $headers = array_merge($headers, array(__('Type of error', true), __('Amount (all Welds)', true), __('Amount (Ne-Welds)', true))); }
			echo $this->Html->tableHeaders($headers);

			// Auswertungsbereiche spaltenweise in die Tabellenzeile eintragen
			foreach($welderrors['all'] as $_key => $_welderrors){
				
				$row = array();
				if(isset($_welderrors['code']) && isset($_welderrors['value'])){
					array_push($row, $_welderrors['code']);
					array_push($row, $_welderrors['value']);
					array_push($row, isset($welderrors['ne'][$_key]) ? $welderrors['ne'][$_key]['value'] : 0);
				}	

				echo $this->Html->tableCells($row);
			}
		?>
		</table>
	</div>
	<div id="overview"><center><img src="img/indicator.gif" /></center></div>
	<div id="welders"><center><img src="img/indicator.gif" /></center></div>
	<div id="errors"><center><img src="img/indicator.gif" /></center></div>
	<div id="reports"><center><img src="img/indicator.gif" /></center></div>
</div>

<div class="clear" id="testdiv"></div>
</div>
<div class="clear" id="testdiv"><?php  //var_dump(Router::url(array_merge(array('action'=>'diagram'), $VarsArray))); ?></div>
<script type="text/javascript">
	$(document).ready(function(){

		$data = {
			"ajax_true": 1,
			"extra": {
				"width": Math.max(600, 1*$('#datatable').width()),
				"height": Math.min(800, Math.max(500, 1*$('#datatable').height()))
			}
		}

		$('#overview').load(
			"<?php echo Router::url(array_merge(array('action'=>'diagram'), $VarsArray)); ?>",
			$.extend(true, {"type": "overview"}, $data)
		);

		$('#welders').load(
			"<?php echo Router::url(array_merge(array('action'=>'diagram'), $VarsArray)); ?>",
			$.extend(true, {"type": "welders"}, $data)
		);

		$('#reports').load(
			"<?php echo Router::url(array_merge(array('action'=>'diagram'), $VarsArray)); ?>",
			$.extend(true, {"type": "reports"}, $data)
		);

		$('#errors').load(
			"<?php echo Router::url(array_merge(array('action'=>'errors'), $VarsArray)); ?>",
			$.extend(true, {}, $data)
		);

		$(".statistics").tabs();

		<?php

		if($welds['statistics']['all']['all'] == 0):
			echo '$(".statistics").tabs( "disable" ).hide().find("li, a").off().unbind().on("click", function() { return false;});';
		endif;

		if(isset($welderrors['all']) && count($welderrors['all']) == 0):
			echo '$(".statistics").tabs( "disable", 3 );';
		endif;
		?>
	});
</script>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>