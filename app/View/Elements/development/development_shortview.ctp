<?php
if(Configure::read('DevelopmentsEnabled') == false) return;
if(!isset($order['Development']['progress'])) return;
?>
<div class="">
<?php
$PointsProcessed = $order['Development']['progress']['ok'] . ' ' . __('of',true) . ' ' . $order['Development']['progress']['all'];
echo __('Measuring points',true) . ' ' .  __('processed',true) . ': ' . $PointsProcessed ;
echo $this->element('development/development_progress_bar',array('progress_all' => $order['Development']['progress']));?>
</div>
