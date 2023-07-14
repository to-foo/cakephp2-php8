<?php
if(!isset($this->request->data['lineplot'])) return;
if(count($this->request->data['lineplot']) == 0) return;

?>
<div id="MonitoringContainer" class="div_plot_container">
<?php
//echo '<div class="normal"><img id="' . $odo['leistung']['id'] . '" class="" src="data:image/png;base64, ' . $odo['leistung']['diagramm']  . ' " /></div>';

foreach ($this->request->data['lineplot'] as $key => $value) {

  if(!isset($value['diagramm'])) continue;

  echo '<div class="normal"><img id="' . $value['id'] . '" class="" src="data:image/png;base64, ' . $value['diagramm']  . ' " /></div>';
}

?>
</div>
