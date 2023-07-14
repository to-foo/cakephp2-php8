<?php if(isset($Message)) echo $Message;?>
<ul class="expediting_legend_tooltip">
<?php 
if(count($Data) > 0){
	foreach($Data as $_key => $_Data){
		echo '<li class="plan">';
		echo '<span></span>';
		echo $_Data['Supplierfile']['basename'];
		if($_Data['Supplierfile']['file_exists'] == false) echo ' (' . __('file not found',true) .')';
		echo '</li>';
	}
} else {
	echo '<li class="plan">';
	echo __('No documents available.',true);
	echo '</li>';
}
?>
<div class="clear"></div>
</ul>