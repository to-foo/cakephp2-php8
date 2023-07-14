<?php
foreach($xml->section->item as $_key => $_xml){
if(trim($_xml->condition->key) != 'enabled') continue;
$class= null;
if(!empty($_xml->class)) $class = trim($_xml->class);
echo '<td class="'.$class.'">';
echo '<span class="discription_mobil">';
echo trim($_xml->description->$locale);
echo '</span>';
echo h((trim($_examiner[trim($_xml->model)][trim($_xml->key)])));
echo '</td>';
}
?>
