<?php
App::import('Vendor', 'jpgraph/jpgraph');
App::import('Vendor', 'jpgraph/jpgraph_line');

$ydata = array(5,11,10);

// Create the graph.
$graph = new Graph(350,250);
$graph->SetScale("textlin");
$graph->img->SetMargin(30,90,40,50);
$graph->xaxis->SetFont(FF_FONT1,FS_BOLD);
$graph->title->Set("Example 1.1 same y-values");

// Create the linear plot
$lineplot=new LinePlot($ydata);
$lineplot->SetLegend("Test");
$lineplot->SetColor("blue");
$lineplot->SetWeight(5);

// Add the plot to the graph
$graph->Add($lineplot);

if(isset($return) && $return === true) {
	$data = $graph->Stroke( _IMG_HANDLER );
	ob_start();
	imagepng($data);
	$data = base64_encode(ob_get_contents());
	ob_end_clean();
	echo $data;
} else {
	// Display the graph
	$graph->Stroke();
}
?>
