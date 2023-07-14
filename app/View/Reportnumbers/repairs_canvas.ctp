<div class="modalarea detail">
<h2><?php echo h(__('Weld repair tracking')); ?></h2>
<div><canvas height="400" width="800"  id="FirstCanva"></canvas></div>    
<script type="text/javascript">
$(document).ready(function(){

var canvas = new fabric.StaticCanvas('FirstCanva');
var scale = 1;
var objects = new Array();

var standardcolor = "#425f7a";
var standardangle= 180;

var fontSize = 16;

function setSVGImage(canvas,path,left,top,scale){
	fabric.loadSVGFromURL(path, function(objects, options) {
		var obj = fabric.util.groupSVGElements(objects, options);
		obj.set({ left: left, top: top, angle: 0 }).scale(scale);
		canvas.add(obj);
	});
}

function setArrowLine(standardcolor,standardangle){
	var triangle = new fabric.Triangle({
		width: 10, 
		height: 15, 
		fill: standardcolor, 
		left: 106, 
		top: 205,
		angle: standardangle
	});

	var line = new fabric.Line([45, 0, 45, 195], {
		left: 100,
		top: 10,
		stroke: standardcolor
	});

		var objs = [line, triangle];
		var alltogetherObj = new fabric.Group(objs);
		canvas.add(alltogetherObj);
}

function setRepairText(text,left,top,width,fontSize){
	var textbox = new fabric.Textbox(text, {
		left: left,
		top: top,
		width: width,
		fontSize: fontSize
	});

	canvas.add(textbox);
};

	
setSVGImage(canvas,'../svg/fabric/weld_error.svg',20,10,scale);	
setSVGImage(canvas,'../svg/fabric/weld_not_okay.svg',20,80,scale);	
setSVGImage(canvas,'../svg/fabric/weld_okay.svg',20,150,scale);

setArrowLine(standardcolor,standardangle);	

setRepairText("Testing report 2019-1550, Weld N1 ne (2010) 15-22",110,30,300,fontSize);	

});
</script>
<?php echo $this->JQueryScripte->ModalFunctions(); ?>
