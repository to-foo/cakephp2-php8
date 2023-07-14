<?php
echo '<table>';
echo $this->Html->tableHeaders(array(
							__('Name',true),
							__('Datum',true),
							__('Eingang',true),
							__('Ausgang',true),
							__('Zeitraum',true),
						)
					);

foreach($data as $_key => $_data){
	foreach($_data as $__key => $__data){
		 echo $this->Html->tableCells(array(array($_key,$__key,@$__data['Herein']['time'],@$__data['Hinaus']['time'],$__data['Zeitraum'])));
	}
}
echo '</table>';
?>