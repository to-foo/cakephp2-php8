<?php if(isset($Message)) echo $Message;?>
<ul class="expediting_legend_tooltip">
<?php 
foreach($Expeditings as $_key => $_Expeditings){
	echo '<li class="' . $_Expeditings['Expediting']['class'] . '">';
	echo '<span></span>';
	echo $_Expeditings['Expediting']['description'];
	echo '</li>';
}
?>
<div class="clear"></div>
</ul>