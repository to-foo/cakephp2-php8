<?php
echo '<div id="advance_diagrammcontent" class="hint advance_content advance_diagrammcontent">';
	if(isset($this->request->data['GanttDiagrammStart'])) echo '<div class="total"><img class="" src="data:image/png;base64, ' . $this->request->data['GanttDiagrammStart'] . ' " /></div>';

echo '</div>';
?>
