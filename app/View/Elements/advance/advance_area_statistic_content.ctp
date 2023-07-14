<?php
echo '<div id="advance_diagrammcontent" class="hint advance_content advance_diagrammcontent">';

	if(isset($this->request->data['GanttDiagramm'])) echo '<div class="total"><img class="" src="data:image/png;base64, ' . $this->request->data['GanttDiagramm'] . ' " /></div>';
	if(isset($this->request->data['LineDiagramm'])) echo '<div class="total"><img class="" src="data:image/png;base64, ' . $this->request->data['LineDiagramm'] . ' " /></div>';

echo '</div>';
?>
