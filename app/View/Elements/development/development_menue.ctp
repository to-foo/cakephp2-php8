<?php
if(Configure::read('DevelopmentsEnabled') == false) return;
if(!isset($progress)) return;
if(count($progress) == 0) return;
?>

<h4 class="listemax"><?php echo __('Testing progress',true);?></h4>
<div class="listemax"><ul>
<?php
//pr($progress['Order']);
$AllByOrder = $progress['Order']['delivery_amount'] * $progress['Order']['count_welds'];
$AdditionalPoints = $progress['Order']['Development']['progress']['all'] - $AllByOrder;
$InitialPoints = $progress['Order']['delivery_amount'] * $progress['Order']['count_welds'];
$DeletedPoints = $InitialPoints - $progress['Order']['Development']['progress']['inital'];
if($AdditionalPoints < 0) $AdditionalPoints = 0;
$PointsProcessed = $progress['Order']['Development']['progress']['ok'] . ' ' . __('of',true) . ' ' . $progress['Order']['Development']['progress']['all'];
echo '<li>' . __('Measuring points',true) . ' ' .  __('processed',true) . ': ' . $PointsProcessed . '</li>';
echo '<li>'. __('Initial measuring points',true) . ': ' . $InitialPoints . '</li>';
echo '<li>'. __('Deleted initial measuring points',true) . ': ' . $DeletedPoints . '</li>';
echo '<li>'. __('Additional measuring points',true) . ': ' . $progress['Order']['Development']['progress']['additional'] . '</li>';
//echo $progress['Order']['Development']['progress']['prozent_mr'] . '%';
?>
<li><?php echo $this->element('development/development_progress_bar',array('progress_all' => $progress['Order']['Development']['progress']));?></li>
<li>
<?php
echo $this->Html->link(
    $progress['Order']['Development']['links'][0]['discription'],
    array_merge(
      array(
        'controller' => 'developments',
        'action' => $progress['Order']['Development']['links'][0]['action']
      )
    )
    ,
    array(
      'class' => 'modal round'
    )
  );
?>
</li>
</ul>
</div>
