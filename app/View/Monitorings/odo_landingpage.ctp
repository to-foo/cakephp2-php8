<h4><?php echo __('Monitoring',true);?></h4>
<?php
$RefreshUrl = $this->Html->url(array_merge(array('action' => 'json_scheme'),$this->request->projectvars['VarsArray']));
echo $this->Form->input('RefreshUrl',array('type' => 'hidden','value' => $RefreshUrl));
$RefreshTime = 10000;
echo $this->Form->input('RefreshTime',array('type' => 'hidden','value' => $RefreshTime));

echo $this->element('monitorings/diagramm/odometer_landingpage');
echo $this->element('monitorings/js/refresh_diagramm_landingpage');
?>
