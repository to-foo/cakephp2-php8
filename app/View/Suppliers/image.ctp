<?php 
if(!isset($imData)){
echo __('An error has occurred');
    return;
} 
echo '<img class="draggable" src="data:image/jpeg;base64, ' . $imData . ' " />';
?>
