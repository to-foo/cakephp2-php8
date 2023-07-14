<?php
if(!isset($this->request->data['odometer'])) return;
if(count($this->request->data['odometer']) == 0) return;
?>
<div class="odo_start single_diagramm">
<?php

foreach ($this->request->data['odometer'] as $key => $value) {

  if(!isset($value['diagramm'])) continue;

  echo '<img id="' . $value['id'] . '" class="" src="data:image/png;base64, ' . $value['diagramm']  . ' " />';

}
?>
</div>
