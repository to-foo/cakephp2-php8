<div class="modalarea">
<h2><?php echo __('Measure points');?></h2>
<div>
<table cellpadding="0" cellspacing="0">
<tr>
<th><?php echo __('Testing method', true); ?></th>
<th><?php echo __('Discription', true); ?></th>
<th><?php echo __('Dimensions', true); ?></th>
<th><?php echo __('Filmposition', true); ?></th>
<th><?php echo __('Last modified', true); ?></th>
<th><?php echo __('Result', true); ?></th>
<th>&nbsp;</th>
</tr>
<?php
$i = 0;

foreach($WeldArray as $_key => $_WeldArray){
	foreach($_WeldArray as $__key => $__WeldArray){
		foreach($__WeldArray as $___key => $___WeldArray){
			foreach($___WeldArray as $____key => $____WeldArray){
				
				$class = null;
				
				if ($i++ % 2 == 0) {
					$class = ' class="altrow"';
				}

				if (isset($____WeldArray['result_e']) && $____WeldArray['result_e'] == 1) {
					$class = ' class="settled"';
				}

				if (isset($____WeldArray['result_ne']) && $____WeldArray['result_ne'] == 1) {
					$class = ' class="ne"';
				}
				
				echo '<tr '.$class.'>';
				echo '<td>'.$_key.'</td>';
				echo '<td>'.@$____WeldArray['description'].'</td>';
				echo '<td>'.@$____WeldArray['dimension'].'</td>';
				echo '<td>'.@$____WeldArray['film_position'].'</td>';
				echo '<td>'.@$____WeldArray['modified'].'</td>';
				
				// auf die Schnelle
				$resultArray = array(0 => '-', 1 => 'e', 2 => 'ne');
				
				echo '<td>'.@$resultArray[$____WeldArray['result']];
				echo '<td class="actions">';
				
				$this->request->projectvars['VarsArray'][4] = $____WeldArray['reportnumber_id'];
				$this->request->projectvars['VarsArray'][5] = $____WeldArray['id'];

//				echo $this->Navigation->makeLink('reportnumbers','deleteevalution',__('Delete'),'icon icon_delete ajax',null,$this->request->projectvars['VarsArray']);
				echo $this->Navigation->makeLink('reportnumbers','duplicatevalution',__('Duplicat'),'icon icon_duplicate ajax',null,$this->request->projectvars['VarsArray']);
				echo $this->Navigation->makeLink('reportnumbers','editevalution',__('Edit'),'icon icon_edit ajax',null,$this->request->projectvars['VarsArray']);
						
				echo '</td>';
				echo '</tr>';
			}
		}
	}
}
?>
</table>
</div>
<div class="clear" id="testdiv">
</div>

<?php echo $this->JqueryScripte->ModalFunctions(); ?>