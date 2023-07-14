<?php
$color = '37a500';
if($progress_all['all'] == 0)	$bg_color = 'bbbbbb';
if($progress_all['all'] > 0)	$bg_color = '97df73';
if($progress_all['rep'] > 0)	{$bg_color = 'e6a533';$color = 'c93b20';}
echo '<div class="dev_diagramm" style="margin: 0.5em;background:#'.$bg_color.'">';
echo '<div style="background:#'.$color.'; min-height:1em; width:' . number_format($progress_all['prozent_mr'], 2, '.', ',') . '%"></div>';
echo '</div>';
?>
