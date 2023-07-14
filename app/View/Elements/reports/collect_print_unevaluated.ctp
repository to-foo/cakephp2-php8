<?php
if(!isset($unevaluated)) return;
if(count($unevaluated) == 0) return;

echo '<ul class="hint">';
echo '<li><b>' . __('Unevaled welds',true) . '</b></li>';
foreach ($unevaluated as $key => $value) echo '<li>' . $value . '</li>';
echo '</ul>';
?>
