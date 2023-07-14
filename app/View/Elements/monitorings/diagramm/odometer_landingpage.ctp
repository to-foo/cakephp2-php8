<?php
if(!isset($this->request->data['odometer'])) return;
if(count($this->request->data['odometer']) == 0) return;
?>
<div class="odo_start single_diagramm">
<?php echo '<img id="' . $this->request->data['odometer']['Kessel_Betriebsdruck']['id'] . '" class="" src="data:image/png;base64, ' . $this->request->data['odometer']['Kessel_Betriebsdruck']['diagramm'] . ' " />';?>
</div>
