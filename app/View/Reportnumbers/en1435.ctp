<div class="modalarea">
<h2><?php echo __('Aufnahmeanordnungen nach EN 1435'); ?></h2>
<table summary="Aufnahmeanordnung EN 1435">
<tbody>
<?php
foreach($En1435 as $_En1435){
	echo'<tr>';
	echo'<td>';
	echo'<img src="img/'.$_En1435['bild'].'" />';
	echo'<p><strong>Bild '.$_En1435['nummer'].'</strong> '.$_En1435['erklaerung'].'</p>';
	echo'</td>';
	echo'</tr>';
}
?>
</tbody>
</table>
</div>
<div class="clear" id="mytest"></div>
<?php
echo $this->element('js/close_modal');
echo $this->element('js/minimize_modal');
echo $this->element('js/maximize_modal');
?>
