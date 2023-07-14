<?php
	if(isset($this->request->data['RingPlotDiagramm'])){
		echo '<div class="div_plot_container">';

		foreach ($this->request->data['RingPlotDiagramm'] as $key => $value) echo '<div class="normal"><img class="" src="data:image/png;base64, ' . $value  . ' " /></div>';
		echo '</div>';
	}

	if(isset($this->request->data['GanttDiagramm'])) echo '<div class="total"><img class="" src="data:image/png;base64, ' . $this->request->data['GanttDiagramm'] . ' " /></div>';
	if(isset($this->request->data['LineDiagramm'])) echo '<div class="total"><img class="" src="data:image/png;base64, ' . $this->request->data['LineDiagramm'] . ' " /></div>';
?>
